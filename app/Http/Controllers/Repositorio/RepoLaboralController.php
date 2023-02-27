<?php

namespace App\Http\Controllers\Repositorio;

use App\User;
use App\Persona;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Doc2Txt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RepoLaboralController extends Controller
{
    public function index(Request $request){

        $usuario = User::find(Auth::user()->id);
        $administrador = $usuario->hasAnyRole('Repositorio.administrador|Administrador|Webmaster');
        $update = $usuario->hasAnyRole('Repositorio.laboral_upd');
        
        $usuariofinal = $usuario->hasAnyRole('Repositorio.laboral');

        
        if($request->input('listar_directorio')){
            return ['resoluciones' => $this->listar_directorio('storage\app\repositorio\laboral','/.*docx*/')];    

        }else if($request->input('listar_actas')){
            return ['resoluciones' => $this->listar_directorio('storage\app\repositorio\laboral_actas','/.*docx*/')];    

        }
        else if ($administrador) {
            
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('repositorio/laboral/registro',compact('user_roles', 'usuario'));

        } else if ($update) {
            
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('repositorio/laboral/registro',compact('user_roles', 'usuario'));

        }
         else if($usuariofinal){
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('repositorio/laboral/buscador',compact('user_roles', 'usuario'));
        } else{
            return abort(403);
        }
    }


    public function listar_directorio($folder, $pattern) {

        $registrosDB = DB::connection('sqlsrv_j')->table('scj_laboral')
                        ->select('r_archivoword')
                        ->get();

        $arrayRegistros = array();
        if(sizeof($registrosDB) > 0){
            foreach ($registrosDB as $registro) {
                array_push($arrayRegistros, $registro->r_archivoword);
            }
        }

        if (file_exists($folder)) {
            $dir = new \RecursiveDirectoryIterator($folder);
            $ite = new \RecursiveIteratorIterator($dir);
            $files = new \RegexIterator($ite, $pattern, \RegexIterator::GET_MATCH);
            $fileList = array();
            //dd($arrayRegistros);
            foreach($files as $file) {
                if(!preg_match('#[$]#', $file[0]) && !in_array(substr($file[0], 32), $arrayRegistros) ){
                    $row = array();
                    $row['r_archivo'] =substr($file[0], strlen($folder)+1);
                    if (file_exists($file[0])) {
                        $docObj = new Doc2Txt('storage\\app\\repositorio\\laboral\\'.$row['r_archivo']);

                        $txt = $docObj->convertToText();
                        //$row['crawlertxt']  = $txt;   

                        if(preg_match_all('/[0-9]{3,5}-[0-9][0-9][0-9][0-9]-[0-9]{1,3}-[0-9][0-9][0-9][0-9]-[a-zA-Z][a-zA-Z]-[a-zA-Z][a-zA-Z]-[0-9][0-9]/', $txt, $matches))
                        {
                            list($n_exp, $anio_exp, $inc_exp, $ubi_exp, $org_exp, $esp_exp, $ins_exp) = explode('-', $matches[0][0]);

                            $row['expediente']  = str_pad((string)$n_exp, 5, "0", STR_PAD_LEFT).'-'.$anio_exp.'-'.$inc_exp.'-'.$ubi_exp.'-'.$org_exp.'-'.$esp_exp.'-'.$ins_exp;   

                        } else {
                            $row['expediente']  = null;   
                        }
                        
                        $row['f_modificacion_file'] = date('Y-m-d H:i:s',filemtime($file[0]));
                        $row['size'] = filesize($file[0]);
                    }

                    array_push($fileList, $row);
                }
            }
            return $fileList;
        } else {
            return [];
        }
    }

    public function getData(Request $request){
        $repositorio_list = array();

        foreach ($request->resoluciones as $resolucion) {
            $row = array();
            $docObj = new Doc2Txt('storage\\app\\repositorio\\laboral\\'.$resolucion['r_archivo']);

            $row['r_archivo'] =$resolucion['r_archivo'];
            $row['f_modificacion_file'] = $resolucion['f_modificacion_file'];
            $row['size'] = $resolucion['size'];


            $txt = $docObj->convertToText();
            //$row['crawlertxt']  = $txt;   

            if(preg_match_all('/[0-9][0-9][0-9][0-9][0-9]-[0-9][0-9][0-9][0-9]-[0-9]+-[0-9][0-9][0-9][0-9]-[a-zA-Z][a-zA-Z]-[a-zA-Z][a-zA-Z]-[0-9][0-9]/', $txt, $matches))
            {
                $row['expediente']  = $matches[0][0];   

            } else {
                $row['expediente']  = null;   
            }

            
            array_push($repositorio_list, $row);

        }
        return  ['resoluciones' => $repositorio_list];

    }

    public function updateData(Request $request){
        $repositorio_list = array();
        $persona = Persona::find(Auth::user()->persona_id);

        DB::beginTransaction();
        try{
            foreach ($request->resoluciones as $resolucion) {
                $row = $resolucion;
                $docObj = new Doc2Txt('storage\\app\\repositorio\\laboral\\'.$resolucion['r_archivo']);

                $row['r_archivo'] =$resolucion['r_archivo'];
                $row['f_modificacion_file'] = $resolucion['f_modificacion_file'];
                $row['size'] = $resolucion['size'];


                $txt = $docObj->convertToText();
                $row['crawlertxt']  = $txt;   

                $existe = DB::connection('sqlsrv_j')->table('scj_laboral')
                                        ->where('r_archivoword', $row['r_archivo'])
                                        ->first();
                if(!$existe){
                    $insert = DB::connection('sqlsrv_j')->table('scj_laboral')
                                ->insert([
                                    'exp_n_unico' => ($row['n_unico'] != '')? strval($row['n_unico']) : null,
                                    'exp_n_incidente' => ($row['n_incidente'] != '')? strval($row['n_incidente']) : null,
                                    'c_especialidad' => ($row['c_especialidad'] != '')? strval($row['c_especialidad']) : null,
                                    'c_materia' => ($row['c_materia'] != '')? strval($row['c_materia']) : null,
                                    'x_formato' =>  ($row['expediente'] != '')? strval($row['expediente']) : null,
                                    'txt_demandado' => ($row['txt_demandado'] != '')? strval($row['txt_demandado']) : null,
                                    'txt_demandante' => ($row['txt_demandante'] != '')? strval($row['txt_demandante']) : null,
                                    'r_archivoword' => ($row['r_archivo'] != '')? strval($row['r_archivo']) : null,
                                    'crawlerword' => strval($row['crawlertxt']),
                                    'f_modificacion_file' => date('Ymd H:i:s', strtotime($row['f_modificacion_file'])),
                                    'size_file' => $row['size']*1,
                                    'user_update' => strval($persona->numero_documento),
                                ]);
                }
                
            }
            $response["statusBD"] = true;
            $response["messageDB"] = "Documentos cargados correctamente.";
        }
        catch (\Exception $e) {
            DB::rollback();
            $response["statusBD"] = false;
            $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
        }
        return $response;

    }

    public function buscador(Request $request){
        if($request->input('init')){
            $especialidad   = DB::connection('sqlsrv_j')->table('scj_especialidad')
                            ->where('l_visualiza', 'S')
                            ->get();

            $materia        = DB::connection('sqlsrv_j')->table('scj_materia_maestro')
                            ->where('l_activo', 'S')
                            ->get();


            return ['especialidad'=> $especialidad, 'materia' => $materia];
        }
        elseif ($request->input('search') && $request->input('modo')) {

            /*********************BUSQUEDA TIPO 1:  CONTENGAN SOLAMENTE ESTAS PALABRAS*/
            if ($request->modo == 1) {
                $preposiciones = array("a", "ante", "bajo",'con', 'contra', 'de', 'desde', 'en','entre', 'hacia', 'hasta', 'para', 'por', 'según', 'sin', 'so', 'sobre', 'tras', 'durante', 'mediante', 'versus', 'via', 'vía', 'y', "A", "ANTE", "BAJO",'CON', 'CONTRA', 'DE', 'DESDE', 'EN','ENTRE', 'HACIA', 'HASTA', 'PARA', 'POR', 'SEGÚN', 'SIN', 'SO', 'SOBRE', 'TRAS', 'DURANTE', 'MEDIANTE', 'VERSUS', 'VIA', 'VÍA', 'Y');
                $palabras = explode(" ", $request->buscar);

                $b_especialidad = false;
                $b_materia = false;
                $b_acto = false;

                $n_resultados = DB::connection('sqlsrv_j')->table('scj_laboral')
                                ->select(DB::RAW("count(id) AS n_resultados"))
                                ->when (1, function ($query) use ($palabras, $preposiciones)
                                {
                                    $subquery = '';
                                    $j=0;
                                    for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                        $key = array_search($palabras[$i], $preposiciones);
                                        if(false !== $key){

                                        } else{
                                            if ($j != 0) {
                                                $subquery .= ' AND ';
                                            }
                                            $subquery .= "CONTAINS (crawlerword, '".$palabras[$i]."' )";        
                                            $j++;
                                        }      
                                    }   
                                    return $query->whereRAW($subquery);                                 
                                })
                               
                                ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral.c_materia')
                                //->whereNotNull('r_archivopdf')
                                ->get();


                $filtros = DB::connection('sqlsrv_j')->table('scj_laboral')
                                ->select(DB::RAW("DISTINCT scj_laboral.c_especialidad, scj_laboral.c_materia"))
                                ->when (1, function ($query) use ($palabras, $preposiciones)
                                {
                                    $subquery = '';
                                    $j=0;
                                    for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                        $key = array_search($palabras[$i], $preposiciones);
                                        if(false !== $key){

                                        } else{
                                            if ($j != 0) {
                                                $subquery .= ' AND ';
                                            }
                                            $subquery .= "CONTAINS (crawlerword, '".$palabras[$i]."' )";        
                                            $j++;
                                        }      
                                    }   
                                    return $query->whereRAW($subquery);                                 
                                })
                                ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral.c_materia')
                                ->whereNotNull('scj_laboral.c_especialidad')
                                ->get();
               

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select('scj_laboral.*', 'scj_materia_maestro.x_desc_materia')
                            ->when (1, function ($query) use ($palabras, $preposiciones)
                            {
                                $subquery = '';
                                $j=0;
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                        $key = array_search($palabras[$i], $preposiciones);
                                        if(false !== $key){

                                        } else{
                                            if ($j != 0) {
                                                $subquery .= ' AND ';
                                            }
                                            $subquery .= "CONTAINS (crawlerword, '".$palabras[$i]."' )";        
                                            $j++;
                                        }
                                    
                                }   
                                return $query->whereRAW($subquery);                                 
                            })
                            ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral.c_materia')
                            //->whereNotNull('r_archivopdf')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('f_modificacion_file', 'desc')
                            ->get();
                /*foreach ($resoluciones as $resolucion) {
                    $resolucion->crawlerword = utf8_encode(substr($resolucion->crawlerword, 200,1000));
                }*/

            }


            /*********************BUSQUEDA TIPO 2:  CONTENGAN ALGUNAS DE ESTAS PALABRAS*/

            if ($request->modo == 2) {

                $palabras = explode(" ", $request->buscar);

                $n_resultados = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select(DB::RAW("count(id) AS n_resultados"))
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerword, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select(DB::RAW("DISTINCT scj_laboral.c_especialidad, scj_laboral.c_materia"))
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerword, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->get();

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select('scj_laboral.*', 'scj_materia_maestro.x_desc_materia')
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerword, '".$palabras[$i]."' )");    
                                }                                
                            })
                            
                            ->join('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('f_modificacion_file', 'desc')
                            ->get();


            }

            /*********************BUSQUEDA TIPO 3: FRASE COMPLETA*/

            if ($request->modo == 3) {
                $palabras = $request->buscar;

                 $n_resultados = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select(DB::RAW("count(id) AS n_resultados"))
                            ->whereRAW( "CONTAINS (crawlerword, '".'"'.$request->buscar.'"'."' )")
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select(DB::RAW("DISTINCT scj_laboral.c_especialidad, scj_laboral.c_materia"))
                            ->whereRAW( "CONTAINS (crawlerword, '".'"'.$request->buscar.'"'."' )")
                            ->get();

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select('scj_laboral.*', 'scj_materia_maestro.x_desc_materia')
                            ->whereRAW( "CONTAINS (crawlerword, '".'"'.$request->buscar.'"'."' )")
                            
                            ->join('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('f_modificacion_file', 'desc')
                            ->get();

              
            }

            return [
                    'resoluciones' => $resoluciones,
                    'filtros' => $filtros,
                    'palabras' => $palabras,
                    'n_resultados' => $n_resultados,
                ];
        }
        else{
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('repositorio/laboral/buscador',compact('user_roles', 'usuario'));
        }
    }

    public function buscador_post(Request $request){
        $response = null;

        if ($request->modo == 1) {
                $preposiciones = array("a", "ante", "bajo",'con', 'contra', 'de', 'desde', 'en','entre', 'hacia', 'hasta', 'para', 'por', 'según', 'sin', 'so', 'sobre', 'tras', 'durante', 'mediante', 'versus', 'via', 'vía', 'y', "A", "ANTE", "BAJO",'CON', 'CONTRA', 'DE', 'DESDE', 'EN','ENTRE', 'HACIA', 'HASTA', 'PARA', 'POR', 'SEGÚN', 'SIN', 'SO', 'SOBRE', 'TRAS', 'DURANTE', 'MEDIANTE', 'VERSUS', 'VIA', 'VÍA', 'Y');
                $palabras = explode(" ", $request->buscar);

                $b_especialidad = false;
                $b_materia = false;
                $b_acto = false;

                if ($request->input('esp') || $request->input('mat') || $request->input('acto')) {
                    $b_especialidad = (sizeof($request->esp) == 0)? false: true;
                    $b_materia = (sizeof($request->mat) == 0 )? false: true;
                    $b_acto = (sizeof($request->acto) == 0)? false: true;

                    $especialidad = $request->esp;
                    $materia = $request->mat;
                    $acto = $request->acto;   
                }
                


                $n_resultados = DB::connection('sqlsrv_j')->table('scj_laboral')
                                ->select(DB::RAW("count(id) AS n_resultados"))
                                ->when (1, function ($query) use ($palabras, $preposiciones)
                                {
                                    $subquery = '';
                                    $j=0;
                                    for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                        $key = array_search($palabras[$i], $preposiciones);
                                        if(false !== $key){

                                        } else{
                                            if ($j != 0) {
                                                $subquery .= ' AND ';
                                            }
                                            $subquery .= "CONTAINS (crawlerword, '".$palabras[$i]."' )";        
                                            $j++;
                                        }      
                                    }   
                                    return $query->whereRAW($subquery);                                 
                                })
                                ->when($b_especialidad == true, function($query) use ($especialidad){
                                    return $query->whereIN('scj_laboral.c_especialidad', $especialidad);
                                })
                                ->when($b_materia, function($query) use ($materia){
                                    return $query->whereIN('c_materia', $materia);
                                })
                                ->get();
                                
                $filtros = DB::connection('sqlsrv_j')->table('scj_laboral')
                                ->select(DB::RAW("DISTINCT scj_laboral.c_especialidad, scj_laboral.c_materia"))
                                ->when (1, function ($query) use ($palabras, $preposiciones)
                                    {
                                        $subquery = '';
                                        $j=0;
                                        for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                            $key = array_search($palabras[$i], $preposiciones);
                                            if(false !== $key){
    
                                            } else{
                                                if ($j != 0) {
                                                    $subquery .= ' AND ';
                                                }
                                                $subquery .= "CONTAINS (crawlerword, '".$palabras[$i]."' )";        
                                                $j++;
                                            }      
                                        }   
                                        return $query->whereRAW($subquery);                                 
                                    })
                                ->get();

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select('scj_laboral.*','scj_materia_maestro.x_desc_materia')
                            ->when (1, function ($query) use ($palabras, $preposiciones)
                            {
                                $subquery = '';
                                $j=0;
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                        $key = array_search($palabras[$i], $preposiciones);
                                        if(false !== $key){

                                        } else{
                                            if ($j != 0) {
                                                $subquery .= ' AND ';
                                            }
                                            $subquery .= "CONTAINS (crawlerword, '".$palabras[$i]."' )";        
                                            $j++;
                                        }
                                    
                                }   
                                return $query->whereRAW($subquery);                                 
                            })
                            ->when($b_especialidad, function($query) use ($especialidad){
                                    return $query->whereIN('scj_laboral.c_especialidad', $especialidad);
                            })
                            ->when($b_materia, function($query) use ($materia){
                                return $query->whereIN('scj_laboral.c_materia', $materia);
                            })
                            ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('f_modificacion_file', 'desc')
                            ->get();

            return [
                    'resoluciones' => $resoluciones,
                    'filtros' => $filtros,
                    'palabras' => $palabras,
                    'n_resultados' => $n_resultados,
                ];
        }
        if ($request->modo == 2) {

                $palabras = explode(" ", $request->buscar);

                $n_resultados = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select(DB::RAW("count(id) AS n_resultados"))
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerword, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select(DB::RAW("DISTINCT scj_laboral.c_especialidad, scj_laboral.c_materia"))
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerword, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->get();


                $resoluciones = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select('scj_laboral.*', 'scj_materia_maestro.x_desc_materia')
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerword, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('f_modificacion_file', 'desc')
                            ->get();

            return [
                    'resoluciones' => $resoluciones,
                    'filtros' => $filtros,
                    'palabras' => $palabras,
                    'n_resultados' => $n_resultados,
                ];
        }
        if ($request->modo == 3) {
                $palabras = $request->buscar;

                 $n_resultados = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select(DB::RAW("count(id) AS n_resultados"))
                            ->whereRAW( "CONTAINS (crawlerword, '".'"'.$request->buscar.'"'."' )")
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select(DB::RAW("DISTINCT scj_laboral.c_especialidad, scj_laboral.c_materia"))
                            ->whereRAW( "CONTAINS (crawlerword, '".'"'.$request->buscar.'"'."' )")
                            ->get();


                $resoluciones = DB::connection('sqlsrv_j')->table('scj_laboral')
                            ->select('scj_laboral.*', 'scj_materia_maestro.x_desc_materia')
                            ->whereRAW( "CONTAINS (crawlerword, '".'"'.$request->buscar.'"'."' )")
                            ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('f_modificacion_file', 'desc')
                            ->get();  
            return [
                    'resoluciones' => $resoluciones,
                    'palabras' => $palabras,
                    'filtros' => $filtros,
                    'n_resultados' => $n_resultados,
                ]; 
        }
    }



    public function buscadorActas(Request $request){
        if($request->input('init')){
            $especialidad   = DB::connection('sqlsrv_j')->table('scj_especialidad')
                            ->where('l_visualiza', 'S')
                            ->get();

            $materia        = DB::connection('sqlsrv_j')->table('scj_materia_maestro')
                            ->where('l_activo', 'S')
                            ->get();


            return ['especialidad'=> $especialidad, 'materia' => $materia];
        }
        elseif ($request->input('search') && $request->input('modo')) {

            /*********************BUSQUEDA TIPO 1:  CONTENGAN SOLAMENTE ESTAS PALABRAS*/
            if ($request->modo == 1) {
                $preposiciones = array("a", "ante", "bajo",'con', 'contra', 'de', 'desde', 'en','entre', 'hacia', 'hasta', 'para', 'por', 'según', 'sin', 'so', 'sobre', 'tras', 'durante', 'mediante', 'versus', 'via', 'vía', 'y', "A", "ANTE", "BAJO",'CON', 'CONTRA', 'DE', 'DESDE', 'EN','ENTRE', 'HACIA', 'HASTA', 'PARA', 'POR', 'SEGÚN', 'SIN', 'SO', 'SOBRE', 'TRAS', 'DURANTE', 'MEDIANTE', 'VERSUS', 'VIA', 'VÍA', 'Y');
                $palabras = explode(" ", $request->buscar);

                $b_especialidad = false;
                $b_materia = false;
                $b_acto = false;

                $n_resultados = DB::connection('sqlsrv_j')->table('scj_laboral_actas')
                                ->select(DB::RAW("count(id) AS n_resultados"))
                                ->when (1, function ($query) use ($palabras, $preposiciones)
                                {
                                    $subquery = '';
                                    $j=0;
                                    for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                        $key = array_search($palabras[$i], $preposiciones);
                                        if(false !== $key){

                                        } else{
                                            if ($j != 0) {
                                                $subquery .= ' AND ';
                                            }
                                            $subquery .= "CONTAINS (crawlerword, '".$palabras[$i]."' )";        
                                            $j++;
                                        }      
                                    }   
                                    return $query->whereRAW($subquery);                                 
                                })
                               
                                ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral_actas.c_materia')
                                //->whereNotNull('r_archivopdf')
                                ->get();


                $filtros = DB::connection('sqlsrv_j')->table('scj_laboral_actas')
                                ->select(DB::RAW("DISTINCT scj_laboral_actas.c_especialidad, scj_laboral_actas.c_materia"))
                                ->when (1, function ($query) use ($palabras, $preposiciones)
                                {
                                    $subquery = '';
                                    $j=0;
                                    for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                        $key = array_search($palabras[$i], $preposiciones);
                                        if(false !== $key){

                                        } else{
                                            if ($j != 0) {
                                                $subquery .= ' AND ';
                                            }
                                            $subquery .= "CONTAINS (crawlerword, '".$palabras[$i]."' )";        
                                            $j++;
                                        }      
                                    }   
                                    return $query->whereRAW($subquery);                                 
                                })
                                ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral_actas.c_materia')
                                ->whereNotNull('scj_laboral_actas.c_especialidad')
                                ->get();
               

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_laboral_actas')
                            ->select('scj_laboral_actas.*', 'scj_materia_maestro.x_desc_materia')
                            ->when (1, function ($query) use ($palabras, $preposiciones)
                            {
                                $subquery = '';
                                $j=0;
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                        $key = array_search($palabras[$i], $preposiciones);
                                        if(false !== $key){

                                        } else{
                                            if ($j != 0) {
                                                $subquery .= ' AND ';
                                            }
                                            $subquery .= "CONTAINS (crawlerword, '".$palabras[$i]."' )";        
                                            $j++;
                                        }
                                    
                                }   
                                return $query->whereRAW($subquery);                                 
                            })
                            ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral_actas.c_materia')
                            //->whereNotNull('r_archivopdf')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('f_modificacion_file', 'desc')
                            ->get();
                /*foreach ($resoluciones as $resolucion) {
                    $resolucion->crawlerword = utf8_encode(substr($resolucion->crawlerword, 200,1000));
                }*/

            }


            /*********************BUSQUEDA TIPO 2:  CONTENGAN ALGUNAS DE ESTAS PALABRAS*/

            if ($request->modo == 2) {

                $palabras = explode(" ", $request->buscar);

                $n_resultados = DB::connection('sqlsrv_j')->table('scj_laboral_actas')
                            ->select(DB::RAW("count(id) AS n_resultados"))
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerword, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_laboral_actas')
                            ->select(DB::RAW("DISTINCT scj_laboral_actas.c_especialidad, scj_laboral_actas.c_materia"))
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerword, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->get();

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_laboral_actas')
                            ->select('scj_laboral_actas.*', 'scj_materia_maestro.x_desc_materia')
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerword, '".$palabras[$i]."' )");    
                                }                                
                            })
                            
                            ->join('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral_actas.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('f_modificacion_file', 'desc')
                            ->get();


            }

            /*********************BUSQUEDA TIPO 3: FRASE COMPLETA*/

            if ($request->modo == 3) {
                $palabras = $request->buscar;

                 $n_resultados = DB::connection('sqlsrv_j')->table('scj_laboral_actas')
                            ->select(DB::RAW("count(id) AS n_resultados"))
                            ->whereRAW( "CONTAINS (crawlerword, '".'"'.$request->buscar.'"'."' )")
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_laboral_actas')
                            ->select(DB::RAW("DISTINCT scj_laboral_actas.c_especialidad, scj_laboral_actas.c_materia"))
                            ->whereRAW( "CONTAINS (crawlerword, '".'"'.$request->buscar.'"'."' )")
                            ->get();

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_laboral_actas')
                            ->select('scj_laboral_actas.*', 'scj_materia_maestro.x_desc_materia')
                            ->whereRAW( "CONTAINS (crawlerword, '".'"'.$request->buscar.'"'."' )")
                            
                            ->join('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_laboral_actas.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('f_modificacion_file', 'desc')
                            ->get();

              
            }

            return [
                    'resoluciones' => $resoluciones,
                    'filtros' => $filtros,
                    'palabras' => $palabras,
                    'n_resultados' => $n_resultados,
                ];
        }
        else{
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('repositorio/laboral/actas',compact('user_roles', 'usuario'));
        }
    }



    public function download($id)
    {
        $documento = DB::connection('sqlsrv_j')->table('scj_laboral')
                ->where('id', $id)
                ->first();

        //header('content-Type:'.$documento->content_type);
        $rutaDeArchivo = storage_path()."\\app\\repositorio\\laboral\\".$documento->r_archivoword; 
        //dd($rutaDeArchivo);

        return response()->download($rutaDeArchivo);

 
    }


    public function actas(){

        
        $usuario = User::find(Auth::user()->id);

        
        $administrador = $usuario->hasAnyRole('Repositorio.administrador|Administrador|Webmaster');
        
        $usuariofinal = $usuario->hasAnyRole('Repositorio.laboral');


        if ($administrador) {
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('repositorio/laboral/registro_actas',compact('user_roles', 'usuario'));

        } else if($usuariofinal){
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('repositorio/laboral/actas',compact('user_roles', 'usuario'));
        } else{
            return abort(403);
        }
    }


    public function getUser(){
        $usuario = DB::table('users')
                            ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->where('users.id', Auth::user()->id)
                            ->first();
        return json_encode($usuario);
    }

    public function getRoles(){
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
        }
        return  json_encode($user_roles);
    }
}