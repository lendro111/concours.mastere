<?php

namespace App\Http\Controllers;

use App\Models\AnneeBac;
use App\Models\Candidat;
use App\Models\Choix;
use App\Models\Diplome;
use App\Models\Document;
use App\Models\Etablissement;
use App\Models\Formulaire;
use App\Models\Lycee;
use App\Models\Personne;
use App\Models\Serie;
use App\Models\Specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CandidatController extends Controller
{

    public function index($id){

        return view('candidats.index');

    }

    public function afficher($idCandidat){

        $infos = Formulaire::getInfosMoyennesCandidat($idCandidat, session("session")->idSession);

        // On récupère les types de moyennes distincts
        $typesmoyennes = $infos->pluck('libelleTypemoyenne', 'idTypemoyenne')->unique();

        // On groupe les infos par discipline
        $infos = $infos->groupBy('libelleDiscipline');


        return view('candidat.afficher', [

            "infosducandidat" => Candidat::innfosDuCandidat($idCandidat),
            "choixducandidat" => Choix::choixDuCandidat($idCandidat),
            "documentsducandidat" => Document::documentsDuCandidat($idCandidat),
            "typesmoyennes" => $typesmoyennes,
            "infos" => $infos,

        ]);

    }

    public function modifier($idCandidat){

        return view('candidat.modifier', [

            "infosducandidat" => Candidat::innfosDuCandidat($idCandidat),
            "anneebacs" => AnneeBac::query()->where("id", "!=", 1)->orderBy("id", "desc")->get(),
            "diplomes" => Diplome::query()->orderBy("libelle", "asc")->get(),
            "series" => Serie::query()->orderBy("libelleSerie", "asc")->get(),
            "specialites" => Specialite::query()->orderBy("libelle", "asc")->get(),
            "lycees" => Lycee::query()->orderBy("libelle", "asc")->get(),
            "etablissements" => Etablissement::query()->orderBy("libelle", "asc")->get(),

        ]);

    }

    public function valider($idCandidat)
    {

        $candidat = Candidat::query()->findOrFail($idCandidat);

        if (!is_null($candidat)){

            $candidat->update([

                "valideDossier" => 1,
                "userUpdate" => Auth::guard("admin")->id()

            ]);

            return response()->json([
                'success' => true,
                'message' => "Dossier validé avec succès",
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun candidat trouvé.'
        ], 200);

    }

    public function invalider($idCandidat)
    {

        $candidat = Candidat::query()->findOrFail($idCandidat);

        if (!is_null($candidat)){

            $candidat->update([

                "valideDossier" => 0,
                "userUpdate" => Auth::guard("admin")->id()

            ]);

            return response()->json([
                'success' => true,
                'message' => "Dossier invalidé avec succès",
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun candidat trouvé.'
        ], 200);

    }

    public function modifierPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'idCandidat' => 'required|exists:candidats,id',
            'password' => 'required|min:8|confirmed', // 'confirmed' vérifie password_confirmation
        ], [

            'idCandidat.required' => 'Le candidat est obligatoire.',
            'idCandidat.exists' => 'Le candidat est absent dans la base de donnée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit être au minimum 8 caractères.',
            'password.confirmed' => 'Veillez confirmer le mot de passe.',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $candidat = Candidat::innfosDuCandidat($data['idCandidat']);

        $personne =Personne::query()->findOrFail($candidat->idPersonne);

        $personne->update([

            'password' => Hash::make($data['password']),
            "userUpdate" => Auth::guard("admin")->id()

        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le mot de passe a été mis à jour avec succès !'
        ]);

    }

    public function modifiercandidat(Request $request)
    {

        $rules = [
            'personnes_id' => "required|numeric",
            'nom' => "required|string|max:255",
            'prenoms' => "required|string|max:255",
            'dateNaissance' => "required|string|max:255",
            'lieuNaissance' => "required|string|max:255",
            'genre' => "required|string",
            'telephone' => "required|string|max:10",
            'nomEtPrenomsDunProche' => "required|string|max:255",
            'telephoneDunProche' => "required|string|max:255",
            'anneebacs_id' => "required|integer",
            'lycee' => "required",
            'serie' => "required",
            'diplome' => "required",
        ];

// Ajout dynamique des règles si les champs existent
        if ($request->has('financement')) {
            $rules['financement'] = "required|string|max:255";
        }

        if ($request->has('etablissementsuperieur')) {
            $rules['etablissementsuperieur'] = "required|string|max:255";
        }

        if ($request->has('specialite')) {
            $rules['specialite'] = "required|string|max:255";
        }

        if ($request->has('niveauetudes')) {
            $rules['niveauetudes'] = "required|string|max:255";
        }

// Maintenant on crée le validator
        $validator = Validator::make($request->all(), $rules, [
            'nom.required' => 'Le nom est obligatoire.',
            'personnes_id.required' => 'L\'id de la personne est obligatoire.',
            'personnes_id.numeric' => 'L\'id doit être un nombre.',
            'prenoms.required' => 'Le prénoms est obligatoire.',
            'dateNaissance.required' => 'La date de Naissance est obligatoire.',
            'lieuNaissance.required' => 'Le Lieu de Naissance est obligatoire.',
            'genre.required' => 'Le genre est obligatoire.',
            'telephone.required' => 'Le numéro de Téléphone est obligatoire.',
            'nomEtPrenomsDunProche.required' => 'Le Nom de votre Proche est obligatoire.',
            'telephoneDunProche.required' => 'Le Téléphone de votre Proche est obligatoire.',
            'anneebacs_id.required' => 'L\'Année du BAC est obligatoire.',
            'lycee.required' => 'Le lycée est obligatoire.',
            'serie.required' => 'La série est obligatoire.',
            'diplome.required' => 'Le diplôme est obligatoire.',
            'etablissementsuperieur.required' => 'L\'établissement supérieur est obligatoire.',
            'specialite.required' => 'La spécialité est obligatoire.',
            'financement.required' => 'La source de financement est obligatoire.',
            'niveauetudes.required' => 'Veuillez sélectionner un niveau d\'étude.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $personne = Personne::getPersonneDetails($data['personnes_id']);

        if (session("codeconcours") == "MSTAU"){

            if ($personne->lycees_id == $data['lycee'] && $personne->diplomes_id == $data['diplome'] && $personne->anneeBacs_id == $data["anneebacs_id"] && $personne->series_id ==  $data['serie'] &&
                $personne->nom == $data['nom'] && $personne->prenoms == $data['prenoms'] && $personne->dateNaissance == $data['dateNaissance'] && $personne->lieuNaissance == $data['lieuNaissance'] &&
                $personne->genre == $data['genre'] && $personne->telephone == $data['telephone'] && $personne->nomEtPrenomsDunProche == $data["nomEtPrenomsDunProche"] && $personne->telephoneDunProche == $data["telephoneDunProche"] &&
                $personne->financements == $data["financement"] && $personne->niveauetudes == $data["niveauetudes"]
            ) {

                return response()->json([
                    'success' => false,
                    'message' => 'Aucun Changement Effectué.'
                ], 200);

            }

        }else{

            if ($personne->lycees_id == $data['lycee'] && $personne->diplomes_id == $data['diplome'] && $personne->anneeBacs_id == $data["anneebacs_id"] && $personne->series_id ==  $data['serie'] &&
                $personne->nom == $data['nom'] && $personne->prenoms == $data['prenoms'] && $personne->dateNaissance == $data['dateNaissance'] && $personne->lieuNaissance == $data['lieuNaissance'] &&
                $personne->genre == $data['genre'] && $personne->telephone == $data['telephone'] && $personne->nomEtPrenomsDunProche == $data["nomEtPrenomsDunProche"] && $personne->telephoneDunProche == $data["telephoneDunProche"]
            ) {

                return response()->json([
                    'success' => false,
                    'message' => 'Aucun Changement Effectué.'
                ], 200);

            }

        }


        $personne->update([

            'lycees_id' =>  $data['lycee'],
            'etablissements_id' => $request->has('etablissementsuperieur') ? $data['etablissementsuperieur'] : 2,
            'specialites_id' => $request->has('specialite') ? $data['specialite'] : 1,
            'diplomes_id' =>  $data['diplome'],
            'nom'  =>  mb_strtoupper($data['nom']),
            'prenoms' => mb_strtoupper($data['prenoms']),
            'dateNaissance' => $data['dateNaissance'],
            'lieuNaissance' =>  mb_strtoupper($data['lieuNaissance']),
            'genre' =>   $data['genre'],
            'telephone' => mb_strtoupper($data['telephone']),
            'nomEtPrenomsDunProche' => mb_strtoupper($data['nomEtPrenomsDunProche']),
            'telephoneDunProche' =>  mb_strtoupper($data['telephoneDunProche']),
            'anneeBacs_id' =>  $data['anneebacs_id'],
            'niveauetudes' => $request->has('financement') ?  $data['niveauetudes'] : null,
            'financements' =>  $request->has('financement') ? $data['financement'] : null,
            'series_id' =>   $data['serie'],
            'userUpdate' =>   Auth::guard("admin")->id(),

        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mise à jour effectué avec succès!'
        ]);

    }


}
