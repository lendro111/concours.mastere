<?php

namespace App\Http\Controllers;

use App\Models\EtablissementSuperieur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EtablissementSuperieurController extends Controller
{

    public function index(){
        
        return view('etablissementsuperieurs.index', [
        "superieur" => EtablissementSuperieur::query()
            ->where("supprimer", 0)
            ->orderBy("etablissementSuperieurOrigine", "asc")
            ->get(),
        "titre" => mb_strtoupper("Etablissements Supérieurs")
       ]);
       
    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'etablissementSuperieurOrigine' => ['required', 'string', 'unique:etablissement_superieurs,etablissementSuperieurOrigine'],
        ], [
            "etablissementSuperieurOrigine.required" => "Le libellé de l'établissement supérieur d'origine est obligatoire",
            "etablissementSuperieurOrigine.string" => "Le libellé de l'établissement supérieur d'origine doit être de type chaîne de caractère",
            "etablissementSuperieurOrigine.unique" => "Le libellé de l'établissement supérieur d'origine doit être unique",

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataEtablissementSuperieur = [

            "etablissementSuperieurOrigine" => mb_strtoupper($data['etablissementSuperieurOrigine']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        EtablissementSuperieur::query()->create($dataEtablissementSuperieur);

        return response()->json([
            'success' => true,
            'message' => 'Etablissement supérieur ajouté avec succès'
        ]);

    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [
            'idEtablissementSuperieur' => ['required',],
            'etablissementSuperieurOrigine' => ['required', 'string'],
        ], [
            "idEtablissementSuperieur.required" => "L'établissement supérieur est obligatoire",
            "etablissementSuperieurOrigine.required" => "Le libellé de l'établissement supérieur est obligatoire",
            "etablissementSuperieurOrigine.string" => "Le libellé l'établissement supérieur doit être de type chaîne de caractère",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $etablissementsuperieur = EtablissementSuperieur::query()->findOrFail($data['idetablissementSuperieur']);

        if (!is_null($etablissementsuperieur)) {

            if ($etablissementsuperieur->nom_etablissementSuperieur == $data['etablissementSuperieurOrigine']) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cet établissement supérieur']
                    ]
                ], 422);

            }

            $dataEtablissementSuperieur = [

                "nom_etablissementSuperieur" => mb_strtoupper($data['etablissementSuperieurOrigine']),
                "userUpdate" => Auth::guard("admin")->id(),

            ];

            $etablissementsuperieur->update($dataEtablissementSuperieur);

            return response()->json([
                'success' => true,
                'message' => 'Etablissement supérieur mise à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucun Etablissement supérieur trouvée']
            ]
        ], 422);


    }
}
