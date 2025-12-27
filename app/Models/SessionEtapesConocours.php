<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionEtapesConocours extends Model
{

    protected $table = "sessionetapesconcours";

    protected $fillable = [

        'session_id',
        'etapesConcours_id',
        'statut',
        'dateDebut',
        'dateFin',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
        'supprimer',

    ];

}
