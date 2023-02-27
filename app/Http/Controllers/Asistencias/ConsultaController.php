<?php

namespace App\Http\Controllers\Asistencias;

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
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, 20000);
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
                    'data'      => json_decode($response)
                ];

                //return json_decode($response);

                //return response()->json($response);
                //print_r($response);
                //print_r($info);
            } else {
                return ['status' => $info['http_code'], 'message' => curl_error($curl),'data'=> ($response)];
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
                    'data'      => json_decode($response)
                ];
            } else {
                return ['status' => $info['http_code'], 'message' => curl_error($curl), 'data'      => ($response)];
            }
            curl_close($curl);
        }
        
    }

    public function consultaMeta(Request $request){
        $url = 'http://172.28.206.57/apisybase_a/index.php/Metas/'.$request->url_meta.'?dni_u='.$request->dni_u.'&f_ini='.$request->f_ini.'&t_ini='.$request->t_ini.'&f_fin='.$request->f_fin.'&t_fin='.$request->t_fin.'';
        return $this->consultaAPI($url);
    }
    
}