<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\Request;

class CyclesController extends Controller
{

    public function index(){

        return view('cycles.index');

    }

    public function recuperercycles(){

        $cycles = Cycle::query()->where("supprimer", "=", 0)->orderBy('libelle', 'asc')->get();

        return response()->json($cycles);

    }

}
