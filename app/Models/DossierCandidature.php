<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DossierCandidature extends Model
{

    protected $table = 'dossierscandidatures';

    protected $fillable = [

        'sessions_id',
        'documentsdossiers_id',
        'requis',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
        'supprimer',


    ];

    public static function listeDocumentsParSession($sessionId)
    {
        return self::query()
            ->where('dossierscandidatures.sessions_id', $sessionId)
            ->pluck('dossierscandidatures.documentsdossiers_id as document_id', 'dossierscandidatures.documentsdossiers_id')
            ->toArray();
    }


    public static function listeIDsDossierCandidature($sessionId)
    {
        return self::query()
            ->where('dossierscandidatures.sessions_id', $sessionId)
            ->pluck('dossierscandidatures.id')
            ->toArray();
    }

    public static function listeDocumentsParSessionNew($sessionId)
    {
        return self::query()
            ->select([
                'dc.id as idDossierCandidature',
                'dd.id as idDocumentDossier',
                'dd.libelle',
                'dd.code',
            ])
            ->from('dossierscandidatures as dc')
            ->join('documentsdossiers as dd', 'dc.documentsdossiers_id', '=', 'dd.id')
            ->join('session as s', 's.id', '=', 'dc.sessions_id')
            ->where('s.id', $sessionId)
            ->get();
    }

    public static function listeDossierCandidature()
    {
        return DB::table('dossierscandidatures as dc')
            ->join('session as s', function($join) {
                $join->on('s.id', '=', 'dc.session_id')
                    ->where('c.supprimer', 0);
            })
            ->join('calendriers as ca', 'ca.id', '=', 's.calendriers_id')
            ->join('annees as a', 'a.id', '=', 's.annees_id')
            ->where('s.supprimer', 0)
            ->select(
                's.id as idSession',
                's.statut',
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'ca.dateDebut',
                'ca.dateFin',
                'a.id as idAnnee',
                'a.libelle'
            )
            ->orderBy('ca.dateDebut', 'desc')
            ->get();
    }


}
