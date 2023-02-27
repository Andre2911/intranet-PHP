<?php

namespace App\Http\Controllers;

use App\Persona;
use Illuminate\Http\Request;
use Auth;
use App\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;


use Illuminate\Support\Facades\Validator;

class UserInfoController extends Controller{

    public function index(Request $request){

        
        $response = [];
        $usuario_id = Auth::user()->id;
        
        define('TIMEZONE', 'America/Lima');
        date_default_timezone_set(TIMEZONE);
        ini_set('date.timezone', 'America/Lima'); 
        

        if($request->input('init')){
                $persona = DB::table('users')
                            ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->where('users.id', $usuario_id)
                            ->first();

                $roles = User::select('users.id')->where('id', '=', Auth::user()->id)->with('roles', 'persona')->get();

                $response['usuario'] = $persona;
                $response['roles'] = $roles;

                return $response;
        }
    }

    public function perfil(){
        $usuario = DB::table('users')
                            ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email', 'persona.numero_documento',
                                        'plaza_titular.c_plaza as c_plaza_titular',       
                                        'plaza_titular.nombre_plaza as n_plaza_titular',       
                                        'of_titular.nombre_oficina as n_oficina_titular',       
                                        'cargo_titular.c_regimen',       
                                        'plaza_fisica.nombre_plaza as n_plaza_fisica',   
                                        'plaza_fisica.c_plaza as c_plaza_fisica',   
                                        'of_fisica.nombre_oficina as n_oficina_fisica'        
                            )
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->leftjoin('persona_has_plaza_tit','persona_has_plaza_tit.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plaza_titular', 'plaza_titular.id', 'persona_has_plaza_tit.op_plaza_tit_id')
                            ->leftjoin('op_oficinas as of_titular', 'of_titular.id', 'plaza_titular.op_oficina_id')
                            ->leftjoin('op_cargos as cargo_titular', 'cargo_titular.id', 'plaza_titular.op_cargo_id')

