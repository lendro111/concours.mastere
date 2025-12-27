<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Typesmoyenne extends Model
{
    protected $table = 'typesmoyennes';

    protected $fillable = [
        "libelleTypeMoyenne",
        "code",
        "coefficient",
        'supprimer',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
    ];
}
