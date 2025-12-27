<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EtablissementController extends Controller
{
    public function index()
    {

        return view('etablissements.index', [

            "etablissement" => Etablissement::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),

            "titre" => mb_strtoupper("Etablissements")

        ]);
    }



    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'codeEtablissement' => ['required', 'string', 'unique:etablissement,codeEtablissement'],
            'libelle' => ['required', 'string', 'unique:etablissement,libelle'],
        ], [
            "codeEtablissement.unique" => "Le code de l'établissement doit être unique",
            "codeEtablissement.required" => "Le code de l'établissement est obligatoire",
            "libelle.required" => "Le libellé de l'établissement  est obligatoire",
            "libelle.string" => "Le libellé de l'établissement doit être de type chaîne de caractère",
            "libelle.unique" => "Le libellé de l'établissement doit être unique",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataEtablissements = [

            "codeEtablissement" => mb_strtoupper($data['codeEtablissement']),
            "libelle" => mb_strtoupper($data['libelle']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        Etablissement::query()->create($dataEtablissements);

        return response()->json([
            'success' => true,
            'message' => 'Etablissement ajouté avec succès'
        ]);

    }


    public function modifier(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'idEtablissement' => ['required',],
            'codeEtablissement' => ['required', 'string'],
            'libelle' => ['required', 'string'],
        ], [
            "idEtablissement.required" => "L'établissement est obligatoire",
            "codeEtablissement.string" => "Le code de l'établissement doit être de type chaîne de caractère",
            "codeEtablissement.required" => "Le code de l'établissement est obligatoire",
            "libelle.required" => "Le libellé de l'établissement  est obligatoire",
            "libelle.string" => "Le libellé de l'établissement doit être de type chaîne de caractère",

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $etablissement = Etablissement::query()->findOrFail($data['idEtablissement']);

        if (!is_null($etablissement)) {

            if ($etablissement->nom_etablissement == $data['codeEtablissement'] && $etablissement->libelle == $data['libelle'] ) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cette école']
                    ]
                ], 422);

            }

            $dataEtablissement = [

            "codeEtablissement" => mb_strtoupper($data['codeEtablissement']),
            "libelle" => mb_strtoupper($data['libelle']),
            "userAdd" => Auth::guard("admin")->id(),

            ];

            $etablissement->update($dataEtablissement);

            return response()->json([
                'success' => true,
                'message' => 'Etablissement mise à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucune Etablissement trouvée']
            ]
        ], 422);


    }

    public function supprimer($id)
    {

        $etablissements = Etablissement::query()->findOrFail($id);

        $etablissements->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Etablissement supprimée avec succès']);

    }
}

