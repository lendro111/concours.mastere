<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{

    protected $table = "etablissement";

    protected $fillable = [
        'codeEtablissement',
        'libelle',
        'supprimer',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
    ];
}
