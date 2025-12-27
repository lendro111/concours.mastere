<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{

    public function index(){

        return view('utilisateurs.roles.index', [

            "roles" => Role::query()->where("guard_name", "=", "admin")->orderBy('name', 'asc')->get(),
            "titre" => mb_strtoupper("Roles")

        ]);

    }

    public function ajouter(Request $request){

        //unique:roles,name
        $role = Role::query()->where("name", "=", $request->name)->first();

        $verif = ($role && $role->guard_name === "operateur") ? "unique:roles,name" : "";

        $validator = Validator::make($request->all(),[
            'libelle' => "required|" . $verif,
        ],[
            'libelle.required' => 'Le nom du rôle est obligatoire.',
            'libelle.unique' => 'Le role doit être unique.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        Role::create([
            'name' => $data['libelle'],
        ]);


        return response()->json([
            'message' => "Role ajouté avec succès",
            'success' => true,
        ]);

    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [

            'name' => ['required', 'string'],
            'roles_id' => ['required']

        ], [

            "name.required" => "Veillez entrer un nom pour votre autorisation",
            "name.unique" => "Cette Autorisations existe déjà dans la base de donnée",

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $role = Role::query()->findOrFail($data['roles_id']);

        $dataRole = [
            "name" => $data['name'],
        ];

        $role->update($dataRole);

        return response()->json([
            'status' => 'success',
            'message' => 'Rôle modifiée avec succès',
            'permission' => $role
        ]);

    }

    public function supprimer($id){

        $role = Role::query()->findOrFail($id);

        $role->delete();

        return response()->json(['message' => 'Entrée supprimée avec succès']);

    }

    public function recupererroles(){

        $role = Role::query()->orderBy('name', 'asc')->get();
        return response()->json($role);

    }

    public function chargerautorisations($id)
    {

        $role = Role::query()->findOrFail($id);

        $permissions =  Permission::query()->where("guard_name" , "=", "admin")->orderBy("name", "asc")->get()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0]; // Rubrique = partie avant le point
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return response()->json([
            'grouped' => $permissions,
            'assigned' => $rolePermissions
        ]);
    }

    public function ajouterautorisations(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'permissions' => ['required', 'array'],
            'role_id' => ['required']
        ], [
            "permissions.required" => "Veuillez cocher des permissions pour votre rôle",
            "permissions.array" => "Les permissions doivent être un tableau",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $role = Role::query()->findOrFail($data['role_id']);

        $existingPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = $data['permissions'];

        // Tri pour comparaison
        sort($existingPermissions);
        sort($newPermissions);

        if ($existingPermissions === $newPermissions) {
            return response()->json([
                'errors' => [
                    'global' => ['Pas de modification effectuée']
                ]
            ], 422);
        }

        // Si on a retiré des permissions → reset + assignation
        if (count($newPermissions) < count($existingPermissions)) {
            $role->syncPermissions($newPermissions);
            return response()->json(['message' => 'Permissions mises à jour (modification détectée)']);
        }

        // Sinon on ajoute uniquement les nouvelles
        $permissionsToAdd = array_diff($newPermissions, $existingPermissions);

        if (count($permissionsToAdd)) {
            $role->givePermissionTo($permissionsToAdd);
            return response()->json(['message' => 'Nouvelles autorisations ajoutées avec succès']);
        }

        return response()->json([
            'errors' => [
                'global' => ['Pas de modification effectuée']
            ]
        ], 422);

    }

}
