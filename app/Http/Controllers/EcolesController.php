<?php

namespace App\Http\Controllers;

use App\Models\Ecole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EcolesController extends Controller
{

    public function index(){

        return view('ecoles.index', [

            "ecoles" => Ecole::query()->where("supprimer", "0")->orderBy("libelleEcole", "asc")->get(),
            "titre" => mb_strtoupper("Ecoles")

        ]);

    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'libelleEcole' => ['required', 'string', 'unique:ecoles,libelleEcole'],
            'codeEcole' => ['required', 'string', 'unique:ecoles,codeEcole'],
        ], [
            "libelleEcole.required" => "Le libellé de l'école est obligatoire",
            "libelleEcole.string" => "Le libellé l'école doit être de type chaîne de caractère",
            "libelleEcole.unique" => "Le libellé de l'école doit être unique",
            "codeEcole.required" => "Le Code de l'école est obligatoire",
            "codeEcole.string" => "Le Code de l'école doit être de type chaîne de caractère",
            "codeEcole.unique" => "Le Code de l'école doit être unique",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataEcoles = [

            "libelleEcole" => mb_strtoupper($data['libelleEcole']),
            "codeEcole" => mb_strtoupper($data['codeEcole']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        Ecole::query()->create($dataEcoles);

        return response()->json([
            'success' => true,
            'message' => 'Ecole ajoutée avec succès'
        ]);

    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [
            'idEcole' => ['required',],
            'libelleEcole' => ['required', 'string'],
            'codeEcole' => ['required', 'string'],
        ], [
            "idEcole.required" => "L'école est obligatoire",
            "libelleEcole.required" => "Le libellé de l'école est obligatoire",
            "libelleEcole.string" => "Le libellé l'école doit être de type chaîne de caractère",
            "codeEcole.required" => "Le Code de l'école est obligatoire",
            "codeEcole.string" => "Le Code de l'école doit être de type chaîne de caractère",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $ecole = Ecole::query()->findOrFail($data['idEcole']);

        if (!is_null($ecole)) {

            if ($ecole->nom_ecole == $data['libelleEcole'] && $ecole->libelle == $data['codeEcole'] ) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cette école']
                    ]
                ], 422);

            }

            $dataEcoles = [

                "nom_ecole" => mb_strtoupper($data['libelleEcole']),
                "libelle" => mb_strtoupper($data['codeEcole']),
                "userUpdate" => Auth::guard("admin")->id(),

            ];

            $ecole->update($dataEcoles);

            return response()->json([
                'success' => true,
                'message' => 'Ecole mise à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucune Ecole trouvée']
            ]
        ], 422);
        

    }

    public function supprimer($id)
    {

        $ecole = Ecole::query()->findOrFail($id);

        $ecole->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Ecole supprimée avec succès']);

    }

    public function recupererecoles(){

        $ecoles = Ecole::query()->where("supprimer", "=", 0)->orderBy('libelleEcole', 'asc')->get();

        return response()->json($ecoles);

    }

}
