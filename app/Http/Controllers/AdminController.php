<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminHasConcours;
use App\Models\Concours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{

    public function index(){

        return view('utilisateurs.administrateurs.index', [

            "roles" => Role::query()->where("guard_name", "=", "admin")->orderBy('name', 'asc')->get(),
            "titre" => mb_strtoupper('Administrateurs'),
            "admins" => Admin::listeAdminsAvecRoles(),
            "concours" => Concours::query()->where("supprimer", "=", 0)->orderBy('libelleConcours', 'asc')->get(),

        ]);

    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'nom' => ['required', 'string'],
            'prenoms' => ['required', 'string'],
            'email' => ['required','string'],
            'password' => ['required', 'string', 'min:8'],
            'roles_id' => ['required', 'exists:roles,id'],
            'concours_id' => ['required', 'array'],
        ], [
            "nom.required" => "Le nom est obligatoire",
            "nom.string" => "Le nom doit être de type chaîne de caractère",
            "prenoms.required" => "Le prenoms est obligatoire",
            "prenoms.string" => "Le prenoms doit être de type chaîne de caractère",
            "password.required" => "Le mot de passe est obligatoire",
            "password.min" => "Le mot de passe doit faire plus de 8 caractères",
            "email.required" => "L'email est obligatoire",
            "email.string" => "L'email doit être de type chaîne de caractère",
            "concours_id.required" => "Le concours est obligatoire",
            "concours_id.array" => "Le concours doit être de type tableau",
            "roles_id.required" => "Veillez selectionner un role",
            "roles_id.exists" => "Le role n'existe pas",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $compte = Admin::query()->where('email', $data['email'])->first();

        if (is_null($compte)) {

            $role = Role::query()->findOrFail((int)$data['roles_id']);

            $dataAdmin = [

                "nom" => mb_strtoupper($data['nom']),
                "prenoms" => mb_strtoupper($data['prenoms']),
                "email" => $data['email'],
                "password" => Hash::make($data['password']),
                "userAdd" => Auth::guard("admin")->id(),

            ];

            $admin = Admin::query()->create($dataAdmin);

            if ((int)$data['concours_id'][0] === 0) {

                $concours = Concours::query()->where("supprimer", "=", 0)->orderBy('libelleConcours', 'asc')->get();

                foreach ($concours as $value) {

                    $dataAdminHasConcours = [

                        "admins_id" => (int)$admin->id,
                        "concours_id" => (int)$value->id,
                        "userAdd" => Auth::guard("admin")->id(),

                    ];

                    AdminHasConcours::query()->create($dataAdminHasConcours);

                }

            }else{

                foreach ($data['concours_id'] as $value) {

                    $dataAdminHasConcours = [

                        "admins_id" => (int)$admin->id,
                        "concours_id" => (int)$value,
                        "userAdd" => Auth::guard("admin")->id(),

                    ];

                    AdminHasConcours::query()->create($dataAdminHasConcours);

                }

            }

            $admin->syncRoles($role->name);

            return response()->json([
                'success' => true,
                'message' => 'Compte ajouté avec succès'
            ]);

        }elseif ($compte->supprimer == 1) {

            $dataCompte = [

                "supprimer" => 0,
                "userUpdate" => Auth::id(),

            ];

            $compte->update($dataCompte);

            return response()->json([
                'success' => true,
                'message' => 'Compte ajouté avec succès'
            ]);

        }

        return response()->json([
            'errors' => [
                'global' => ['L\'email doit être unique']
            ]
        ], 422);

    }

    public function modifier(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'admin_id' => ['required', 'string'],
            'nom' => ['required', 'string'],
            'prenoms' => ['required', 'string'],
            'email' => ['required', 'string', Rule::unique('admins', 'email')->ignore($request->admin_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'roles_id' => ['required', 'exists:roles,id'],
        ], [
            "admin_id.required" => "L'id de l'Admin est obligatoire",
            "nom.required" => "Le nom est obligatoire",
            "nom.string" => "Le nom doit être de type chaîne de caractère",
            "prenoms.required" => "Le prenoms est obligatoire",
            "prenoms.string" => "Le prenoms doit être de type chaîne de caractère",
            "email.required" => "L'email est obligatoire",
            "email.string" => "L'email doit être de type chaîne de caractère",
            "email.unique" => "L'email doit être unique",
            "roles_id.required" => "Veuillez sélectionner un rôle",
            "roles_id.exists" => "Le rôle n'existe pas",
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $role = Role::query()->findOrFail($data['roles_id']);

        $admin = Admin::query()->findOrFail($data['admin_id']);

        if ($request->filled('password')) {
            $dataAdmin = [

                "nom" => mb_strtoupper($data['nom']),
                "prenoms" => mb_strtoupper($data['prenoms']),
                "email" => $data['email'],
                "password" => Hash::make($data['password']),
                "userUpdate" => Auth::id(),

            ];
        }else{

            $dataAdmin = [

                "nom" => mb_strtoupper($data['nom']),
                "prenoms" => mb_strtoupper($data['prenoms']),
                "email" => $data['email'],
                "userUpdate" => Auth::id(),

            ];

        }

        $admin->update($dataAdmin);

        if (!$admin->hasRole($role->name)) {

            $admin->syncRoles($role->name);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Compte modifiée avec succès',
        ]);

    }

    public function supprimer($id)
    {

        $admin = Admin::query()->findOrFail($id);

        $dataAdmin = [

            "supprimer" => 1,
            "userDelete" => Auth::id(),

        ];

        $admin->update($dataAdmin);

        return response()->json(['message' => 'Entrée supprimée avec succès']);

    }

    public function fermer($id)
    {

        $admin = Admin::query()->findOrFail($id);

        $dataAdmin = [

            "statut" => 0,
            "userUpdate" => Auth::id(),

        ];

        $admin->update($dataAdmin);

        return response()->json(['message' => 'Compte fermé avec succès']);

    }

    public function activer($id)
    {

        $admin = Admin::query()->findOrFail($id);

        $dataAdmin = [

            "statut" => 1,
            "userUpdate" => Auth::id(),

        ];

        $admin->update($dataAdmin);

        return response()->json(['message' => 'Compte activé avec succès']);

    }

    public function recupererconcoursadmin($id)
    {

        $concours = Concours::listeConcoursAdmin($id);

        $concoursIds = $concours->pluck('id');

        return response()->json($concoursIds);

    }

    public function modifierconcours(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => ['required', 'string'],
            'concours_id' => ['required', 'array'],
        ], [
            "admin_id.required" => "L'id de l'admin est obligatoire",
            "concours_id.required" => "Le concours est obligatoire",
            "concours_id.array" => "Le concours doit être de type tableau",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ((int)$data['concours_id'][0] === 0) {

            $concours = Concours::query()->where("supprimer", "=", 0)->orderBy('libelleConcours', 'asc')->get();

            $concoursselectionnes = $concours->pluck('id');

        }else{

            $concoursselectionnes = collect($data['concours_id']);

        }

        // Récupérer les concours déjà associés à l'admin
        $concoursadmin = Concours::listeConcoursAdmin($data['admin_id'])->pluck('id');

        // Comparer les concours
        $concoursaajouter = $concoursselectionnes->diff($concoursadmin);      // Ceux à ajouter
        $concoursasupprimer = $concoursadmin->diff($concoursselectionnes);   // Ceux à supprimer

        if ($concoursaajouter->isEmpty() && $concoursasupprimer->isEmpty()) {
            // Aucun changement
            return response()->json([
                'message' => 'Aucun changement n\'a été effectué.'
            ], 200);
        }

        // Ajouter les nouveaux concours
        foreach ($concoursaajouter as $concoursId) {
            AdminHasConcours::create([
                "admins_id" => $data['admin_id'],
                "concours_id" => $concoursId,
                "userUpdate" => Auth::guard("admin")->id(),
            ]);
        }

        // Retirer les concours en surplus
        if (!$concoursasupprimer->isEmpty()) {
            AdminHasConcours::where('admins_id', $data['admin_id'])
                ->whereIn('concours_id', $concoursasupprimer)
                ->delete();
        }

        return response()->json([
            'message' => 'La liste des concours de l\'admin a été mise à jour avec succès.',
            'added' => $concoursaajouter->values(),
            'removed' => $concoursasupprimer->values()
        ], 200);
    }


}
