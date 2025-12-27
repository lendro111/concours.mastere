<?php

namespace App\Http\Controllers;
use App\Models\DossierCandidature;
use App\Models\DocumentDossier;
use App\Models\Session;
use Illuminate\Http\Request;

class DossierCandidatureController extends Controller
{
    public function index(){

        return view('dossierscandidatures.index', [

            'dossierscandidatures' => DossierCandidature::listeDossierCandidature(),
            "documentsdossiers" => DocumentDossier::query()->where("supprimer", "0")->orderBy("libelle", "asc")->get(),
            "sessions" => Session::listeSessions(),
            'titre' => mb_strtoupper("Candidatures"),

        ]);

    }
}
