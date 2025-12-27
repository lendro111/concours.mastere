<?php

namespace App\Http\Controllers;

use App\Models\Concours;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FilieresController extends Controller
{

    public function index()
    {

        return view('filieres.index', [

            "filieres" => Filiere::listeFilieres(),
            "concours" => Concours::listeConcours(),
            "titre" => mb_strtoupper("Filières"),

        ]);

    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'concours_id' => ['required'],
            'libelleFiliere' => ['required', 'string', 'unique:filieres,libelleFiliere'],
            'codeFiliere' => ['required', 'string', 'unique:filieres,codeFiliere'],
        ], [
            "libelleFiliere.required" => "Le libellé de la filière est obligatoire",
            "libelleFiliere.string" => "Le libellé de la filiere doit être de type chaîne de caractère",
            "libelleFiliere.unique" => "Le libellé de la filière doit être unique",
            "codeFiliere.required" => "Le Code de la filière est obligatoire",
            "codeFiliere.string" => "Le Code de la filière doit être de type chaîne de caractère",
            "codeFiliere.unique" => "Le Code de la filière doit être unique",
            "concours_id.required" => "Le concours est obligatoire",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataFilieres = [

            "libelleFiliere" => mb_strtoupper($data['libelleFiliere']),
            "codeFiliere" => mb_strtoupper($data['codeFiliere']),
            "concours_id" => $data['concours_id'],
            "userAdd" => Auth::guard("admin")->id(),

        ];

        Filiere::query()->create($dataFilieres);

        return response()->json([
            'success' => true,
            'message' => 'Filière ajoutée avec succès'
        ]);


    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [
            'concours_id' => ['required'],
            'libelleFiliere' => ['required', 'string'],
            'codeFiliere' => ['required', 'string'],
            'idFiliere' => ['required', 'string'],
        ], [
            "libelleFiliere.required" => "Le libellé de la filière est obligatoire",
            "libelleFiliere.string" => "Le libellé de la filiere doit être de type chaîne de caractère",
            "codeFiliere.required" => "Le Code de la filière est obligatoire",
            "codeFiliere.string" => "Le Code de la filière doit être de type chaîne de caractère",
            "concours_id.required" => "Le concours est obligatoire",
            "idFiliere.required" => "La Filière est obligatoire",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $filiere = Filiere::query()->findOrFail($data['idFiliere']);

        $concours = Concours::query()->findOrFail($data['concours_id']);

        if (!is_null($filiere)) {

            if ($filiere->libelleFiliere == $data['libelleFiliere'] && $filiere->codeFiliere == $data['codeFiliere'] && !is_null($concours) && $filiere->concours_id == $data['concours_id']) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cette Filière']
                    ]
                ], 422);

            }

            $dataFilieres = [

                "libelleFiliere" => mb_strtoupper($data['libelleFiliere']),
                "codeFiliere" => mb_strtoupper($data['codeFiliere']),
                "concours_id" => $data['concours_id'],
                "userUpdate" => Auth::guard("admin")->id(),


            ];

            $filiere->update($dataFilieres);

            return response()->json([
                'success' => true,
                'message' => 'Filière mise à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucune Filière trouvée']
            ]
        ], 422);


    }

    public function supprimer($id)
    {

        $filieres = Filiere::query()->findOrFail($id);

        $filieres->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Filière supprimée avec succès']);

    }

}
