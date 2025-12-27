<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{

    use HasFactory, Notifiable, HasRoles;

    protected $table = 'admins';

    protected $fillable = [
        'nom',
        'prenoms',
        'email',
        'password',
        'statut',
        'userAdd',
        'userUpdate',
        'userDelete',
        'supprimer',
        'deleted_at',
    ];

    public static function listeAdminsAvecRoles()
    {
        return DB::table('admins as a')
            ->join('model_has_roles as mhr', 'mhr.model_id', '=', 'a.id')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('a.supprimer', '=', 0)
            ->orderBy('a.nom', 'asc')
            ->select('a.*', 'r.id as idRole', 'r.name as role_name')
            ->get();
    }


}
