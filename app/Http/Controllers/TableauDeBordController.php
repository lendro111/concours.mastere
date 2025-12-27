<?php

namespace App\Http\Controllers;

use App\Models\Annee;
use App\Models\Candidat;
use App\Models\Concours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableauDeBordController extends Controller
{

    public function index()
    {

        return view('tableaudebord.index', [

            "listecandidats" => Candidat::listeCandidatsParSession(session("session")->idSession),
            "listecandidatsayantaumoinsundocument" => Candidat::candidatsAvecUnDocuments(session("session")->idSession),
            "listecandidatsayantfiniinscription" => Candidat::candidatsAyantFiniInscription(session("session")->idSession),
            "listecandidaturevalider" => Candidat::listeCandidatsValiderParSession(session("session")->idSession),
            "concours" => Concours::ConcoursAdmin(Auth::guard('admin')->id()),

        ]);

    }

    public function imprimerlistecandidatenexcel($idAnnee)
    {

        $listecandidats = Candidat::listeCandidatsParSession(session("session")->idSession);

        $annee = Annee::query()->where("id", $idAnnee)->first();

        $nomfichier = "Liste-des-candidats-" . $annee->libelle . ".xlsx";

        return view("exports.listecandidat", [

            "listecandidats" => $listecandidats,
            "nomfichier" => $nomfichier,

        ]);

    }

     public function imprimerlistecandidaturevalider($idAnnee)
    {

        $listecandidats = Candidat::listeCandidatsValiderParSession(session("session")->idSession);

        $annee = Annee::query()->where("id", $idAnnee)->first();

        $nomfichier = "Liste-des-candidatures-validees-" . $annee->libelle . ".xlsx";

        return view("exports.listecandidat", [

            "listecandidats" => $listecandidats,
            "nomfichier" => $nomfichier,

        ]);

    }

    public function listecandidatsayantaumoinsundocumentenexcel($idAnnee)
    {

        $listecandidatsayantaumoinsundocument =  Candidat::candidatsAvecUnDocuments(session("session")->idSession);

        $annee = Annee::query()->where("id", $idAnnee)->first();

        $nomfichier = "Liste-des-candidats-ayant-au-moins-un-document-" . $annee->libelle . ".xlsx";

        return view("exports.listecandidat", [

            "listecandidats" => $listecandidatsayantaumoinsundocument,
            "nomfichier" => $nomfichier,

        ]);

    }
    public function listecandidatsayantfinis($idAnnee)
    {

        $listecandidatsayantayantfinis =  Candidat::candidatsAyantFiniInscription(session("session")->idSession);

        $annee = Annee::query()->where("id", $idAnnee)->first();

        $nomfichier = "Liste-des-candidatures-non-validees-" . $annee->libelle . ".xlsx";

        return view("exports.listecandidat", [

            "listecandidats" => $listecandidatsayantayantfinis,
            "nomfichier" => $nomfichier,

        ]);

    }

    public function listecandidats()
    {

        $listecandidats = Candidat::listeCandidatsParSession(session("session")->idSession);

        if ($listecandidats->isNotEmpty()){

            return response()->json([
                'success' => true,
                'message' => "Récuperation des candidats effectuée avec succès",
                'candidats' => $listecandidats
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun candidat trouvé pour cette session.'
        ], 200);

    }
    public function listecandidaturevalider()
    {

        $listecandidats = Candidat::listeCandidatsValiderParSession(session("session")->idSession);

        if ($listecandidats->isNotEmpty()){

            return response()->json([
                'success' => true,
                'message' => "Récuperation des candidats effectuée avec succès",
                'candidats' => $listecandidats
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun candidat trouvé pour cette session.'
        ], 200);

    }

    public function listecandidatsayantaumoinsundocument()
    {

        $listecandidatsayantaumoinsundocument =  Candidat::candidatsAvecUnDocuments(session("session")->idSession);

        if ($listecandidatsayantaumoinsundocument->isNotEmpty()){

            return response()->json([
                'success' => true,
                'message' => "Récuperation des candidats effectuée avec succès",
                'candidats' => $listecandidatsayantaumoinsundocument
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun candidat trouvé pour cette session.'
        ], 200);

    }

}
