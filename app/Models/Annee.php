<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Annee extends Model
{

    protected $table = "annees";

    protected $fillable = ["annee"];

    public static function listeAnneesAvecSession()
    {
        return DB::table('annees as a')
            ->join('session as s', 's.annees_id', '=', 'a.id')
            ->groupBy('a.id')
            ->select('a.*')
            ->get();
    }

}
