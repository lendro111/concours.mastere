<?php

namespace App\Http\Controllers;

use App\Models\Annee;
use App\Models\Calendrier;
use App\Models\Concours;
use App\Models\DocumentDossier;
use App\Models\DossierCandidature;
use App\Models\EtapesConcours;
use App\Models\Session;
use App\Models\SessionEtapesConocours;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SessionsController extends Controller
{

    public function index()
    {

        return view('sessions.index', [

            "sessions" => Session::listeSessions(),
            "annees" => Annee::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),
            'concours' => Concours::listeConcours(),
            'etapes' => EtapesConcours::query()->where("supprimer", "0")->orderBy("code", "asc")->get(),
            'pieces' => DocumentDossier::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),
            'titre' => mb_strtoupper("Sessions"),

        ]);

    }

    public function ajouter(Request $request){

        $validator = Validator::make($request->all(), [
            'concours_id' => ['required', 'string'],
            'annees_id' => ['required', 'string'],
            'calendrier' => ['required', 'string'],
            'age' => ['required','numeric'],
            'ageBac' => ['required', "numeric"],
            'etapes_id' => ['required'],
            'pieces' => ['required', "array"],
        ], [
            "concours_id.required" => "Le concours est obligatoire",
            "concours_id.string" => "Le concours doit être de type chaîne de caractère",
            "annees_id.required" => "L'année est obligatoire",
            "annees_id.string" => "L'année doit être de type chaîne de caractère",
            "calendrier.required" => "Le calendrier est obligatoire",
            "calendrier.string" => "Le calendrier doit être de type chaîne de caractère",
            "age.required" => "L'âge maximum est obligatoire",
            "age.numeric" => "L'âge maximum  doit être de type entier",
            "ageBac.required" => "L'âge maximum du bac est obligatoire",
            "ageBac.numeric" => "L'âge maximum du bac  doit être de type entier",
            "etapes_id.required" => "L'étape du concours est obligatoire",
            "pieces.required" => "Les pièces à fournir sont obligatoire",
            "pieces.array" => "Les pièces à fournir doivent être un tableau",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $session = Session::listeSessionsParConcoursEtAnnee($data['concours_id']);

        if (is_null($session)) {

            $elementsCalendrier = explode(" ", $data['calendrier']);

            $dateDebut = $elementsCalendrier[0];

            $dateFin = $elementsCalendrier[2];

            $dataCalendriers = [

                "dateDebut" =>  $dateDebut,
                "dateFin"  =>  $dateFin,
                "userAdd" => Auth::guard("admin")->id(),

            ];

            $calendrier = Calendrier::query()->create($dataCalendriers);

            $dataSessions = [

                "concours_id" => $data['concours_id'],
                "annees_id" => $data['annees_id'],
                "calendriers_id" =>  $calendrier->id,
                "ageMaxPersonne" => $data['age'],
                "ageMaxBac" => $data['ageBac'],
                "userAdd" =>  Auth::guard("admin")->id(),

            ];

            $session = Session::query()->create($dataSessions);

            $etapesconcours = EtapesConcours::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get();

            foreach ($etapesconcours as $value) {

                $dataSessionEtapes = [

                    'session_id' =>  $session->id,
                    'etapesConcours_id'  => $value->id,
                    'statut' => $value->id == $data['etapes_id'] ? 1 : 0,
                    'userAdd' =>  Auth::guard("admin")->id(),

                ];

                SessionEtapesConocours::query()->create($dataSessionEtapes);

            }

            foreach ($data['pieces'] as $value) {

                $dataPieces = [

                    'sessions_id' => $session->id,
                    'documentsdossiers_id' => $value,
                    'userAdd' =>  Auth::guard("admin")->id(),

                ];

                DossierCandidature::query()->create($dataPieces);

            }

            return response()->json([
                'success' => true,
                'message' => 'Session ajoutée avec succès'
            ]);

        }

        return response()->json([
            'errors' => [
                'global' => ['Cette session existe déjà dans la base donnée']
            ]
        ], 422);

    }


    public function modifier($idSession)
    {

        $session = Session::sessionDetails($idSession);

        $dateDebut = $this->parseDateFlexible($session->dateDebut)->format('d/m/Y');
        $dateFin   = $this->parseDateFlexible($session->dateFin)->format('d/m/Y');

        return view('sessions.modifier', [

            "session" => $session,
            "dateDebut" => $dateDebut,
            "dateFin" => $dateFin,
            "annees" => Annee::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),
            "piecespourlasession" => DossierCandidature::listeDocumentsParSession($idSession),
            'concours' => Concours::listeConcours(),
            'etapes' => EtapesConcours::query()->where("supprimer", "0")->orderBy("code", "asc")->get(),
            'pieces' => DocumentDossier::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),
            'titre' => mb_strtoupper("Modifier une session"),

        ]);

    }

    public function modifiersessions(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'concours_id' => ['required', 'string'],
            'annees_id' => ['required', 'string'],
            'calendrier' => ['required', 'string'],
            'age' => ['required','numeric'],
            'ageBac' => ['required', "numeric"],
            'etapes_id' => ['required'],
            'pieces' => ['required', "array"],
        ], [
            "concours_id.required" => "Le concours est obligatoire",
            "concours_id.string" => "Le concours doit être de type chaîne de caractère",
            "annees_id.required" => "L'année est obligatoire",
            "annees_id.string" => "L'année doit être de type chaîne de caractère",
            "calendrier.required" => "Le calendrier est obligatoire",
            "calendrier.string" => "Le calendrier doit être de type chaîne de caractère",
            "age.required" => "L'âge maximum est obligatoire",
            "age.numeric" => "L'âge maximum  doit être de type entier",
            "ageBac.required" => "L'âge maximum du bac est obligatoire",
            "ageBac.numeric" => "L'âge maximum du bac  doit être de type entier",
            "etapes_id.required" => "L'étape du concours est obligatoire",
            "pieces.required" => "Les pièces à fournir sont obligatoire",
            "pieces.array" => "Les pièces à fournir doivent être un tableau",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $elementsCalendrier = explode(" ", $data['calendrier']);

        $dateDebut = $elementsCalendrier[0];
        $dateFin = $elementsCalendrier[2];

        $session = Session::listeSessionsParConcoursEtAnnee($data['concours_id']);

        if (!$session) {
            return response()->json(['error' => "Concours introuvable."], 404);
        }

        // Comparaison des champs simples
        $modifications = [];

        if ($session->idConcours !== (int)$data["concours_id"]) $modifications['concours_id'] = $data["concours_id"];
        if ($session->idAnnee !== (int)$data["annees_id"]) $modifications['annees_id'] = $data["annees_id"];
        if ($session->dateDebut !== $dateDebut) $modifications['dateDebut'] = $dateDebut;
        if ($session->dateFin !== $dateFin) $modifications['dateFin'] = $dateFin;
        if ($session->ageMaxPersonne != $data['age']) $modifications['ageMaxPersonne'] = $data['age'];
        if ($session->ageMaxBac != $data['ageBac']) $modifications['ageMaxBac'] = $data['ageBac'];
        if ($session->idEtapeConcours != $data['etapes_id']) $modifications['etapes_id'] = $data['etapes_id'];

        $idPiecesActuels = DossierCandidature::listeDocumentsParSession($session->idSession);

        $idsPiecesNouveaux = $data["pieces"];

        $piecesaajouter = array_diff($idsPiecesNouveaux, $idPiecesActuels);

        $piecesasupprimer = array_diff($idPiecesActuels, $idsPiecesNouveaux);

        $sessionUpdate = Session::query()->findOrFail($session->idSession);

        // Vérifier s’il y a des changements
        if (empty($modifications) && empty($piecesaajouter) && empty($piecesasupprimer)) {
            return response()->json([
                'errors' => [
                    'global' => ['Vous n\'avez effectué aucune modification.']
                ]
            ], 422);
        }

        if (!empty($modifications)) {

            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $dateDebut) && preg_match('/\d{2}\/\d{2}\/\d{4}/', $dateFin)) {
                // Si la date est au format 04/11/2025
                $partDebut = explode('/', $dateDebut);
                $partFin = explode('/', $dateFin);
                $dateFormateeDebut = $partDebut[2] . '-' . $partDebut[1] . '-' . $partDebut[0]; // 2025-11-04
                $dateFormateeFin = $partFin[2] . '-' . $partFin[1] . '-' . $partFin[0]; // 2025-11-04
            } else {
                // Si elle est déjà au bon format
                $dateFormateeDebut = $dateDebut;
                $dateFormateeFin = $dateFin;
            }

            $calendrier = Calendrier::query()->updateOrCreate(
                [
                    'dateDebut' => $dateFormateeDebut,
                    'dateFin' => $dateFormateeFin,
                ],
                [
                    'dateDebut' => $dateDebut,
                    'dateFin' => $dateFin,
                    'userAdd' => Auth::guard('admin')->id(),
                    'userUpdate' => Auth::guard('admin')->id(),
                ]
            );


            $dataSessions = [

                "concours_id" => $data['concours_id'],
                "annees_id" => $data['annees_id'],
                "calendriers_id" =>  $calendrier->id,
                "ageMaxPersonne" => $data['age'],
                "ageMaxBac" => $data['ageBac'],
                "userUpdate" =>  Auth::guard("admin")->id(),

            ];

            $sessionUpdate->update($dataSessions);

            if ($session->idEtapeConcours != $data['etapes_id']){

                $etapesconcoursancien = SessionEtapesConocours::query()->where("etapesConcours_id", "=", $session->idEtapeConcours)->where('session_id', $sessionUpdate->id)->first();
                $etapesconcoursnouveau = SessionEtapesConocours::query()->where("etapesConcours_id", "=", $data['etapes_id'])->where('session_id', $sessionUpdate->id)->first();

                $dataetapesconcoursancien = [

                    'statut' => 0,
                    'userUpdate' =>  Auth::guard("admin")->id(),

                ];

                $dataetapesconcoursnouveau = [

                    'statut' => 1,
                    'userUpdate' =>  Auth::guard("admin")->id(),

                ];


                $etapesconcoursancien->update($dataetapesconcoursancien);
                $etapesconcoursnouveau->update($dataetapesconcoursnouveau);

            }


        }

        if (!empty($piecesaajouter) ) {

            foreach ($piecesaajouter as $value) {

                $dataPieces = [

                    'sessions_id' => $sessionUpdate->id,
                    'documentsdossiers_id' => $value,
                    'userAdd' =>  Auth::guard("admin")->id(),

                ];

                DossierCandidature::query()->create($dataPieces);

            }

        }

        if (!empty($piecesasupprimer) ) {

            foreach ($piecesasupprimer as $value) {

                DossierCandidature::query()->where('documentsdossiers_id', $value)
                    ->where('sessions_id', $sessionUpdate->id)
                    ->delete();

            }

        }

        return response()->json([
            'success' => true,
            'message' => 'Modification effectuée avec succès'
        ]);
    }

    public function ouverture($id){

        $session = Session::query()->findOrFail($id);

        if (!is_null($session)) {

            $session->update([

                "statut" => 1,
                "userUpdate" => Auth::guard('admin')->id(),

            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ouverture de la session effectuée avec succès'
            ]);

        }

        return response()->json([
            'errors' => [
                'global' => ['La session n\'a pas été trouvée']
            ]
        ], 422);


    }

    public function fermeture($id){

        $session = Session::query()->findOrFail($id);

        if (!is_null($session)) {

            $session->update([

                "statut" => 0,
                "userUpdate" => Auth::guard('admin')->id(),

            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fermeture de la session effectuée avec succès'
            ]);

        }

        return response()->json([
            'errors' => [
                'global' => ['La session n\'a pas été trouvée']
            ]
        ], 422);


    }

    public function supprimer($id){

        $session = Session::query()->findOrFail($id);

        if (!is_null($session)) {

            $session->update([

                "supprimer" => 1,
                "userUpdate" => Auth::guard('admin')->id(),

            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suppression effectuée avec succès'
            ]);

        }

        return response()->json([
            'errors' => [
                'global' => ['La session n\'a pas été trouvée']
            ]
        ], 422);

    }

    private function parseDateFlexible($date)
    {
        $formats = ['d/m/Y', 'Y-m-d', 'Y/m/d', 'd-m-Y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date);
            } catch (\Exception $e) {
                // Essayer le format suivant
            }
        }

        // Dernière tentative : laisser Carbon deviner
        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }


}
