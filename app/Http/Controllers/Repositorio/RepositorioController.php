<?php

namespace App\Http\Controllers\Repositorio;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class RepositorioController extends Controller
{

    public function adminvista($vista){

        $usuario = DB::table('users')
                            ->select('persona.numero_documento', 'persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
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

            if (view()->exists('repositorio/'.$vista)){
                return view('repositorio/'.$vista, compact('user_roles', 'usuario'));
            } else{
            return abort('404');
            }

        } else{
            $user_roles = json_encode($user_roles);

            return view('repositorio /'.$vista, compact('user_roles', 'usuario'));

        }
    }
}