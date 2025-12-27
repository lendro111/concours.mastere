<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Serie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SerieController extends Controller
{
    public function index(){

        return view('series.index', [

            "series" => Serie::query()->where("supprimer", "0")->orderBy("libelleSerie", "asc")->get(),
            "titre" => mb_strtoupper("Series")

        ]);
    }


    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'libelleSerie' => ['required', 'string', 'unique:series,libelleSerie'],
        ], [
            "libelleSerie.required" => "Le libellé de la série est obligatoire",
            "libelleSerie.string" => "Le libellé de la série doit être de type chaîne de caractère",
            "libelleSerie.unique" => "Le libellé de la série doit être unique",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataSeries = [

            "libelleSerie" => mb_strtoupper($data['libelleSerie']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        Serie::query()->create($dataSeries );

        return response()->json([
            'success' => true,
            'message' => 'Serie ajouté avec succès'
        ]);

    }

    public function modifier(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'idSerie' => ['required',],
            'libelleSerie' => ['required', 'string'],
        ], [
            "idSerie.required" => "La série est obligatoire",
            "libelleSerie.required" => "Le libellé de la série est obligatoire",
            "libelleSerie.string" => "Le libellé de la série doit être de type chaîne de caractère",

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $serie = Serie::query()->findOrFail($data['idSerie']);

        if (!is_null($serie)) {

            if ($serie->nom_serie == $data['libelleSerie'] ) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cette série']
                    ]
                ], 422);

            }

            $dataSeries = [

            "libelleSerie" => mb_strtoupper($data['libelleSerie']),
            "userAdd" => Auth::guard("admin")->id(),

            ];

            $serie->update($dataSeries);

            return response()->json([
                'success' => true,
                'message' => 'Série mise à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucune série trouvée']
            ]
        ], 422);


    }

    public function supprimer($id)
    {

        $serie = Serie::query()->findOrFail($id);

        $serie->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Série supprimée avec succès']);

    }

    public function recupererseries(){

        $series = Serie::query()->where("supprimer", "=", 0)->orderBy('libelleSerie', 'asc')->get();

        return response()->json($series);
    }
}
