<?php

namespace App\Http\Controllers;

use App\Models\EtapesConcours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EtapesConcoursController extends Controller
{

    public function index(){

        return view('etapesconcours.index', [

            "etapes" => EtapesConcours::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),
            "titre" => mb_strtoupper("Etapes du concours"),

        ]);

    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'libelleEtape' => ['required', 'string', 'unique:etapesconcours,libelle'],
            'codeEtape' => ['required', 'string', 'unique:etapesconcours,code'],
        ], [
            "libelleEtape.required" => "Le libellé de l'étape du concours est obligatoire",
            "libelleEtape.string" => "Le libellé l'étape du concours doit être de type chaîne de caractère",
            "libelleEtape.unique" => "Le libellé de l'étape du concours doit être unique",
            "codeEtape.required" => "Le Code de l'étape du concours est obligatoire",
            "codeEtape.string" => "Le Code de l'étape du concours doit être de type chaîne de caractère",
            "codeEtape.unique" => "Le Code de l'étape du concours doit être unique",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataEtapes = [

            "libelle" => mb_strtoupper($data['libelleEtape']),
            "code" => mb_strtoupper($data['codeEtape']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        EtapesConcours::query()->create($dataEtapes);

        return response()->json([
            'success' => true,
            'message' => 'Etapes du concours ajoutée avec succès'
        ]);


    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [
            'idEtape' => ['required'],
            'libelleEtape' => ['required', 'string'],
            'codeEtape' => ['required', 'string'],
        ], [
            "libelleEtape.required" => "Le libellé de l'étape du concours est obligatoire",
            "libelleEtape.string" => "Le libellé l'étape du concours doit être de type chaîne de caractère",
            "codeEtape.required" => "Le Code de l'étape du concours est obligatoire",
            "codeEtape.string" => "Le Code de l'étape du concours doit être de type chaîne de caractère",
            "idEtape.required" => "L'étape du concours est obligatoire",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $etape = EtapesConcours::query()->findOrFail($data['idEtape']);

        if (!is_null($etape)) {

            if ($etape->libelle == $data['libelleEtape'] && $etape->code == $data['codeEtape'] ) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cette école']
                    ]
                ], 422);

            }

            $dataEtapes = [

                "libelle" => mb_strtoupper($data['libelleEtape']),
                "code" => mb_strtoupper($data['codeEtape']),
                "userUpdate" => Auth::guard("admin")->id(),

            ];

            $etape->update($dataEtapes);

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

    public function supprimer($id)
    {

        $etape = EtapesConcours::query()->findOrFail($id);

        $etape->delete();

        return response()->json(['message' => 'Etape concours supprimée avec succès']);

    }

}
