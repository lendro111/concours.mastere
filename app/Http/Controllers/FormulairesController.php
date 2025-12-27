<?php

namespace App\Http\Controllers;

use App\Models\Concours;
use App\Models\Formulaire;
use App\Models\Typesmoyenne;
use App\Models\Discipline;
use App\Models\Serie;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormulairesController extends Controller
{
    public function index(){

        return view('formulaires.index', [

            'formulaires' => Formulaire::listeFormulaire(),
            'concours' => Concours::query()->where("supprimer", "0")->orderBy("libelleConcours")->get(),
            'typesmoyennes' => Typesmoyenne::query()->where("supprimer", "0")->orderBy("libelleTypeMoyenne")->get(),
            'discipline' => Discipline::query()->where("supprimer", "0")->orderBy("libelle")->get(),
            'serie' => Serie::query()->where("supprimer", "0")->orderBy("libelleSerie")->get(),
            'titre' => mb_strtoupper("Formulaires"),

        ]);

    }

    public function ajouter(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'coefficient' => ['required', 'numeric', 'unique:formulaires,coefficient'],
            'concours_id' => ['required', 'string'],
            'typesmoyennes_id' => ['required', 'string'],
            'disciplines_id' => ['required', 'string'],
            'series_id' => ['required', 'string'],            
        ], [
            'concours_id.required' => "Le concours est obligatoire",
            'typesmoyennes_id.required' => "Le type de moyenne est obligatoire",
            'disciplines_id.required' => "La discipline est obligatoire",
            'series_id.required' => "La série est obligatoire",
            'coefficient.required' => "Le coefficient est obligatoire",
            'coefficient.numeric' => "Le coefficient doit être de type numérique",
            "coefficient.unique" => "Ce formulaire existe déjà dans la base de donnée",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Vérifie si le formulaire existe déjà pour la même combinaison
        $formulaireExistant = Formulaire::query()->where('coefficient', $data['coefficient'])->first();

        if ($formulaireExistant) {
            return response()->json([
                'errors' => [
                    'global' => ['Ce formulaire existe déjà dans la base de données']
                ]
            ], 422);
        }


        Formulaire::create([
            "coefficient" => $data['coefficient'],
            "concours_id" => $data['concours_id'],
            "typesmoyennes_id" => $data['typesmoyennes_id'],
            "disciplines_id" => $data['disciplines_id'],
            "series_id" => $data['series_id'],
            "userAdd" => Auth::guard("admin")->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Formulaire ajouté avec succès'
        ]);
    }

    public function modifier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idFormulaire' => ['required', 'exists:formulaires,id'],
            'coefficient' => ['required', 'numeric'],
            'concours_id' => ['required', 'exists:concours,id'],
            'typesmoyennes_id' => ['required', 'exists:typesmoyennes,id'],
            'disciplines_id' => ['required', 'exists:disciplines,id'],
            'series_id' => ['required', 'exists:series,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $formulaire = Formulaire::findOrFail($data['idFormulaire']);

        if (
            $formulaire->coefficient == $data['coefficient'] &&
            $formulaire->concours_id == $data['concours_id'] &&
            $formulaire->typesmoyennes_id == $data['typesmoyennes_id'] &&
            $formulaire->disciplines_id == $data['disciplines_id'] &&
            $formulaire->series_id == $data['series_id']
        ) {
            return response()->json([
                'errors' => [
                    'global' => ['Aucune modification effectuée']
                ]
            ], 422);
        }

        $formulaire->update([
            'coefficient' => $data['coefficient'],
            'concours_id' => $data['concours_id'],
            'typesmoyennes_id' => $data['typesmoyennes_id'],
            'disciplines_id' => $data['disciplines_id'],
            'series_id' => $data['series_id'],
            'userUpdate' => Auth::guard('admin')->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Formulaire mis à jour avec succès'
        ]);
    }

    public function supprimer($id)
    {

        $formulaires = Formulaire::query()->findOrFail($id);

        $formulaires->update([

           "supprimer" => 1,
           "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'formulaire supprimé avec succès']);

    }


    

}

