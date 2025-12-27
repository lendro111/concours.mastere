<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcoursCycle extends Model
{

    protected $table = 'concourscycles';

    protected $fillable = [
        'concours_id',
        'cycles_id',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
        'supprimer',
    ];

}
