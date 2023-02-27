<?php

namespace App\Http\Controllers\SIJ;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsultaController extends Controller
{
    public function consultaAPI($url){
        if($url){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: php-curl'));
            curl_setopt($curl, CURLOPT_TIMEOUT, 200);
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, 200000);
            /**
             * CURLOPT_SSL_VERIFYPEER
             * FALSE para que cURL no verifique el peer del certificado.
             * Para usar diferentes certificados para la verificación se pueden especificar con la opción CURLOPT_CAINFO
             * o se puede especificar el directorio donde se encuentra el certificado con la opción CURLOPT_CAPATH.
             * - tomado de php.net
             *
             */
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            /**
             * curl_exec
             * Ejecuta la sesión cURL que se le pasa como parámetro.
             * - tomado de php.net
             */
            $response = curl_exec($curl);
            /**
             *  curl_getinfo
             *  Obtener información relativa a una transferencia específica
             *  - tomado de php.net
             */
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 200) {
                return [
                    'status'    => $info['http_code'], 
                    'message'   => curl_error($curl),
                    'data'      => json_decode($response),
                    'url'      => $url
                ];

                //return json_decode($response);

                //return response()->json($response);
                //print_r($response);
                //print_r($info);
            } else {
                return ['status' => $info['http_code'],  'url'=> $url, 'message' => curl_error($curl),'data'=> ($response)];
                //echo "Curl error: " . curl_error($curl);
            }
            curl_close($curl);
            //$webService->get();
        }
        
    }

    public function consultaAPI_POST($url, $data){
        if($url){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: php-curl','Content-Type: application/json' ));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

            curl_setopt($curl, CURLOPT_TIMEOUT, 100);
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1000000);
           
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 200) {
                return [
                    'status'    => $info['http_code'], 
                    'message'   => curl_error($curl),
                    'data'      => json_decode($response),
                    'url'      => $url

                ];
            } else {
                return ['status' => $info['http_code'], 'message' => curl_error($curl), 'data'      => ($response)];
            }
            curl_close($curl);
        }
        
    }

    public function listarInstancia(Request $request){
        $url = "http://".env('APP_SYBASE')."/apisybase/index.php/Sij/listarInstancias";
        return $this->consultaAPI($url);
    }
    public function listarInstanciasUser(Request $request){
        $url = "http://".env('APP_SYBASE')."/apisybase/index.php/Sij/listarInstanciasUser?c_dni_usuario=".$request->c_dni;
        return $this->consultaAPI($url);
    }
    public function listarAudiencias(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/listarAudiencias?c_provincia='.$request->c_provincia.'&c_sede='.$request->c_sede.'&c_org_jurisd='.$request->c_org_jurisd.'&c_especialidad='.$request->c_especialidad.'&c_instancia='.$request->c_instancia.'&f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }
    public function dataAudiencia(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/dataAudiencia?c_provincia='.$request->c_provincia.'&c_sede='.$request->c_sede.'&c_org_jurisd='.$request->c_org_jurisd.'&c_especialidad='.$request->c_especialidad.'&c_instancia='.$request->c_instancia.'&f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'&NUM_EXPEDIENTE='.$request->x_formato.'&FECHA_INI_ACTIVIDAD='.str_replace(" ", '%20',$request->f_ini_aud).'&FECHA_FIN_ACTIVIDAD='.str_replace(" ", '%20',$request->f_fin_aud);
        return $this->consultaAPI($url);
    }
    public function dataAudActa(Request $request){
        $url = 'http://'.env('APP_SYBASE').':9001/downloadFileFTP/?ruta='.$request->ruta.'&filename='.$request->filename.'&destino=\\\\'.env('APP_SERVER').'\\resoluciones$&ftpUsername='.$request->ftpUsername.'&ftpPassword='.$request->ftpPassword.'&ftpIP='.$request->ftpIP.':21&filename_destino='.$request->filename_destino;
        $url = str_replace(" ", '%20',$url);
       // return $url;
        return $this->consultaAPI($url);
    }

    public function downloadFileFTP(Request $request){
        $url = 'http://'.env('APP_SYBASE').':9001/downloadFileFTP/?ruta='.$request->ruta.'&filename='.$request->filename.'&destino='.$request->destino.'&ftpUsername='.$request->ftpUsername.'&ftpPassword='.$request->ftpPassword.'&ftpIP='.$request->ftpIP.':21&filename_destino='.$request->filename_destino;
        $url = str_replace(" ", '%20',$url);
        return $this->consultaAPI($url);
    }

    public function listarEscritos(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/listarEscritos?c_provincia='.$request->c_provincia.'&c_sede='.$request->c_sede.'&c_org_jurisd='.$request->c_org_jurisd.'&c_especialidad='.$request->c_especialidad.'&c_instancia='.$request->c_instancia.'&f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }
    
    public function dataEscrito(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/dataEscrito';
        return $this->consultaAPI_POST($url, array(
                                                'c_provincia' => $request->c_provincia,
                                                'c_sede' => $request->c_sede,
                                                'c_org_jurisd' => $request->c_org_jurisd,
                                                'c_especialidad' => $request->c_especialidad, 
                                                'c_instancia' => $request->c_instancia,
                                                'f_ini' => $request->f_ini,
                                                'f_fin' => $request->f_fin,
                                                'NUM_EXPEDIENTE' => $request->x_formato,
                                                'FECHA_INGRESO_ESCRITO' => $request->f_ing_escr,
                                                'COD_UNICO_EXPEDIENTE' => $request->n_unico 
                                            ));

    }


    public function listarResolucionesrsij(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/listarResolucionesrsij?c_provincia='.$request->c_provincia.'&c_sede='.$request->c_sede.'&c_org_jurisd='.$request->c_org_jurisd.'&c_especialidad='.$request->c_especialidad.'&c_instancia='.$request->c_instancia.'&f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }

    public function dataResolucion(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/dataResolucion';
        return $this->consultaAPI_POST($url, array(
                                                'c_provincia' => $request->c_provincia,
                                                'c_sede' => $request->c_sede,
                                                'c_org_jurisd' => $request->c_org_jurisd,
                                                'c_especialidad' => $request->c_especialidad, 
                                                'c_instancia' => $request->c_instancia,
                                                'f_ini' => $request->f_ini,
                                                'f_fin' => $request->f_fin,
                                                'FECHA_RESOL_PROYECTO' => $request->f_res_proy,
                                                'COD_UNICO_EXPEDIENTE' => $request->n_unico 
                                            ));

    }



    public function listarNotificaciones(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/listarNotificaciones?c_provincia='.$request->c_provincia.'&c_sede='.$request->c_sede.'&c_org_jurisd='.$request->c_org_jurisd.'&c_especialidad='.$request->c_especialidad.'&c_instancia='.$request->c_instancia.'&f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }

    /***** MODULO REPOSITORIO LABORAL PRIVADO */
    public function listarResoluciones(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/listarResoluciones?c_provincia='.$request->c_provincia.'&c_sede='.$request->c_sede.'&c_org_jurisd='.$request->c_org_jurisd.'&c_especialidad='.$request->c_especialidad.'&c_instancia='.$request->c_instancia.'&f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }
    
    /***** MODULO REPOSITORIO LABORAL PRIVADO */
    public function listarCupones(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/listarCupones';
        return $this->consultaAPI_POST($url, array('nap_secretario' => $request->nap_secretario,'nam_secretario' => $request->nam_secretario,'nno_secretario' => $request->nno_secretario ));
    }


    public function consultaExpedienteMas(Request $request){

        $url = 'http://'.env('APP_SYBASE').'/apisybase()/azangaro/index.php/Api/datosExpedientebyXFMas';
        return $this->consultaAPI_POST($url, array('resoluciones' => $request->resoluciones));
    }


    public function consultaXusuario(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Usuario/'.$request->url_meta.'?dni_u='.$request->dni_u.'&f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }


    public function listarExpedientes(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/listarExpedientes?c_provincia='.$request->c_provincia.'&c_org_jurisd='.$request->c_org_jurisd.'&c_especialidad='.$request->c_especialidad.'&c_instancia='.$request->c_instancia.'&n_expediente='.$request->n_expediente.'&n_anio='.$request->n_anio.'';
        return $this->consultaAPI($url);
    }

    public function buscarCedulas(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Notificacion/buscarCedulas?n_unico='.$request->n_unico.'&n_incidente='.$request->n_incidente.'';
        return $this->consultaAPI($url);
    }
    public function buscarDigitalizados(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Notificacion/listarDigitalizados?n_unico='.$request->n_unico.'&n_incidente='.$request->n_incidente.'';
        return $this->consultaAPI($url);
    }


    public function listarDataHitos(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Stat/listarFEE?n_anio='.$request->anio.'&n_mes='.$request->mes.'';
        return $this->consultaAPI($url);
    }

    public function listarMOficinas(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Stat/listarMOficinas';
        return $this->consultaAPI($url);
    }


    public function reporteEscritos(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/reporteEscritos?f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }
    public function reporteDemandas(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/reporteDemandas?f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }
    public function reporteResoluciones(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/reporteResoluciones?f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }
    public function reporteAudiencias(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/reporteAudiencias?f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }
    public function reporteNotificaciones(Request $request){
        $url = 'http://'.env('APP_SYBASE').'/apisybase/index.php/Sij/reporteNotificaciones?f_ini='.$request->f_ini.'&f_fin='.$request->f_fin.'';
        return $this->consultaAPI($url);
    }
    
}