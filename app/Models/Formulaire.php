<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Formulaire extends Model
{

    protected $table = 'formulaires';

    protected $fillable = [
        "concours_id ",
        "typesmoyennes_id ",
        "disciplines_id ",
        "series_id ",
        "coefficient",
        'supprimer',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
    ];

    public static function listeFormulaire()
    {
        return DB::table('formulaires as f')
        ->join('concours as c', 'c.id', '=', 'f.concours_id')
        ->join('typesmoyennes as tm', 'tm.id', '=', 'f.typesmoyennes_id')
        ->join('disciplines as d', 'd.id', '=', 'f.disciplines_id')
        ->join('series as s', 's.id', '=', 'f.series_id')
        ->where('f.supprimer', 0)
        ->select(
            'f.id as id',              
            'c.id as concours_id',
            'c.ecoles_id',
            'c.logos_id',
            'c.libelleConcours',
            'tm.id as typesmoyennes_id',
            'tm.libelleTypeMoyenne',
            'tm.code',
            'tm.coefficient',
            'd.id as disciplines_id',
            'd.concours_id',
            'd.libelle',
            'd.coef',
            's.id as series_id',
            's.libelleSerie',
            'f.coefficient'            
        )
        ->orderBy('c.libelleConcours')
        ->get();

    }



    public static function getInfosMoyennesCandidat($idCandidat, $idSession)
    {
        return DB::table(DB::raw('(
            SELECT DISTINCT f.disciplines_id, f.typesmoyennes_id, f.concours_id
            FROM formulaires f
            WHERE f.concours_id = (SELECT s.concours_id FROM session s WHERE s.id = '.$idSession.')
        ) as f'))
            ->join('disciplines as d', 'd.id', '=', 'f.disciplines_id')
            ->join('typesmoyennes as tm', 'tm.id', '=', 'f.typesmoyennes_id')
            ->leftJoin('moyennedossier as md', function ($join) use ($idCandidat) {
                $join->on('md.typesmoyennes_id', '=', 'tm.id')
                    ->on('md.disciplines_id', '=', 'f.disciplines_id') // 🔑 à ne pas oublier
                    ->where('md.candidats_id', '=', $idCandidat);
            })
            ->select(
                'd.id as idDiscipline',
                'd.libelle as libelleDiscipline',
                'tm.id as idTypemoyenne',
                'tm.libelleTypemoyenne as libelleTypemoyenne',
                'md.id as idMoyennedossier',
                'md.moyenne'
            )
            ->orderBy('d.id')
            ->orderBy('tm.id')
            ->get();
    }

}
