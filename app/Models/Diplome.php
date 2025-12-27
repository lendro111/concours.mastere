<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diplome extends Model
{

    protected $table = "diplomes";

    protected $fillable = [
        'libelle',
        'userAdd',
        'userUpdate',
        'userDelete',
        'supprimer',
        'deleted_at',
    ];

}
