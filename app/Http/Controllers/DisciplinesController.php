<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\Concours;
use Illuminate\Http\Request;

use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DisciplinesController extends Controller
{
    public function index(){

        return view('disciplines.index', [

        "disciplines" => Discipline::listeDisciplines(),
        "concours" => Concours::listeConcours(),
        "titre" => mb_strtoupper("DISCIPLINES")
    ]);

    }


    public function ajouter(Request $request){

        $validator = Validator::make($request->all(), [
            'libelle' => ['required', 'string', 'unique:disciplines,libelle'],
            'coef' => ['required', 'numeric'],
            'concours_id' => ['required'],
        ], [
            'coef.required' => "Le coefficient de la discipline est obligatoire",
            "libelle.required" => "Le libellé de la discipline est obligatoire",
            "libelle.string" => "Le libellé de la discipline doit être de type chaîne de caractère",
            "libelle.unique" => "Le libellé de la discipline doit être unique",
            'coef.numeric' => "Le coefficient de la discipline doit être de type numérique",
            'concours_id.required' => "Le concours est obligatoire",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataDisciplines = [

            "libelle" => mb_strtoupper($data['libelle']),
            "coef" => mb_strtoupper($data['coef']),
            "concours_id" => $data['concours_id'],
            "userAdd" => Auth::guard("admin")->id(),

        ];

        Discipline::query()->create($dataDisciplines);

        return response()->json([
            'success' => true,
            'message' => 'Disciplines ajoutée avec succès'
        ]);


    }


    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [
            'concours_id' => ['required'],
            'libelle' => ['required', 'string'],
            'coef' => ['required', 'numeric'],
            'idDiscipline' => ['required', 'string'],
        ], [
            'coef.required' => "Le coefficient de la discipline est obligatoire",
            "libelle.required" => "Le libellé de la discipline est obligatoire",
            "libelle.string" => "Le libellé de la discipline doit être de type chaîne de caractère",
            'coef.numeric' => "Le coefficient de la discipline doit être de type numérique",
            'concours_id.required' => "Le concours est obligatoire",
            "idDiscipline.required" => "La discipline est obligatoire",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $discipline = Discipline::query()->findOrFail($data['idDiscipline']);

        $concours = Concours::query()->findOrFail($data['concours_id']);

        if (!is_null($discipline)) {

            if ($discipline->libelle == $data['libelle'] && $discipline->coef == $data['coef'] && !is_null($concours) && $discipline->concours_id == $data['concours_id']) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cette Discipline']
                    ]
                ], 422);

            }

            $dataDisciplines = [

                "libelle" => mb_strtoupper($data['libelle']),
                "coef" => mb_strtoupper($data['coef']),
                "concours_id" => $data['concours_id'],
                "userUpdate" => Auth::guard("admin")->id(),


            ];

            $discipline->update($dataDisciplines);

            return response()->json([
                'success' => true,
                'message' => 'Discipline mise à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucune Discipline trouvée']
            ]
        ], 422);


    }

    public function supprimer($id)
    {

        $disciplines = Discipline::query()->findOrFail($id);

        $disciplines->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Filière supprimée avec succès']);

    }

    public function recupererdisciplines()
    {

        $disciplines = Discipline::query()->where("supprimer", "=", 0)->orderBy('libelle', 'asc')->get();

        return response()->json($disciplines);

    }

}
