<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{

    protected $table = 'filieres';

    protected $fillable = [
        'libelleFiliere',
        'codeFiliere',
        'concours_id',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
        'supprimer',
    ];

    public static function listeFilieres()
    {
        return self::select(
            'filieres.*',
            'c.libelleConcours',
            'c.codeConcours',
            'c.id as idConcours',
            'e.codeEcole',
            'e.libelleEcole'
        )
            ->join('concours as c', function ($join) {
                $join->on('c.id', '=', 'filieres.concours_id')
                    ->where('c.supprimer', 0);
            })
            ->join('ecoles as e', function ($join) {
                $join->on('e.id', '=', 'c.ecoles_id')
                    ->where('e.supprimer', 0);
            })
            ->where('filieres.supprimer', "=", 0)
            ->get();
    }


}
