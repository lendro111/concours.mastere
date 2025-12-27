<?php

namespace App\Http\Controllers;

use App\Models\Specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SpecialitesController extends Controller
{
    public function index(){

        return view('specialites.index', [

            "specialites" => Specialite::query()->where("supprimer", "0")->orderBy("libelleSpecialite", "asc")->get(),
            "titre" => mb_strtoupper("Specialites")

        ]);

    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'libelleSpecialite' => ['required', 'string', 'unique:specialites,codeSpecialite'],
            'codeSpecialite' => ['required', 'string', 'unique:specialites,codeSpecialite'],
        ], [
            "libelleSpecialite.required" => "Le libellé de la spécialité est obligatoire",
            "libelleSpecialite.string" => "Le libellé de la spécialité doit être de type chaîne de caractère",
            "libelleSpecialite.unique" => "Le libellé de la spécialité doit être unique",
            "codeSpecialite.required" => "Le Code de la spécialité est obligatoire",
            "codeSpecialite.string" => "Le Code de la spécialité doit être de type chaîne de caractère",
            "codeSpecialite.unique" => "Le Code de la spécialité doit être unique",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataSpecialite = [

            "libelleSpecialite" => mb_strtoupper($data['libelleSpecialite']),
            "codeSpecialite" => mb_strtoupper($data['codeSpecialite']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        Specialite::query()->create($dataSpecialite);

        return response()->json([
            'success' => true,
            'message' => 'Spécialité ajoutée avec succès'
        ]);

    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [
            'idSpecialite' => ['required',],
            'libelleSpecialite' => ['required', 'string'],
            'codeSpecialite' => ['required', 'string'],
        ], [
            "idSpecialite.required" => "L'école est obligatoire",
            "libelleSpecialite.required" => "Le libellé de la spécialité est obligatoire",
            "libelleSpecialite.string" => "Le libellé de la spécialité doit être de type chaîne de caractère",
            "codeSpecialite.required" => "Le Code de de la spécialité est obligatoire",
            "codeSpecialite.string" => "Le Code de la spécialité doit être de type chaîne de caractère",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $specialite = Specialite::query()->findOrFail($data['idSpecialite']);

        if (!is_null($specialite)) {

            if ($specialite->nom_specialite == $data['libelleSpecialite'] && $specialite->codeSpecialite == $data['codeSpecialite'] ) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cette spécialité']
                    ]
                ], 422);

            }

            $dataSpecialite = [

                "libelleSpecialite" => mb_strtoupper($data['libelleSpecialite']),
                "codeSpecialite" => mb_strtoupper($data['codeSpecialite']),
                "userUpdate" => Auth::guard("admin")->id(),

            ];

            $specialite->update($dataSpecialite);

            return response()->json([
                'success' => true,
                'message' => 'Spécialité mise à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucune spécialité trouvée']
            ]
        ], 422);
        

    }

    public function supprimer($id)
    {

        $specialite = Specialite::query()->findOrFail($id);

        $specialite->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Spécialité supprimée avec succès']);

    }

    

}


