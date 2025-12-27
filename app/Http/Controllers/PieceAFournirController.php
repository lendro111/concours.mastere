<?php

namespace App\Http\Controllers;

use App\Models\DocumentDossier;
use App\Models\DossierCandidature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PieceAFournirController extends Controller
{

    public function index(){

        return view('listespiecesafournir.index', [

            "pieces" => DocumentDossier::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),
            "titre" => mb_strtoupper("Pièces à fournir"),

        ]);

    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'libellePiece' => ['required', 'string', 'unique:documentsdossiers,libelle'],
            'codePiece' => ['required', 'string', 'unique:documentsdossiers,code'],
        ], [
            "libellePiece.required" => "Le libellé de la pièce à fournir est obligatoire",
            "libellePiece.string" => "Le libelle de la pièce à fournir doit être de type chaîne de caractère",
            "libellePiece.unique" => "Le libellé de la pièce à fournir doit être unique",
            "codePiece.required" => "Le Code de la pièce à fournir est obligatoire",
            "codePiece.string" => "Le Code de la pièce à fournir doit être de type chaîne de caractère",
            "codePiece.unique" => "Le Code de la pièce à fournir doit être unique",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataPieces = [

            "libelle" => mb_strtoupper($data['libellePiece']),
            "code" => mb_strtoupper($data['codePiece']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        DocumentDossier::query()->create($dataPieces);

        return response()->json([
            'success' => true,
            'message' => 'Pièce à fournir ajoutée avec succès'
        ]);


    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [
            'idPiece' => ['required'],
            'libellePiece' => ['required', 'string'],
            'codePiece' => ['required', 'string'],
        ], [
            "libellePiece.required" => "Le libellé de la pièce à fournir est obligatoire",
            "libellePiece.string" => "Le libelle de la pièce à fournir doit être de type chaîne de caractère",
            "codePiece.required" => "Le Code de la pièce à fournir est obligatoire",
            "codePiece.string" => "Le Code de la pièce à fournir doit être de type chaîne de caractère",
            "idPiece.required" => "La pièce à fournir est obligatoire",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $piece = DocumentDossier::query()->findOrFail($data['idPiece']);

        if (!is_null($piece)) {

            if ($piece->libelle == $data['libellePiece'] && $piece->code == $data['codePiece'] ) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cette pièce']
                    ]
                ], 422);

            }

            $dataEtapes = [

                "libelle" => mb_strtoupper($data['libellePiece']),
                "code" => mb_strtoupper($data['codePiece']),
                "userUpdate" => Auth::guard("admin")->id(),

            ];

            $piece->update($dataEtapes);

            return response()->json([
                'success' => true,
                'message' => 'Etape concours mise à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucune Etape concours trouvée']
            ]
        ], 422);

    }

    public function nonrequis(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'pieces_id' => ['required', 'array'],
        ], [
            "pieces_id.required" => "Veillez sélectionner au moins une pièces",
            "pieces_id.array" => "Les pièces sélectionnés doivent être un tableau",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        foreach ($data['pieces_id'] as $value) {

            $piece = DossierCandidature::query()->findOrFail($value);

            if (!is_null($piece)) {

                $piece->update([

                    "requis" => 0,
                    "userUpdate" => Auth::guard("admin")->id(),

                ]);

            }

        }

        return response()->json([
            'success' => true,
            'message' => 'Document(s) marqué comme non requis effectué avec succès'
        ]);

    }


    public function listePieces($idSession)
    {
        $documents = DocumentDossier::listeDocumentsRequisParSession($idSession);

        return response()->json($documents);
    }


    public function supprimer($id)
    {

        $piece = DocumentDossier::query()->findOrFail($id);

        $piece->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Pièce supprimée avec succès']);

    }

}
