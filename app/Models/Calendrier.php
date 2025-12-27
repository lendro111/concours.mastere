<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendrier extends Model
{

    protected $table = 'calendriers';

    protected $fillable = [

        "dateDebut",
        "dateFin",
        "ordre",
        "userAdd",
        "userUpdate",
        "userDelete",
        "deleted_at",
        "supprimer",

    ];

}
