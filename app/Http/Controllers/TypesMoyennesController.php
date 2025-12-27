<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Typesmoyenne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TypesMoyennesController extends Controller
{
    public function index(){

        return view('typesmoyennes.index', [

            "typesmoyennes" => Typesmoyenne::query()->where("supprimer", "0")->orderBy("libelleTypeMoyenne", "asc")->get(),
            "titre" => mb_strtoupper("Types moyennes")

        ]);
    }

    public function ajouter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'libelleTypeMoyenne' => ['required', 'string', 'unique:typesmoyennes,libelleTypeMoyenne'],
            'code' => ['required', 'string', 'unique:typesmoyennes,code'],
            'coefficient' => ['required', 'string'],
        ], [
            "libelleTypeMoyenne.required" => "Le libellé du type moyenne est obligatoire",
            "libelleTypeMoyenne.string" => "Le libellé du type moyenne doit être de type chaîne de caractère",
            "libelleTypeMoyenne.unique" => "Le libellé du type moyenne doit être unique",
            "code.required" => "Le Code du type moyenne est obligatoire",
            "code.string" => "Le Code du type moyenne doit être de type chaîne de caractère",
            "code.unique" => "Le Code du type moyenne doit être unique",
            "coefficient.required" => "Le coefficient du type moyenne est obligatoire",
            "coefficient.numeric" => "Le coefficient du type moyenne doit être de type numérique",            
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $dataTypeMoyennes = [

            "libelleTypeMoyenne" => mb_strtoupper($data['libelleTypeMoyenne']),
            "code" => mb_strtoupper($data['code']),
            "coefficient" => mb_strtoupper($data['coefficient']),
            "userAdd" => Auth::guard("admin")->id(),

        ];

        Typesmoyenne::query()->create($dataTypeMoyennes);

        return response()->json([
            'success' => true,
            'message' => 'Type moyenne ajouté avec succès'
        ]);

    }

    public function modifier(Request $request){

        $validator = Validator::make($request->all(), [
            'idTypeMoyenne' => ['required',],
            'libelleTypeMoyenne' => ['required', 'string'],
            'code' => ['required', 'string'],
            'coefficient' => ['required', 'string'],
        ], [
            "idTypeMoyenne.required" => "L'école est obligatoire",
            "libelleTypeMoyenne.required" => "Le libellé du type moyenne est obligatoire",
            "libelleTypeMoyenne.string" => "Le libellé du type moyenne doit être de type chaîne de caractère",
            "code.required" => "Le code du type moyenne est obligatoire",
            "code.string" => "Le code du type moyenne doit être de type chaîne de caractère",
            "coefficient.required" => "Le coefficient du type moyenne est obligatoire",
            "coefficient.string" => "Le coefficient du type moyenne doit être de type chaîne de caractère",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $typesmoyenne = Typesmoyenne::query()->findOrFail($data['idTypeMoyenne']);

        if (!is_null($typesmoyenne)) {

            if ($typesmoyenne->nom_typemoyenne == $data['libelleTypeMoyenne'] && $typesmoyenne->code == $data['code']  && $typesmoyenne->coefficient == $data['coefficient'] ) {

                return response()->json([
                    'errors' => [
                        'global' => ['Aucune modification éffectuer pour cet type de moyenne']
                    ]
                ], 422);

            }

            $dataTypesMoyenne = [

                "libelleTypeMoyenne" => mb_strtoupper($data['libelleTypeMoyenne']),
                "code" => mb_strtoupper($data['code']),
                "coefficient" => mb_strtoupper($data['coefficient']),
                "userUpdate" => Auth::guard("admin")->id(),

            ];

            $typesmoyenne->update($dataTypesMoyenne);

            return response()->json([
                'success' => true,
                'message' => 'Type moyenne mis à jour avec succès'
            ]);


        }

        return response()->json([
            'errors' => [
                'global' => ['Aucune Ecole trouvée']
            ]
        ], 422);
        

    }

    public function supprimer($id)
    {

        $typesmoyenne = Typesmoyenne::query()->findOrFail($id);

        $typesmoyenne->update([

            "supprimer" => 1,
            "userDelete" => Auth::guard("admin")->id(),

        ]);

        return response()->json(['message' => 'Type moyenne supprimé avec succès']);

    }

    public function recuperertypesmoyennes(){

        $typesmoyenne = Typesmoyenne::query()->where("supprimer", "=", 0)->orderBy('libelleTypeMoyenne', 'asc')->get();

        return response()->json($typesmoyenne);
    }
}
