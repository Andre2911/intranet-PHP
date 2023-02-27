<?php

namespace App\Http\Controllers;

use App\Persona;
use Auth;
use App\User;
use App\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller{

    public function index(Request $request){
        $usuario = User::find(Auth::user()->id);
        $response=null;

        
        define('TIMEZONE', 'America/Lima');
        date_default_timezone_set(TIMEZONE);
        ini_set('date.timezone', 'America/Lima'); 
        

        if ( !$usuario->hasAnyRole('Webmaster|Asistencia') ){
            return abort('403');
        }

        if($request->input('init')){

                $time =  getdate();
                $response['time'] = $time;

                $dias = ['Domino', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes','Sábado'];
                $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio','julio', 'agosto', 'setiembre', 'octubre', 'noviembre', 'diciembre'];
                $response['fecha'] = $dias[$time['wday']] .', '.$time['mday'].' de '.$meses[$time['mon']-1].' de '.$time['year'];

                $fecha_consulta = time();

                $asistencia = DB::table('asistencias')
                                ->where('user_id', Auth::user()->id)
                                ->where('fecha', date('Y-m-d',$fecha_consulta) )
                                ->get();
                $response['asistencia'] = $asistencia;
                return $response;
        } else if($request->input('listar')){
            if($request->input('search') && $request->search != null){
                $tabla = Asistencia::where(function ($query) use ($request) {
                            $query->where('fecha','like','%'.$request->search.'%');
                        })
                        ->where('user_id', Auth::user()->id)
                        ->orderBy('id','asc')
                        ->paginate(config('global.numero_x_paginas'));
            }else{
                $tabla = Asistencia::where('user_id', Auth::user()->id)
                        ->orderBy('id','asc')
                        ->paginate(config('global.numero_x_paginas'));
            }

                
            if ($tabla == NULL){ abort('404'); }

            $response['asistencias'] = $tabla;
            return $response;
        } 
        else if($request->input('reporte')){
            if($request->input('search') && $request->search != null){
                $tabla = Asistencia::
                        select('asistencias.*', 'persona.numero_documento', DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"))
                        ->where(function ($query) use ($request) {
                            $query->where('fecha','like','%'.$request->search.'%')
                            ->orwhere( DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres)"),'like','%'.$request->search.'%');
                        })
                        ->join('users', 'users.id', 'asistencias.user_id')
                        ->join('persona', 'persona.id', 'users.persona_id')
                        ->orderBy('id','asc')
                        ->paginate(config('global.numero_x_paginas'));
            }else{
                $tabla = Asistencia::
                        select('asistencias.*', 'persona.numero_documento', DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"))
                        ->join('users', 'users.id', 'asistencias.user_id')
                        ->join('persona', 'persona.id', 'users.persona_id')
                        ->orderBy('asistencias.id','asc')
                        ->paginate(config('global.numero_x_paginas'));
            }

                
            if ($tabla == NULL){ abort('404'); }

            $response['asistencias'] = $tabla;
            return $response;
        } 
    }

    public function store (Request $request){
        $usuario = User::find(Auth::user()->id);

        if ($usuario->hasAnyRole('Webmaster|Asistencia') ){

            $fecha_registro = time();

            DB::beginTransaction();
            try{
                $existe = DB::table('asistencias')
                            ->where('user_id', Auth::user()->id)
                            ->where('fecha', date('Y-m-d',$fecha_registro))
                            ->get();

                if(sizeof($existe) == 0){
                    $registroAsistencia = DB::table('asistencias')
                                    ->insert([
                                        'fecha' =>  date('Y-m-d',$fecha_registro),
                                        'user_id' => Auth::user()->id,
                                        'hora_inicio' => date('H:i:s',$fecha_registro)
                                    ]);
                    DB::commit(); 
                    $response["hora_ingreso"] = date('H:i:s',$fecha_registro);
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Hora de Ingreso registrado con exito.";
                } else{
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Ya fue registrado su ingreso para el día de hoy, actualice la Página por favor.";
                }
                
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        }

        return abort('403');
       
    }

    public function update(Request $request, $id){
        $usuario = User::find(Auth::user()->id);

        if ($usuario->hasAnyRole('Webmaster|Asistencia') ){

            $fecha_registro = time();

            DB::beginTransaction();
            try{
                $existe = DB::table('asistencias')
                            ->where('id', $id)
                            ->where('user_id', Auth::user()->id)
                            ->where('fecha', date('Y-m-d',$fecha_registro))
                            ->get();

                if(sizeof($existe) == 1){
                    $registroAsistencia = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_fin' => date('H:i:s',$fecha_registro)
                                    ]);
                    DB::commit(); 
                    $response["hora_fin"] = date('H:i:s',$fecha_registro);
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Hora de salida registrado con exito.";
                } else{
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Hubo un problema al actualizar en la base de datos.";
                }
                
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        }

        return abort('403');
    }

}