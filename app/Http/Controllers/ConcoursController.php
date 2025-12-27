<?php

namespace App\Http\Controllers;

use App\Models\Concours;
use App\Models\ConcoursCycle;
use App\Models\Cycle;
use App\Models\Ecole;
use App\Models\Logo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ConcoursController extends Controller
{

    public function index(){

        return view('concours.index', [

            'concours' => Concours::listeConcours(),
            'ecoles' => Ecole::query()->where("supprimer", "0")->orderBy("libelleEcole")->get(),
            'cycles' => Cycle::query()->where("supprimer", "0")->orderBy("libelle")->get(),
            'titre' => mb_strtoupper("Concours"),

        ]);

    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'libelleConcours' => ['required', 'string'],
            'codeConcours' => ['required', 'string', 'unique:concours,codeConcours'],
            'baseMatricule' => ['required','string'],
            'cycles_id' => ['required'],
            'ecoles_id' => ['required'],
        ], [
            "libelleConcours.required" => "Le libellé du concours est obligatoire",
            "libelleConcours.string" => "Le libellé du concours doit être de type chaîne de caractère",
            "codeConcours.required" => "Le Code du concours est obligatoire",
            "codeConcours.string" => "Le Code du concours doit être de type chaîne de caractère",
            "codeConcours.unique" => "Ce concours existe déjà dans la base de donnée",
            "baseMatricule.required" => "La base du Matricule est obligatoire",
            "baseMatricule.string" => "La base du Matricule doit être de type chaîne de caractère",
            "cycles_id.required" => "Le cycle est obligatoire",
            "ecoles_id.required" => "L'école est obligatoire",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $concours = Concours::query()->where('codeConcours', $data['codeConcours'])->first();

        if (is_null($concours)) {

            $dataConcours = [

                "libelleConcours" => mb_strtoupper($data['libelleConcours']),
                "CodeConcours" => mb_strtoupper($data['codeConcours']),
                "ecoles_id" => $data['ecoles_id'],
                "logos_id" => 1,
                "userAdd" => Auth::guard("admin")->id(),


            ];

            $concoursAjouter = Concours::query()->create($dataConcours);

            $dataConcoursCycle = [

                "cycles_id" => $data['cycles_id'],
                "concours_id" => $concoursAjouter->id,
                "userAdd" => Auth::guard("admin")->id(),

            ];

            ConcoursCycle::query()->create($dataConcoursCycle);

            return response()->json([
                'success' => true,
                'message' => 'Concours ajouté avec succès'
            ]);

        }

        return response()->json([
            'errors' => [
                'global' => ['Ce concours existe déjà dans la base donnée']
            ]
        ], 422);

    }


    public function modifier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idConcours' => ['required', 'integer', 'exists:concours,id'],
            'libelleConcours' => ['required', 'string'],
            'codeConcours' => ['required', 'string'],
            'baseMatricule' => ['required', 'string'],
            'cycles_id' => ['required', 'integer', 'exists:cycles,id'],
            'ecoles_id' => ['required', 'integer', 'exists:ecoles,id'],
        ], [
            "idConcours.required" => "Le concours est obligatoire",
            "libelleConcours.required" => "Le libellé du concours est obligatoire",
            "libelleConcours.string" => "Le libellé du concours doit être une chaîne",
            "codeConcours.required" => "Le Code du concours est obligatoire",
            "codeConcours.string" => "Le Code du concours doit être une chaîne",
            "baseMatricule.required" => "La base du matricule est obligatoire",
            "cycles_id.required" => "Le cycle est obligatoire",
            "ecoles_id.required" => "L'école est obligatoire",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Le concours à modifier
        $concours = Concours::findOrFail($data['idConcours']);

        // Vérifier si concours-cycle existe déjà
        $concoursCycle = ConcoursCycle::where('concours_id', $data['idConcours'])
            ->where('cycles_id', $data['cycles_id'])
            ->first();

        // Vérifier si rien n'a changé
        if (
            $concours->libelleConcours == $data['libelleConcours'] &&
            $concours->codeConcours == $data['codeConcours'] &&
            $concours->baseMatricule == $data['baseMatricule'] &&
            $concours->ecoles_id == $data['ecoles_id'] &&
            $concoursCycle !== null
        ) {
            return response()->json([
                'errors' => [
                    'global' => ['Aucune modification effectuée pour ce concours']
                ]
            ], 422);
        }

        // Mise à jour du concours
        $concours->update([
            "ecoles_id" => $data['ecoles_id'],
            "libelleConcours" => mb_strtoupper($data['libelleConcours']),
            "codeConcours" => mb_strtoupper($data['codeConcours']),
            "baseMatricule" => mb_strtoupper($data['baseMatricule']),
            "userUpdate" => Auth::guard("admin")->id(),
        ]);


        if ($concoursCycle === null) {
            ConcoursCycle::create([
                "concours_id" => $data['idConcours'],
                "cycles_id" => $data['cycles_id'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Concours mis à jour avec succès'
        ]);
    }



    public function ajouterlogo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:3072', // 3 Mo
            'concours_id' => 'required|integer|exists:concours,id'
        ], [
            "file.required" => "Le logo est obligatoire",
            "file.image" => "Le logo doit être une image",
            "file.max" => "Le logo ne doit pas dépasser 3 Mo",
            "concours_id.string" => "Le concours est obligatoire",
            "concours_id.exists" => "Le concours sélectionner n'existe pas",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $base64Image = file_get_contents($request->file('file')->getRealPath());

            $extensionPhoto = $request->file('file')->getMimeType();

            // Récupérer le concours
            $concours = Concours::query()->findOrFail($request->input('concours_id'));

            $ancienLogo = Logo::query()->find($concours->logos_id);

            // Vérifier si le concours a déjà un logo
            if (!is_null($ancienLogo)) {

                if ($concours->logos_id == 1) {

                    $nouveauLogo = Logo::query()->create([
                        'path' => null,
                        'image' => $base64Image,
                        'typeimage' => $extensionPhoto,
                        'userAdd' => Auth::guard("admin")->id(),
                    ]);

                    $concours->update([
                        'logos_id' => $nouveauLogo->id,
                        'userUpdate' => Auth::guard("admin")->id(),
                    ]);

                }else{

                    $ancienLogo->update([

                        'path' => null,
                        'image' => $base64Image,
                        'typeimage' => $extensionPhoto,
                        'userUpdate' => Auth::guard("admin")->id(),

                    ]);

                }


            }else{

                $nouveauLogo = Logo::query()->create([
                    'path' => null,
                    'image' => $base64Image,
                    'typeimage' => $extensionPhoto,
                    'userAdd' => Auth::guard("admin")->id(),
                ]);

                $concours->update([
                    'logos_id' => $nouveauLogo->id,
                    'userUpdate' => Auth::guard("admin")->id(),
                ]);

            }


            return response()->json([
                'success' => true,
                'fileName' => $fileName,
                'message' => 'Logo ajouté avec succès !'
            ]);
        }


        return response()->json(['success' => false, 'message' => 'Aucun fichier reçu'], 400);

    }


    public function supprimer($id)
    {

        $concours = Concours::query()->findOrFail($id);

        $concours->update([

           "supprimer" => 1,
           "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Concours supprimé avec succès']);

    }


    public function recupererconcours()
    {

        $concours = Concours::query()->where("supprimer", "=", 0)->orderBy('libelleConcours', 'asc')->get();

        return response()->json($concours);

    }

    public function recupererconcoursadminpourafficher($id)
    {

        $concours = Concours::listeConcoursAdmin($id);

        if ($concours->isNotEmpty()) {

            return response()->json([
                'success' => true,
                'message' => "Récuperation des concours effectuée avec succès",
                'concours' => $concours
            ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun Concours lié à l\'admin.'
        ], 200);

    }

}
