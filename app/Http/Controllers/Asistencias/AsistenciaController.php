<?php

namespace App\Http\Controllers\Asistencias;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Auth;
use App\User;
use App\Persona;
use App\Asistencia;
use App\PDF_MC_Table;

class AsistenciaController extends Controller
{
    public function index(Request $request){

        $usuario = User::find(Auth::user()->id);
        $response=null;
        $persona = Persona::find(Auth::user()->persona_id);
        
        define('TIMEZONE', 'America/Lima');
        date_default_timezone_set(TIMEZONE);
        ini_set('date.timezone', 'America/Lima'); 
        

        /*if ( !$usuario->hasAnyRole('Webmaster|Asistencia') ){
            return abort('403');
        }*/
        if($request->input('getTime')){
            $time =  getdate();
            $response['time'] = $time;
            $fecha_consulta = date('Y-m-d',time());
            $fecha_actual = date("Y-m-d");
            $tiempo_actual = date("H:i:s");

            /**** HALLAR ULTIMA ASISTENCIA */
            $last_asistencia_tmp = null;
            if($time['wday'] == 1){  // SI HOY ES LUNES
                $fecha_consulta = date("Y-m-d", strtotime($fecha_actual."- 3 days")); 
                $feriado = DB::table('feriados')
                                ->where('fecha',  date('Y-m-d',$fecha_consulta))
                                ->first();
                if($feriado){
                    $fecha_consulta = date("Y-m-d", strtotime($fecha_actual."- 4 days"));             
                }
            } else{
                $fecha_consulta = date("Y-m-d", strtotime($fecha_actual."- 1 days")); 

                $feriado = DB::table('feriados')->where('fecha', date('Y-m-d', strtotime($fecha_consulta)))
                                ->first();
                if($feriado){
                    if( date('N', strtotime($fecha_consulta)) == 1){
                        $fecha_consulta = date("Y-m-d",strtotime($fecha_actual."- 4 days")); 
                    } else{
                        $fecha_consulta = date("Y-m-d",strtotime($fecha_actual."- 2 days")); 
                    }
                }
            }

            /**** FERIADOS */
            $last_asistencia = Asistencia::with('actividades')
                                ->where('user_id', $persona->numero_documento)
                                ->where('fecha',  $fecha_consulta)
                                ->first();
            
            if($last_asistencia){
                $response['last_date'] = $fecha_consulta;
                if($last_asistencia->tipo == 2 && $last_asistencia->hora_fin != null){
                    $response['last_time'] = $last_asistencia->hora_fin;
                } else{
                    if($last_asistencia->hora_fin2 != null){
                        if($last_asistencia->hora_fin2 < '13:00:00' ){
                            $response['last_time'] = '18:30:00'; // CONSIDERAMOS QUE ES PRESENCIAL TURNO B (TARDE)
                        } else{
                            $response['last_time'] = $last_asistencia->hora_fin2;
                        }
                    } else if($last_asistencia->hora_fin != null && $last_asistencia->hora_fin2 == null && sizeof($last_asistencia->actividades) > 0){
                        $response['last_time'] = $last_asistencia->hora_fin;
                    } else{
                        $response['last_time'] = date("H:i:s",strtotime($tiempo_actual."- 1 days")); 
                    }
                }
            } else{
                if($time['wday'] == 1){ // CONSULTAMOS VIERNES
                    $fecha_consulta = date("Y-m-d", strtotime($fecha_actual."- 3 days")); 
                    $feriado = DB::table('feriados')
                                ->where('fecha',  date('Y-m-d',$fecha_consulta))
                                ->first();
                    if($feriado){
                        $response['last_date'] = date("Y-m-d",strtotime($fecha_actual."- 4 days"));
                        $response['last_time'] = date("H:i:s",strtotime($tiempo_actual."- 4 days")); 
                    } else{
                        $response['last_date'] = date("Y-m-d",strtotime($fecha_actual."- 3 days"));
                        $response['last_time'] = date("H:i:s",strtotime($tiempo_actual."- 3 days")); 
                    }
                } else{
                    $fecha_consulta = date("Y-m-d", strtotime($fecha_actual."- 1 days")); 

                    $feriado = DB::table('feriados')->where('fecha', date('Y-m-d', strtotime($fecha_consulta)))
                                    ->first();
                    if($feriado){
                        if( date('N', strtotime($fecha_consulta)) == 1){
                            $response['last_date'] = date("Y-m-d",strtotime($fecha_actual."- 4 days")); 
                            $response['last_time'] = date("H:i:s",strtotime($tiempo_actual."- 4 days"));

                        } else{
                            $response['last_date'] = date("Y-m-d",strtotime($fecha_actual."- 2 days")); 
                            $response['last_time'] = date("H:i:s",strtotime($tiempo_actual."- 2 days"));
                        }
                    } else{
                        $response['last_date'] = date("Y-m-d",strtotime($fecha_actual."- 1 days")); 
                        $response['last_time'] = date("H:i:s",strtotime($tiempo_actual."- 1 days"));
                    }
                }
            }

            $response['last_asistencia'] = $last_asistencia;

            return $response;
        }
        elseif($request->input('init')){

            $time =  getdate();
            $response['time'] = $time;

            $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes','Sábado'];
            $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio','julio', 'agosto', 'setiembre', 'octubre', 'noviembre', 'diciembre'];
            $response['fecha'] = $dias[$time['wday']] .', '.$time['mday'].' de '.$meses[$time['mon']-1].' de '.$time['year'];

            $fecha_consulta = time();

            $asistencia = DB::table('asistencias')
                            ->where('user_id', $persona->numero_documento)
                            ->where('fecha', date('Y-m-d',$fecha_consulta) )
                            ->get();

            $config = DB::table('asistencias_config')
                                ->first();
                                    
            if($config){

                $ing_presencial_disponible = (  date('H:i:s',time()) >= $config->h_disp_ing_presencial && 
                                                date('H:i:s',time()) <= $config->h_salida_presencial)? true: false;
                $h_presencial_disponible = $config->h_disp_ing_presencial;

                /***** SUMAMOS 15 MIN A LA HORA DE INGRESO PRESENCIAL */

                $ing_autorizacion_disponible = (date('H:i:s',time()) >= date('H:i:s',(strtotime($config->h_ingreso_presencial) +(15*60))) )? true: false;

                $ing_remoto_disponible = (date('H:i:s',time()) >= $config->h_disp_ing_remoto)? true: false;
                $h_remoto_disponible = $config->h_disp_ing_remoto;
                $salida_disponible = (date('H:i:s',time()) >= $config->h_disp_salida_presencial)? true: false;

                /***** CALCULAMOS TIEMPO  */
                if(sizeof($asistencia) == 1){
                    $ini_remoto2_disponible = ((date('H:i:s',time()) >= $config->h_fin_refrigerio) && sizeof($asistencia) > 0 && $asistencia[0]->hora_fin != null)? true: false;

                    if($asistencia[0]->hora_inicio2 != null){
                        $tiempo_remoto_c = date('H:i:s', strtotime("00:00:00") + strtotime(date('H:i:s',$fecha_consulta)) - strtotime($asistencia[0]->hora_inicio2) );

                        list($horas, $minutos, $segundos) = explode(':', $tiempo_remoto_c);
    
                        $total_tiempo_remoto_c = ($horas * 60) + $minutos; 
    
                        $fin_remoto2_disponible = ($total_tiempo_remoto_c > intval($config->max_horas_diarias))? true:false;
    
                    } else{
                        $fin_remoto2_disponible = false;

                    }

                    if($asistencia[0]->tipo == 2){
                        $salida_disponible = (date('H:i:s',time()) >= $config->h_disp_salida_remoto)? true: false;
                    }
                    
                } else{
                    $ini_remoto2_disponible = false;
                    $fin_remoto2_disponible = false;
                }

                /****** FIN CONTROLES  */
 
                $refrigerio_disponible = (date('H:i:s',time()) >= $config->h_disp_ini_refrigerio)? true: false;
                //$refrigerio_disponible =false;
                $refrigerio_r_disponible = (date('H:i:s',time()) >= $config->h_disp_fin_refrigerio)? true: false;
                //$refrigerio_r_disponible = false;

                $magistrado = DB::table('users')
                                ->select('op_cargos.nombre_cargo')
                                ->where('users.id', Auth::user()->id)
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'users.persona_id')
                                ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->join('op_cargos', 'op_cargos.id', 'op_plazas_tit.op_cargo_id')
                                ->where('op_cargos.nombre_cargo', 'like','JUEZ%')
                                ->where('op_cargos.nombre_cargo', 'not like','%ASISTENTE%')
                                ->first();

                $juez = false;
                if($magistrado){
                    if(sizeof($asistencia) > 0 ){
                        $refrigerio_disponible = false;
                        $refrigerio_r_disponible = false;
                    }
                    $juez = true;
                }

                $plaza_activa = DB::table('persona_has_plaza_fun')
                                ->where('persona_id', Auth::user()->persona_id)
                                ->first();

                if($plaza_activa){
                    $programa_hoy = ['user'=>$persona->numero_documento, 'fecha' => $fecha_consulta];
                }
                else{
                    $programa_hoy = [];
                }

                $ips_internas = DB::table('sedes')
                                ->get();

                $in_red = false;
                $sede_ip_usuario = explode('.',$_SERVER['REMOTE_ADDR']);

                foreach ($ips_internas as $sede) {
                    $sede_ip_partes = explode('.', $sede->ip_sede);
                    if($sede_ip_partes[0] ==  $sede_ip_usuario[0] && $sede_ip_partes[1] ==  $sede_ip_usuario[1]){
                        $in_red = true;
                    }
                }

                $in_remoto = false;
                $ip_remota = '172.18.3.233';
                $sede_ip_remoto = explode('.', $ip_remota);

                if($sede_ip_remoto[0] ==  $sede_ip_usuario[0] && $sede_ip_remoto[1] ==  $sede_ip_usuario[1]){
                    //$in_red = true;
                    $in_remoto = true;
                }

                $ip_remota = '172.17.176.94';
                $sede_ip_remoto = explode('.', $ip_remota);

                if($sede_ip_remoto[0] ==  $sede_ip_usuario[0] && $sede_ip_remoto[1] ==  $sede_ip_usuario[1]  && $sede_ip_remoto[2] ==  $sede_ip_usuario[2]  && $sede_ip_remoto[3] ==  $sede_ip_usuario[3]){
                    $in_red = false;
                    $in_remoto = true;
                }

                $ip_remota = '172.17.176.95';
                $sede_ip_remoto = explode('.', $ip_remota);

                if($sede_ip_remoto[0] ==  $sede_ip_usuario[0] && $sede_ip_remoto[1] ==  $sede_ip_usuario[1]  && $sede_ip_remoto[2] ==  $sede_ip_usuario[2]  && $sede_ip_remoto[3] ==  $sede_ip_usuario[3]){
                    $in_red = false;
                    $in_remoto = true;
                }


                $ip_remota = '172.17.176.84';
                $sede_ip_remoto = explode('.', $ip_remota);

                if($sede_ip_remoto[0] ==  $sede_ip_usuario[0] && $sede_ip_remoto[1] ==  $sede_ip_usuario[1]  && $sede_ip_remoto[2] ==  $sede_ip_usuario[2]  && $sede_ip_remoto[3] ==  $sede_ip_usuario[3]){
                    $in_red = false;
                    $in_remoto = true;
                }


                $ip_remota = '172.17.176.85';
                $sede_ip_remoto = explode('.', $ip_remota);

                if($sede_ip_remoto[0] ==  $sede_ip_usuario[0] && $sede_ip_remoto[1] ==  $sede_ip_usuario[1]  && $sede_ip_remoto[2] ==  $sede_ip_usuario[2]  && $sede_ip_remoto[3] ==  $sede_ip_usuario[3]){
                    $in_red = false;
                    $in_remoto = true;
                }

                $ip_remota = '::1';

                if($ip_remota ==  $_SERVER['REMOTE_ADDR']){
                    $in_red = false;
                    $in_remoto = true;
                }

                $is_excepcional = false;
                /****** EXCEPCIONES */
                    /*** SEDES EXCEPCIONALES */
                    if($plaza_activa){
                        $personal = DB::table('users')
                                    ->select('op_cargos.nombre_cargo', 'op_plazas_tit.op_oficinaf_id')
                                    ->where('users.id', Auth::user()->id)
                                    ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'users.persona_id')
                                    ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                    ->join('op_cargos', 'op_cargos.id', 'op_plazas_tit.op_cargo_id')
                                    ->first();
    

                        $sede_excepcional = DB::table('asistencias_excepciones')
                                        ->where('op_oficina_id', $personal->op_oficinaf_id)
                                        ->where('activo', 1)
                                        ->first();

                        if($sede_excepcional){
                            $is_excepcional = true;
                        }
                    } else{
                        $is_excepcional = false;
                        $in_red =false;
                        $in_remoto= false;
                    }
                
                $horas_laborables = intval($config->max_horas_diarias);

                /*********** EXCEPCIONES DB */
                $horario_excepcional = DB::table('asistencias_excepciones_u')
                            ->where('user_id', $persona->numero_documento)
                            ->first();

                if($horario_excepcional){
                    $ing_presencial_disponible = (date('H:i:s',time()) >= $config->h_disp_ing_excepcional)? true: false;
                    $h_presencial_disponible = $config->h_disp_ing_excepcional;
                    $ing_autorizacion_disponible = (date('H:i:s',time()) >=  date('H:i:s',(strtotime($config->h_ingreso_excepcional) +(15*60))) )? true: false;
                    $salida_disponible = (date('H:i:s',time()) >= $config->h_disp_salida_excepcional)? true: false;
                    $horas_laborables = intval($config->max_horas_excepcional);
                    $config->h_ingreso_presencial = $config->h_ingreso_excepcional;
                    $config->h_salida_presencial = $config->h_salida_excepcional;
                }

                /******** METAS MENSUALES: CONSULTAMOS SI EL USUARIO TIENE JEFE SUPERVISOR */
                $salidaf_disponible = false;
                $registra_metas = false;
                $metas_mm = null;
                
                $usuario_metas = DB::table('users')
                                    ->select('username', 'p_externo.op_oficinaa_id as p_ext_a', 'p_propio.op_oficinaa_id as p_prop_b')
                                    ->join('persona', 'persona.id', 'users.persona_id')
                                    ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                    ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                    ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                    ->leftjoin('asistencias_parent as p_externo', 'p_externo.op_oficinaa_id', 'op_oficinas.id')
                                    ->leftjoin('asistencias_parent as p_propio', 'p_propio.op_oficinab_id', 'op_oficinas.id')
                                    ->where('users.id', Auth::user()->id)
                                    ->where('op_plazas_tit.jefe_Area', 0)
                                    ->first();


                if($usuario_metas && sizeof($asistencia) > 0){
                    if($usuario_metas->p_ext_a != null || $usuario_metas->p_prop_b != null){
                        
                        if($asistencia[0]->hora_inicio2 == null && $asistencia[0]->tipo == 1){
                            $tiempo_presencial = date('H:i:s', strtotime("00:00:00") + strtotime(date('H:i:s', $fecha_consulta)) - strtotime($asistencia[0]->hora_inicio) );
    
                            list($horas, $minutos, $segundos) = explode(':', $tiempo_presencial);
        
                            $total_tiempo_presencial = ($horas * 60) + $minutos; 
        
                            $salidaf_disponible = ($total_tiempo_presencial > $horas_laborables)? true:false;

                            if(sizeof($asistencia) > 0){
                                $asistencias_actividades = DB::table('asistencias_actividades')
                                                ->where('asistencia_id', $asistencia[0]->id)
                                                ->get();
                                if(sizeof($asistencias_actividades) > 0){
                                    $registra_metas = true;
                                }
                            }
                        }

                        $metas_mm = DB::table('asistencias_metas')
                                        ->select(DB::raw("CONCAT(asistencias_metas.actividad, CASE WHEN asistencias_metas.b_sij = 1 THEN ' [SIJ]' ELSE '' END) AS actividad"),
                                        'asistencias_metas.id as meta_id',
                                        'asistencias_metas.b_sij', 'asistencias_metas.url_meta', 
                                        DB::raw("0 as cantidad"),
                                        DB::raw("0 as tiempo"),
                                        )
                                        ->join('asistencias_meta_mes', 'asistencias_meta_mes.meta_id', 'asistencias_metas.id' )
                                        ->where('asistencias_meta_mes.user_id', $persona->numero_documento)
                                        ->where('asistencias_meta_mes.anio', $time['year'])
                                        ->where('asistencias_meta_mes.mes', ($time['mon']*1))
                                        ->get();
                    }
                }

                $feriado = DB::table('feriados')
                                ->where('fecha',  date('Y-m-d',$fecha_consulta))
                                ->first();

                /*********   AUTORIZACIÓN DE INGRESO */
                $aut_ingreso = DB::table('sbp_boletas')
                                ->select('autorizacion_ji')
                                ->where('idusuario',  $persona->numero_documento)
                                ->where('fecha_permiso',  date('Y-m-d',$fecha_consulta) )
                                ->where('regularizacion', 1)
                                ->first();
                /********* FIN DE AUTORIZACIÓN DE INGRESO */

                /********** TURNO A 07:45 - 12:45 */
                    //
                    $turnoA_remoto = false;
                    $turnoA_presencial = false;
                    
                    $turnoA_remoto = (  (date('H:i:s',time()) > '00:00:01' && date('H:i:s',time()) < '04:30:00') || 
                                        (date('H:i:s',time()) > '12:45:01' && date('H:i:s',time()) < '20:45:00')  )? true: false;

                    $turnoA_presencial = ( date('H:i:s',time()) > '07:00:00' && date('H:i:s',time()) < '12:44:59' )? true: false;
                    /***** SUMAMOS 15 MIN A LA HORA DE INGRESO PRESENCIAL */
                    $autorizacion_disponibleA = (date('H:i:s',time()) >= date('H:i:s',(strtotime('07:45:00') +(15*60))) )? true: false;

                /********* FIN TURNO A */
                /********** TURNO B 13:15 - 18:30*/

                    $turnoB_presencial = false;
                    $turnoB_remoto = false;

                    $turnoB_remoto = (  (date('H:i:s',time()) > '00:00:01' && date('H:i:s',time()) < '10:00:00') || 
                                        (date('H:i:s',time()) > '18:45:00' && date('H:i:s',time()) < '20:45:00')  )? true: false;

                    $turnoB_presencial = ( date('H:i:s',time()) > '13:15:00' && date('H:i:s',time()) < '18:30:00' )? true: false;
                    $autorizacion_disponibleB = (date('H:i:s',time()) >= date('H:i:s',(strtotime('13:45:00') +(15*60))) )? true: false;

                    $h_turno_B = array(
                                        'h_ingreso_presencial'=> '13:30:00', 
                                        'h_salida_presencial'=>'18:30:00',
                                        'h_disp_ing_presencial'=>'13:00:00',
                                    );

                    if($horario_excepcional){
                        $turnoB_remoto = (  (date('H:i:s',time()) > '00:00:01' && date('H:i:s',time()) < '10:00:00') || 
                                        (date('H:i:s',time()) > '19:15:00' && date('H:i:s',time()) < '20:45:00')  )? true: false;

                        $turnoB_presencial = ( date('H:i:s',time()) > '13:00:00' && date('H:i:s',time()) < '19:15:00' )? true: false;
                        $autorizacion_disponibleB = (date('H:i:s',time()) >= date('H:i:s',(strtotime('13:15:00') +(15*60))) )? true: false;

                        $h_turno_B = array(
                            'h_ingreso_presencial'=> '13:15:00', 
                            'h_salida_presencial'=>'19:15:00',
                            'h_disp_ing_presencial'=>'13:00:00',
                        );
                    }
                /********* FIN TURNO B */
                    if(sizeof($asistencia)){
                        if($asistencia[0]->hora_inicio2 != null){
                            $salida_compl_disponible = (date('H:i:s',time()) >= date('H:i:s',(strtotime($asistencia[0]->hora_inicio2) + (120*60))) )? true: false;
                        } else{
                            $salida_compl_disponible = false;
                        }
                    } else{
                        $salida_compl_disponible = false;
                    }


                $config->turnoB = $h_turno_B;


                $response['asistencia'] = $asistencia;
                $response['salida_disponible'] = $salida_disponible;
                $response['refrigerio_disponible'] = $refrigerio_disponible;
                $response['refrigerio_r_disponible'] = $refrigerio_r_disponible;
                $response['ing_remoto_disponible'] = $ing_remoto_disponible;
                $response['h_remoto_disponible'] = $h_remoto_disponible;
                $response['ing_presencial_disponible'] = $ing_presencial_disponible;
                $response['h_presencial_disponible'] = $h_presencial_disponible;
                $response['ing_autorizacion_disponible'] = $ing_autorizacion_disponible;
                $response['ini_remoto2_disponible'] = $ini_remoto2_disponible;
                $response['fin_remoto2_disponible'] = $fin_remoto2_disponible;
                $response['magistrado'] =  $juez;
                $response['programa_hoy'] = $programa_hoy;
                $response['in_red']     = false;//$in_red;
                $response['in_remoto']  = true;//$in_remoto;
                $response['is_excepcional'] = $is_excepcional;
                $response['feriado'] = $feriado;
                $response['aut_ingreso'] = $aut_ingreso;
                $response['horas_laborales'] = $horas_laborables;
                $response['config'] = $config;

                /******* TURNOS NOVIEMBRE */

                $response['turnoA_remoto'] = $turnoA_remoto;
                $response['turnoA_presencial'] = $turnoA_presencial;
                $response['turnoB_remoto'] = $turnoB_remoto;
                $response['turnoB_presencial'] = $turnoB_presencial;
                $response['autorizacion_disponibleA'] = $autorizacion_disponibleA;
                $response['autorizacion_disponibleB'] = $autorizacion_disponibleB;
                $response['fin_remoto2_disponible'] = $salida_compl_disponible;

                

                /********* METAS MENSUALES */

                $response['salidaf_disponible'] = $salidaf_disponible;
                $response['registra_metas'] = $registra_metas;
                $response['metas'] = $metas_mm;


                $response['ip_remota'] = $_SERVER['REMOTE_ADDR'];
                return $response;
            } else{
                $response['programa_hoy'] = [];
                /********* METAS MENSUALES */

                $response['ip_remota'] = $_SERVER['REMOTE_ADDR'];
                return $response;
            }
        } else if($request->input('anuncio')){
            $anuncio = DB::table('asistencias_comunicado')
                                ->first();

            $response['anuncio'] = $anuncio;
            return $response;

        } else if($request->input('listar')){
            if($request->input('search') && $request->search != null){
                $tabla = Asistencia::where(function ($query) use ($request) {
                            $query->where('fecha','like','%'.$request->search.'%');
                        })
                        ->where('user_id', Auth::user()->username)
                        ->orderBy('id','desc')
                        ->paginate($request->perpage);
            }else{
                if($request->input('perpage') == "all"){
                    $tabla = Asistencia::where('user_id', Auth::user()->username)
                    ->orderBy('id','desc')
                    ->paginate(5000);
                } else{
                    $tabla = Asistencia::where('user_id', Auth::user()->username)
                    ->orderBy('id','desc')
                    ->paginate($request->perpage);
                }
            }

                
            if ($tabla == NULL){ abort('404'); }

            $response['asistencias'] = $tabla;
            return $response;
        
        } else if($request->input('listarAllDay')){
            $fecha_registro = time();

            if($request->input('search') && $request->search != null){
                $tabla = Asistencia::select('asistencias.*', 
                            DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                            DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                            'op_plazas_tit.nombre_plaza'
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        //->where('fecha', date('Y-m-d',$fecha_registro))
                        ->where('fecha', $request->date)
                        ->where(DB::raw("CONCAT(persona.numero_documento, persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres)"),'like','%'.$request->search.'%')
                        ->orderBy('asistencias.hora_inicio','desc')
                        ->paginate(15);
            }else{
                if($request->input('perPage')){
                    $tabla = Asistencia::select('asistencias.*', 
                            DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                            DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                            'op_plazas_tit.nombre_plaza'
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        //->where('fecha', date('Y-m-d',$fecha_registro))
                        ->where('fecha', $request->date)
                        ->orderBy('asistencias.hora_inicio','desc')
                        ->paginate(5000);
                }else{

                    $tabla = Asistencia::select('asistencias.*', 
                        DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                        DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                            'op_plazas_tit.nombre_plaza'
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        //->where('fecha', date('Y-m-d',$fecha_registro))
                        ->where('fecha', $request->date)
                        ->orderBy('asistencias.hora_inicio','desc')
                        ->paginate(15);
                }
            }

                
            if ($tabla == NULL){ abort('404'); }

            $response['asistencias'] = $tabla;
            return $response;
        } else if($request->input('listarFechas')){
            $fecha_registro = time();

            if($request->input('search') && $request->search != null){
                $tabla = Asistencia::select('asistencias.*', 
                            DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                            DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                            'op_plazas_tit.nombre_plaza',
                            'op_regimen.regimen_base'
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        ->leftJoin('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                        //->where('fecha', date('Y-m-d',$fecha_registro))
                        ->where('fecha','>=', $request->dateini)
                        ->where('fecha','<=', $request->datefin)
                        ->where(DB::raw("CONCAT(persona.numero_documento, persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres)"),'like','%'.$request->search.'%')
                        ->orderBy('asistencias.id','desc')
                        ->paginate(15);
            } else{
                if($request->input('perPage')){
                    if($request->input('search2') && $request->search2 != null){
                        $tabla = Asistencia::select('asistencias.*', 
                                DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                                DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                                'op_plazas_tit.nombre_plaza',
                                'op_regimen.regimen_base'
                            )
                            ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                            ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                            ->leftJoin('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                            ->where('fecha','>=', $request->dateini)
                            ->where('fecha','<=', $request->datefin)
                            ->where(DB::raw("CONCAT(persona.numero_documento, persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres)"),'like','%'.$request->search2.'%')
                            ->orderBy('asistencias.id','desc')
                            ->paginate(100000);
                    } else{
                        $tabla = Asistencia::select('asistencias.*', 
                                DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                                DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                                'op_plazas_tit.nombre_plaza',
                                'op_regimen.regimen_base'
                            )
                            ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                            ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                            ->leftJoin('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                            //->where('fecha', date('Y-m-d',$fecha_registro))
                            ->where('fecha','>=', $request->dateini)
                            ->where('fecha','<=', $request->datefin)
                            ->orderBy('asistencias.id','desc')
                            ->paginate(100000);
                    }
                }else{

                    $tabla = Asistencia::select('asistencias.*', 
                        DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                        DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                        'op_plazas_tit.nombre_plaza',
                        'op_regimen.regimen_base'
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        ->leftJoin('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                        //->where('fecha', date('Y-m-d',$fecha_registro))
                        ->where('fecha','>=', $request->dateini)
                        ->where('fecha','<=', $request->datefin)
                        ->orderBy('asistencias.id','desc')
                        ->paginate(15);
                }
            }

                
            if ($tabla == NULL){ abort('404'); }

            $response['asistencias'] = $tabla;
            return $response;
        } else if($request->input('listarFechasSup')){
            $fecha_registro = time();
            $usuario = User::find(Auth::user()->id);
            $persona = Persona::find($usuario->persona_id);

            $oficina = DB::table('op_oficinas')
                        ->select('op_oficinas.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                        ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                        ->where('persona.id', $persona->id)
                        ->first();

            if($request->input('search') && $request->search != null){
                $tabla = Asistencia::select('asistencias.*', 
                            DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                            DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                            'op_plazas_tit.nombre_plaza'
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        ->leftjoin('asistencias_parent', 'asistencias_parent.op_oficinab_id', 'asistencias.op_oficina_id')
                        ->where('fecha','>=', $request->dateini)
                        ->where('fecha','<=', $request->datefin)
                        ->where(function ($query) use ($oficina) {
                            $query->where('asistencias_parent.op_oficinaa_id', $oficina->id);
                            $query->orWhere('asistencias.op_oficina_id', $oficina->id);
                        })
                        ->where(DB::raw("CONCAT(persona.numero_documento, persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres)"),'like','%'.$request->search.'%')
                        ->orderBy('asistencias.id','desc')
                        ->paginate(15);
            }else{
                if($request->input('perPage')){
                    if($request->input('search2') && $request->search2 != null){
                        $tabla = Asistencia::select('asistencias.*', 
                                DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                                DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                                'op_plazas_tit.nombre_plaza'
                            )
                            ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                            ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                            ->join('asistencias_parent', 'asistencias_parent.op_oficinab_id', 'asistencias.op_oficina_id')
                            ->where('fecha','>=', $request->dateini)
                            ->where('fecha','<=', $request->datefin)
                            ->where(DB::raw("CONCAT(persona.numero_documento, persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres)"),'like','%'.$request->search2.'%')
                            ->where('asistencias_parent.op_oficinaa_id ', $oficina->id)
                            ->orderBy('asistencias.id','desc')
                            ->paginate(100000);
                    } else{
                        $tabla = Asistencia::select('asistencias.*', 
                                DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                                DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                                'op_plazas_tit.nombre_plaza'
                            )
                            ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                            ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                            ->join('asistencias_parent', 'asistencias_parent.op_oficinab_id', 'asistencias.op_oficina_id')
                            ->where('fecha','>=', $request->dateini)
                            ->where('fecha','<=', $request->datefin)
                            ->where('asistencias_parent.op_oficinaa_id ', $oficina->id)
                            ->orderBy('asistencias.id','desc')
                            ->paginate(100000);
                    }
                }else{

                    $tabla = Asistencia::select('asistencias.*', 
                        DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                        DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                            'op_plazas_tit.nombre_plaza'
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        ->join('asistencias_parent', 'asistencias_parent.op_oficinab_id', 'asistencias.op_oficina_id')
                        //->where('fecha', date('Y-m-d',$fecha_registro))
                        ->where('fecha','>=', $request->dateini)
                        ->where('fecha','<=', $request->datefin)
                        ->where('asistencias_parent.op_oficinaa_id', $oficina->id)
                        ->orderBy('asistencias.id','desc')
                        ->paginate(15);
                }
            }

                
            if ($tabla == NULL){ abort('404'); }

            $response['asistencias'] = $tabla;
            return $response;
        } else if($request->input('listarActividades')){
            $tabla = Asistencia::select('asistencias.*', 
                        DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                        DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                            'asistencias_actividades.descripcion',
                            'asistencias_actividades.tiempo',
                            'asistencias_actividades.meta_cantidad',
                            'op_plazas_tit.nombre_plaza',
                            'op_regimen.regimen_base',
                            'view_op_oficinas_all.nombre_oficina',
                            'parent.nombre_oficina as nombre_parent',
                            'view_op_oficinas_all.distrito'
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        ->leftJoin('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                        ->join('asistencias_actividades', 'asistencias_actividades.asistencia_id', 'asistencias.id')
                        ->leftJoin('view_op_oficinas_all', 'view_op_oficinas_all.ID', 'asistencias.op_oficina_id')
                        ->leftJoin('view_op_oficinas_all as parent', 'parent.ID', 'view_op_oficinas_all.parent_id')
                        //->where('fecha', date('Y-m-d',$fecha_registro))
                        ->where('fecha','=', $request->dateini)
                        ->orderBy('asistencias.id','desc')
                        ->paginate(999999);

            if ($tabla == NULL){ abort('404'); }

            $response['data'] = $tabla;
            return $response;
        } else if($request->input('listarActividadesSup')){

            $usuario = User::find(Auth::user()->id);
            $persona = Persona::find($usuario->persona_id);

            $oficina = DB::table('op_oficinas')
                        ->select('op_oficinas.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                        ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                        ->where('persona.id', $persona->id)
                        ->first();

            $tabla = Asistencia::select('asistencias.*', 
                        DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                        DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                            'asistencias_actividades.descripcion',
                            'asistencias_actividades.tiempo',
                            'asistencias_actividades.meta_cantidad',
                            'op_plazas_tit.nombre_plaza',
                            'op_regimen.regimen_base',
                            'view_op_oficinas_all.nombre_oficina',
                            'parent.nombre_oficina as nombre_parent',
                            'view_op_oficinas_all.distrito'
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        ->leftJoin('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                        ->join('asistencias_actividades', 'asistencias_actividades.asistencia_id', 'asistencias.id')
                        ->join('asistencias_parent', 'asistencias_parent.op_oficinab_id', 'asistencias.op_oficina_id')
                        ->leftJoin('view_op_oficinas_all', 'view_op_oficinas_all.ID', 'asistencias.op_oficina_id')
                        ->leftJoin('view_op_oficinas_all as parent', 'parent.ID', 'view_op_oficinas_all.parent_id')
                        //->where('fecha', date('Y-m-d',$fecha_registro))
                        ->where('fecha','=', $request->dateini)
                        ->where('asistencias_parent.op_oficinaa_id', $oficina->id)
                        ->orderBy('asistencias.id','desc')
                        ->paginate(999999);

            if ($tabla == NULL){ abort('404'); }

            $response['data'] = $tabla;
            return $response;
        } else if($request->input('listarBloqueo')){
            if($request->input('search') && $request->search != null){
                $tabla = Asistencia::where(function ($query) use ($request) {
                            $query->where('fecha','like','%'.$request->search.'%');
                        })
                        ->where('user_id', Auth::user()->username)
                        ->orderBy('id','desc')
                        ->paginate(200);
            }else{
                $tabla = DB::table('asistencias_userlock')
                        ->select('asistencias_userlock.*', 
                        DB::raw("CONCAT(users.pers_paterno, ' ', users.pers_materno, ' ', users.pers_nombres) as persona")
                        )
                        ->join('users', 'users.username', 'asistencias_userlock.user_id')
                        ->orderBy('id','asc')
                        ->paginate(200);
            }

                
            if ($tabla == NULL){ abort('404'); }

            $response['asistencias'] = $tabla;
            return $response;
        
        } else if($request->input('excepciones')){
            $tabla = DB::table('asistencias_excepciones')
                        ->select('asistencias_excepciones.id',
                                'view_op_oficinas.nombre_oficina',
                                'view_op_oficinas.distrito'
                            )
                        ->join('view_op_oficinas', 'view_op_oficinas.ID', 'asistencias_excepciones.op_oficina_id')
                        ->where('asistencias_excepciones.activo', 1)
                        ->orderBy('asistencias_excepciones.id','desc')
                        ->paginate(5000);

            $response['asistencias'] = $tabla;
            return $response;
        } 
    }

    public function store (Request $request){
        $usuario = User::find(Auth::user()->id);

        //if ($usuario->hasAnyRole('Webmaster|Asistencia') ){

        $dia_registro = date("w");
        $dias = array("domingo","lunes","martes","miércoles","jueves","viernes","sábado");

        $config = DB::table('asistencias_config')
                    ->first();
        if($config){
            $hora_laboral = $config->h_ingreso_presencial;
            $ini_rec = $config->h_ini_refrigerio;
            $fin_rec = $config->h_fin_refrigerio;
            $fin_lab = $config->h_salida_presencial;
            $hora_receso = strval( date("H:i:s", strtotime("00:00:00") + strtotime($fin_rec) - strtotime($ini_rec)) );

            $fecha_registro = time();
            $ip =  $_SERVER['REMOTE_ADDR'];
            Log::info('Asistencia_i->[IP] '.$ip.'[USER] '.Auth::user()->username);
            DB::beginTransaction();
            try{
                    $persona = Persona::select('persona.*', 'op_plazas_tit.nombre_plaza', 'op_plazas_tit.id as plaza_id',
                                 'op_oficinas.id as oficina_id', 'op_oficinas.nombre_oficina')
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')            
                                ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                ->where('persona.id', Auth::user()->persona_id)
                                ->first();         

                    if(!$persona){
                        $response["statusBD"] = false;
                        $response["messageDB"] = "Su usuario no tiene asignada una PLAZA FÍSICA comuniquese con el responsable de asistencias en la Oficina de Personal.";
                        return $response;
                    }
                    $user_lock = DB::table('asistencias_userlock')
                        ->where('user_id', $persona->numero_documento)
                        ->where('active_block', 1)
                        ->get();

                	$existe = DB::table('asistencias')
	                            ->where('user_id', $persona->numero_documento)
	                            ->where('fecha', date('Y-m-d',$fecha_registro))
	                            ->get();

                    $feriado = DB::table('feriados')
                                ->where('fecha',  date('Y-m-d',$fecha_registro))
                                ->first();

                    if($dia_registro == 0 || $dia_registro == 6 || ($feriado != null && $feriado->asistencia_pre*1 == 0 && $feriado->asistencia_rem*1 == 0)){
                        $response["statusBD"] = false;
                        $response["messageDB"] = "No fue posible realizar el registro de asistencia por ser día no laborable o feriado";
                    } else if(sizeof($existe) == 0){
                        if($persona){
                            if($request->input('tipo')){
                                $registroAsistencia = DB::table('asistencias')
                                    ->insert([
                                        'fecha' =>  date('Ymd',$fecha_registro),
                                        'user_id' => $persona->numero_documento,
                                        'hora_inicio' => date('H:i:s',$fecha_registro),
                                        'tipo' => $request->tipo,
                                        'ip_registro' => $_SERVER['REMOTE_ADDR'],
                                        'op_plaza_id' => $persona->plaza_id,
                                        'op_oficina_id' => $persona->oficina_id
                                    ]);
                                $response["statusBD"] = true;
                                $response["messageDB"] = "Hora de Ingreso registrado con exito.";
        
                            } elseif($request->input('turno') && $request->input('tipot')){

                                if($request->tipot == 1){
                                    $registroAsistencia = DB::table('asistencias')
                                                            ->insert([
                                                                'fecha' =>  date('Ymd',$fecha_registro),
                                                                'user_id' => $persona->numero_documento,
                                                                'hora_inicio' => date('H:i:s',$fecha_registro),
                                                                'tipo' => $request->tipot,
                                                                'turno' => $request->turno,
                                                                'ip_registro' => $_SERVER['REMOTE_ADDR'],
                                                                'op_plaza_id' => $persona->plaza_id,
                                                                'op_oficina_id' => $persona->oficina_id
                                                            ]);
                                    $response["statusBD"] = true;
                                    $response["messageDB"] = "Hora de Ingreso registrado con exito.";
                                } else{
                                    $registroAsistencia = DB::table('asistencias')
                                                            ->insert([
                                                                'fecha' =>  date('Ymd',$fecha_registro),
                                                                'user_id' => $persona->numero_documento,
                                                                'hora_inicio2' => date('H:i:s',$fecha_registro),
                                                                'tipo' => 1,
                                                                'turno' => $request->turno,
                                                                'ip_registro2' => $_SERVER['REMOTE_ADDR'],
                                                                'op_plaza_id' => $persona->plaza_id,
                                                                'op_oficina_id' => $persona->oficina_id
                                                            ]);
                                    $response["statusBD"] = true;
                                    $response["messageDB"] = "Hora de Ingreso registrado con exito.";
                                }
                                
        
                            } elseif($request->input('autorizacion')){
                                /*********** AUTORIZACIÓN DE INGRESO */

                                    /*********** EXCEPCIONES DB */
                                    $horario_excepcional = DB::table('asistencias_excepciones_u')
                                                ->where('user_id', $persona->numero_documento)
                                                ->first();

                                    if($horario_excepcional){
                                        $hora_laboral = $config->h_ingreso_excepcional;
                                        $ini_rec = $config->h_ini_refrigerio;
                                        $fin_rec = $config->h_fin_refrigerio;
                                        $fin_lab = $config->h_salida_excepcional;
                                    }
                                
                                $sede = DB::table('persona')
                                            ->select('alias', 'sedes.cod_local')
                                            ->join('persona_has_plaza_fun','persona_has_plaza_fun.persona_id','persona.id')
                                            ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                            ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                            ->join('sedes', 'sedes.cod_local', 'op_oficinas.sede_id')
                                            ->where('persona.id', Auth::user()->persona_id)
                                            ->first();


                                $dataBoleta = DB::table('sbp_boletas')
                                        ->where('fecha_permiso', date('Ymd',$fecha_registro))
                                        ->where('estado', 1)
                                        ->where('idusuario', $persona->numero_documento)
                                        ->where('regularizacion', 1)
                                        ->first();
                                        
                                if ($dataBoleta) {
                                    $response["statusBD"] = false;
                                    $response["messageDB"] = "Ud. ya tiene una boleta de autorización de ingreso generada el día de hoy";
                                    return $response;   
                                }

                                $longitud = 10; 
                                $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"; 
                                $tmp=""; 
                                for($i=0;$i<$longitud;$i++){ 
                                    $tmp.=$caracteres[rand(0,strlen($caracteres)-1)]; 
                                } 

                                $codigo = str_replace(' ', '', (strtoupper($sede->alias))).$tmp; 

                                ini_set('date.timezone', 'America/Lima'); 

                                if(date('H:i:s',$fecha_registro) <= $ini_rec){
                                    $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime(date('H:i:s',$fecha_registro)) - strtotime($hora_laboral) ));
                                
                                } elseif (date('H:i:s',$fecha_registro) >  $ini_rec && date('H:i:s',$fecha_registro) <= $fin_rec) {
                                    $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($ini_rec) - strtotime($hora_laboral) ));
                                
                                } elseif (date('H:i:s',$fecha_registro) > $fin_rec && date('H:i:s',$fecha_registro) <= $fin_lab) {
                                    $total_hora_temp = (date("H:i:s",strtotime("00:00:00") + strtotime(date('H:i:s',$fecha_registro)) - strtotime($hora_laboral) ));
                                    $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($total_hora_temp) - strtotime($hora_receso) ));
                                
                                } elseif ($request->hora_ini >= $ini_rec && $hora_final<=$fin_rec) {
                                    $total_hora = 0;
                                
                                } else {
                                    $total_hora_temp = date("H:i:s", strtotime("00:00:00") + strtotime($fin_lab) - strtotime($hora_laboral));
                                    $total_hora = (date("H:i:s", strtotime("00:00:00") + strtotime($total_hora_temp) - strtotime($hora_receso) ));
                                }
                                
                                list($horas, $minutos, $segundos) = explode(':', $total_hora);
                                $total_permiso = ($horas * 60) + $minutos;

                                if (date('H:i:s',$fecha_registro) < $hora_laboral) {
                                    $response["statusBD"] = false;
                                    $response["messageDB"] = "Revisar la hora de ingreso, no puede ser anterior a la hora de ingreso.";
                                } else{
                                    $query = DB::table('sbp_boletas')
                                        ->insert([
                                            'idboleta' => $codigo,
                                            'idmotivo' => 4,
                                            'idusuario' => $persona->numero_documento,
                                            'detalle_motivo' => 'Autorización de ingreso',//$request->detalle_motivo,
                                            'fecha_permiso' => date('Ymd',$fecha_registro),
                                            'hora_ini' => $hora_laboral,
                                            'hora_fin' => date('H:i:s',$fecha_registro),
                                            'total_permiso' => $total_permiso,
                                            'estado_inicio' => 2,
                                            'regularizacion' => 1,
                                            'cod_local' => $sede->cod_local,
                                            'op_plaza_id' => $persona->plaza_id,
                                            'op_oficina_id' => $persona->oficina_id
                                        ]);

                                    $registroAsistencia = DB::table('asistencias')
                                                        ->insert([
                                                        'fecha' =>  date('Ymd',$fecha_registro),
                                                        'user_id' => $persona->numero_documento,
                                                        'hora_inicio' => date('H:i:s',$fecha_registro),
                                                        'tipo' => 1,
                                                        'ip_registro' => $_SERVER['REMOTE_ADDR'],
                                                        'op_plaza_id' => $persona->plaza_id,
                                                        'op_oficina_id' => $persona->oficina_id
                                                    ]);
                                    $response["statusBD"] = true;
                                    $response["messageDB"] = "Boleta de autorización de ingreso registrada con exito.";
                                }

                            } elseif($request->input('autorizacionT')){
                                /*********** AUTORIZACIÓN DE INGRESO POR TURNOS */

                                if($request->turno == 'A'){
                                    $hora_laboral = '07:45:00';
                                    $fin_lab = '12:45';
                                    
                                } else{
                                    $hora_laboral = '13:30:00';
                                    $fin_lab = '18:30:00';
                                }
                                
                                $sede = DB::table('persona')
                                            ->select('alias', 'sedes.cod_local')
                                            ->join('persona_has_plaza_fun','persona_has_plaza_fun.persona_id','persona.id')
                                            ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                            ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                            ->join('sedes', 'sedes.cod_local', 'op_oficinas.sede_id')
                                            ->where('persona.id', Auth::user()->persona_id)
                                            ->first();


                                $dataBoleta = DB::table('sbp_boletas')
                                        ->where('fecha_permiso', date('Ymd',$fecha_registro))
                                        ->where('estado', 1)
                                        ->where('idusuario', $persona->numero_documento)
                                        ->where('regularizacion', 1)
                                        ->first();
                                        
                                if ($dataBoleta) {
                                    $response["statusBD"] = false;
                                    $response["messageDB"] = "Ud. ya tiene una boleta de autorización de ingreso generada el día de hoy";
                                    return $response;   
                                }

                                $longitud = 10; 
                                $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"; 
                                $tmp=""; 
                                for($i=0;$i<$longitud;$i++){ 
                                    $tmp.=$caracteres[rand(0,strlen($caracteres)-1)]; 
                                } 

                                $codigo = str_replace(' ', '', (strtoupper($sede->alias))).$tmp; 

                                ini_set('date.timezone', 'America/Lima'); 

                                $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime(date('H:i:s',$fecha_registro)) - strtotime($hora_laboral) ));
                                
                                list($horas, $minutos, $segundos) = explode(':', $total_hora);
                                $total_permiso = ($horas * 60) + $minutos;

                                if (date('H:i:s',$fecha_registro) < $hora_laboral) {
                                    $response["statusBD"] = false;
                                    $response["messageDB"] = "Revisar la hora de ingreso, no puede ser anterior a la hora de ingreso.";
                                } elseif(date('H:i:s',$fecha_registro) > $fin_lab){
                                    $response["statusBD"] = false;
                                    $response["messageDB"] = "No puede marcar asistencia presencial por estar fuera del horario laboral";
                                } else{
                                    $query = DB::table('sbp_boletas')
                                        ->insert([
                                            'idboleta' => $codigo,
                                            'idmotivo' => 4,
                                            'idusuario' => $persona->numero_documento,
                                            'detalle_motivo' => 'Autorización de ingreso',//$request->detalle_motivo,
                                            'fecha_permiso' => date('Ymd',$fecha_registro),
                                            'hora_ini' => $hora_laboral,
                                            'hora_fin' => date('H:i:s',$fecha_registro),
                                            'total_permiso' => $total_permiso,
                                            'estado_inicio' => 2,
                                            'regularizacion' => 1,
                                            'cod_local' => $sede->cod_local,
                                            'op_plaza_id' => $persona->plaza_id,
                                            'op_oficina_id' => $persona->oficina_id
                                        ]);

                                    $registroAsistencia = DB::table('asistencias')
                                                        ->insert([
                                                        'fecha' =>  date('Ymd',$fecha_registro),
                                                        'user_id' => $persona->numero_documento,
                                                        'hora_inicio' => date('H:i:s',$fecha_registro),
                                                        'tipo' => 1,
                                                        'turno' => $request->turno,
                                                        'ip_registro' => $_SERVER['REMOTE_ADDR'],
                                                        'op_plaza_id' => $persona->plaza_id,
                                                        'op_oficina_id' => $persona->oficina_id
                                                    ]);
                                    $response["statusBD"] = true;
                                    $response["messageDB"] = "Boleta de autorización de ingreso registrada con exito.";
                                }

                            } else{
                                $response["statusBD"] = false;
                                $response["messageDB"] = "Error al actualizar en la Base de Datos, comuniquese con el administrador del sistema";
                            }
                            
                            $response["hora_ingreso"] = date('H:i:s', $fecha_registro);
                            
                            DB::commit(); 
                        } else{
                            DB::rollback();
                            $response["statusBD"] = false;
                            $response["messageDB"] = "Su usuario no tiene asignada una PLAZA FÍSICA comuniquese con el responsable de asistencias en la Oficina de Personal.";
                        }
	                    
                    } else{
	                    DB::rollback();
	                    $response["statusBD"] = false;
	                    $response["messageDB"] = "Ya fue registrado su ingreso para el día de hoy, actualice la Página por favor.";
	                }
                //}
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
        } else{
            $response["statusBD"] = false;
            $response["messageDB"] = "Falta configuración comuniquese con el administrador";
        }

        return $response;

        //}

        //return abort('403');
       
    }

    public function update(Request $request, $id){
        $usuario = User::find(Auth::user()->id);

        //if ($usuario->hasAnyRole('Webmaster|Asistencia') ){
        
            $fecha_registro = time();
            $persona = Persona::find(Auth::user()->persona_id);
            $response = null;
            $ip =  $_SERVER['REMOTE_ADDR'];
    
            DB::beginTransaction();
            try{
                $existe = DB::table('asistencias')
                            ->where('id', $id)
                            ->where('user_id', $persona->numero_documento)
                            ->where('fecha', date('Y-m-d',$fecha_registro))
                            ->whereNull('hora_fin')
                            ->get();
                            
                $presencial_c = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->where('user_id', $persona->numero_documento)
                                    ->where('fecha', date('Y-m-d',$fecha_registro))
                                    ->whereNotNull('hora_fin')
                                    ->get();       
                if(sizeof($existe) == 1){
                    if($request->input('refrigerio_ini')){
                        
                        $registroRefrigerio = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'refrigerio_inicio' => date('H:i:s',$fecha_registro)
                                    ]);
                        DB::commit(); 
                        Log::info('Asistencia_ri->[IP] '.$ip.'[USER] '.Auth::user()->username);

                        $response["statusBD"] = true;
                        $response["messageDB"] = "Inicio de refrigerio registrado con exito.";
                    } else if($request->input('refrigerio_fin')){
                        
                        $registroRefrigerio = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'refrigerio_fin' => date('H:i:s',$fecha_registro)
                                    ]);
                        DB::commit(); 
                        Log::info('Asistencia_rf->[IP] '.$ip.'[USER] '.Auth::user()->username);
                        $response["statusBD"] = true;
                        $response["messageDB"] = "Retorno de refrigerio registrado con exito.";
                    } else if($request->input('actividades')){
                        foreach ($request->actividades as $actividad) {
                            $registroActividad = DB::table('asistencias_actividades')
                                    ->insert([
                                        'asistencia_id' =>  $id,
                                        'descripcion' => $actividad['actividad'],
                                        'tiempo' => $actividad['tiempo'],
                                        'detalle' => (isset($actividad['detalle']))? $actividad['detalle'] : null
                                    ]);
                        }
                        
                        if($request->input('remoto2_fin')){
                            $registroAsistencia = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_fin2' => date('H:i:s',$fecha_registro)
                                    ]);
                            DB::commit(); 
                            Log::info('Asistencia_sc->[IP] '.$ip.'[USER] '.Auth::user()->username);

                        } else{
                            $registroAsistencia = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_fin' => date('H:i:s',$fecha_registro)
                                    ]);
                            DB::commit(); 
                            Log::info('Asistencia_s->[IP] '.$ip.'[USER] '.Auth::user()->username);

                        }
                        
                        $response["statusBD"] = true;
                        $response["messageDB"] = "Hora de salida registrado con exito.";

                    } elseif($request->input('ingresoT')){
                        $registroAsistencia = DB::table('asistencias')
                                                ->where('id', $id)
                                                ->update([
                                                    'hora_inicio' => date('H:i:s', $fecha_registro)
                                                ]);
                        DB::commit(); 
                        Log::info('Asistencia_i->[IP] '.$ip.'[USER] '.Auth::user()->username);

                        $response["statusBD"] = true;
                        $response["messageDB"] = "Inicio presencial registrado con exito.";
                    } elseif($request->input('autorizacionT')){
                        /*********** AUTORIZACIÓN DE INGRESO POR TURNOS */
                        if($request->turno == 'A'){
                            $hora_laboral = '07:45:00';
                            $fin_lab = '12:45';
                        } else{
                            $hora_laboral = '13:30:00';
                            $fin_lab = '18:30:00';
                        }
                        
                        $sede = DB::table('persona')
                                    ->select('alias', 'sedes.cod_local')
                                    ->join('persona_has_plaza_fun','persona_has_plaza_fun.persona_id','persona.id')
                                    ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                    ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                    ->join('sedes', 'sedes.cod_local', 'op_oficinas.sede_id')
                                    ->where('persona.id', Auth::user()->persona_id)
                                    ->first();


                        $dataBoleta = DB::table('sbp_boletas')
                                ->where('fecha_permiso', date('Ymd',$fecha_registro))
                                ->where('estado', 1)
                                ->where('idusuario', $persona->numero_documento)
                                ->where('regularizacion', 1)
                                ->first();
                                
                        if ($dataBoleta) {
                            $response["statusBD"] = false;
                            $response["messageDB"] = "Ud. ya tiene una boleta de autorización de ingreso generada el día de hoy";
                            return $response;   
                        }

                        $longitud = 10; 
                        $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"; 
                        $tmp=""; 
                        for($i=0;$i<$longitud;$i++){ 
                            $tmp.=$caracteres[rand(0,strlen($caracteres)-1)]; 
                        } 

                        $codigo = str_replace(' ', '', (strtoupper($sede->alias))).$tmp; 
                        ini_set('date.timezone', 'America/Lima'); 
                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime(date('H:i:s',$fecha_registro)) - strtotime($hora_laboral) ));
                        
                        list($horas, $minutos, $segundos) = explode(':', $total_hora);
                        $total_permiso = ($horas * 60) + $minutos;

                        if (date('H:i:s',$fecha_registro) < $hora_laboral) {
                            $response["statusBD"] = false;
                            $response["messageDB"] = "Revisar la hora de ingreso, no puede ser anterior a la hora de ingreso.";
                        } elseif(date('H:i:s',$fecha_registro) > $fin_lab){
                            $response["statusBD"] = false;
                            $response["messageDB"] = "No puede marcar asistencia presencial por estar fuera del horario laboral";
                        } else{
                            $query = DB::table('sbp_boletas')
                                ->insert([
                                    'idboleta' => $codigo,
                                    'idmotivo' => 4,
                                    'idusuario' => $persona->numero_documento,
                                    'detalle_motivo' => 'Autorización de ingreso',//$request->detalle_motivo,
                                    'fecha_permiso' => date('Ymd',$fecha_registro),
                                    'hora_ini' => $hora_laboral,
                                    'hora_fin' => date('H:i:s',$fecha_registro),
                                    'total_permiso' => $total_permiso,
                                    'estado_inicio' => 2,
                                    'regularizacion' => 1,
                                    'cod_local' => $sede->cod_local,
                                    'op_plaza_id' => $persona->plaza_id,
                                    'op_oficina_id' => $persona->oficina_id
                                ]);

                            $registroAsistencia = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_inicio' => date('H:i:s', $fecha_registro)
                                    ]);
                            DB::commit(); 
                            Log::info('Asistencia_i->[IP] '.$ip.'[USER] '.Auth::user()->username);
                            
                            $response["statusBD"] = true;
                            $response["messageDB"] = "Boleta de autorización de ingreso registrada con exito.";
                        }

                    } else{
                        if($request->input('remoto2_fin')){
                            $registroAsistencia = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_fin2' => date('H:i:s',$fecha_registro)
                                    ]);
                            DB::commit(); 
                            $response["statusBD"] = true;
                            $response["messageDB"] = "Hora de salida remota registrado con exito.";

                            Log::info('Asistencia_sc->[IP] '.$ip.'[USER] '.Auth::user()->username);

                        } elseif($existe[0]->tipo == 1 && $existe[0]->hora_inicio < '13:00:00'){
                            $registroAsistencia = DB::table('asistencias')
                                        ->where('id', $id)
                                        ->update([
                                            'hora_fin' => date('H:i:s',$fecha_registro)
                                        ]);
                            DB::commit();
                            $response["statusBD"] = true;
                            $response["messageDB"] = "Hora de salida registrado con exito.";

                        } elseif( date('H:i:s',$fecha_registro) >= '13:45:00'){
                            $registroAsistencia = DB::table('asistencias')
                                        ->where('id', $id)
                                        ->update([
                                            'hora_fin' => date('H:i:s',$fecha_registro)
                                        ]);
                            DB::commit();
                            $response["statusBD"] = true;
                            $response["messageDB"] = "Hora de salida registrado con exito.";
                        } else{
                            //$response["statusBD"] = false;
                            //$response["messageDB"] = "La hora de refrigerio por el día de hoy es automática, a partir del 02/12/2020 deberá marcar la salida y retorno del refrigerio";
                        }
                        Log::info('Asistencia_s->[IP] '.$ip.'[USER] '.Auth::user()->username);

                    }
                        
                    $response["hora_fin"] = date('H:i:s',$fecha_registro);
                    
                    
                } else if(sizeof($presencial_c) == 1 ){
                    if($request->input('remoto2_ini')){
                        $registroComplementarioIni = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_inicio2' => date('H:i:s',$fecha_registro),
                                        'ip_registro2' => $_SERVER['REMOTE_ADDR']
                                    ]);
                        DB::commit(); 
                        Log::info('Asistencia_i2->[IP] '.$ip.'[USER] '.Auth::user()->username);

                        $response["statusBD"] = true;
                        $response["remoto2_ini"] = date('H:i:s',$fecha_registro);
                        $response["messageDB"] = "Inicio del periodo complementario (REMOTO) registrado con exito.";
                    } else if($request->input('remoto2_fin') && $request->input('actividades')){

                        if($presencial_c[0]->hora_fin2 == null){
                            foreach ($request->actividades as $actividad) {
                                $registroActividad = DB::table('asistencias_actividades')
                                        ->insert([
                                            'asistencia_id' =>  $id,
                                            'descripcion' => $actividad['actividad'],
                                            'tiempo' => $actividad['tiempo'],
                                            'detalle' => (isset($actividad['detalle']))? $actividad['detalle'] : null
                                        ]);
                            }
    
                            $registroAsistencia = DB::table('asistencias')
                                        ->where('id', $id)
                                        ->update([
                                            'hora_fin2' => date('H:i:s',$fecha_registro)
                                        ]);
                            DB::commit(); 
                            Log::info('Asistencia_s2->[IP] '.$ip.'[USER] '.Auth::user()->username);

                            $response["statusBD"] = true;
                            $response["messageDB"] = "Hora de salida registrado con exito.";
                        } else{
                            $response["statusBD"] = false;
                            $response["messageDB"] = "Su hora de salida ya ha sido registrada anteriormente.";
                        }
                    }
                }else{
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Hubo un problema al actualizar en la base de datos.";
                }
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos, vuelca a intentarlo, de persistir, comuníquese con el administrador de sistemas";
            }
            return $response;
        //}

        //return abort('403');
    }

    public function getAsistencia(Request $request) {

        $asistencia = Asistencia::select('asistencias.*', 
                                DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                                DB::raw("IIF (asistencias.tipo = 1, 'Presencial', 'Remoto' ) tipo_trabajo"),
                                'op_plazas_tit.nombre_plaza',
                                'view_op_oficinas_all.nombre_oficina',
                                'view_op_oficinas_all.distrito'
                            )
                            ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                            ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                            ->leftJoin('view_op_oficinas_all', 'view_op_oficinas_all.id', 'asistencias.op_oficina_id')
                            ->where('asistencias.id', $request->asistencia['id'])
                            ->first();

        $boletas = DB::table('sbp_boletas')
                            ->select('sbp_boletas.*', 'sbp_motivos.idmotivo', 'sbp_motivos.codigo_motivo', 'sbp_motivos.descripcion', 'sbp_motivos.condicion',
                                DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno, ', ', persona.nombres, '[',persona.numero_documento,']') as nombre_completo"),
                                DB::raw("IIF (persona.numero_documento = '".$asistencia->user_id."', 0, 1 ) autorizacion"),
                                DB::raw("CONCAT(jefe_i.ap_paterno,' ',jefe_i.ap_materno) AS jefe_inm_aut_id")
                            
                            )
                            ->join('persona', 'persona.numero_documento', 'sbp_boletas.idusuario')
                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                            ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                            ->join('sbp_motivos', 'sbp_motivos.idmotivo', 'sbp_boletas.idmotivo')
                            ->leftJoin('persona as jefe_i', 'jefe_i.numero_documento', 'sbp_boletas.jefe_inm_aut_id')
                            ->where('persona.numero_documento', $asistencia->user_id)
                            ->where('fecha_permiso', $asistencia->fecha)
                            ->where('estado', 1)
                            ->orderBY('created_at', 'desc')
                            ->get();
        
        $actividades = DB::table('asistencias_actividades')
                            ->where('asistencia_id', $asistencia->id)
                            ->get();

        $response["data"] = $asistencia;
        $response["boletas"] = $boletas;
        $response["actividades"] = $actividades;
        $response["statusBD"] = true;
        $response["messageBD"] = "Datos Cargados correctamente";
        return $response;
    }

    public function updateAsistencia(Request $request) {

        $asistencia = Asistencia::where('asistencias.id', $request->asistencia['id'])
                            ->first();
        
        $persona = Persona::find(Auth::user()->persona_id);
        $fecha_registro = time();

        if($asistencia){
            DB::beginTransaction();
            $asistencia2 = array('tipo' => $request->asistencia['tipo']);;
            try{
                $asistencia2['tipo'] = ($asistencia->tipo != $request->asistencia['tipo'])? $request->asistencia['tipo']*1 : $asistencia->tipo*1;
                $asistencia2['hora_inicio'] = ($asistencia->hora_inicio != $request->asistencia['hora_inicio'])? $request->asistencia['hora_inicio'] : $asistencia->hora_inicio;
                $asistencia2['refrigerio_inicio'] = ($asistencia->refrigerio_inicio != $request->asistencia['refrigerio_inicio'])? $request->asistencia['refrigerio_inicio'] : $asistencia->refrigerio_inicio;
                $asistencia2['refrigerio_fin'] = ($asistencia->refrigerio_fin != $request->asistencia['refrigerio_fin'])? $request->asistencia['refrigerio_fin'] : $asistencia->refrigerio_fin;
                $asistencia2['hora_fin'] = ($asistencia->hora_fin != $request->asistencia['hora_fin'])? $request->asistencia['hora_fin'] : $asistencia->hora_fin;
                $asistencia2['hora_inicio2'] = ($asistencia->hora_inicio2 != $request->asistencia['hora_inicio2'])? $request->asistencia['hora_inicio2'] : $asistencia->hora_inicio2;
                $asistencia2['hora_fin2'] = ($asistencia->hora_fin2 != $request->asistencia['hora_fin2'])? $request->asistencia['hora_fin2'] : $asistencia->hora_fin2;
                $asistencia2['turno'] = $request->asistencia['turno'];
                $asistencia2['observacion'] = $request->asistencia['observacion'];
                $asistencia2['user_update'] = $persona->numero_documento;
                $asistencia2['updated_at'] =  date('Ymd H:i:s',$fecha_registro);

                $update = Asistencia::where('asistencias.id', $request->asistencia['id'])
                                    ->update($asistencia2);
                DB::commit();
                $response["statusBD"] = true;
                $response["messageBD"] = "Datos actualizados correctamente";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos, vuelca a intentarlo, de persistir, comuníquese con el administrador de sistemas";
            }
        }
        return $response;
    }

    public function createAsistencia(Request $request) {

        $asistencia = Asistencia::where('asistencias.user_id', $request->asistencia['persona_id'])
                            ->where('asistencias.fecha',  $request->asistencia['f_inicio'])
                            ->first();
        
        $persona = Persona::find(Auth::user()->persona_id);
        $fecha_registro = time();

        if(!$asistencia){
            DB::beginTransaction();
            $asistencia2 = array('tipo' => $request->asistencia['tipo']*1);
            try{
                $asistencia2['tipo'] = $request->asistencia['tipo']*1;
                $asistencia2['fecha'] = $request->asistencia['f_inicio'] ;
                $asistencia2['user_id'] = $request->asistencia['persona_id'] ;
                $asistencia2['hora_inicio'] = (isset($request->asistencia['hora_inicio']))? $request->asistencia['hora_inicio'] : null;
                $asistencia2['refrigerio_inicio'] = isset($request->asistencia['refrigerio_inicio'])? $request->asistencia['refrigerio_inicio'] : null;
                $asistencia2['refrigerio_fin'] = isset($request->asistencia['refrigerio_fin'])? $request->asistencia['refrigerio_fin'] : null;
                $asistencia2['hora_fin'] = isset($request->asistencia['hora_fin'])? $request->asistencia['hora_fin'] : null;
                $asistencia2['hora_inicio2'] = isset($request->asistencia['hora_inicio2'])? $request->asistencia['hora_inicio2'] : null;
                $asistencia2['hora_fin2'] = isset($request->asistencia['hora_fin2'])? $request->asistencia['hora_fin2'] : null;
                $asistencia2['turno'] = isset($request->asistencia['turno'])? $request->asistencia['turno'] : null;
                $asistencia2['observacion'] = $request->asistencia['observacion'];
                $asistencia2['ip_registro'] = '-';
                $asistencia2['user_update'] = $persona->numero_documento;
                $asistencia2['updated_at'] =  date('Ymd H:i:s',$fecha_registro);

                $update = Asistencia::create($asistencia2);
                DB::commit();
                $response["statusBD"] = true;
                $response["messageBD"] = "Datos actualizados correctamente";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageBD"] = "Error al actualizar en la Base de Datos, vuelca a intentarlo, de persistir, comuníquese con el administrador de sistemas".$e;
            }
        } else{
            $response["statusBD"] = false;
            $response["messageBD"] = "El usuario ya cuenta con asistencia para dicha fecha";
        }
        return $response;
    }

    public function salidaMetas(Request $request, $id){
        $usuario = User::find(Auth::user()->id);

        $time =  getdate();
        $fecha_registro = time();
        $persona = Persona::find(Auth::user()->persona_id);
        $response = null;
        $ip =  $_SERVER['REMOTE_ADDR'];

        DB::beginTransaction();
        try{

            $asistencia = DB::table('asistencias')
                            ->where('id', $id)
                            ->where('user_id', $persona->numero_documento)
                            ->where('fecha', date('Y-m-d',$fecha_registro))
                            ->whereNull('hora_fin')
                            ->whereNull('hora_inicio2')
                            ->get();
                            
            $presencial_c = DB::table('asistencias')
                                ->where('id', $id)
                                ->where('user_id', $persona->numero_documento)
                                ->where('fecha', date('Y-m-d',$fecha_registro))
                                ->whereNotNull('hora_inicio2')
                                ->get();    

            /********* SALIDA PRESENCIAL y REMOTO PURO */
            if(sizeof($asistencia) == 1){
                if(sizeof($request->actividades) == 0){

                    /********************* NO REGISTRA METAS Y MARCA SALIDA PRESENCIAL PASADO 480 MIN */
                    if($asistencia[0]->tipo == 1  && $asistencia[0]->hora_inicio < '13:00:00'){

                        $registroAsistencia = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_fin' => date('H:i:s',$fecha_registro)
                                    ]);
                        DB::commit();
                        Log::info('Asistencia_s_presencial->[IP] '.$ip.'[USER] '.Auth::user()->username);

                        $response["statusBD"] = true;
                        $response["messageDB"] = "Hora de salida registrado con exito.";
                    }
                    /********************* NO REGISTRA METAS Y MARCA SALIDA REMOTA PASADO 480 MIN */
                    elseif($asistencia[0]->tipo == 2){// && $asistencia[0]->hora_inicio < '13:00:00'){
                        $registroAsistencia = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_fin' => date('H:i:s',$fecha_registro)
                                    ]);
                        DB::commit();
                        Log::info('Asistencia_s_remoto->[IP] '.$ip.'[USER] '.Auth::user()->username);

                        $response["statusBD"] = true;
                        $response["messageDB"] = "Hora de salida registrada, no se registraron metas";
                    }
                }
                else{
                        /************* REGISTRA METAS Y MARCA SALIDA PRESENCIAL O REMOTO */
                        foreach ($request->actividades as $actividad) {
                            if(isset($actividad['cantidad']) && $actividad['cantidad']*1 > 0){
                                $registroActividad = DB::table('asistencias_actividades')
                                    ->insert([
                                        'asistencia_id' =>  $id,
                                        'descripcion' => $actividad['actividad'],
                                        'tiempo' => ($actividad['tiempo'] == '')? 0: $actividad['tiempo'],
                                        'meta_id' => $actividad['meta_id'],
                                        'meta_anio' => $time['year'],
                                        'meta_mes' => $time['mon'],
                                        'meta_cantidad' => $actividad['cantidad'],
                                        'detalle' => (isset($actividad['detalle']))? $actividad['detalle'] : null
                                    ]);
                            }
                        }

                        $registroAsistencia = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_fin' => date('H:i:s',$fecha_registro)
                                    ]);
                        DB::commit(); 
                        Log::info('Asistencia_s_remoto->[IP] '.$ip.'[USER] '.Auth::user()->username);
                        $response["statusBD"] = true;
                        $response["messageDB"] = "Hora de salida registrado con exito.";

                }
               // dd(sizeof($request->actividades));
            }

            /********** SALIDA COMPLEMENTARIO REMOTO */
            if(sizeof($presencial_c) == 1){
                if($request->remoto2_fin && $request->input('actividades') && sizeof($request->actividades) == 0){
                    $registroAsistencia = DB::table('asistencias')
                                        ->where('id', $id)
                                        ->update([
                                            'hora_fin2' => date('H:i:s',$fecha_registro)
                                        ]);
                    DB::commit(); 
                    Log::info('Asistencia_s_complementario->[IP] '.$ip.'[USER] '.Auth::user()->username);
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Hora de salida registrado con exito.";
                } else if($request->remoto2_fin && $request->input('actividades') && sizeof($request->actividades) > 0){
                    foreach ($request->actividades as $actividad) {
                        if(isset($actividad['cantidad']) && $actividad['cantidad']*1 > 0){

                            $registroActividad = DB::table('asistencias_actividades')
                                    ->insert([
                                        'asistencia_id' =>  $id,
                                        'descripcion' => $actividad['actividad'],
                                        'tiempo' => 0,
                                        'meta_id' => $actividad['meta_id'],
                                        'meta_anio' => $time['year'],
                                        'meta_mes' => $time['mon'],
                                        'meta_cantidad' => $actividad['cantidad'],
                                        'detalle' => (isset($actividad['detalle']))? $actividad['detalle'] : null
                                    ]);
                        }
                    }
                    $registroAsistencia = DB::table('asistencias')
                                    ->where('id', $id)
                                    ->update([
                                        'hora_fin2' => date('H:i:s',$fecha_registro)
                                    ]);
                    DB::commit(); 
                    Log::info('Asistencia_s_complementario->[IP] '.$ip.'[USER] '.Auth::user()->username);
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Hora de salida registrado con exito.";

                }

            }
            
        } catch (\Exception $e) {
            DB::rollback();
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al actualizar en la Base de Datos, vuelca a intentarlo, de persistir, comuníquese con el administrador de sistemas".$e;
        }
        return $response;
    }

    public function reporteDiaPDF($fecha)
    {        
        
        $fecha_registro = time();

        $dataAsistencia = Asistencia::select('asistencias.*', 
                    DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona")
                )
                ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                ->where('fecha', $fecha)
                ->orderBy('asistencias.id','asc')
                ->get();
        
        $avatarUrl = public_path().'/image/logo.png';
        $arrContextOptions=array(
                        "ssl"=>array(
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        ),
                    );
        $type = pathinfo($avatarUrl, PATHINFO_EXTENSION);
        $avatarData = file_get_contents($avatarUrl, false, stream_context_create($arrContextOptions));
        $avatarBase64Data = base64_encode($avatarData);
        $logo = 'data:image/' . $type . ';base64,' . $avatarBase64Data;


        $titulo = 'CONTROL DE ASISTENCIA DEL DÍA '.$fecha;

        date_default_timezone_set('UTC');
        date_default_timezone_set('America/Lima');
        ini_set('date.timezone', 'America/Lima'); 
        setlocale(LC_TIME, 'es_ES.UTF-8');                
            
        $fecha =  date('l, d M Y H:i:s');

        $view =  \View::make('asistencia.reporteAsistenciaDia', compact('dataAsistencia', 'logo', 'fecha', 'titulo'))->render();
        $pdf = \App::make('dompdf.wrapper');
        $pdf->setOptions(['dpi' => 120, 'defaultPaperSize', 'A4']);
        //$pdf->loadHTML($view)->setPaper('a4', 'landscape');
        $pdf->loadHTML($view)->setPaper('a4', 'portrait');
        //$pdf->loadHTML($view)->setPaper('a5', 'landscape');
        return $pdf->stream('reporte');
        //return view('pdf.boletapermiso',compact('dataBoleta', 'logo', 'fecha', 'motivos', 'codigoQR'));

        //return $pdf->download('listado.pdf');
        
    }


    public function reporteInformeDia2($fecha)
    { 
        
        //header("Content-Disposition:attachment;filename='downloaded.pdf'");

        header('Content-type: application/pdf');

        $dataAsistencia = Asistencia::select(
                    'asistencias.*', 
                    'op_plazas_tit.nombre_plaza', 
                    'view_op_oficinas.nombre_oficina as nombre_oficina', 
                    'parent.nombre_oficina as parent_oficina',
                    'view_op_oficinas.distrito',

                    'asist_plaza.nombre_plaza as asist_nombre_plaza', 
                    'asist_ofi.nombre_oficina as asist_nombre_oficina', 
                    'asist_parent.nombre_oficina as asist_parent_oficina',
                    'asist_ofi.distrito as asist_distrito',

                    'persona.numero_documento as DNI',
                    DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona")
                )
                ->with('actividades')
                ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                ->leftjoin('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                
                ->leftjoin('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                ->leftjoin('view_op_oficinas', 'view_op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                ->leftjoin('op_oficinas as parent', 'parent.id', 'view_op_oficinas.parent_id')
                
                ->leftjoin('op_plazas_tit as asist_plaza', 'asist_plaza.id', 'asistencias.op_plaza_id')
                ->leftjoin('view_op_oficinas as asist_ofi', 'asist_ofi.id', 'asistencias.op_oficina_id')
                ->leftjoin('op_oficinas as asist_parent', 'asist_parent.id', 'asist_ofi.parent_id')
                
                ->where('fecha', $fecha)
                //->where('user_id', '46324743')
                //->where('asistencias.tipo', 2)
                ->where(function ($query)  {
                    $query->whereNotNULL('asistencias.hora_fin');
                    $query->orWhereNotNULL('asistencias.hora_fin2');
                })
                ->orderBy('persona.ap_paterno','asc')
                ->get();

        //return $dataAsistencia;


        $pdf = new PDF_MC_Table();
        // T�tulos de las columnas
        $pdf->SetFont('Arial','',14);

        foreach ($dataAsistencia as $asistencia) {
            if(sizeof(json_decode($asistencia->actividades)) > 0 && ($asistencia->tipo ==2 || $asistencia->hora_fin2 != null)) {
                $pdf->AddPage();
                // Logo
                $pdf->Cell(80);
                $pdf->Image(public_path().'../../public/image/logo.png',95,8,20);
                // Arial bold 15
                $pdf->Ln(20);
                $pdf->SetFont('Arial','',15);
                // Movernos a la derecha
                $pdf->Cell(50);
                // T�tulo
                $pdf->Cell(30,10,'Corte Superior de Justicia de Arequipa');
                // Salto de l�nea
                $pdf->Ln(8);
                $pdf->SetFont('Arial','',14);
                $pdf->Cell(70);
                $pdf->Cell(40,10,'Oficina de Personal');
                // Salto de l�nea
                $pdf->Ln(10);
                $pdf->SetFont('Arial','I',14);
                $pdf->Cell(40);
                $pdf->Cell(20,10,utf8_decode('"Año del Fortalecimiento de la Soberanía Nacional"'));

                $pdf->Ln(20);
                $pdf->SetFont('Arial','B',15);
                $pdf->Cell(5);
                $pdf->Cell(30,10,utf8_decode('INFORME DE CUMPLIMIENTO DE LABOR EFECTIVA - TRABAJO REMOTO'));


                $pdf->Ln(16);
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(5);
                $pdf->Cell(40,10,utf8_decode('DEPENDENCIA : '));
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(5);
                if($asistencia->asist_nombre_plaza){
                    if($asistencia->asist_parent_oficina != $asistencia->asist_distrito){
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->asist_parent_oficina.' - '.$asistencia->asist_distrito)),1,0,'L');
                    } else{
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->asist_nombre_oficina.' - '.$asistencia->asist_distrito)),1,0,'L');
                    }
                } else{
                    if($asistencia->parent_oficina != $asistencia->distrito){
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->parent_oficina.' - '.$asistencia->distrito)),1,0,'L');
                    } else{
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->nombre_oficina.' - '.$asistencia->distrito)),1,0,'L');
                    }
                }

                $pdf->Ln(12);
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(5);
                $pdf->Cell(40,10,utf8_decode('AREA/SUBAREA : '));
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(5);
                if($asistencia->asist_nombre_plaza){
                    if($asistencia->asist_parent_oficina != $asistencia->asist_distrito){
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->asist_nombre_oficina)),1,0,'L');
                    } else{
                        $pdf->Cell(140,10,utf8_decode(''),1,0,'L');
                    }
                } else{
                    if($asistencia->parent_oficina != $asistencia->distrito){
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->nombre_oficina)),1,0,'L');
                    } else{
                        $pdf->Cell(140,10,utf8_decode(''),1,0,'L');
                    }
                }

                $pdf->Ln(12);
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(5);
                $pdf->Cell(55,10,utf8_decode('APELLIDOS Y NOMBRES : '));
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(5);
                $pdf->Cell(125,10,utf8_decode($asistencia->persona),1,0,'L');

                $pdf->Ln(12);
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(5);
                $pdf->Cell(40,10,utf8_decode('FECHA : '));
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(5);
                $pdf->Cell(50,10,$fecha,1,0,'L');

                $header = array(utf8_decode('N°'), 'ACTIVIDADES O TAREAS', 'TIEMPO (EN MINUTOS)');
                
                $pdf->Ln(16);
                // Anchuras de las columnas
                $w = array(15, 145, 30);
                $i = 1;
                $tiempoTotal = 0;

                $pdf->SetWidths($w);

                $pdf->Row($header);
                    

                $concat_metas = '';
                foreach(json_decode($asistencia->actividades) as $actividad)
                {
                    if($concat_metas != '' && $asistencia->hora_fin2 != null){
                        $concat_metas .= ' - ';
                    }
                    if($actividad->tiempo == 0 && $asistencia->hora_fin2 != null){

                        $concat_metas .= '('.$actividad->meta_cantidad.') '.$actividad->descripcion;
                    } else{
                        if($actividad->meta_cantidad != null && $actividad->meta_cantidad > 0){
                            if($actividad->detalle != null){
                                $pdf->Row(array($i++,  '('.$actividad->meta_cantidad.') '.utf8_decode($actividad->descripcion. ': '.$actividad->detalle), $actividad->tiempo));
                            } else{
                                $pdf->Row(array($i++,  '('.$actividad->meta_cantidad.') '.utf8_decode($actividad->descripcion), $actividad->tiempo));
                            }
                        } else{
                            if($actividad->detalle != null){
                                $pdf->Row(array($i++, utf8_decode($actividad->descripcion. ': '.$actividad->detalle), $actividad->tiempo));
                            } else{
                                $pdf->Row(array($i++, utf8_decode($actividad->descripcion), $actividad->tiempo));
                            }
                        }
                        $tiempoTotal += $actividad->tiempo;
                    }
                }
                if($concat_metas != ''){
                    $pdf->Row(array($i++, utf8_decode($concat_metas), 120));
                    $tiempoTotal += 120;

                }

                // L�nea de cierre
                $pdf->RowB(array('','TOTAL TIEMPO LABORADO EN EL DIA', $tiempoTotal), 10);

                $pdf->SetY(-30);
                // Arial italic 8
                $pdf->SetFont('Arial','I',8);
                // N�mero de p�gina
                //$pdf->Cell(0,10,'Page '.$pdf->PageNo().'/{nb}',0,0,'C');
                $pdf->Cell(20);
                $pdf->Image(public_path().'../../public/image/footer.png',15,260,180);

            }
            
        }
        
        $pdf->Output('D','Informe '.$fecha.'.pdf',false);

        
    }

    public function reporteUsuario($usuario, $fechaini, $fechafin)
    { 
        
        //header("Content-Disposition:attachment;filename='downloaded.pdf'");

        header('Content-type: application/pdf');

        $dataAsistencia = Asistencia::select(
                    'asistencias.*', 
                    'op_plazas_tit.nombre_plaza', 
                    'view_op_oficinas.nombre_oficina as nombre_oficina', 
                    'parent.nombre_oficina as parent_oficina',
                    'view_op_oficinas.distrito',

                    'asist_plaza.nombre_plaza as asist_nombre_plaza', 
                    'asist_ofi.nombre_oficina as asist_nombre_oficina', 
                    'asist_parent.nombre_oficina as asist_parent_oficina',
                    'asist_ofi.distrito as asist_distrito',
                    
                    'persona.numero_documento as DNI',
                    DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona")
                )
                ->with('actividades')
                ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                ->leftjoin('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                
                ->leftjoin('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                ->leftjoin('view_op_oficinas', 'view_op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                ->leftjoin('op_oficinas as parent', 'parent.id', 'view_op_oficinas.parent_id')
                
                ->leftjoin('op_plazas_tit as asist_plaza', 'asist_plaza.id', 'asistencias.op_plaza_id')
                ->leftjoin('view_op_oficinas_all as asist_ofi', 'asist_ofi.id', 'asistencias.op_oficina_id')
                ->leftjoin('op_oficinas as asist_parent', 'asist_parent.id', 'asist_ofi.parent_id')
                

                ->where('fecha', '>=', date('Ymd H:i:s', strtotime(($fechaini.' 00:00:00'))))
                ->where('fecha', '<=', date('Ymd H:i:s', strtotime(($fechafin.' 23:59:59'))))
                ->where('user_id', $usuario)
                //->where('asistencias.tipo', 2)
                //->whereNotNULL('asistencias.hora_fin')
                ->orderBy('asistencias.fecha','asc')
                ->get();

        //dd($dataAsistencia);

        $pdf = new PDF_MC_Table();
        // T�tulos de las columnas
        $pdf->SetFont('Arial','',14);

        foreach ($dataAsistencia as $asistencia) {
            if(sizeof(json_decode($asistencia->actividades)) > 0 && ($asistencia->tipo == 2 || $asistencia->hora_fin2 != null)) {
                $pdf->AddPage();
                // Logo
                $pdf->Cell(80);
                $pdf->Image(public_path().'../../public/image/logo.png',95,8,20);
                // Arial bold 15
                $pdf->Ln(20);
                $pdf->SetFont('Arial','',15);
                // Movernos a la derecha
                $pdf->Cell(50);
                // T�tulo
                $pdf->Cell(30,10,'Corte Superior de Justicia de Arequipa');
                // Salto de l�nea
                $pdf->Ln(8);
                $pdf->SetFont('Arial','',14);
                $pdf->Cell(70);
                $pdf->Cell(40,10,'Oficina de Personal');
                // Salto de l�nea
                $pdf->Ln(10);
                $pdf->SetFont('Arial','I',14);
                $pdf->Cell(40);
                $pdf->Cell(30,10,utf8_decode('"Año del Fortalecimiento de la Soberanía Nacional"'));

                $pdf->Ln(20);
                $pdf->SetFont('Arial','B',15);
                $pdf->Cell(5);
                $pdf->Cell(30,10,utf8_decode('INFORME DE CUMPLIMIENTO DE LABOR EFECTIVA - TRABAJO REMOTO'));


                $pdf->Ln(16);
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(5);
                $pdf->Cell(40,10,utf8_decode('DEPENDENCIA : '));
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(5);
                
                if($asistencia->asist_nombre_plaza){
                    if($asistencia->asist_parent_oficina != $asistencia->asist_distrito){
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->asist_parent_oficina.' - '.$asistencia->asist_distrito)),1,0,'L');
                    } else{
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->asist_nombre_oficina.' - '.$asistencia->asist_distrito)),1,0,'L');
                    }
                } else{
                    if($asistencia->parent_oficina != $asistencia->distrito){
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->parent_oficina.' - '.$asistencia->distrito)),1,0,'L');
                    } else{
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->nombre_oficina.' - '.$asistencia->distrito)),1,0,'L');
                    }
                }
                

                $pdf->Ln(12);
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(5);
                $pdf->Cell(40,10,utf8_decode('AREA/SUBAREA : '));
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(5);
                if($asistencia->asist_nombre_plaza){
                    if($asistencia->asist_parent_oficina != $asistencia->asist_distrito){
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->asist_nombre_oficina)),1,0,'L');
                    } else{
                        $pdf->Cell(140,10,utf8_decode(''),1,0,'L');
                    }
                } else{
                    if($asistencia->parent_oficina != $asistencia->distrito){
                        $pdf->Cell(140,10,utf8_decode(mb_strtoupper($asistencia->nombre_oficina)),1,0,'L');
                    } else{
                        $pdf->Cell(140,10,utf8_decode(''),1,0,'L');
                    }
                }

                $pdf->Ln(12);
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(5);
                $pdf->Cell(55,10,utf8_decode('APELLIDOS Y NOMBRES : '));
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(5);
                $pdf->Cell(125,10,utf8_decode($asistencia->persona),1,0,'L');

                $pdf->Ln(12);
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(5);
                $pdf->Cell(40,10,utf8_decode('FECHA : '));
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(5);
                $pdf->Cell(50,10,$asistencia->fecha,1,0,'L');

                $header = array(utf8_decode('N°'), 'ACTIVIDADES O TAREAS', 'TIEMPO (EN MINUTOS)');
                
                $pdf->Ln(16);
                // Anchuras de las columnas
                $w = array(15, 145, 30);
                $i = 1;
                $tiempoTotal = 0;

                $pdf->SetWidths($w);

                $pdf->Row($header);
                    
                $concat_metas = '';
                foreach(json_decode($asistencia->actividades) as $actividad)
                {
                    if($concat_metas != '' && $asistencia->hora_fin2 != null){
                        $concat_metas .= ' - ';
                    }
                    if($actividad->tiempo == 0 && $asistencia->hora_fin2 != null){

                        $concat_metas .= '('.$actividad->meta_cantidad.') '.$actividad->descripcion;
                    } else{
                        if($actividad->meta_cantidad != null && $actividad->meta_cantidad > 0){
                            if($actividad->detalle != null){
                                $pdf->Row(array($i++,  '('.$actividad->meta_cantidad.') '.utf8_decode($actividad->descripcion. ': '.$actividad->detalle), $actividad->tiempo));
                            } else{
                                $pdf->Row(array($i++,  '('.$actividad->meta_cantidad.') '.utf8_decode($actividad->descripcion), $actividad->tiempo));
                            }
                        } else{
                            if($actividad->detalle != null){
                                $pdf->Row(array($i++, utf8_decode($actividad->descripcion. ': '.$actividad->detalle), $actividad->tiempo));
                            } else{
                                $pdf->Row(array($i++, utf8_decode($actividad->descripcion), $actividad->tiempo));
                            }
                        }
                        $tiempoTotal += $actividad->tiempo;
                    }
                }
                if($concat_metas != ''){
                    $pdf->Row(array($i++, utf8_decode($concat_metas), 120));
                    $tiempoTotal += 120;

                }
                // L�nea de cierre
                $pdf->RowB(array('','TOTAL TIEMPO LABORADO EN EL DIA', $tiempoTotal), 10);

                $pdf->SetY(-30);
                // Arial italic 8
                $pdf->SetFont('Arial','I',8);
                // N�mero de p�gina
                //$pdf->Cell(0,10,'Page '.$pdf->PageNo().'/{nb}',0,0,'C');
                $pdf->Cell(20);
                $pdf->Image(public_path().'../../public/image/footer.png',15,260,180);

            }
            
        }
        
        $pdf->Output('D','Informe '.$usuario.'_'.$fechaini.'-'.$fechafin.'.pdf',false);

        
    }

    function tabla($header, $data)
    {
        // Anchuras de las columnas
        $w = array(40, 35, 45, 40);
        // Cabeceras
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C');
        $this->Ln();
        // Datos
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row[0],'LR');
            $this->Cell($w[1],6,$row[1],'LR');
            $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
            $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
            $this->Ln();
        }
        // L�nea de cierre
        $this->Cell(array_sum($w),0,'','T');
    }
    
    
    public function Listapersonal(){
        $usuario = User::find(Auth::user()->id);
        $persona = Persona::find(Auth::user()->persona_id);
        $jefe_supervisor = DB::table('users')
                                ->select('username', 'p_externo.op_oficinaa_id as p_ext_a', 'p_propio.op_oficinaa_id as p_prop_b')
                                ->join('persona', 'persona.id', 'users.persona_id')
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                ->leftjoin('asistencias_parent as p_externo', 'p_externo.op_oficinaa_id', 'op_oficinas.id')
                                ->leftjoin('asistencias_parent as p_propio', 'p_propio.op_oficinab_id', 'op_oficinas.id')
                                ->where('users.id', Auth::user()->id)
                                ->where('op_plazas_tit.jefe_Area', 1)
                                ->first();


        if($jefe_supervisor){

            $oficina = DB::table('op_oficinas')
                        ->select('op_oficinas.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                        ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                        ->where('persona.id', $persona->id)
                        ->first();

            $personal = Asistencia::select('persona.numero_documento', 
                                DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as nombrecompleto")
                        )
                        ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'asistencias.op_plaza_id')
                        ->leftjoin('asistencias_parent', 'asistencias_parent.op_oficinab_id', 'asistencias.op_oficina_id')
                        ->where(function ($query) use ($oficina) {
                            $query->where('asistencias_parent.op_oficinaa_id', $oficina->id);
                            $query->orWhere('asistencias.op_oficina_id', $oficina->id);
                        })
                        ->distinct()
                        ->get();
        } else if ($usuario->hasAnyRole('Asistencia.supervisor|Webmaster'))
        {
            $personal = DB::table('persona')
                                ->select('persona.numero_documento', 
                                    DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as nombrecompleto")
                                )
                                ->leftjoin('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
         						->leftJoin('op_plazas_tit','op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->leftJoin('view_op_oficinas', 'view_op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                             //    ->where('is_admin', '!=', 1)
                           ->get();

        } else{

            $personal = DB::table('persona')
         						->select('persona.numero_documento', 
                                     DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as nombrecompleto")
                                )
                                ->leftjoin('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                ->leftJoin('op_plazas_tit','op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->leftJoin('view_op_oficinas', 'view_op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                           //->where('is_admin', '!=', 1)
                           ->where('persona.id', Auth::user()->persona_id)
                           ->get();
        }

        $persona = Persona::find(Auth::user()->persona_id);
        
         return ['personal' => $personal, 'usuario' => $persona->numero_documento];    
    }
    public function Listalabor(Request $request, $fechaini){

        //$usuario = User::find();

        $labor = Asistencia::select(
                                    'asistencias.*', 
                                    'op_plazas_tit.nombre_plaza', 
                                    'view_op_oficinas.nombre_oficina as dependencia', 
                                    'persona.numero_documento as DNI',
                                    DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona")
                                )
                                ->with('actividades')
                                ->join('persona', 'persona.numero_documento', 'asistencias.user_id')
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                ->where('fecha', '>=', date('Ymd H:i:s', strtotime(($fechaini.' 00:00:00'))))
                                ->where('fecha', '<=', date('Ymd H:i:s', strtotime(($fechaini.' 23:59:59'))))
                                ->where('user_id', Auth::user()->id)
                                ->where('asistencias.tipo', 2)
                                //->whereNotNULL('asistencias.hora_fin')
                                ->orderBy('asistencias.fecha','asc')
                                ->get();

        return [ 'labor' => $labor];
    }

    public function ListalaborRemota(Request $request){

        $persona = Persona::find(Auth::user()->persona_id);
        $remoto = Asistencia::select('fecha', 'hora_fin')
                                ->where('user_id', $persona->numero_documento)
                                ->where('asistencias.tipo', 2)
                                //->whereNotNULL('asistencias.hora_fin')
                                ->orderBy('asistencias.fecha','asc')
                                ->get();

        return [ 'remoto' => $remoto];
    }

    public function Excepcion(Request $request, $id = null){

        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }
        $persona = Persona::find($usuario->persona_id);
        $fecha_registro = time();

        if($request->input('of_ID')){
            /****** CONSULTAR SI LA EXCEPCION FUE REGISTRADA Y ACTIVA */
            $excepcion_DB = DB::table('asistencias_excepciones')
                                ->where('op_oficina_id', $request->of_ID)
                                ->first();
            DB::beginTransaction();
            try{
                if($excepcion_DB){
                    $excepcion_UPDATE = DB::table('asistencias_excepciones')
                                        ->where('op_oficina_id', $request->of_ID)
                                        ->update([
                                            'activo' => 1,
                                            'user_update' => $persona->numero_documento,
                                            'updated_at' =>  date('Ymd H:i:s', $fecha_registro)
                                        ]);
                } else{
                    $excepcion = DB::table('asistencias_excepciones')
                                        ->insert([
                                            'op_oficina_id'=> $request->of_ID,
                                            'user_update' => $persona->numero_documento,
                                        ]);   
                }

                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Excepción registrada con exito.";
        
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        }

        if($id != null){
            $excepcion_UPDATE = DB::table('asistencias_excepciones')
                                        ->where('id', $id)
                                        ->update([
                                            'activo' => 0,
                                            'user_update' => $persona->numero_documento,
                                            'updated_at' =>  date('Ymd H:i:s', $fecha_registro)
                                        ]);
            $response["statusBD"] = true;
            $response["messageDB"] = "Excepción actualizada con exito.";
            return $response;

        }

        return abort(403);
    }


    public function RegularizaLabor(Request $request, $id){

        $usuario = User::find(Auth::user()->id);
        $persona = Persona::find(Auth::user()->persona_id);

        $fecha_registro = time();
        
        $ip =  $_SERVER['REMOTE_ADDR'];
        Log::info('Asistencia_r->[IP] '.$ip.'[USER] '.Auth::user()->username);

        DB::beginTransaction();
        try{
                $existe = DB::table('asistencias')
                            ->where('id', $id)
                            ->where('user_id',$persona->numero_documento)
                            //->where('fecha', date('Y-m-d',$fecha_registro))
                            //->whereNull('hora_fin')
                            ->get();

                if(sizeof($existe) == 1){

                    if($request->input('actividades')){
                        foreach ($request->actividades as $actividad) {
                            $registroActividad = DB::table('asistencias_actividades')
                                    ->insert([
                                        'asistencia_id' =>  $id,
                                        'descripcion' => $actividad['actividad'],
                                        'tiempo' => $actividad['tiempo'],
                                        'detalle' => (isset($actividad['detalle']))? $actividad['detalle'] : null
                                    ]);
                        }
                        DB::commit(); 
                    } 
                    $response["hora_fin"] = date('H:i:s',$fecha_registro);
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Actividades regularizadas con exito.";
                } else{
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Hubo un problema al actualizar en la base de datos. probablemente ya haya sido completado su asistencia";
                }
            //}
        } catch (\Exception $e) {
            DB::rollback();
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
        }
        return $response;
  
    }

    public function getMetasTrabajadores(Request $request, $id = null){
        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }
        $fecha_registro = time();
        $response = null;


                    $oficinas_h = DB::table('persona')
                                        ->select('persona.numero_documento','persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'view_op_oficinas.*', 'persona.id',
                                            DB::raw("CONCAT(view_op_oficinas.nombre_oficina, ' [',parent.nombre_oficina, '] - ', view_op_oficinas.distrito) as nombre_oficina"),
                                            DB::raw("(SELECT TOP 1 jefe_vb from asistencias_meta_mes where asistencias_meta_mes.user_id = persona.numero_documento AND anio=".$request->periodo['anio']." AND mes = ".$request->periodo['mes']." ) as metames"),
                                            DB::raw("CONCAT((SELECT SUM(meta_cantidad) FROM asistencias_actividades JOIN asistencias ON asistencias.id = asistencias_actividades.asistencia_id WHERE asistencias.user_id = persona.numero_documento AND meta_anio = ".$request->periodo['anio']." AND meta_mes = ".$request->periodo['mes']."), ' / ', (SELECT SUM(meta_cantidad) FROM asistencias_meta_mes WHERE asistencias_meta_mes.user_id = persona.numero_documento AND asistencias_meta_mes.anio=".$request->periodo['anio']." AND asistencias_meta_mes.mes = ".$request->periodo['mes']." )) as avance",
                                            ),
                                            DB::raw("(SELECT TOP 1 filename_anexo FROM asistencias_metas_anexo WHERE asistencias_metas_anexo.user_id = persona.numero_documento AND asistencias_metas_anexo.anio = ".$request->periodo['anio']." AND asistencias_metas_anexo.mes = ".$request->periodo['mes']." )  as anexo"),
                                            DB::raw("(SELECT TOP 1 supervisor_vb FROM asistencias_metas_anexo WHERE asistencias_metas_anexo.user_id = persona.numero_documento AND asistencias_metas_anexo.anio = ".$request->periodo['anio']." AND asistencias_metas_anexo.mes = ".$request->periodo['mes']." ) as enable_anexo")
                                        )
                                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                        ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                        ->join('op_oficinas as oficina_b', 'oficina_b.id', 'op_plazas_tit.op_oficinaf_id')
                                        ->join('asistencias_parent', 'asistencias_parent.op_oficinab_id', 'oficina_b.id')
                                        ->leftjoin('view_op_oficinas', 'view_op_oficinas.ID',  'oficina_b.id')
                                        ->join('op_oficinas as parent', 'parent.id', 'view_op_oficinas.parent_id')
                                        ->whereNotNULL('asistencias_parent.op_oficinaa_id')
                                        ->where('op_plazas_tit.jefe_area', 0)
                                        ->orderby('persona.id')
                                        ->get();
        
                    $oficina_p = DB::table('persona')
                                        ->select('persona.numero_documento', 'persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'view_op_oficinas.*', 'persona.id',
                                        DB::raw("CONCAT(view_op_oficinas.nombre_oficina, ' [',parent.nombre_oficina, '] - ', view_op_oficinas.distrito) as nombre_oficina"),
                                        DB::raw("(SELECT TOP 1 jefe_vb from asistencias_meta_mes where asistencias_meta_mes.user_id = persona.numero_documento AND anio=".$request->periodo['anio']." AND mes = ".$request->periodo['mes']." ) as metames"),
                                        DB::raw("CONCAT((SELECT SUM(meta_cantidad) FROM asistencias_actividades JOIN asistencias ON asistencias.id = asistencias_actividades.asistencia_id WHERE asistencias.user_id = persona.numero_documento AND meta_anio = ".$request->periodo['anio']." AND meta_mes = ".$request->periodo['mes']."), ' / ', (SELECT SUM(meta_cantidad) FROM asistencias_meta_mes WHERE asistencias_meta_mes.user_id = persona.numero_documento AND asistencias_meta_mes.anio=".$request->periodo['anio']." AND asistencias_meta_mes.mes = ".$request->periodo['mes']." )) as avance"),
                                        DB::raw("(SELECT TOP 1 filename_anexo FROM asistencias_metas_anexo WHERE asistencias_metas_anexo.user_id = persona.numero_documento AND asistencias_metas_anexo.anio = ".$request->periodo['anio']." AND asistencias_metas_anexo.mes = ".$request->periodo['mes']." )  as anexo"),
                                        DB::raw("(SELECT TOP 1 supervisor_vb FROM asistencias_metas_anexo WHERE asistencias_metas_anexo.user_id = persona.numero_documento AND asistencias_metas_anexo.anio = ".$request->periodo['anio']." AND asistencias_metas_anexo.mes = ".$request->periodo['mes']." ) as enable_anexo")

                                        )
                                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                        ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                        ->join('op_oficinas as oficina_a', 'oficina_a.id', 'op_plazas_tit.op_oficinaf_id')
                                        ->join('asistencias_parent', 'asistencias_parent.op_oficinaa_id', 'oficina_a.id')
                                        ->leftjoin('view_op_oficinas', 'view_op_oficinas.ID',  'oficina_a.id')
                                        ->join('op_oficinas as parent', 'parent.id', 'view_op_oficinas.parent_id')
                                        ->whereNotNULL('asistencias_parent.op_oficinaa_id')
                                        //->where('oficina_a.id', $oficina->id)
                                        ->where('op_plazas_tit.jefe_area', 0)
                                        ->distinct()
                                        ->orderby('persona.id')
                                        ->get();

                    $oficinas_h = $oficinas_h->merge($oficina_p);


                $response["statusBD"] = true;
                $response["messageDB"] = "Datos cargados correctamente";
            
                $response['oficinas_h'] = $oficinas_h;
        return $response;
    }


    public function getFeriado(){
        $tabla = DB::table('feriados')
                ->paginate(5000);

        $response['asistencias'] = $tabla;
        return $response;
    }
    
    public function Feriado(Request $request, $id = null){

        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }
        $persona = Persona::find($usuario->persona_id);
        $fecha_registro = time();

        if($request->input('f_inicio')){
            /****** CONSULTAR SI EL FERIADO FUE REGISTRADO ANTERIORMENTE */
            $feriado_DB = DB::table('feriados')
                                ->where('fecha', $request->f_inicio)
                                ->first();
            DB::beginTransaction();
            try{
                if($feriado_DB){
                    $response["statusBD"] = false;
                    $response["messageDB"] = "El feriado ya fué registrado en la Base de Datos.";
                } else{
                    $feriado = DB::table('feriados')
                                        ->insert([
                                            'fecha'=> $request->f_inicio,
                                            'descripcion'=> $request->descripcion,
                                            'asistencia_pre'=> $request->asistencia_pre,
                                            'asistencia_rem'=> $request->asistencia_rem,
                                            'user_update' => $persona->numero_documento,
                                        ]);   
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Fecha feriado registrada con exito.";
                }
        
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
        }

        if($id != null){
            $feriado_UPDATE = DB::table('feriados')
                                        ->where('id', $id)
                                        ->delete();

            $response["statusBD"] = true;
            $response["messageDB"] = "Feriado eliminado con exito.";
            return $response;

        }
        return abort(403);
    }

    public function getConfiguracion(){
        $tabla = DB::table('asistencias_config')
                ->first();

        $response['configuracion'] = $tabla;
        return $response;
    }

    public function Configuracion(Request $request, $id = null){
        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }
        $persona = Persona::find($usuario->persona_id);
        $fecha_registro = time();

        if($request->input('h_ingreso_presencial')){
            
            /****** CONSULTAR SI EXISTE CONFIGURACION */
            $config_DB = DB::table('asistencias_config')
                                ->where('id', 1)
                                ->first();
            DB::beginTransaction();
            try{
                if($config_DB){
                    /****** CERRAMOS PAPELETAS PASADAS */
                    $fecha_logueo = time();

                    $config = DB::table('asistencias_config')
                            ->first();
                    if($config){
                        $ini_lab = $config->h_ingreso_presencial;
                        $ini_rec = $config->h_ini_refrigerio;
                        $fin_rec = $config->h_fin_refrigerio;
                        $fin_lab = $config->h_salida_presencial;
                        $hora_receso = strval( date("H:i:s", strtotime("00:00:00") + strtotime($fin_rec) - strtotime($ini_rec)) );

                        /*************** NOTIFICADORES */
                        if($persona->numero_documento == '44786659')
                        {
                            $ini_lab = $config->h_disp_ing_excepcional;
                            $fin_lab = $config->h_disp_salida_excepcional;

                        }

                        $hora_final = $fin_lab;


                        if (date('H:i:s',$fecha_logueo) > $fin_lab) {
                            
                            /****** 
                                CERRAMOS LAS BOLETAS DEL DIA DE HOY PASADA LAS 5 DE LA TARDE 
                            *******/
                            $boletasHoy = DB::table('sbp_boletas')
                                            ->where('estado_inicio', 1)
                                            ->where('autorizacion_ji', 1)
                                            ->where('fecha_permiso', date('Ymd', $fecha_logueo))
                                            ->get();

                            if ($boletasHoy) {
                                foreach ($boletasHoy as $boleta) {
                                    if($boleta->hora_ini >= $ini_lab && $hora_final <= $ini_rec){
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($boleta->hora_ini) ));
                                    }elseif ($boleta->hora_ini>=$fin_rec && $hora_final<=$fin_lab) {
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($boleta->hora_ini) ));
                                    }elseif ($boleta->hora_ini>=$ini_rec && $hora_final<=$fin_lab) {
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($fin_rec) ));
                                    }elseif ($boleta->hora_ini>=$ini_lab && $hora_final<=$fin_rec) {
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($ini_rec) - strtotime($boleta->hora_ini) ));
                                    }else {
                                        $total_hora_temp = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($boleta->hora_ini) ));
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($total_hora_temp) - strtotime($hora_receso) ));
                                    }
                                    
                                    list($horas, $minutos, $segundos) = explode(':', $total_hora);
                                    $total_permiso = ($horas * 60) + $minutos; 
                                    try{
                                        $query = DB::table('sbp_boletas')
                                            ->where('id', $boleta->id)
                                            ->update([
                                                    'hora_fin' => $hora_final,
                                                    'total_permiso' => $total_permiso,
                                                    'estado_inicio' => 2,
                                            ]);
                                    
                                    } catch (\Exception $e) {
                                        
                                    }    
                                }
                            }
                        } else{
                            /****** 
                                CERRAMOS LAS BOLETAS DEL DIA ANTERIOR 
                            *******/
                            $boletasAyer = DB::table('sbp_boletas')
                                            ->where('estado_inicio', 1)
                                            ->where('fecha_permiso', '<', date('Ymd', $fecha_logueo))
                                            ->get();

                            if ($boletasAyer) {
                                foreach ($boletasAyer as $boleta) {
                                    if($boleta->hora_ini >= $ini_lab && $hora_final <= $ini_rec){
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($boleta->hora_ini) ));
                                    }elseif ($boleta->hora_ini>=$fin_rec && $hora_final<=$fin_lab) {
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($boleta->hora_ini) ));
                                    }elseif ($boleta->hora_ini>=$ini_rec && $hora_final<=$fin_lab) {
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($fin_rec) ));
                                    }elseif ($boleta->hora_ini>=$ini_lab && $hora_final<=$fin_rec) {
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($ini_rec) - strtotime($boleta->hora_ini) ));
                                    }else {
                                        $total_hora_temp = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($boleta->hora_ini) ));
                                        $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($total_hora_temp) - strtotime($hora_receso) ));
                                    }
                                    
                                    list($horas, $minutos, $segundos) = explode(':', $total_hora);
                                    $total_permiso = ($horas * 60) + $minutos; 
                                    try{
                                        $query = DB::table('sbp_boletas')
                                            ->where('id', $boleta->id)
                                            ->update([
                                                    'hora_fin' => $hora_final,
                                                    'total_permiso' => $total_permiso,
                                                    'estado_inicio' => 2,
                                            ]);
                                    
                                    } catch (\Exception $e) {
                                        
                                    }    
                                }
                            }
                        }                 
                    }


                    $feriado = DB::table('asistencias_config')
                                        ->where('id', 1)
                                        ->update([
                                            'h_ingreso_presencial'=> $request->h_ingreso_presencial,
                                            'h_ingreso_remoto'=> $request->h_ingreso_remoto,
                                            'h_ingreso_excepcional'=> $request->h_ingreso_excepcional,
                                            'h_disp_ing_presencial'=> $request->h_disp_ing_presencial,
                                            'h_disp_ing_remoto'=> $request->h_disp_ing_remoto,
                                            'h_disp_ing_excepcional'=> $request->h_disp_ing_excepcional,
                                            'h_ini_refrigerio'=> $request->h_ini_refrigerio,
                                            'h_fin_refrigerio'=> $request->h_fin_refrigerio,
                                            'h_disp_ini_refrigerio'=> $request->h_disp_ini_refrigerio,
                                            'h_disp_fin_refrigerio'=> $request->h_disp_fin_refrigerio,
                                            'h_salida_presencial'=> $request->h_salida_presencial,
                                            'h_salida_remoto'=> $request->h_salida_remoto,
                                            'h_salida_excepcional'=> $request->h_salida_excepcional,
                                            'h_disp_salida_presencial'=> $request->h_disp_salida_presencial,
                                            'h_disp_salida_remoto'=> $request->h_disp_salida_remoto,
                                            'h_disp_salida_excepcional'=> $request->h_disp_salida_excepcional,
                                            'h_aut_ingreso'=> $request->h_aut_ingreso,
                                            'h_aut_ingreso_excepcional'=> $request->h_aut_ingreso_excepcional,
                                            'max_horas_diarias'=> $request->max_horas_diarias,
                                            'max_horas_excepcional'=> $request->max_horas_excepcional,
                                            'user_update' => $persona->numero_documento,
                                            'updated_at' => date('Ymd H:i:s', $fecha_registro),
                                        ]);   
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Configuración actualizada con exito.";

                } else{
                    $response["statusBD"] = false;
                    $response["messageDB"] = "No existe una configuración preestablecida.";
                }
        
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
        }

    }


    public function adminvista($vista){

       /* $usuario = DB::table('users')
                            ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->where('users.id', Auth::user()->id)
                            ->first();
        */
        $usuario = DB::table('users')
                            ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email', 'persona.numero_documento',
                                        'plaza_fisica.nombre_plaza as n_plaza_fisica',   
                                        'plaza_fisica.c_plaza as c_plaza_fisica',   
                                        'of_fisica.nombre_oficina as n_oficina_fisica'        
                            )
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->leftjoin('persona_has_plaza_fun','persona_has_plaza_fun.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plaza_fisica', 'plaza_fisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            ->leftjoin('op_oficinas as of_fisica', 'of_fisica.id', 'plaza_fisica.op_oficinaf_id')

                            ->where('users.id', Auth::user()->id)
                            ->first();

        $usuario = json_encode($usuario);
        
        $user_roles_db = DB::table('model_has_roles')
                            ->select('roles.name')
                            ->where('model_id', Auth::user()->id)
                            ->join('roles', 'roles.id', 'model_has_roles.role_id')
                            ->get();
        $user_roles = [];



        $jefe_supervisor = DB::table('users')
                                ->select('username', 'p_externo.op_oficinaa_id as p_ext_a', 'p_propio.op_oficinaa_id as p_prop_b')
                                ->join('persona', 'persona.id', 'users.persona_id')
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                ->leftjoin('asistencias_parent as p_externo', 'p_externo.op_oficinaa_id', 'op_oficinas.id')
                                ->leftjoin('asistencias_parent as p_propio', 'p_propio.op_oficinab_id', 'op_oficinas.id')
                                ->where('users.id', Auth::user()->id)
                                ->where('op_plazas_tit.jefe_Area', 1)
                                ->first();


        if($jefe_supervisor){
            if($jefe_supervisor->p_ext_a != null){
            //if($jefe_supervisor->p_ext_a != null || ($jefe_supervisor->p_ext_a == null && $jefe_supervisor->p_prop_b == null)){
                array_push($user_roles, utf8_encode('Asistencia.JSupervisor'));
            }
        }
        

        $usuario_metas = DB::table('users')
                                    ->select('username', 'p_externo.op_oficinaa_id as p_ext_a', 'p_propio.op_oficinaa_id as p_prop_b')
                                    ->join('persona', 'persona.id', 'users.persona_id')
                                    ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                    ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                    ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                    ->leftjoin('asistencias_parent as p_externo', 'p_externo.op_oficinaa_id', 'op_oficinas.id')
                                    ->leftjoin('asistencias_parent as p_propio', 'p_propio.op_oficinab_id', 'op_oficinas.id')
                                    ->where('users.id', Auth::user()->id)
                                    ->where('op_plazas_tit.jefe_Area', 0)
                                    ->first();


        if($usuario_metas){
            if($usuario_metas->p_ext_a != null || $usuario_metas->p_prop_b != null){
            //if($jefe_supervisor->p_ext_a != null || ($jefe_supervisor->p_ext_a == null && $jefe_supervisor->p_prop_b == null)){
                array_push($user_roles, utf8_encode('Asistencia.UsuarioMetas'));
            }
        }

        if (sizeof($user_roles_db)>0) {
            foreach($user_roles_db as $role){
                array_push($user_roles, utf8_encode($role->name));
            }
            $user_roles = json_encode($user_roles);
            //print_r($user_roles);

            if (view()->exists('asistencia/'.$vista)){
                return view('asistencia/'.$vista, compact('user_roles', 'usuario'));
            } else{
               return abort('404');
            }

        } else{
            $user_roles = json_encode($user_roles);

            return view('asistencia/'.$vista, compact('user_roles', 'usuario'));

        }
    }
}

