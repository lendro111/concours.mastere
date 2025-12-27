<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discipline extends Model
{
    protected $table = 'disciplines';

    protected $fillable = [
        'concours_id',
        'libelle',
        'coef',
        'userAdd',
        'userUpdate',
        'userDelete',
        'supprimer',
        'deleted_at',
    ];

    public static function listeDisciplines()
    {
        return self::select(
            'disciplines.*',
            'c.libelleConcours',
            'c.codeConcours',
            'c.id as idConcours',
            'e.codeEcole',
            'e.libelleEcole'
        )
            ->join('concours as c', function ($join) {
                $join->on('c.id', '=', 'disciplines.concours_id')
                    ->where('c.supprimer', 0);
            })
            ->join('ecoles as e', function ($join) {
                $join->on('e.id', '=', 'c.ecoles_id')
                    ->where('e.supprimer', 0);
            })
            ->where('disciplines.supprimer', "=", 0)
            ->get();
    }
}
