<?php

namespace App\Http\Controllers;

use App\Models\Diplome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DiplomesController extends Controller
{
    public function index()
    {

        return view('diplomes.index', [

            "diplome" => Diplome::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),

            "titre" => mb_strtoupper("diplomes")

        ]);
    }



    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'libelle' => ['required', 'string', 'unique:diplomes,libelle'],
        ], [
            "libelle.required" => "Le libellé du diplome est obligatoire",
            "libelle.string" => "Le libellé du diplome doit être de type chaîne de caractère",
            "libelle.unique" => "Le libellé du diplome doit être unique",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $datadiplomes = [

            "libelle" => mb_strtoupper($data['libelle']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        Diplome::query()->create($datadiplomes);

        return response()->json([
            'success' => true,
            'message' => 'Diplome ajouté avec succès'
        ]);

    }


    public function modifier(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'idDiplome' => ['required',],
            'libelle' => ['required', 'string'],
        ], [
            "idDiplome.required" => "Le diplome est obligatoire",
            "libelle.required" => "Le libellé du diplome est obligatoire",
            "libelle.string" => "Le libellé du diplome doit être de type chaîne de caractère",

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $diplome = Diplome::query()->findOrFail($data['idDiplome']);

        if (!is_null($diplome)) {

            if ($diplome->nom_diplome == $data['libelle'] ) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cet diplome']
                    ]
                ], 422);

            }

            $dataDiplomes = [

            "libelle" => mb_strtoupper($data['libelle']),
            "userAdd" => Auth::guard("admin")->id(),

            ];

            $diplome->update($dataDiplomes);

            return response()->json([
                'success' => true,
                'message' => 'Diplome mis à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucun Diplome trouvé']
            ]
        ], 422);


    }

    public function supprimer($id)
    {

        $diplomes = Diplome::query()->findOrFail($id);

        $diplomes->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Diplome supprimée avec succès']);

    }
}

