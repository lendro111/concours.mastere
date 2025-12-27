<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtablissementSuperieur extends Model
{
    protected $table = "etablissement_superieurs";

    protected $fillable = [
        'etablissementSuperieurOrigine',
        'supprimer',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
    ];
}
