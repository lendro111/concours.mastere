<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtapesConcours extends Model
{

    public $table = 'etapesconcours';

    protected $fillable = [

        "libelle",
        "code",
        "userAdd",
        "userUpdate",
        "userDelete",
        "deleted_at",
        "supprimer",

    ];

}
