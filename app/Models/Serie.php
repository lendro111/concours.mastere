<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{

    protected $table = "series";

    protected $fillable = [
        'libelleSerie',
        'supprimer',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
    ];
}
