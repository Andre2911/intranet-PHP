<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Log;
use App\Persona;
use Illuminate\Support\Facades\DB;

class ToolController extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
    }

    public function getDNI($dni){
        //return view('intranet/home');     
        //$req = base64_encode( json_encode( array('ledni'=>$dni, 'pass'=>"0xOCE7") ) );
        $ip =  $_SERVER['REMOTE_ADDR'];
        $resultado = null;

        $personaDB = Persona::where('numero_documento', $dni)->first();

        Log::info('INFO PC->[Consulta_dni2] '.$ip);

        //$uri = "http://172.28.206.57:7000/c_dni/?dni=" . $dni;
        $uri = "http://172.28.206.57:7000/c_dni/?dni=" . $dni;

        $api = curl_init();
        curl_setopt($api, CURLOPT_URL, $uri );
        curl_setopt($api, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($api, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($api, CURLOPT_TIMEOUT, 8);
        curl_setopt($api, CURLOPT_TIMEOUT_MS, 8000);
        //curl_setopt($api, CURLOPT_POSTFIELDS, array( 'request' => $req ) );
        $res1 = curl_exec($api);

        //$res = json_decode($res);
        $resultado = null;
        $res = array('IsSuccess' => true, 'datos' => []);

        if ($res1){
            $res = (Object)[];
            $res->datos = json_decode($res1);

            if($res->datos[0] != ""){
                $resultado['IsSuccess'] = true;
                $resultado['datos'] = null;
                $resultado['datos']['ap_paterno'] = $res->datos[2];
                $resultado['datos']['ap_materno'] = $res->datos[3];
                $resultado['datos']['nombres'] = $res->datos[5];
                $resultado['datos']['direccion'] = 
                
                (($res->datos[52] != '')? $res->datos[52].' '.$res->datos[46].' ':''). 

                $res->datos[41].' '. //JR
                $res->datos[42].' '. //Direccion
                $res->datos[43].''. // Nro
                (($res->datos[48]!= '')? $res->datos[48].' '.$res->datos[49] : ''). //Mz//Lt
                ' - '.$res->datos[12].' - '.$res->datos[11].' - '.$res->datos[10];
                $resultado['datos']['sexo'] = ($res->datos[17] == "MASCULINO")? 1:0;
                $resultado['datos']['fecha_nacimiento'] = $res->datos[28];
                if($personaDB){
                    $resultado['datos']['id'] = $personaDB->id;
                }
            } else{
                $resultado['IsSuccess'] = false;
            }
            
        } else{
            $resultado['IsSuccess'] = false;
        }

        if (!$resultado['IsSuccess']) {
            $uri = "http://csjjunin.gob.pe/api/consultareniec/" . $dni;

                $api = curl_init();
                curl_setopt($api, CURLOPT_URL, $uri );
                curl_setopt($api, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($api, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($api, CURLOPT_TIMEOUT, 6);
                curl_setopt($api, CURLOPT_CONNECTTIMEOUT, 6);
                curl_setopt($api, CURLOPT_CONNECTTIMEOUT_MS, 5000);

                $output = curl_exec($api);
                curl_close($api);
                $resultado = null;
                if(strlen($output) > 0){
                    //dd(substr($output,4,strlen($output)-1));
                    $output = str_replace("\"", "", $output);
                    $output = str_replace("\u00d1", "Ñ", $output);
                    $persona = explode(",",substr($output,4,strlen($output)-1));
                    $resultado['IsSuccess'] = true;
                    $resultado['datos'] = null;
                    $resultado['datos']['ap_paterno'] = $persona[2];
                    $resultado['datos']['ap_materno'] = $persona[3];
                    $resultado['datos']['nombres'] = $persona[5];
                    $resultado['datos']['direccion'] = 
                    
                    (($persona[52] != '')? $persona[52].' '.$persona[46].' ':''). 

                    $persona[41].' '. //JR
                    $persona[42].' '. //Direccion
                    $persona[43].''. // Nro
                    (($persona[48]!= '')? $persona[48].' '.$persona[49] : ''). //Mz//Lt
                    ' - '. 
                    $persona[12].' - '.$persona[11].' - '.$persona[10];
                    $resultado['datos']['sexo'] = ($persona[17] == "MASCULINO")? 1:0;
                    $resultado['datos']['fecha_nacimiento'] = str_replace("\\","",$persona[28]);
                    if($personaDB){
                        $resultado['datos']['id'] = $personaDB->id;
                    }
        
                } else{
                    $resultado['IsSuccess'] = false;    
                }
        }
        //$res = utf8_decode($res)
        //$res = utf8_code_deep($apiGet);
        return $resultado;
    }

    public function getDNIu($dni){
        //return view('intranet/home');     
        //$req = base64_encode( json_encode( array('ledni'=>$dni, 'pass'=>"0xOCE7") ) );
        $ip =  $_SERVER['REMOTE_ADDR'];
        $resultado = null;
        $personaDB = Persona::where('numero_documento', $dni)->first();

        Log::info('INFO PC->[Consulta_dni2] '.$ip);

        //$uri = "http://172.28.206.57:7000/c_dni/?dni=" . $dni;
        $uri = "http://172.28.206.57:7000/c_dni/?dni=" . $dni;

        $api = curl_init();
        curl_setopt($api, CURLOPT_URL, $uri );
        curl_setopt($api, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($api, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($api, CURLOPT_TIMEOUT, 10);
        curl_setopt($api, CURLOPT_TIMEOUT_MS, 8000);
        //curl_setopt($api, CURLOPT_POSTFIELDS, array( 'request' => $req ) );
        $res1 = curl_exec($api);

        //$res = json_decode($res);
        $resultado = null;
        $res = array('IsSuccess' => true, 'datos' => []);

        if ($res1){
            $res = (Object)[];
            $res->datos = json_decode($res1);  
            $resultado['IsSuccess'] = true;
            $resultado['datos'] = null;
            $resultado['datos']['ap_paterno'] = $res->datos[2];
            $resultado['datos']['ap_materno'] = $res->datos[3];
            $resultado['datos']['nombres'] = $res->datos[5];
            $resultado['datos']['direccion'] = 
            
            (($res->datos[52] != '')? $res->datos[52].' '.$res->datos[46].' ':''). 

            $res->datos[41].' '. //JR
            $res->datos[42].' '. //Direccion
            $res->datos[43].''. // Nro
            (($res->datos[48]!= '')? $res->datos[48].' '.$res->datos[49] : ''). //Mz//Lt
            ' - '.$res->datos[12].' - '.$res->datos[11].' - '.$res->datos[10];
            $resultado['datos']['sexo'] = ($res->datos[17] == "MASCULINO")? 1:0;
            $resultado['datos']['fecha_nacimiento'] = $res->datos[28];
            if($personaDB){
                $resultado['datos']['id'] = $personaDB->id;
            }
        } else{
            $resultado['IsSuccess'] = false;
        }

        if (!$resultado['IsSuccess']) {
            $uri = "http://csjjunin.gob.pe/api/consultareniec/" . $dni;

                $api = curl_init();
                curl_setopt($api, CURLOPT_URL, $uri );
                curl_setopt($api, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($api, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($api, CURLOPT_TIMEOUT, 6);
                curl_setopt($api, CURLOPT_CONNECTTIMEOUT, 6);
                curl_setopt($api, CURLOPT_CONNECTTIMEOUT_MS, 5000);

                $output = curl_exec($api);
                curl_close($api);
                $resultado = null;
                if(strlen($output) > 0){
                    //dd(substr($output,4,strlen($output)-1));
                    $output = str_replace("\"", "", $output);
                    $output = str_replace("\u00d1", "Ñ", $output);
                    $persona = explode(",",substr($output,4,strlen($output)-1));
                    $resultado['IsSuccess'] = true;
                    $resultado['datos'] = null;
                    $resultado['datos']['ap_paterno'] = $persona[2];
                    $resultado['datos']['ap_materno'] = $persona[3];
                    $resultado['datos']['nombres'] = $persona[5];
                    $resultado['datos']['direccion'] = 
                    
                    (($persona[52] != '')? $persona[52].' '.$persona[46].' ':''). 

                    $persona[41].' '. //JR
                    $persona[42].' '. //Direccion
                    $persona[43].''. // Nro
                    (($persona[48]!= '')? $persona[48].' '.$persona[49] : ''). //Mz//Lt
                    ' - '. 
                    $persona[12].' - '.$persona[11].' - '.$persona[10];
                    $resultado['datos']['sexo'] = ($persona[17] == "MASCULINO")? 1:0;
                    $resultado['datos']['fecha_nacimiento'] = str_replace("\\","",$persona[28]);
                } else{
                    $resultado['IsSuccess'] = false;    
                }
        }
        //$res = utf8_decode($res)
        //$res = utf8_code_deep($apiGet);
        return $resultado;
    }


    public function getDNIi($dni){
        $ip =  $_SERVER['REMOTE_ADDR'];
        $resultado = null;
        $personaDB = Persona::where('numero_documento', $dni)->first();

        Log::info('INFO PC->[Consulta_dni2] '.$ip);

        $uri = "http://172.28.206.57:7000/c_dni/?dni=" . $dni;

        $api = curl_init();
        curl_setopt($api, CURLOPT_URL, $uri );
        curl_setopt($api, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($api, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($api, CURLOPT_TIMEOUT, 10);
        curl_setopt($api, CURLOPT_TIMEOUT_MS, 8000);
        $res1 = curl_exec($api);

        $resultado = null;
        $res = array('IsSuccess' => true, 'datos' => []);

        if ($res1){
            $res = (Object)[];
            $res->datos = json_decode($res1);  
            $resultado['IsSuccess'] = true;
            $resultado['datos'] = null;
            $resultado['datos']['ap_paterno'] = $res->datos[2];
            $resultado['datos']['ap_materno'] = $res->datos[3];
            $resultado['datos']['nombres'] = $res->datos[5];
            $resultado['datos']['direccion'] = 
            
            (($res->datos[52] != '')? $res->datos[52].' '.$res->datos[46].' ':''). 

            $res->datos[41].' '. //JR
            $res->datos[42].' '. //Direccion
            $res->datos[43].''. // Nro
            (($res->datos[48]!= '')? $res->datos[48].' '.$res->datos[49] : ''). //Mz//Lt
            ' - '.$res->datos[12].' - '.$res->datos[11].' - '.$res->datos[10];
            $resultado['datos']['sexo'] = ($res->datos[17] == "MASCULINO")? 1:0;
            $resultado['datos']['fecha_nacimiento'] = $res->datos[28];
            $resultado['datos']['foto'] = $res->datos[58];
            if($personaDB){
                $resultado['datos']['id'] = $personaDB->id;
            }
        } else{
            $resultado['IsSuccess'] = false;
        }

        if (!$resultado['IsSuccess']) {
            $uri = "http://csjjunin.gob.pe/api/consultareniec/" . $dni;

                $api = curl_init();
                curl_setopt($api, CURLOPT_URL, $uri );
                curl_setopt($api, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($api, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($api, CURLOPT_TIMEOUT, 6);
                curl_setopt($api, CURLOPT_CONNECTTIMEOUT, 6);
                curl_setopt($api, CURLOPT_CONNECTTIMEOUT_MS, 5000);

                $output = curl_exec($api);
                curl_close($api);
                $resultado = null;
                if(strlen($output) > 0){
                    //dd(substr($output,4,strlen($output)-1));
                    $output = str_replace("\"", "", $output);
                    $output = str_replace("\u00d1", "Ñ", $output);
                    $persona = explode(",",substr($output,4,strlen($output)-1));
                    $resultado['IsSuccess'] = true;
                    $resultado['datos'] = null;
                    $resultado['datos']['ap_paterno'] = $persona[2];
                    $resultado['datos']['ap_materno'] = $persona[3];
                    $resultado['datos']['nombres'] = $persona[5];
                    $resultado['datos']['direccion'] = 
                    
                    (($persona[52] != '')? $persona[52].' '.$persona[46].' ':''). 

                    $persona[41].' '. //JR
                    $persona[42].' '. //Direccion
                    $persona[43].''. // Nro
                    (($persona[48]!= '')? $persona[48].' '.$persona[49] : ''). //Mz//Lt
                    ' - '. 
                    $persona[12].' - '.$persona[11].' - '.$persona[10];
                    $resultado['datos']['sexo'] = ($persona[17] == "MASCULINO")? 1:0;
                    $resultado['datos']['fecha_nacimiento'] = str_replace("\\","",$persona[28]);
                    $resultado['datos']['foto'] = $persona[58];

                } else{
                    $resultado['IsSuccess'] = false;    
                }
        }
        return $resultado;
    }

    public function getPermisos($sede){

        $response = null;
        $fecha_registro = time();

        try{
            $query = DB::table('sbp_boletas')
                    ->select('hora_ini', 'hora_fin', 'estado', 'estado_inicio','autorizacion_ji',
                        DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as usuario"))
                    ->join('sbp_ctrlingreso', 'sbp_ctrlingreso.cod_local', 'sbp_boletas.cod_local')
                    ->join('persona', 'persona.numero_documento', 'sbp_boletas.idusuario')
                    ->where('estado_inicio', '!=', 2)
                    ->where('estado', 1)
                    ->where('sbp_ctrlingreso.cod_control', $sede)
                    ->where('fecha_permiso', date('Ymd',$fecha_registro))
                    ->orderBy('sbp_boletas.created_at', 'desc')
                    ->get();

            if (sizeof($query)>0) {
                
                $response["boletas"] = $query;
            } else {
                $response["statusBD"] = false;
            }
        } catch (\Exception $e) {
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al consultar en la Base de Datos. Comuniquese con el administrador".$e;
        }
        return $response;
    }

    public function seguridadID($dni){
        $response = null;

        try{

            $query = DB::table('model_has_roles')
                        ->join('users','model_has_roles.model_id', 'users.id')
                        ->join('roles','roles.id', 'model_has_roles.role_id')
                        ->join('persona','persona.id', 'users.persona_id')
                        ->leftJoin('sbp_seguridad', 'sbp_seguridad.id_users', 'users.id')
                        ->where('roles.name', 'Permisos.seguridad')
                        ->where('persona.numero_documento', $dni)
                        ->first();
        
            /*$query = DB::table('sbp_seguridad')
                    ->where('id_users', $dni)
                    ->first();
                    */
            if ($query) {
                $response["statusBD"] = true;
            } else {
                $response["statusBD"] = false;
            }
            
        
        } catch (\Exception $e) {
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al consultar en la Base de Datos. Comuniquese con el administrador";
        }
        return $response;
    }

    public function personalID($dni){
        $response = null;

        try{
            $query = DB::table('persona')
                    ->where('numero_documento', $dni)
                    ->first();
            if ($query) {
                $response["nombres"] = $query->nombres;
                $response["apPaterno"] = $query->ap_paterno;
                $response["apMaterno"] = $query->ap_materno;
                $response["statusBD"] = true;
            } else {
                $response["statusBD"] = false;
            }
            
        
        } catch (\Exception $e) {
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al consultar en la Base de Datos. Comuniquese con el administrador";
        }
        return $response;
    }

    public function getBoleta($dni, $sede){
        $response = null;
        $fecha_registro = time();

        try{
            $query = DB::table('sbp_boletas')
                    ->join('sbp_ctrlingreso', 'sbp_ctrlingreso.cod_local', 'sbp_boletas.cod_local')
                    ->where('idusuario', $dni)
                    ->where('estado_inicio', '!=', 2)
                    ->where('estado', 1)
                    ->where('sbp_ctrlingreso.cod_control', $sede)
                    ->where('fecha_permiso', date('Ymd',$fecha_registro))
                    ->orderBy('created_at', 'dsc')
                    ->first();
            if ($query) {

                if($query->estado_inicio == 1 && substr($query->hora_ini,0,5) == date('H:i',$fecha_registro) ){
                    $response["statusBD"] = 'error';    
                    $response["messageDB"] = "Ya se ha registrado el inicio de su boleta";
                    return $response;
                }

                $response["statusBD"] = true;
                $response["id"] = $query->id;
                $response["inicio"] = $query->estado_inicio;
                $response["hini"] = $query->hora_ini;
                $response["hfin"] = $query->hora_fin;
            } else {
                $response["statusBD"] = false;
            }
        } catch (\Exception $e) {
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al consultar en la Base de Datos. Comuniquese con el administrador";
        }
        return $response;
    }
    public function postBoleta(Request $request, $id){
        $response = null;
        $fecha_registro = time();
        $hora_minutos = null;

            $entrada = "08:00:00";
            $inireceso = "13:00:00";
            $finreceso = "14:00:00";
            $salida = "17:00:00";
            $hora_descanso = "01:00:00";

        if ($request->inicio == 2) {
            $hora_ini = date("H:i:s",strtotime($request->ini));
            $hora_final = date("H:i:s",$fecha_registro);

            if($hora_ini >= $entrada && $hora_final <= $inireceso){
                $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($hora_ini) ));
            }elseif ($hora_ini>=$finreceso && $hora_final<=$salida) {
                $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($hora_ini) ));
            }else {
                $total_hora_temp = (date("H:i:s",strtotime("00:00:00") + strtotime($hora_final) - strtotime($hora_ini) ));
                $total_hora = (date("H:i:s",strtotime("00:00:00") + strtotime($total_hora_temp) - strtotime($hora_descanso) ));
            }

            list($horas, $minutos, $segundos) = explode(':', $total_hora);
            $hora_minutos = ($horas * 60) + $minutos;        
        }

        if (date('H:i:s',$fecha_registro) > $salida) {
            $response["statusBD"] = false;
            $response["messageDB"] = "No se puede iniciar Boleta de Permiso, finalizo la Hora laboral.";
            return $response;
        }

        if (date('H:i:s',$fecha_registro) < $entrada) {
            $response["statusBD"] = false;
            $response["messageDB"] = "La boleta ya fue iniciada a las 8 de la mañana";
            return $response;
        }

        $datosPersonal = DB::table('users')
                            ->select('pers_paterno', 'pers_materno', 'pers_nombres')
                            ->join('sbp_boletas', 'sbp_boletas.idusuario', 'users.id')
                            ->where('sbp_boletas.id', $id)
                            ->first();


        try{
            $query = DB::table('sbp_boletas')
                    ->where('id', $id)
                    ->update([
                            'hora_ini' => ($request->inicio == 1)? date('H:i:s',$fecha_registro) : $request->ini,
                            'hora_fin' => ($request->inicio == 2)? $hora_final : $request->fin,
                            'total_permiso' => ($request->inicio == 2)? $hora_minutos : null,
                            'autorizacion_seg' => ($request->inicio > 0)? true :false,
                            'estado_inicio' => $request->inicio,
                    ]);
            $response["statusBD"] = true;
            $response["nombres"] = $datosPersonal->pers_nombres;
            $response["apPaterno"] = $datosPersonal->pers_paterno;
            $response["apMaterno"] = $datosPersonal->pers_materno;
            $response["messageDB"] = "Los datos fueron actualizados con exito.";
        } catch (\Exception $e) {
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al consultar en la Base de Datos. Comuniquese con el administrador";
        }
        return $response;
    }

}