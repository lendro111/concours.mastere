<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Annee;
use App\Models\Concours;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function index()
    {
        return view('auth.index');
    }

    public function recupererconcours(Request $request){

        /*dd(Hash::make($request->get('password')));*/

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ], [

            'email.required' => 'L\'Email est obligatoire',
            'password.required' => 'Le mot de passe est obligatoire',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        $admin = Admin::query()->where('email', $credentials['email'])->first();

        $remember = $request->filled('remember');

        if ($admin->statut == 1){

            if (Auth::guard('admin')->attempt($credentials, $remember)) {

                $request->session()->regenerate();

                $concours = Concours::ConcoursAdminPourLaSessionEnCours(Auth::guard('admin')->id());

                return response()->json([
                    'success' => true,
                    'concours' => $concours
                ]);
            }

            return response()->json(['errors' => "Mot de passe ou email incorrect."], 422);

        }

        return response()->json(['errors' => "Votre compte a été vérrouillé. Veillez contacter l'administrateur"], 422);

    }

    public function login($idSession)
    {

        $session = Session::infosSession($idSession, Auth::guard('admin')->id());

        $annees = Annee::listeAnneesAvecSession();

        if(!is_null($session)){

            $this->miseensession($session, $annees);

            $redirectUrl = redirect()->intended(route('tableaudebord.index'))->getTargetUrl();

            return response()->json([
                'success' => "Connexion réussie",
                'redirect' => $redirectUrl
            ], 200);

        }

        $sessionprecedent = Session::infosSessionPrecedent($idSession, Auth::guard('admin')->id());

        if(!is_null($sessionprecedent->last())){

            $this->miseensession($sessionprecedent->last(), $annees);

            $redirectUrl = redirect()->intended(route('tableaudebord.index'))->getTargetUrl();

            return response()->json([
                'success' => "Connexion réussie",
                'redirect' => $redirectUrl
            ], 200);

        }

        return response()->json(['errors' => "Impossible de se connecter. Veuillez réessayer."], 422);

    }

    public function changerconcours($idConcours)
    {

        $session = Session::infosSessionParConcours($idConcours, Auth::guard('admin')->id());

        $annees = Annee::listeAnneesAvecSession();

        if(!is_null($session)){

            $this->miseensession($session, $annees);

            return response()->json(['success' => "Connexion réussie"], 200);

        }

        $sessionprecedent = Session::infosSessionPrecedentParConcours($idConcours, Auth::guard('admin')->id());

        if(!is_null($sessionprecedent->last())){

            $this->miseensession($sessionprecedent->last(), $annees);

            return response()->json(['success' => "Connexion réussie"], 200);

        }

        return response()->json(['errors' => "Impossible de se connecter. Veuillez réessayer."], 422);

    }

    public function changersession(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'annees_id' => 'required|integer'
        ], [

            "annees_id.required" => "L'id de la session est obligatoire",

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();

        $session = Session::getSessionDetails($data["annees_id"], session("idConcours"));

        $annees = Annee::listeAnneesAvecSession();

        if(!is_null($session)){

            $this->miseensession($session, $annees);

            return response()->json([
                'success' => true,
                'message' => 'Session changée avec succès !',
                'idSession' => $data['annees_id']
            ]);

        }

        return response()->json(['errors' => "Pas de Session pour l'année sélectionnée."], 422);

    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');

    }

    private function miseensession($sessions, $annees)
    {

        session()->put('session', $sessions);
        session()->put('idConcours', $sessions->idConcours);
        session()->put('codeconcours', $sessions->codeConcours);
        session()->put('cycles', $sessions->libelleCycle);
        session()->put('notes', $sessions->notes);
        session()->put('annees', $annees);
        session()->put('anneesession', $sessions->idAnne);


    }


}
