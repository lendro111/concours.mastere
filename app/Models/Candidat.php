<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Candidat extends Model
{

    protected $table = 'candidats';

    protected $fillable = [
        'matricule',
        'sessions_id',
        'personnes_id',
        'userAdd',
        'userUpdate',
        'userDelete',
        'valideDossier',
    ];

    public static function listeCandidatsParSession($sessionId)
    {
        return DB::table('session as s')
            ->join('candidats as c', 'c.sessions_id', '=', 's.id')
            ->join('choix as ch', 'ch.candidats_id', '=', 'c.id')
            ->join('filieres as f', 'f.id', '=', 'ch.filieres_id')
            ->join('personnes as p', 'p.id', '=', 'c.personnes_id')
            ->join('specialites as sp', 'sp.id', '=', 'p.specialites_id')
            ->join('series as se', 'se.id', '=', 'p.series_id')
            ->join('lycees as l', 'l.id', '=', 'p.lycees_id')
            ->join('etablissement as e', 'e.id', '=', 'p.etablissements_id')
            ->join('anneebacs as a', 'a.id', '=', 'p.anneeBacs_id')
            ->join('diplomes as d', 'd.id', '=', 'p.diplomes_id')
            ->where('s.id', $sessionId)
            ->groupBy('c.id')
            ->orderBy('c.created_at', 'DESC')
            ->select(
                'p.id as idPersonne',
                'p.nom',
                'p.prenoms',
                'p.email',
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
                'sp.id as idSpecialite',
                'sp.libelleSpecialite as libelleSpecialite',
                'sp.codeSpecialite',
                'se.id as idSerie',
                'se.libelleSerie as libelleSerie',
                'l.id as idLycee',
                'l.libelle as libelleLycee',
                'l.codeLycee',
                'e.id as idEtablissement',
                'e.libelle as libelleEtablissement',
                'e.codeEtablissement',
                'a.libelle as libelleAnnee',
                'd.id as idDiplome',
                'd.libelle as libelleDiplome',
                'c.id as idCandidat',
                'c.matricule',
                'c.created_at',
                'a.id as idAnneebac',
                DB::raw("GROUP_CONCAT(f.codeFiliere SEPARATOR '-') as codeFiliere")
            )
            ->get();
    }

    public static function candidatsAvecUnDocuments($sessionId)
    {
        return DB::table('session as s')
            ->join('candidats as c', 'c.sessions_id', '=', 's.id')
            ->join('personnes as p', 'p.id', '=', 'c.personnes_id')
            ->join('choix as ch', 'ch.candidats_id', '=', 'c.id')
            ->join('filieres as fi', 'fi.id', '=', 'ch.filieres_id')
            ->leftJoin('specialites as sp', 'sp.id', '=', 'p.specialites_id')
            ->join('series as se', 'se.id', '=', 'p.series_id')
            ->join('lycees as l', 'l.id', '=', 'p.lycees_id')
            ->leftJoin('etablissement as e', 'e.id', '=', 'p.etablissements_id')
            ->join('diplomes as di', 'di.id', '=', 'p.diplomes_id')
            ->leftJoin('photos as ph', 'ph.id', '=', 'p.photos_id')
            ->join('anneebacs as ab', 'ab.id', '=', 'p.anneeBacs_id')
            ->join('documents as d', 'd.candidats_id', '=', 'c.id')
            ->where('s.id', $sessionId)
            ->whereNotNull('d.filePath')
            ->groupBy('c.id')
            ->orderBy('c.created_at', 'DESC')
            ->select(
                'c.id as idCandidat',
                'c.matricule',
                'c.created_at',
                'p.id as idPersonne',
                'p.nom',
                'p.prenoms',
                'ab.libelle as libelleAnnee',
                DB::raw('DATE_FORMAT(p.dateNaissance, "%d-%m-%Y") as dateNaissance'),
                'p.lieuNaissance',
                'p.genre',
                'p.telephone',
                'p.email',
                'p.nomEtPrenomsDunProche',
                'p.telephoneDunProche',
                'p.etablissementOrigine',
                'p.etablissementSuperieurOrigine',
                's.id as idSession',
                'p.professions',
                'p.employeurs',
                'p.experiences',
                'p.financements',
                'p.niveauetudes',
                'sp.id as idSpecialite',
                'sp.libelleSpecialite as libelleSpecialite',
                'sp.codeSpecialite',
                'se.id as idSerie',
                'se.libelleSerie as libelleSerie',
                'l.id as idLycee',
                'l.libelle as libelleLycee',
                'l.codeLycee',
                'e.id as idEtablissement',
                'e.libelle as libelleEtablissement',
                'e.codeEtablissement',
                'di.id as idDiplome',
                'di.libelle as libelleDiplome',
                'ph.photo_path',
                'ph.photo_type',
                'ph.photo_nom',
                DB::raw("GROUP_CONCAT(DISTINCT fi.codeFiliere SEPARATOR '-') AS codeFiliere")
            )
            ->get();
    }

    public static function candidatsAyantFiniInscription($idSession)
    {
        return DB::table('candidats as c')
            ->join('choix as ch', 'ch.candidats_id', '=', 'c.id')
            ->join('filieres as fi', 'fi.id', '=', 'ch.filieres_id')
            ->join('personnes as p', 'p.id', '=', 'c.personnes_id')
            ->leftJoin('specialites as sp', 'sp.id', '=', 'p.specialites_id')
            ->join('series as se', 'se.id', '=', 'p.series_id')
            ->join('lycees as l', 'l.id', '=', 'p.lycees_id')
            ->leftJoin('etablissement as e', 'e.id', '=', 'p.etablissements_id')
            ->join('diplomes as d', 'd.id', '=', 'p.diplomes_id')
            ->leftJoin('photos as ph', 'ph.id', '=', 'p.photos_id')
            ->join('session as s', 's.id', '=', 'c.sessions_id')
            ->join('anneebacs as ab', 'ab.id', '=', 'p.anneeBacs_id')
            ->where('s.id', $idSession)
            ->where('c.valideDossier', '=', 0)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('dossierscandidatures as dc_req')
                    ->join('documentsdossiers as ddreq', 'ddreq.id', '=', 'dc_req.documentsdossiers_id')
                    ->whereColumn('dc_req.sessions_id', 'c.sessions_id')
                    ->where('ddreq.requis', 1)
                    ->whereNotIn('ddreq.id', function ($sub) {
                        $sub->select('dd2.id')
                            ->from('documents as d2')
                            ->join('dossierscandidatures as dc2', 'dc2.id', '=', 'd2.dossiersCandidature_id')
                            ->join('documentsdossiers as dd2', 'dd2.id', '=', 'dc2.documentsdossiers_id')
                            ->whereColumn('d2.candidats_id', 'c.id')
                            ->whereNotNull('d2.filePath');
                    });
            })
            ->select(
                'c.id as idCandidat',
                'c.matricule',
                'c.created_at',
                'p.id as idPersonne',
                'p.nom',
                'p.prenoms',
                'ab.libelle as libelleAnnee',
                DB::raw('DATE_FORMAT(p.dateNaissance, "%d-%m-%Y") as dateNaissance'),
                'p.lieuNaissance',
                'p.genre',
                'p.telephone',
                'p.email',
                'p.nomEtPrenomsDunProche',
                'p.telephoneDunProche',
                'p.etablissementOrigine',
                'p.etablissementSuperieurOrigine',
                's.id as idSession',
                'p.professions',
                'p.employeurs',
                'p.experiences',
                'p.financements',
                'p.niveauetudes',
                'sp.id as idSpecialite',
                'sp.libelleSpecialite as libelleSpecialite',
                'sp.codeSpecialite',
                'se.id as idSerie',
                'se.libelleSerie as libelleSerie',
                'l.id as idLycee',
                'l.libelle as libelleLycee',
                'l.codeLycee',
                'e.id as idEtablissement',
                'e.libelle as libelleEtablissement',
                'e.codeEtablissement',
                'd.id as idDiplome',
                'd.libelle as libelleDiplome',
                'ph.photo_path',
                'ph.photo_type',
                'ph.photo_nom',
                DB::raw("GROUP_CONCAT(fi.codeFiliere SEPARATOR '-') as codeFiliere")
            )
            ->groupBy('c.id')
            ->orderBy('c.created_at', 'DESC')
            ->get();
    }

    public static function innfosDuCandidat($idCandidat)
    {
        return DB::table('personnes as p')
            ->join('specialites as s', 's.id', '=', 'p.specialites_id')
            ->join('series as se', 'se.id', '=', 'p.series_id')
            ->join('lycees as l', 'l.id', '=', 'p.lycees_id')
            ->join('etablissement as e', 'e.id', '=', 'p.etablissements_id')
            ->join('anneebacs as a', 'a.id', '=', 'p.anneeBacs_id')
            ->join('diplomes as d', 'd.id', '=', 'p.diplomes_id')
            ->leftJoin('photos as ph', 'ph.id', '=', 'p.photos_id')
            ->join('candidats as c', 'c.personnes_id', '=', 'p.id')
            ->select(
                'p.id as idPersonne',
                'p.nom',
                'p.prenoms',
                'p.email',
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
                's.id as idSpecialite',
                's.libelleSpecialite as libelleSpecialite',
                's.codeSpecialite',
                'se.id as idSerie',
                'se.libelleSerie as libelleSerie',
                'l.id as idLycee',
                'l.libelle as libelleLycee',
                'l.codeLycee',
                'e.id as idEtablissement',
                'e.libelle as libelleEtablissement',
                'e.codeEtablissement',
                'a.libelle as libelleAnnee',
                'd.id as idDiplome',
                'd.libelle as libelleDiplome',
                'ph.photo_path',
                'ph.photo_type',
                'ph.photo_nom',
                'c.id as idCandidat',
                'c.matricule',
                'c.valideDossier',
                'a.id as idAnneebac',
            )
            ->where('c.id', $idCandidat)
            ->first(); // on récupère une seule ligne
    }

    public static function listeCandidatsNonValiderParSession($sessionId)
    {
        return DB::table('session as s')
            ->join('candidats as c', 'c.sessions_id', '=', 's.id')
            ->join('choix as ch', 'ch.candidats_id', '=', 'c.id')
            ->join('filieres as f', 'f.id', '=', 'ch.filieres_id')
            ->join('personnes as p', 'p.id', '=', 'c.personnes_id')
            ->join('specialites as sp', 'sp.id', '=', 'p.specialites_id')
            ->join('series as se', 'se.id', '=', 'p.series_id')
            ->join('lycees as l', 'l.id', '=', 'p.lycees_id')
            ->join('etablissement as e', 'e.id', '=', 'p.etablissements_id')
            ->join('anneebacs as a', 'a.id', '=', 'p.anneeBacs_id')
            ->join('diplomes as d', 'd.id', '=', 'p.diplomes_id')
            ->where('s.id', $sessionId)
            ->where('c.valideDossier', "=", 0)
            ->groupBy('c.id')
            ->orderBy('c.created_at', 'DESC')
            ->select(
                'p.id as idPersonne',
                'p.nom',
                'p.prenoms',
                'p.email',
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
                'sp.id as idSpecialite',
                'sp.libelleSpecialite as libelleSpecialite',
                'sp.codeSpecialite',
                'se.id as idSerie',
                'se.libelleSerie as libelleSerie',
                'l.id as idLycee',
                'l.libelle as libelleLycee',
                'l.codeLycee',
                'e.id as idEtablissement',
                'e.libelle as libelleEtablissement',
                'e.codeEtablissement',
                'a.libelle as libelleAnnee',
                'd.id as idDiplome',
                'd.libelle as libelleDiplome',
                'c.id as idCandidat',
                'c.matricule',
                'a.id as idAnneebac',
                DB::raw("GROUP_CONCAT(f.codeFiliere SEPARATOR '-') as codeFiliere")
            )
            ->get();
    }

    public static function listeCandidatsValiderParSession($sessionId)
    {
        return DB::table('session as s')
            ->join('candidats as c', 'c.sessions_id', '=', 's.id')
            ->join('choix as ch', 'ch.candidats_id', '=', 'c.id')
            ->join('filieres as f', 'f.id', '=', 'ch.filieres_id')
            ->join('personnes as p', 'p.id', '=', 'c.personnes_id')
            ->join('specialites as sp', 'sp.id', '=', 'p.specialites_id')
            ->join('series as se', 'se.id', '=', 'p.series_id')
            ->join('lycees as l', 'l.id', '=', 'p.lycees_id')
            ->join('etablissement as e', 'e.id', '=', 'p.etablissements_id')
            ->join('anneebacs as a', 'a.id', '=', 'p.anneeBacs_id')
            ->join('diplomes as d', 'd.id', '=', 'p.diplomes_id')
            ->where('s.id', $sessionId)
            ->where('c.valideDossier', "=", 1)
            ->groupBy('c.id')
            ->orderBy('c.updated_at', 'DESC')
            ->select(
                'p.id as idPersonne',
                'p.nom',
                'p.prenoms',
                'p.email',
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
                'sp.id as idSpecialite',
                'sp.libelleSpecialite as libelleSpecialite',
                'sp.codeSpecialite',
                'se.id as idSerie',
                'se.libelleSerie as libelleSerie',
                'l.id as idLycee',
                'l.libelle as libelleLycee',
                'l.codeLycee',
                'e.id as idEtablissement',
                'e.libelle as libelleEtablissement',
                'e.codeEtablissement',
                'a.libelle as libelleAnnee',
                'd.id as idDiplome',
                'd.libelle as libelleDiplome',
                'c.id as idCandidat',
                'c.matricule',
                'c.updated_at',
                'a.id as idAnneebac',
                DB::raw("GROUP_CONCAT(f.codeFiliere SEPARATOR '-') as codeFiliere")
            )
            ->get();
    }

}
