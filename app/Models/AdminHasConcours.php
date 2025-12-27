<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminHasConcours extends Model
{

    protected $table = 'adminshasconcours';

    protected $fillable = [

        'admins_id',
        'concours_id',
        'userAdd',
        'userUpdate',
        'userDelete',
        'supprimer',
        'deleted_at',

    ];

}
