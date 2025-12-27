<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Session extends Model
{

    protected $table = 'session';

    protected $fillable = [

        "concours_id",
        "annees_id",
        "calendriers_id",
        "compteurMatricule",
        "ageMaxPersonne",
        "ageMaxBac",
        "statut",
        "userAdd",
        "userUpdate",
        "userDelete",
        "deleted_at",
        "supprimer",

    ];

    public static function infosSession($idSession, $idAdmin)
    {
        return DB::table('session as s')
            ->join('annees as a', 'a.id', '=', 's.annees_id')
            ->join('concours as c', function($join) {
                $join->on('c.id', '=', 's.concours_id')
                    ->where('c.supprimer', '=', 0);
            })
            ->join('adminshasconcours as ahc', function($join) {
                $join->on('ahc.concours_id', '=', 'c.id')
                    ->where('ahc.supprimer', '=', 0);
            })
            ->join('admins as ad', function($join) {
                $join->on('ad.id', '=', 'ahc.admins_id')
                    ->where('ad.supprimer', '=', 0);
            })
            ->leftJoin('logos as l', 'l.id', '=', 'c.logos_id')
            ->join('concourscycles as cc', 'cc.concours_id', '=', 'c.id')
            ->join('cycles as cy', 'cy.id', '=', 'cc.cycles_id')
            ->where('s.id', $idSession)
            ->where('ad.id', $idAdmin)
            ->select(
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'a.id as idAnne',
                'a.libelle',
                's.id as idSession',
                'c.notes',
                'l.path',
                'cy.id as idCycle',
                'cy.libelle as libelleCycle'
            )
            ->first();
    }

    public static function infosSessionPrecedent($idSession, $idAdmin)
    {
        return DB::table('session as s')
            ->join('annees as a', 'a.id', '=', 's.annees_id')
            ->join('concours as c', function($join) {
                $join->on('c.id', '=', 's.concours_id')
                    ->where('c.supprimer', '=', 0);
            })
            ->join('adminshasconcours as ahc', function($join) {
                $join->on('ahc.concours_id', '=', 'c.id')
                    ->where('ahc.supprimer', '=', 0);
            })
            ->join('admins as ad', function($join) {
                $join->on('ad.id', '=', 'ahc.admins_id')
                    ->where('ad.supprimer', '=', 0);
            })
            ->leftJoin('logos as l', 'l.id', '=', 'c.logos_id')
            ->join('concourscycles as cc', 'cc.concours_id', '=', 'c.id')
            ->join('cycles as cy', 'cy.id', '=', 'cc.cycles_id')
            ->where('s.id', $idSession)
            ->where('ad.id', $idAdmin)
            ->select(
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'a.id as idAnne',
                'a.libelle',
                's.id as idSession',
                'c.notes',
                'l.path',
                'cy.id as idCycle',
                'cy.libelle as libelleCycle'
            )
            ->get();
    }

    public static function infosSessionPrecedentParConcours($idConcours, $idAdmin)
    {
        return DB::table('session as s')
            ->join('annees as a', 'a.id', '=', 's.annees_id')
            ->join('concours as c', function($join) {
                $join->on('c.id', '=', 's.concours_id')
                    ->where('c.supprimer', '=', 0);
            })
            ->join('adminshasconcours as ahc', function($join) {
                $join->on('ahc.concours_id', '=', 'c.id')
                    ->where('ahc.supprimer', '=', 0);
            })
            ->join('admins as ad', function($join) {
                $join->on('ad.id', '=', 'ahc.admins_id')
                    ->where('ad.supprimer', '=', 0);
            })
            ->leftJoin('logos as l', 'l.id', '=', 'c.logos_id')
            ->join('concourscycles as cc', 'cc.concours_id', '=', 'c.id')
            ->join('cycles as cy', 'cy.id', '=', 'cc.cycles_id')
            ->where('c.id', $idConcours)
            ->where('ad.id', $idAdmin)
            ->select(
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'a.id as idAnne',
                'a.libelle',
                's.id as idSession',
                'c.notes',
                'l.path',
                'cy.id as idCycle',
                'cy.libelle as libelleCycle'
            )
            ->get();
    }

    public static function getSessionDetails($idAnnee, $idConcours)
    {
        return DB::table('session as s')
            ->join('annees as a', 'a.id', '=', 's.annees_id')
            ->join('concours as c', 'c.id', '=', 's.concours_id')
            ->leftJoin('logos as l', 'l.id', '=', 'c.logos_id')
            ->join('concourscycles as cc', 'cc.concours_id', '=', 'c.id')
            ->join('cycles as cy', 'cy.id', '=', 'cc.cycles_id')
            ->where('a.id', $idAnnee)
            ->where('c.id', $idConcours)
            ->select(
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'a.id as idAnne',
                'a.libelle',
                's.id as idSession',
                'c.notes',
                'l.path',
                'cy.id as idCycle',
                'cy.libelle as libelleCycle'
            )
            ->first(); // ou ->get() si tu veux plusieurs résultats
    }

    public static function infosSessionParConcours($idConcours, $idAdmin)
    {
        return DB::table('session as s')
            ->join('annees as a', 'a.id', '=', 's.annees_id')
            ->join('concours as c', function($join) {
                $join->on('c.id', '=', 's.concours_id')
                    ->where('c.supprimer', '=', 0);
            })
            ->join('adminshasconcours as ahc', function($join) {
                $join->on('ahc.concours_id', '=', 'c.id')
                    ->where('ahc.supprimer', '=', 0);
            })
            ->join('admins as ad', function($join) {
                $join->on('ad.id', '=', 'ahc.admins_id')
                    ->where('ad.supprimer', '=', 0);
            })
            ->leftJoin('logos as l', 'l.id', '=', 'c.logos_id')
            ->join('concourscycles as cc', 'cc.concours_id', '=', 'c.id')
            ->join('cycles as cy', 'cy.id', '=', 'cc.cycles_id')
            ->where('s.statut', "=", 1)
            ->where('c.id', $idConcours)
            ->where('ad.id', $idAdmin)
            ->select(
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'a.id as idAnne',
                'a.libelle',
                's.id as idSession',
                'c.notes',
                'l.path',
                'cy.id as idCycle',
                'cy.libelle as libelleCycle'
            )
            ->first();
    }

    public static function listeSessions()
    {
        return DB::table('session as s')
            ->join('concours as c', function($join) {
                $join->on('c.id', '=', 's.concours_id')
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

    public static function sessionDetails($idSession)
    {
        return DB::table('session as s')
            ->join('concours as c', function($join) {
                $join->on('c.id', '=', 's.concours_id')
                    ->where('c.supprimer', 0);
            })
            ->join('calendriers as ca', 'ca.id', '=', 's.calendriers_id')
            ->join('annees as a', 'a.id', '=', 's.annees_id')
            ->join('sessionetapesconcours as sec', function($join) {
                $join->on('sec.session_id', '=', 's.id')
                    ->where('sec.statut', 1);
            })
            ->join('etapesconcours as ec', 'ec.id', '=', 'sec.etapesConcours_id')
            ->where('s.id', $idSession)
            ->where('s.supprimer', 0)
            ->select(
                's.*',
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'ca.dateDebut',
                'ca.dateFin',
                'a.id as idAnnee',
                'a.libelle as libelleAnne',
                'sec.id as idSessionEtapeConcours',
                'ec.id as idEtapeConcours',
                'ec.libelle as libelleEtapeConcours',
                'ec.code as codeEtapeConcours'
            )
            ->orderBy('ca.dateDebut', 'desc')
            ->first();
    }


    public static function listeSessionsParConcoursEtAnnee($concoursId)
    {
        return DB::table('session as s')
            ->join('concours as c', function($join) use ($concoursId) {
                $join->on('c.id', '=', 's.concours_id')
                    ->where('c.supprimer', 0)
                    ->where('c.id', $concoursId); // filtre sur le concours
            })
            ->join('calendriers as ca', 'ca.id', '=', 's.calendriers_id')
            ->join('annees as a', 'a.id', '=', 's.annees_id')
            ->join('sessionetapesconcours as sec', function($join) {
                $join->on('sec.session_id', '=', 's.id')
                    ->where('sec.statut', 1);
            })
            ->join('etapesconcours as ec', 'ec.id', '=', 'sec.etapesConcours_id')
            ->where('s.supprimer', 0)
            ->select(

                's.id as idSession',
                's.id as idSession',
                's.ageMaxPersonne',
                's.compteurMatricule',
                's.ageMaxBac',
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'ca.dateDebut',
                'ca.dateFin',
                'a.id as idAnnee',
                'a.libelle',
                'sec.id as idSessionEtapeConcours',
                'ec.id as idEtapeConcours',
                'ec.libelle as libelleEtapeConcours',
                'ec.code as codeEtapeConcours'
            )
            ->orderBy('ca.dateDebut', 'desc')
            ->first();
    }

}
