<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialite extends Model
{

    protected $table = "specialites";

    protected $fillable = [
        'libelleSpecialite',
        'codeSpecialite',
        'userAdd',
        'userUpdate',
        'userDelete',
        'supprimer',
        'deleted_at',
    ];

}
