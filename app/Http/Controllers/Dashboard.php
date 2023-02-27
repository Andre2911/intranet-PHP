<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Illuminate\Support\Facades\DB;

class Dashboard extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        
        //$usuario = User::find(Auth::user()->id);
        
        $usuario = DB::table('users')
                            ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->where('users.id', Auth::user()->id)
                            ->first();

        $usuario = json_encode($usuario);

        $user_roles_db = DB::table('model_has_roles')
                            ->select('roles.name')
                            ->where('model_id', Auth::user()->id)
                            ->join('roles', 'roles.id', 'model_has_roles.role_id')
                            ->get();
        $user_roles = [];

        

        /***  CARGO JUEZ PAZ LETRADO / JUEZ ESPECIALIZADO / JUEZ MIXTO / JUEZ SUPERIOR  */

        $juez = DB::table('users')
                    ->select('username', 'op_cargos.id')
                    ->join('persona', 'persona.id', 'users.persona_id')
                    ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                    ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                    ->join('op_cargos', 'op_cargos.id', 'op_plazas_tit.op_cargo_id')
                    ->where('users.id', Auth::user()->id)
                    ->whereIn('op_cargos.id', [5, 6, 7, 8, 9])
                    ->first();

        if($juez){
            array_push($user_roles, utf8_encode('Magistrado'));
        }
        

        if (sizeof($user_roles_db)>0) {
            foreach($user_roles_db as $role){
                array_push($user_roles, utf8_encode($role->name));
                
                
                
            }
            $user_roles = json_encode($user_roles);
            //print_r($user_roles);

            return view('dashboard', compact('user_roles', 'usuario'));

        } else{
            $user_roles = json_encode($user_roles);

            return view('dashboard', compact('user_roles', 'usuario'));

            //return abort('403');

        }
    }

    public function admin(){

        $usuario = DB::table('users')
                            ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->where('users.id', Auth::user()->id)
                            ->first();

        $usuario = json_encode($usuario);
        
        $user_roles_db = DB::table('model_has_roles')
                            ->select('roles.name')
                            ->where('model_id', Auth::user()->id)
                            ->join('roles', 'roles.id', 'model_has_roles.role_id')
                            ->get();
        $user_roles = [];

        if (sizeof($user_roles_db)>0) {
            foreach($user_roles_db as $role){
                array_push($user_roles, utf8_encode($role->name));
            }
            $user_roles = json_encode($user_roles);
            //print_r($user_roles);
            return view('admin/inicio', compact('user_roles', 'usuario'));

        } else{
           return abort('403');

        }

    }
    public function adminvista($vista){

        $usuario = DB::table('users')
                            ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->where('users.id', Auth::user()->id)
                            ->first();

        $usuario = json_encode($usuario);
        
        $user_roles_db = DB::table('model_has_roles')
                            ->select('roles.name')
                            ->where('model_id', Auth::user()->id)
                            ->join('roles', 'roles.id', 'model_has_roles.role_id')
                            ->get();
        $user_roles = [];

        if (sizeof($user_roles_db)>0) {
            foreach($user_roles_db as $role){
                array_push($user_roles, utf8_encode($role->name));
            }
            $user_roles = json_encode($user_roles);
            //print_r($user_roles);

            if (view()->exists('admin/'.$vista)){
                return view('admin/'.$vista, compact('user_roles', 'usuario'));
            } else{
               return abort('404');
            }

        } else{
           return abort('403');

        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

}
