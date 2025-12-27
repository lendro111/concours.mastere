<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class AutorisationsController extends Controller
{

    public function index(){

        return view('utilisateurs.autorisations.index', [

            "autorisations" => Permission::query()->where("guard_name" , "=", "admin")->orderBy("name", "asc")->get(),
            'titre' => mb_strtoupper("autorisations")

        ]);

    }

    public function ajouter(Request $request){

        $validator = Validator::make($request->all(), [

            'libelle' => [
                'required',
                'string',
                'unique:permissions,name'
            ]
        ], [

            "libelle.required" => "Veillez entrer un nom pour votre autorisation",
            "libelle.unique" => "Cette Autorisations existe déjà dans la base de donnée",

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        Permission::create([
            'name' => $data["libelle"],
        ]);

        return response()->json([
            'message' => "Autorisation ajoutée avec succès",
            'success' => true,
        ]);

    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [

            'name' => ['required', 'string'],
            'autorisations_id' => ['required']

        ], [

            "name.required" => "Veillez entrer un nom pour votre autorisation",
            "name.unique" => "Cette Autorisations existe déjà dans la base de donnée",
            "autorisations_id.required" => "L'id est requis",

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $permission = Permission::query()->findOrFail($data['autorisations_id']);

        $dataPermission = [
            "name" => $data['name'],
        ];

        $permission->update($dataPermission);

        return response()->json([
            'status' => 'success',
            'message' => 'Permission modifiée avec succès',
            'permission' => $permission
        ]);

    }

    public function supprimer($id){

        $permission = Permission::query()->findOrFail($id);

        $permission->delete();

        return response()->json(['message' => 'Entrée supprimée avec succès']);
    }

}
