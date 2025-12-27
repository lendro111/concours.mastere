<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Concours extends Model
{

    protected $table = 'concours';

    protected $fillable = [
        "libelleConcours",
        "CodeConcours",
        "ecoles_id",
        "cycles_id",
        "logos_id",
        "userAdd",
        "supprimer",
    ];

    public static function ConcoursAdminPourLaSessionEnCours($idAdmin)
    {
        return DB::table('admins as a')
            ->join('adminshasconcours as ahc', function ($join) {
                $join->on('ahc.admins_id', '=', 'a.id');
            })
            ->join('concours as c', 'c.id', '=', 'ahc.concours_id')
            ->join('session as s', 's.concours_id', '=', 'c.id')
            ->join('annees as an', 'an.id', '=', 's.annees_id')
            ->leftJoin('logos as l', 'l.id', '=', 'c.logos_id')
            ->join('concourscycles as cc', 'cc.concours_id', '=', 'c.id')
            ->join('cycles as cy', 'cy.id', '=', 'cc.cycles_id')
            ->where('a.id', $idAdmin)
            ->where('s.statut', "=", 1)
            ->groupBy('c.id')
            ->orderBy('c.libelleConcours', 'asc')
            ->select(
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'c.notes',
                's.id as idSession',
                'l.path',
                'a.nom',
                'a.prenoms',
                'a.email',
                'a.contact',
                'cy.id as idCycle',
                'cy.libelle as libelleCycle'
            )
            ->get();
    }

    public static function ConcoursAdmin($idAdmin)
    {
        return DB::table('admins as a')
            ->join('adminshasconcours as ahc', function ($join) {
                $join->on('ahc.admins_id', '=', 'a.id');
            })
            ->join('concours as c', 'c.id', '=', 'ahc.concours_id')
            ->leftJoin('logos as l', 'l.id', '=', 'c.logos_id')
            ->join('concourscycles as cc', 'cc.concours_id', '=', 'c.id')
            ->join('cycles as cy', 'cy.id', '=', 'cc.cycles_id')
            ->where('a.id', $idAdmin)
            ->groupBy('c.id')
            ->orderBy('c.libelleConcours', 'asc')
            ->select(
                'c.id as idConcours',
                'c.libelleConcours',
                'c.codeConcours',
                'c.notes',
                'l.path',
                'a.nom',
                'a.prenoms',
                'a.email',
                'a.contact',
                'cy.id as idCycle',
                'cy.libelle as libelleCycle'
            )
            ->get();
    }

    public static function listeConcoursAdmin($adminId)
    {
        return DB::table('concours as c')
            ->join('adminshasconcours as ahc', 'ahc.concours_id', '=', 'c.id')
            ->join('admins as a', 'a.id', '=', 'ahc.admins_id')
            ->join('concourscycles as cc', 'cc.concours_id', '=', 'c.id')
            ->join('cycles as cy', 'cy.id', '=', 'cc.cycles_id')
            ->join('ecoles as e', 'e.id', '=', 'c.ecoles_id')
            ->leftJoin('logos as l', 'l.id', '=', 'c.logos_id')
            ->where('a.id', $adminId)
            ->select(
                'c.*',
                'cy.libelle as libelleCycle',
                DB::raw("TO_BASE64(l.image) as imageBase64"),
                'l.path',
                'l.typeimage',
                'e.codeEcole',
                'e.libelleEcole'
            )
            ->orderBy('c.libelleConcours')
            ->get();
    }

    public static function listeConcours()
    {
        return DB::table('concours as c')
            ->join('concourscycles as cc', 'cc.concours_id', '=', 'c.id')
            ->join('cycles as cy', 'cy.id', '=', 'cc.cycles_id')
            ->join('ecoles as e', 'e.id', '=', 'c.ecoles_id')
            ->leftJoin('logos as l', 'l.id', '=', 'c.logos_id')
            ->where('c.supprimer', '0')
            ->select(
                'c.*',
                'cy.id as idCycle',
                'cy.libelle as libelleCycle',
                DB::raw("TO_BASE64(l.image) as imageBase64"),
                'l.path',
                'l.typeimage',
                'e.id as idEcole',
                'e.libelleEcole',
                'e.codeEcole'
            )
            ->orderBy('c.libelleConcours')
            ->get();
    }


}
