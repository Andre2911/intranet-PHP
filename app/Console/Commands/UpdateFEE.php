<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\DataHito;

class UpdateFEE extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $anio = Date('Y');
        $mes = Date('m');
        for($i = 1; $i <= $mes; $i++ ){
            Log::info('Iniciando actualización de DATA HITOS: [AÑO]->'.$anio.'[MES]->'.$i);

            $url = 'http://172.28.206.57/apisybase_a/index.php/Stat/listarFEE?n_anio='.$anio.'&n_mes='.$i;
            $consulta = $this->consultaAPI($url);
            Storage :: append("archivo.txt", $consulta['status']);
            if($consulta['status'] == 200 && isset($consulta['data']) && sizeof($consulta['data']) > 0){
                DB::beginTransaction();
                try{
                    $deleteOldData = DataHito::where('n_anio_est', $anio)
                                                ->where('n_mes_est', $i)
                                                ->delete();

                    foreach ($consulta['data'] as $dato) {
                            $dataHito = DataHito::create([
                                'f_registro' => ($dato->FECHA_REG_HITO != null)? date('Ymd H:i:s', strtotime($dato->FECHA_REG_HITO)) : null,
                                'f_ingreso' =>  ($dato->FECHA_INGRESO != null)? date('Ymd H:i:s', strtotime($dato->FECHA_INGRESO)) : null,
                                'l_ind_exp' =>  $dato->TIPO_INCIDENTE,
                                'c_instancia' => $dato->COD_INSTANCIA_SIJ,
                                'n_dependencia' => $dato->NUM_DEPENDENCIA,
                                'n_anio_est'    => $dato->ANIO_ESTADISTICA*1,
                                'n_mes_est'     => $dato->MES_ESTADISTICA*1,
                                'var_id'        => $dato->VAR_ID*1,
                                //'c_acto_procesal' => $dato->COD_TIPO_ACTO_PROCESAL,
                                'c_acto_procesal_hito' => $dato->COD_ACTO_PROCESAL_HITO,
                                'x_formato'     => $dato->NUM_EXPEDIENTE,
                                'n_funcion'     => $dato->NUM_FUNCION*1,
                                'c_especialidad' => $dato->COD_ESPECIALIDAD,
                                'c_especialidad_fee' => $dato->COD_ESPECIALIDAD_FEE,
                                'c_proceso  '   => $dato->COD_PROCESO,
                                'c_delito   '   => $dato->COD_DELITO,
                                'c_provincia'   => $dato->COD_PROV_JUDICIAL_SIJ,
                                'c_distrito'    => $dato->COD_DIST_JUDICIAL_SIJ,
                                'c_org_jurisd'  => $dato->COD_ORG_JURISDICCIONAL,
                                'c_sede'        => $dato->COD_SEDE_JUDICIAL,
                                'c_dni_mag'     => $dato->DNI_JUEZ,
                                'c_dni_sec'     => $dato->DNI_SECRETARIO,
                            ]);
                        //}
                    }

                    DB::commit(); 
                    Log::info('Actualización de DATA HITOS: [AÑO]->'.$anio.'[MES]->'.$i.' [COMPLETO]');

                } catch (\Exception $e) {
                    DB::rollback();
                    Log::info("Error al actualizar en la Base de Datos.".$e);
                }
            }
        }

    }

    public function consultaAPI($url){
        if($url){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: php-curl'));
            curl_setopt($curl, CURLOPT_TIMEOUT, 200);
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, 200000);
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
                return ['status' => $info['http_code'],  'url'=> $url, 'message' => curl_error($curl),'data'=> ($response)];
            }
            curl_close($curl);
        }
        
    }
}