                            ->leftjoin('persona_has_plaza_fun','persona_has_plaza_fun.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plaza_fisica', 'plaza_fisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            ->leftjoin('op_oficinas as of_fisica', 'of_fisica.id', 'plaza_titular.op_oficinaf_id')

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
            return view('usuario/perfil', compact('user_roles', 'usuario'));

        } else{
           $user_roles = json_encode($user_roles);
           return view('usuario/perfil', compact('user_roles', 'usuario'));

        }
    }

    public function perfilProfesional(Request $request){
        if($request->input('init')){

            $perfil_pregrado = DB::table('opbl_academico')
                            ->where('persona_id', Auth::user()->persona_id)
                            ->where('tipo',1)
                            ->get();
            $perfil_postgrado = DB::table('opbl_academico')
                            ->where('persona_id', Auth::user()->persona_id)
                            ->where('tipo',2)
                            ->get();
            $perfil_cargos = DB::table('opbl_cargos')
                            ->select('op_cargo_id as id')
                            ->where('persona_id', Auth::user()->persona_id)
                            ->get();
            $perfil_especialidades = DB::table('opbl_especialidades')
                            ->select('especialidad_id as id')
                            ->where('persona_id', Auth::user()->persona_id)
                            ->get();
            $perfil_sedes = DB::table('opbl_sedes')
                            ->where('persona_id', Auth::user()->persona_id)
                            ->get();

            $sedes = DB::table('sedes')
                            ->select('sedes.ubigeo_id', 'n_distrito')
                            ->join('ubigeos', 'ubigeos.ubigeo_id', 'sedes.ubigeo_id')
                            ->distinct()
                            ->get();


            $response["perfil_pregrado"] = $perfil_pregrado;
            $response["perfil_postgrado"] = $perfil_postgrado;
            $response["perfil_cargos"] = $perfil_cargos;
            $response["perfil_especialidades"] = $perfil_especialidades;
            $response["perfil_sedes"] = $perfil_sedes;
            $response["sedes"] = $sedes;
            $response["statusBD"] = true;
            $response["messageDB"] = "Datos cargados correctamente";            

            return $response;
        } elseif($request->method() == 'POST' && isset($request->pregrados)){

            DB::beginTransaction();
            try{
                $delete1 = DB::table('opbl_academico')
                                    ->where('persona_id', Auth::user()->persona_id)
                                    ->where('tipo', 1)
                                    ->delete();

                foreach($request->pregrados as $pregrado) {
                    $insert1 = DB::table('opbl_academico')
                                        ->insert([
                                            'persona_id' => Auth::user()->persona_id,
                                            'tipo' => 1,
                                            'grado_profesional' => mb_strtoupper($pregrado['grado_profesional']),
                                            'carrera' => mb_strtoupper($pregrado['carrera']),
                                            'universidad' => mb_strtoupper($pregrado['universidad']),
                                            'f_expedicion' => $pregrado['f_expedicion'],
                                        ]);
                }
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Documentos cargados correctamente.";
            }
            catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        } elseif($request->method() == 'POST' && isset($request->postgrados)){

            DB::beginTransaction();
            try{
                $delete2 = DB::table('opbl_academico')
                                    ->where('persona_id', Auth::user()->persona_id)
                                    ->where('tipo', 2)
                                    ->delete();
                foreach($request->postgrados as $postgrado) {
                    $insert2 = DB::table('opbl_academico')
                                    ->insert([
                                        'persona_id' => Auth::user()->persona_id,
                                        'tipo' => 2,
                                        'grado_profesional' => mb_strtoupper($postgrado['grado_profesional']),
                                        'carrera' => mb_strtoupper($postgrado['carrera']),
                                        'universidad' => mb_strtoupper($postgrado['universidad']),
                                        'estado' => mb_strtoupper($postgrado['estado']),
                                        'f_expedicion' => $postgrado['f_expedicion'],
                                    ]);
                }              
                
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Documentos cargados correctamente.";
            }
            catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
        } elseif($request->method() == 'POST' && isset($request->cargos)){
            DB::beginTransaction();
            try{
                $delete1 = DB::table('opbl_cargos')
                                    ->where('persona_id', Auth::user()->persona_id)
                                    ->delete();

                foreach($request->cargos as $cargo) {
                    $insert1 = DB::table('opbl_cargos')
                                    ->insert([
                                        'persona_id' => Auth::user()->persona_id,
                                        'op_cargo_id' => $cargo['id'],
                                    ]);
                }     
                
                $delete2 = DB::table('opbl_especialidades')
                                    ->where('persona_id', Auth::user()->persona_id)
                                    ->delete();

                foreach($request->especialidades as $especialidad) {
                    $insert2 = DB::table('opbl_especialidades')
                                    ->insert([
                                        'persona_id' => Auth::user()->persona_id,
                                        'especialidad_id' => $especialidad,
                                    ]);
                }    
                
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos registrados correctamente.";
            }
            catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
        } elseif($request->method() == 'POST' && isset($request->sedes)){
            DB::beginTransaction();
            try{
                $delete1 = DB::table('opbl_sedes')
                                    ->where('persona_id', Auth::user()->persona_id)
                                    ->delete();

                foreach($request->sedes as $sede) {

                    $consulta1 = DB::table('opbl_sedes')
                                    ->where('persona_id', Auth::user()->persona_id)
                                    ->where('ubigeo_id', $sede['ubigeo_id'])
                                    ->first();

                    if(!$consulta1){
                        $insert1 = DB::table('opbl_sedes')
                                    ->insert([
                                        'persona_id' => Auth::user()->persona_id,
                                        'ubigeo_id' => $sede['ubigeo_id'],
                                    ]);
                    }
                    
                }     
                
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos registrados correctamente.";
            }
            catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
        }
    }

    public function changePassword(Request $request){

        if (!(Hash::check($request->post('old_password'), Auth::user()->password))) {
            // The passwords matches
            $response["statusBD"] = false;
            $response["messageDB"] = "La contraseña actual no coincide";            

            return $response;
        }

        if(strcmp($request->get('old_password'), $request->get('new_password')) == 0){
            //Current password and new password are same
            $response["statusBD"] = false;
            $response["messageDB"] = "La nueva contraseña no puede ser la misma que la actual";            

            return $response;
        }

        $validatedData = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|string|min:6',
        ]);

        //Change Password
        $user = Auth::user();
        $user->password = bcrypt($request->post('new_password'));
        $user->save();

        $response["statusBD"] = true;
        $response["messageDB"] = "Cambio de contraseña exitoso";   
        return $response;
  
    }

    public function store (Request $request){

       
    }

    public function update(Request $request, $id){
    }

}