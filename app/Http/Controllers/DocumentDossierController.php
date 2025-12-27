<?php

namespace App\Http\Controllers;

use App\Models\DocumentDossier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class DocumentDossierController extends Controller
{
    public function index(){

        return view('documentsdossiers.index', [

            "documentsdossiers" => DocumentDossier::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),
            "titre" => mb_strtoupper("Dossiers")

        ]);

    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'libelle' => ['required', 'string', 'unique:documentsdossiers,libelle'],
            'code' => ['required', 'string', 'unique:documentsdossiers,code'],
            'requis' => ['required', 'numeric', 'unique:documentsdossiers,requis'],
            'statutBachelier' => ['required', 'numeric', 'unique:documentsdossiers,statutBachelier'],
        ], [
            "libelle.required" => "Le libellé du dossier est obligatoire",
            "libelle.string" => "Le libellé du dossier doit être de type chaîne de caractère",
            "libelle.unique" => "Le libellé du dossier doit être unique",
            "code.required" => "Le code du dossier est obligatoire",
            "code.string" => "Le code du dossier doit être de type chaîne de caractère",
            "code.unique" => "Le code du dossier doit être unique",
            "requis.required" => "Le document requis du dossier est obligatoire",
            "requis.numeric" => "Le document requis du dossier doit être de type numérique",
            "requis.unique" => "Le document requis du dossier doit être unique",
            "statutBachelier.required" => "Le statut bachelier du dossier est obligatoire",
            "statutBachelier.numeric" => "Le statut bachelier du dossier doit être de type numérique",
            "statutBachelier.unique" => "Le statut bachelier du dossier doit être unique",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataDocumentDossiers = [

            "libelle" => mb_strtoupper($data['libelle']),
            "code" => mb_strtoupper($data['code']),
            "requis" => mb_strtoupper($data['requis']),
            "statutBachelier" => mb_strtoupper($data['statutBachelier']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        DocumentDossier::query()->create($dataDocumentDossiers);

        return response()->json([
            'success' => true,
            'message' => 'Dossier ajouté avec succès'
        ]);

    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [
            'idDocumentDossier' => ['required',],
            'libelle' => ['required', 'string'],
            'code' => ['required', 'string'],
            'requis' => ['required', 'numeric'],
            'statutBachelier' => ['required', 'numeric'],
        ], [
            "idDocumentDossier.required" => "Le dossier est obligatoire",
            "libelle.required" => "Le libellé du dossier est obligatoire",
            "libelle.string" => "Le libellé du dossier doit être de type chaîne de caractère",            
            "code.required" => "Le code du dossier est obligatoire",
            "code.string" => "Le code du dossier doit être de type chaîne de caractère",            
            "requis.required" => "Le document requis du dossier est obligatoire",
            "requis.numeric" => "Le document requis du dossier doit être de type numérique",            
            "statutBachelier.required" => "Le statut bachelier du dossier est obligatoire",
            "statutBachelier.numeric" => "Le statut bachelier du dossier doit être de type numérique",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $documentsdossiers = DocumentDossier::query()->findOrFail($data['idDocumentDossier']);

        if (!is_null($documentsdossiers)) {

            if ($documentsdossiers->nom_ecole == $data['libelle'] 
               && $documentsdossiers->code == $data['code'] 
               && $documentsdossiers->requis == $data['requis']
               && $documentsdossiers->statutBachelier == $data['statutBachelier']) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cet Dossier']
                    ]
                ], 422);

            }

            $dataDocumentDossiers = [

                "libelle" => mb_strtoupper($data['libelle']),
                "code" => mb_strtoupper($data['code']),
                "requis" => mb_strtoupper($data['requis']),
                "statutBachelier" => mb_strtoupper($data['statutBachelier']),
                "userUpdate" => Auth::guard("admin")->id(),

            ];

            $documentsdossiers->update($dataDocumentDossiers);

            return response()->json([
                'success' => true,
                'message' => 'Dossier mis à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucune Dossier trouvée']
            ]
        ], 422);
        

    }

    public function supprimer($id)
    {

        $documentsdossiers = DocumentDossier::query()->findOrFail($id);

        $documentsdossiers->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Dossier supprimé avec succès']);

    }


}

