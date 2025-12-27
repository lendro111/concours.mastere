<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentDossier extends Model
{

    protected $table = 'documentsdossiers';

    protected $fillable = [
        'libelle',
        'code',
        'requis',
        'statutBachelier',
        'userAdd',
        'userUpdate',
        'userDelete',
        'deleted_at',
        'supprimer',
    ];

    public static function listeDocumentsRequisParSession($sessionId)
    {
        return DB::table('documentsdossiers as dd')
            ->join('dossierscandidatures as dc', 'dc.documentsdossiers_id', '=', 'dd.id')
            ->join('session as s', 's.id', '=', 'dc.sessions_id')
            ->where('s.id', $sessionId)
            ->where('dd.requis', 1)
            ->select(
                'dd.id as idDocumentDossier', 'dc.id as idDossierCandidature', 'dc.requis',
                'dd.libelle',
                'dd.code',
            )
            ->get();
    }


    

}
