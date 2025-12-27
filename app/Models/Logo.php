<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logo extends Model
{

    protected $table = 'logos';

    protected $fillable = [
        'path',
        'image',
        'typeimage',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
        'supprimer',
    ];

}
