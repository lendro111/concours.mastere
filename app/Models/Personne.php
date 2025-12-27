<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personne extends Model
{
    protected $table = 'personnes';

    protected $fillable = [
        'id',
        'lycees_id',
        'etablissements_id',
        'specialites_id',
        'diplomes_id',
        'nom',
        'prenoms',
        'dateNaissance',
        'lieuNaissance',
        'genre',
        'telephone',
        'nomEtPrenomsDunProche',
        'telephoneDunProche',
        'professions',
        'employeurs',
        'experiences',
        'financements',
        'niveauetudes',
        'anneeBacs_id',
        'series_id',
        'password',
        'userUpdate',
    ];

    public static function getPersonneDetails($id)
    {
        return self::query()
            ->select(
                'p.id',
                'p.lycees_id',
                'p.etablissements_id',
                'p.specialites_id',
                'p.diplomes_id',
                'p.nom',
                'p.prenoms',
                'p.dateNaissance',
                'p.lieuNaissance',
                'p.genre',
                'p.telephone',
                'p.nomEtPrenomsDunProche',
                'p.telephoneDunProche',
                'p.professions',
                'p.employeurs',
                'p.experiences',
                'p.financements',
                'p.niveauetudes',
                'p.anneeBacs_id',
                'p.series_id'
            )
            ->from('personnes as p')
            ->join('diplomes as d', 'd.id', '=', 'p.diplomes_id')
            ->join('etablissement as e', 'e.id', '=', 'p.etablissements_id')
            ->join('lycees as l', 'l.id', '=', 'p.lycees_id')
            ->join('series as s', 's.id', '=', 'p.series_id')
            ->join('specialites as sp', 'sp.id', '=', 'p.specialites_id')
            ->join('anneebacs as ab', 'ab.id', '=', 'p.anneeBacs_id')
            ->where('p.id', $id)
            ->first();
    }


}
