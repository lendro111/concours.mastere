<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Choix extends Model
{

    protected $table = 'choix';

    protected $fillable = [
        'idCandidat',
    ];


    public static function choixDuCandidat($idCandidat)
    {
        return DB::table('candidats as c')
            ->join('choix as ch', 'ch.candidats_id', '=', 'c.id')
            ->join('filieres as f', 'f.id', '=', 'ch.filieres_id')
            ->join('concours as co', 'co.id', '=', 'f.concours_id')
            ->select(
                'c.id as idCandidat',
                'c.matricule',
                'ch.id as idChoix',
                'ch.choix',
                'ch.ordreChoix',
                'f.id as idFiliere',
                'f.libelleFiliere',
                'f.codeFiliere',
                'co.id as idConcours',
                'co.libelleConcours',
                'co.codeConcours'
            )
            ->where('c.id', $idCandidat)
            ->orderBy('ch.ordreChoix', 'asc')
            ->get();
    }


}
