<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\DataRepo;

class UpdateS1AE extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:S1AE';

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

            $url = 'http://172.28.206.57/apisybase_a/index.php/Stat/listarFormSIJ?form=S1A-E&n_anio='.$anio.'&n_mes='.$i;
            $consulta = $this->consultaAPI($url);
            Storage :: append("archivo.txt", 'actualización de DATA HITOS [SIA-E]: [AÑO]->'.$anio.'[MES]->'.$i.'[status]'.$consulta['status']);
            if($consulta['status'] == 200 && isset($consulta['data'])){
                Log::info('Registrando datos  S1A-E de DATA HITOS: [AÑO]->'.$anio.'[MES]->'.$i.'[#Resultados]->'.$consulta['data']);
                Log::info('Actualización de DATA HITOS: [AÑO]->'.$anio.'[MES]->'.$i.' [COMPLETO]');
            } else{
                Log::error('Error actualización de DATA HITOS S1A-E: [AÑO]->'.$anio.'[MES]->'.$i);
            }
        }

    }

    public function consultaAPI($url){
        if($url){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: php-curl'));
            curl_setopt($curl, CURLOPT_TIMEOUT, 2000);
            //curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1000000);
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
