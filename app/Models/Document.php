<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Document extends Model
{

    protected $table = 'documents';

    protected $fillable = [
        'codeFiliere',
        'codeConcours',
    ];

    public static function documentsDuCandidat($idCandidat)
    {
        return DB::table('candidats as c')
            ->join('documents as d', 'd.candidats_id', '=', 'c.id')
            ->join('dossierscandidatures as dc', 'dc.id', '=', 'd.dossiersCandidature_id')
            ->join('documentsdossiers as dd', 'dd.id', '=', 'dc.documentsdossiers_id')
            ->select(
                'd.id as idDocument',
                'd.filePath',
                'dd.libelle',
                'dd.code',
                'dd.requis',
                'c.id as idCandidat',
            )
            ->where('c.id', $idCandidat)
            ->get();
    }


}
