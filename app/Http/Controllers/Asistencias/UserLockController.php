<?php

namespace App\Http\Controllers\Asistencia;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;
use App\User;
use App\Asistencia;
use App\PDF_MC_Table;

class UserLockController extends Controller
{
    public function index(Request $request){

        $usuario = User::find(Auth::user()->id);
        $response=null;
        if($request->input('listarBloqueo')){
            if($request->input('search') && $request->search != null){
                $tabla =DB::table('asistencias_userlock')
                        ->select('asistencias_userlock.*', 
                        DB::raw("CONCAT(users.pers_paterno, ' ', users.pers_materno, ' ', users.pers_nombres) as persona")
                        )
                        ->where(function ($query) use ($request) {
                            $query->where(DB::raw("CONCAT(users.pers_paterno, users.pers_materno, users.pers_nombres)"), 'like','%'.$request->search.'%');
                        })
                        ->join('users', 'users.id', 'asistencias_userlock.user_id')
                        ->orderBy('asistencias_userlock.id','desc')
                        ->paginate(10);
            }else{
                $tabla = DB::table('asistencias_userlock')
                        ->select('asistencias_userlock.*', 
                        DB::raw("CONCAT(users.pers_paterno, ' ', users.pers_materno, ' ', users.pers_nombres) as persona")
                        )
                        ->join('users', 'users.id', 'asistencias_userlock.user_id')
                        ->orderBy('asistencias_userlock.id','asc')
                        ->paginate(10);
            }

                
            if ($tabla == NULL){ abort('404'); }

            $response['usuarios'] = $tabla;
            return $response;
        
        } 
        
    }

    public function store (Request $request){
        $response = null;
        $usuario = User::find(Auth::user()->id);
        $fecha_registro = time();

        DB::beginTransaction();
        try{

            $user_lock = DB::table('asistencias_userlock')
                            ->where('user_id', $request->id)
                            ->first();

            if($user_lock){
                if($user_lock->active_block){
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "El usuario ya se encuentra bloqueado";
                } else{
                    $update_lock = DB::table('asistencias_userlock')
                                            ->where('id', $user_lock->id)
                                            ->update([
                                                'active_block' => 1,
                                                'updated_at' => date('Ymd H:i:s',$fecha_registro),
                                                'user_update' => Auth::user()->id
                                            ]);
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Bloqueo de usuario actualizado correctamente";
                }
            } else{
                $insert_lock = DB::table('asistencias_userlock')
                                ->insert([
                                    'user_id' => $request->id,
                                    'user_update' =>  Auth::user()->id,
                                ]);
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Bloqueo de usuario registrado correctamente";
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
        }
        return $response;
    //}

        //return abort('403');
       
    }

    public function update(Request $request, $id){
        $response = null;
        $usuario = User::find(Auth::user()->id);
        $fecha_registro = time();

        DB::beginTransaction();
        try{
            $user_lock = DB::table('asistencias_userlock')
                            ->where('id', $request->id)
                            ->where('user_id', $request->user_id)
                            ->first();

            if($user_lock){
                $update_lock = DB::table('asistencias_userlock')
                                        ->where('id', $user_lock->id)
                                        ->update([
                                            'active_block' => !$request->active_block,
                                            'updated_at' => date('Ymd H:i:s',$fecha_registro),
                                            'user_update' => Auth::user()->id
                                        ]);
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Bloqueo de usuario actualizado correctamente";
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
        }
        return $response;
       
    }

    
}

