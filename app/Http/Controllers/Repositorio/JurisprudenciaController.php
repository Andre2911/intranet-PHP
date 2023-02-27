<?php

namespace App\Http\Controllers\Repositorio;

use App\User;
use App\Persona;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Doc2Txt;
use App\Models\Repositorio;
use App\Models\ResolucionInstancia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class JurisprudenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index(Request $request)
    {
        $usuario = User::find(Auth::user()->id);
        $administrador = $usuario->hasAnyRole('Jurisprudencia.administrador|Administrador');
        $usuariofinal = $usuario->hasAnyRole('Jurisprudencia.usuario');

        if ($request->input('init')) {
            $especialidad   = DB::connection('sqlsrv_j')->table('scj_especialidad')
                            ->where('l_visualiza', 'S')
                            ->get();

            $materia        = DB::connection('sqlsrv_j')->table('scj_materia_maestro')
                            ->where('l_activo', 'S')
                            ->get();

            $acto        = DB::connection('sqlsrv_j')->table('scj_acto_procesal')
                            ->select(DB::raw("DISTINCT c_acto as c_acto_procesal, x_desc_acto_procesal, c_especialidad"))
                            ->where('l_activo', 'S')
                            ->get();

            return ['especialidad'=> $especialidad, 'materia' => $materia, 'acto'=> $acto];
        }
        if ($request->input('listar')) {
            if ($request->input('mes') && $request->input('anio')) {
                if ($request->input('mes') < 10) {
                    return ['resoluciones' => $this->listar_directorio('storage\app\resoluciones'.'\\'.$request->anio.'\0'.$request->mes,'/.*doc/')];    
                } else{
                    return ['resoluciones' => $this->listar_directorio('storage\app\resoluciones'.'\\'.$request->anio.'\\'.$request->mes,'/.*doc/')];    
                }
            }else if ($request->input('anio')) {
                return ['resoluciones' => $this->listar_directorio('storage\app\resoluciones'.'\\'.$request->anio,'/.*doc/')];    
            } else{
                if (date('m') < 10) {
                    return ['resoluciones' => $this->listar_directorio('storage\app\resoluciones'.'\\'.date('Y').'\0'.date('m'),'/.*doc/')];    
                } else{
                    return ['resoluciones' => $this->listar_directorio('storage\app\resoluciones'.'\\'.date('Y').'\\'.date('m'),'/.*doc/')];       
                }

                
                //return ['resoluciones' => $this->listar_directorio('storage\app\resoluciones','/.*doc/')];    
            }
            
        } elseif ($request->input('search') && $request->input('modo')) {

            /*********************BUSQUEDA TIPO 1:  CONTENGAN SOLAMENTE ESTAS PALABRAS*/
            if ($request->modo == 1) {
                $preposiciones = array("a", "ante", "bajo",'con', 'contra', 'de', 'desde', 'en','entre', 'hacia', 'hasta', 'para', 'por', 'según', 'sin', 'so', 'sobre', 'tras', 'durante', 'mediante', 'versus', 'via', 'vía', 'y', "A", "ANTE", "BAJO",'CON', 'CONTRA', 'DE', 'DESDE', 'EN','ENTRE', 'HACIA', 'HASTA', 'PARA', 'POR', 'SEGÚN', 'SIN', 'SO', 'SOBRE', 'TRAS', 'DURANTE', 'MEDIANTE', 'VERSUS', 'VIA', 'VÍA', 'Y');
                $palabrasf = str_replace(",","", $request->buscar);
                $palabrasf = str_replace('"',"", $palabrasf);
                $palabrasf = str_replace("'","", $palabrasf);
                $palabras = explode(" ", $palabrasf);

                $b_especialidad = false;
                $b_materia = false;
                $b_acto = false;

                $n_resultados = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                                ->select(DB::RAW("count(id_ingreso) AS n_resultados"))
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
                                            $subquery .= "CONTAINS (crawlerpdf, '".$palabras[$i]."' )";        
                                            $j++;
                                        }      
                                    }   
                                    return $query->whereRAW($subquery);                                 
                                })
                                ->leftJoin('scj_acto_procesal', function($join){
                                    $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                    $join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                                    })
                                ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_resoluciones.c_materia')
                                //->whereNotNull('r_archivopdf')
                                ->get();


                $filtros = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                                ->select(DB::RAW("DISTINCT scj_resoluciones.c_especialidad, scj_resoluciones.c_materia, scj_resoluciones.c_acto_procesal"))
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
                                            $subquery .= "CONTAINS (crawlerpdf, '".$palabras[$i]."' )";        
                                            $j++;
                                        }      
                                    }   
                                    return $query->whereRAW($subquery);                                 
                                })
                                ->leftJoin('scj_acto_procesal', function($join){
                                    $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                    $join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                                    })
                                ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_resoluciones.c_materia')
                                ->get();
               

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select('scj_resoluciones.*', 'scj_acto_procesal.x_desc_acto_procesal', 'scj_materia_maestro.x_desc_materia')
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
                                            $subquery .= "CONTAINS (crawlerpdf, '".$palabras[$i]."' )";        
                                            $j++;
                                        }
                                    
                                }   
                                return $query->whereRAW($subquery);                                 
                            })
                            ->leftJoin('scj_acto_procesal', function($join){
                                $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                $join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                                })
                            ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_resoluciones.c_materia')
                            //->whereNotNull('r_archivopdf')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('anio_res', 'desc')
                            ->orderBy('mes_res', 'desc')
                            ->get();
                /*foreach ($resoluciones as $resolucion) {
                    $resolucion->crawlerpdf = utf8_encode(substr($resolucion->crawlerpdf, 200,1000));
                }*/

            }


            /*********************BUSQUEDA TIPO 2:  CONTENGAN ALGUNAS DE ESTAS PALABRAS*/

            if ($request->modo == 2) {

                $palabras = explode(" ", $request->buscar);

                $n_resultados = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select(DB::RAW("count(id_ingreso) AS n_resultados"))
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerpdf, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select(DB::RAW("DISTINCT scj_resoluciones.c_especialidad, scj_resoluciones.c_materia, scj_resoluciones.c_acto_procesal"))
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerpdf, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->get();

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select('scj_resoluciones.*', 'scj_acto_procesal.x_desc_acto_procesal', 'scj_materia_maestro.x_desc_materia')
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerpdf, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->join('scj_acto_procesal', function($join){
                                $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                $join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                                })
                            ->join('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_resoluciones.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('anio_res', 'desc')
                            ->orderBy('mes_res', 'desc')
                            ->get();


            }

            /*********************BUSQUEDA TIPO 3: FRASE COMPLETA*/

            if ($request->modo == 3) {
                $palabras = $request->buscar;

                 $n_resultados = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select(DB::RAW("count(id_ingreso) AS n_resultados"))
                            ->whereRAW( "CONTAINS (crawlerpdf, '".'"'.$request->buscar.'"'."' )")
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select(DB::RAW("DISTINCT scj_resoluciones.c_especialidad, scj_resoluciones.c_materia, scj_resoluciones.c_acto_procesal"))
                            ->whereRAW( "CONTAINS (crawlerpdf, '".'"'.$request->buscar.'"'."' )")
                            ->get();

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select('scj_resoluciones.*', 'scj_acto_procesal.x_desc_acto_procesal', 'scj_materia_maestro.x_desc_materia')
                            ->whereRAW( "CONTAINS (crawlerpdf, '".'"'.$request->buscar.'"'."' )")
                            ->leftjoin('scj_acto_procesal', function($join){
                                $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                $join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                                })
                            ->leftjoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_resoluciones.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('anio_res', 'desc')
                            ->orderBy('mes_res', 'desc')
                            ->get();

              
            }

            return [
                    'resoluciones' => $resoluciones,
                    'filtros' => $filtros,
                    'palabras' => $palabras,
                    'n_resultados' => $n_resultados,
                ];
        }
        else if ($administrador) {
            
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('repositorio/jurisprudencia/registro',compact('user_roles', 'usuario'));

        } else if($usuariofinal){
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('repositorio/jurisprudencia/buscador',compact('user_roles', 'usuario'));
        }
    }
   
    public function store(Request $request)
    {
        $response = null;

        if ($request->modo == 1) {
                $preposiciones = array("a", "ante", "bajo",'con', 'contra', 'de', 'desde', 'en','entre', 'hacia', 'hasta', 'para', 'por', 'según', 'sin', 'so', 'sobre', 'tras', 'durante', 'mediante', 'versus', 'via', 'vía', 'y', "A", "ANTE", "BAJO",'CON', 'CONTRA', 'DE', 'DESDE', 'EN','ENTRE', 'HACIA', 'HASTA', 'PARA', 'POR', 'SEGÚN', 'SIN', 'SO', 'SOBRE', 'TRAS', 'DURANTE', 'MEDIANTE', 'VERSUS', 'VIA', 'VÍA', 'Y');
                $palabrasf = str_replace(",","", $request->buscar);
                $palabrasf = str_replace('"',"", $palabrasf);
                $palabrasf = str_replace("'","", $palabrasf);
                $palabras = explode(" ", $palabrasf);

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
                


                $n_resultados = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                                ->select(DB::RAW("count(id_ingreso) AS n_resultados"))
                                ->leftjoin('scj_acto_procesal', function($join){
                                    $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                    //$join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                                })
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
                                            $subquery .= "CONTAINS (crawlerpdf, '".$palabras[$i]."' )";        
                                            $j++;
                                        }      
                                    }   
                                    return $query->whereRAW($subquery);                                 
                                })
                                ->when($b_especialidad == true, function($query) use ($especialidad){
                                    return $query->whereIN('scj_resoluciones.c_especialidad', $especialidad);
                                })
                                ->when($b_materia, function($query) use ($materia){
                                    return $query->whereIN('scj_resoluciones.c_materia', $materia);
                                })
                                ->when($b_acto, function($query) use ($acto){
                                    return $query->whereIN('scj_acto_procesal.x_desc_acto_procesal', $acto);
                                })
                                
                                /*->when($b_acto, function($query) use ($especialidad){
                                    return $query->whereIN('scj_acto_procesal.c_especialidad', $especialidad);
                                })*/
                                //->whereRaw('scj_resoluciones.r_archivopdf IS NOT NULL')

                                ->get();
                                
                $filtros = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                                ->select(DB::RAW("DISTINCT scj_resoluciones.c_especialidad, scj_resoluciones.c_materia, scj_resoluciones.c_acto_procesal"))
                                
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
                                                $subquery .= "CONTAINS (crawlerpdf, '".$palabras[$i]."' )";        
                                                $j++;
                                            }      
                                        }   
                                        return $query->whereRAW($subquery);                                 
                                    })
                                ->get();

                $resoluciones = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select('scj_resoluciones.*', 'scj_acto_procesal.x_desc_acto_procesal', 'scj_materia_maestro.x_desc_materia')
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
                                            $subquery .= "CONTAINS (crawlerpdf, '".$palabras[$i]."' )";        
                                            $j++;
                                        }
                                    
                                }   
                                return $query->whereRAW($subquery);                                 
                            })
                            ->join('scj_acto_procesal', function($join){
                                $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                //$join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                                })
                            
                            ->when($b_especialidad, function($query) use ($especialidad){
                                    return $query->whereIN('scj_resoluciones.c_especialidad', $especialidad);
                            })
                            ->when($b_materia, function($query) use ($materia){
                                return $query->whereIN('scj_resoluciones.c_materia', $materia);
                            })
                            ->when($b_acto, function($query) use ($acto){
                                return $query->whereIN('scj_acto_procesal.x_desc_acto_procesal', $acto);
                            })
                            //->whereRaw('scj_resoluciones.r_archivopdf IS NOT NULL')
                            /*->when($b_acto, function($query) use ($especialidad){
                                return $query->whereIN('scj_acto_procesal.c_especialidad', $especialidad);
                            })*/
                            ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_resoluciones.c_materia')
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('anio_res', 'desc')
                            ->orderBy('mes_res', 'desc')
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

                $n_resultados = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select(DB::RAW("count(id_ingreso) AS n_resultados"))
                            ->leftjoin('scj_acto_procesal', function($join){
                                $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                //$join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                            })
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerpdf, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->when($b_especialidad == true, function($query) use ($especialidad){
                                return $query->whereIN('scj_resoluciones.c_especialidad', $especialidad);
                            })
                            ->when($b_materia, function($query) use ($materia){
                                return $query->whereIN('scj_resoluciones.c_materia', $materia);
                            })
                            ->when($b_acto, function($query) use ($acto){
                                return $query->whereIN('scj_acto_procesal.x_desc_acto_procesal', $acto);
                            })
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select(DB::RAW("DISTINCT scj_resoluciones.c_especialidad, scj_resoluciones.c_materia, scj_resoluciones.c_acto_procesal"))
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerpdf, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->get();


                $resoluciones = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select('scj_resoluciones.*', 'scj_acto_procesal.x_desc_acto_procesal', 'scj_materia_maestro.x_desc_materia')
                            ->when (1, function ($query) use ($palabras)
                            {
                                for ($i=0; $i < sizeof($palabras) ; $i++) { 
                                    return $query->orWhereRAW("CONTAINS (crawlerpdf, '".$palabras[$i]."' )");    
                                }                                
                            })
                            ->leftjoin('scj_acto_procesal', function($join){
                                $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                //$join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                            })
                            ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_resoluciones.c_materia')
                            ->when($b_especialidad == true, function($query) use ($especialidad){
                                return $query->whereIN('scj_resoluciones.c_especialidad', $especialidad);
                            })
                            ->when($b_materia, function($query) use ($materia){
                                return $query->whereIN('scj_resoluciones.c_materia', $materia);
                            })
                            ->when($b_acto, function($query) use ($acto){
                                return $query->whereIN('scj_acto_procesal.x_desc_acto_procesal', $acto);
                            })
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('anio_res', 'desc')
                            ->orderBy('mes_res', 'desc')
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

                 $n_resultados = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select(DB::RAW("count(id_ingreso) AS n_resultados"))
                            ->whereRAW( "CONTAINS (crawlerpdf, '".'"'.$request->buscar.'"'."' )")
                            ->join('scj_acto_procesal', function($join){
                                $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                $join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                                })
                            ->when($b_especialidad == true, function($query) use ($especialidad){
                                return $query->whereIN('scj_resoluciones.c_especialidad', $especialidad);
                            })
                            ->when($b_materia, function($query) use ($materia){
                                return $query->whereIN('scj_resoluciones.c_materia', $materia);
                            })
                            ->when($b_acto, function($query) use ($acto){
                                return $query->whereIN('scj_acto_procesal.x_desc_acto_procesal', $acto);
                            })
                            ->get();

                $filtros = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select(DB::RAW("DISTINCT scj_resoluciones.c_especialidad, scj_resoluciones.c_materia, scj_resoluciones.c_acto_procesal"))
                            ->whereRAW( "CONTAINS (crawlerpdf, '".'"'.$request->buscar.'"'."' )")
                            ->get();


                $resoluciones = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                            ->select('scj_resoluciones.*', 'scj_acto_procesal.x_desc_acto_procesal', 'scj_materia_maestro.x_desc_materia')
                            ->whereRAW( "CONTAINS (crawlerpdf, '".'"'.$request->buscar.'"'."' )")
                            ->join('scj_acto_procesal', function($join){
                                $join->on('scj_acto_procesal.c_acto', '=','scj_resoluciones.c_acto_procesal');
                                $join->on('scj_acto_procesal.c_especialidad', '=','scj_resoluciones.c_especialidad');
                                })
                            ->leftJoin('scj_materia_maestro', 'scj_materia_maestro.c_materia', 'scj_resoluciones.c_materia')
                            ->when($b_especialidad == true, function($query) use ($especialidad){
                                return $query->whereIN('scj_resoluciones.c_especialidad', $especialidad);
                            })
                            ->when($b_materia, function($query) use ($materia){
                                return $query->whereIN('scj_resoluciones.c_materia', $materia);
                            })
                            ->when($b_acto, function($query) use ($acto){
                                return $query->whereIN('scj_acto_procesal.x_desc_acto_procesal', $acto);
                            })
                            ->offset(($request->pag*1-1)*10)
                            ->limit(10)
                            ->orderBy('anio_res', 'desc')
                            ->orderBy('mes_res', 'desc')
                            ->get();  
            return [
                    'resoluciones' => $resoluciones,
                    'palabras' => $palabras,
                    'filtros' => $filtros,
                    'n_resultados' => $n_resultados,
                ]; 
        }

            

        if ($request->input('updatedb')) {
            $files = $request->resoluciones;
            //$response['file'] = $files;
            DB::beginTransaction();
                try {
                    foreach ($request->resoluciones as $resolucion) {
                    
                        $repositorio = new Repositorio;
                        $repositorio->anio_res =  $resolucion['año'];
                        $repositorio->mes_res =  $resolucion['mes'];
                        $repositorio->dia_res =  $resolucion['dia'];
                        $repositorio->exp_idcompleto = (string) $resolucion['idcompleto'];
                        $repositorio->exp_idunico =  (string) $resolucion['COD_UNICO_EXPEDIENTE'];
                        $repositorio->exp_incidencia =  (string) $resolucion['NUM_INC_EXPEDIENTE'];
                        $repositorio->exp_f_descargo = (string) substr($resolucion['FECHA_RESOL_DESCARGO'],0,23);
                        $repositorio->r_archivoword = 'storage\\app\\resoluciones'.str_replace('/','\\', $resolucion['RUTA_FTP_ARCHIVO_RESOLUCION']).$resolucion['NOM_ARCHIVO_DOC_RESOLUCION'];
                        $repositorio->r_archivopdf =  'storage\\app\\resoluciones'.str_replace('/','\\', $resolucion['RUTA_FTP_ARCHIVO_RESOLUCION']).$resolucion['NOM_ARCHIVO_PDF_RESOLUCION'];
                        $repositorio->c_especialidad =  (string) $resolucion['COD_ESPECIALIDAD'];
                        $repositorio->c_materia =  (string) $resolucion['COD_MATERIA'];
                        $repositorio->x_formato =  (string) $resolucion['NUM_EXPEDIENTE'];
                        $repositorio->c_acto_procesal = (string) $resolucion['COD_TIPO_ACTO_PROCESAL'];
                        $repositorio->asunto =  (string)$resolucion['DEN_RESOL_PROYECTO'];
                        $repositorio->txt_demandado =  $resolucion['txt_demandado'];
                        $repositorio->txt_demandante =  $resolucion['txt_demandante'];
                        $docObj = new Doc2Txt($repositorio->r_archivoword);

                        $txt = $docObj->convertToText();
                        $repositorio->crawlerpdf = $txt;    

                        $input = ['r_archivoword' => (string) 'storage\\app\\resoluciones'.str_replace('/','\\', $resolucion['RUTA_FTP_ARCHIVO_RESOLUCION']).$resolucion['NOM_ARCHIVO_DOC_RESOLUCION']];
                        $rules = array(
                            'r_archivoword' => 'unique:sqlsrv_j.scj_resoluciones'
                        );

                        
                        $validate = Validator::make($input,$rules);

                        if($validate->passes())
                        {
                            $repositorio->save();

                            $instancia = new ResolucionInstancia;

                            $instancia->scj_resoluciones_id =  $repositorio->id_ingreso;
                            $instancia->c_distrito =  $resolucion['c_distrito'];
                            $instancia->c_provincia =  $resolucion['c_provincia'];
                            $instancia->c_instancia =  $resolucion['COD_INSTANCIA_SIJ'];
                            $instancia->c_org_jurisd =  $resolucion['c_org_jurisd'];
                            $instancia->save();
                        } elseif ($validate->fails()) {
                        
                        }
                        //$response['file'] = $repositorio;
                        //$repositorio->save();
                        
                        DB::commit(); 
                        $response["statusBD"] = true;
                        $response["messageDB"] = "Resoluciones registradas correctamente.";
                    }   
                } catch (\Exception $e) {
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Hubo un error al registrar las resoluciones.".$e;
                    DB::rollback();
                }
            DB::commit();
                
            return $response;
        }
        else{
          
        }
    }

    public function show($id)
    {
        $documento = DB::connection('sqlsrv_j')->table('srp_resoluciones')
                ->where('id_ingreso', $id)
                ->first();
        header('content-Type:'.$documento->content_type);
        echo base64_decode($documento->contentpdf);
    }

    
    public function update(Request $request, $id)
    {
        
    }
    public function destroy($id)
    {
     
    }

    public function filterDB(Request $request) {

        $registrosDB = DB::connection('sqlsrv_j')->table('scj_resoluciones')
                        ->select('exp_idcompleto')
                        ->where(DB::raw("(YEAR(exp_f_descargo))"), $request->anio)
                        ->where(DB::raw("(MONTH(exp_f_descargo))"), $request->mes)
                        ->get();

        $arrayRegistros = array();
        if(sizeof($registrosDB) > 0){
            foreach ($registrosDB as $registro) {
                array_push($arrayRegistros, $registro->exp_idcompleto);
            }
        }

        $fileList = array();

        foreach($request->data as $file) {
            if(!in_array(substr($file['NOM_ARCHIVO_PDF_RESOLUCION'],0, 36), $arrayRegistros)){

                $file['año'] = substr($file['NOM_ARCHIVO_PDF_RESOLUCION'], 19,4);
                $file['mes'] = substr($file['NOM_ARCHIVO_PDF_RESOLUCION'], 23,2);
                $file['dia'] = substr($file['NOM_ARCHIVO_PDF_RESOLUCION'], 25,2);
                $file['idcompleto'] = substr($file['NOM_ARCHIVO_PDF_RESOLUCION'], 0,36);
                

                /********** TEMPORAL: EN CONSULTA REALIZADA, IP FTP NULL */
                if(substr($file['RUTA_FTP_ARCHIVO_RESOLUCION'],0,1) != '/'){
                    $file['RUTA_FTP_ARCHIVO_RESOLUCION'] = '/'.$file['RUTA_FTP_ARCHIVO_RESOLUCION'];
                }
                if($file['IP_FTP_ARCHIVO_RESOLUCION'] == null){
                    $file['IP_FTP_ARCHIVO_RESOLUCION'] = '172.17.176.67';
                    $file['USUARIO_FTP_ARCHIVO_RESOLUC'] = 'sijresol';
                    $file['CLAVE_FTP_ARCHIVO_RESOLUCION'] = 'wsxcde159753';
                }
                array_push($fileList,$file);
            }
        }

        return $fileList;

    }

    public function listar_directorio($folder, $pattern) {

        if (file_exists($folder)) {
            $dir = new \RecursiveDirectoryIterator($folder);
            $ite = new \RecursiveIteratorIterator($dir);
            $files = new \RegexIterator($ite, $pattern, \RegexIterator::GET_MATCH);
            $fileList = array();
            foreach($files as $file) {
                $row = array();
                    $row['año'] = substr($file[0], 25,4);
                    $row['mes'] = substr($file[0], 30,2);
                    $row['dia'] = substr($file[0], 33,2);
                    $row['idcompleto'] = substr($file[0], 36,36);
                    $row['idunico'] = substr($file[0], 36,16);
                    $row['incidente'] = substr($file[0], 52,3);
                    $row['archivo'] = substr($file[0], 36);
                    $row['f_descargo'] = substr($file[0], 55,4).'-'.substr($file[0], 59,2).'-'.substr($file[0], 61,2).' '.substr($file[0], 63,2).':'.substr($file[0], 65,2).':'.substr($file[0], 67,2).'.'.substr($file[0], 69,3);
                    $row['ruta'] = $file[0];
                    $row['pdf'] = (file_exists(substr($file[0],0,-3).'pdf'))? 1:0;
                array_push($fileList, $row);
            }
            return $fileList;
        } else {
            return [];
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
    public function buscador(){
        $usuario = $this->getUser();
        $user_roles = $this->getRoles();
        return view('repositorio\jurisprudencia\buscador',compact('user_roles', 'usuario'));
    }
    public function config(Request $request){
        $usuario = User::find(Auth::user()->id);
        $administrador = $usuario->hasAnyRole('Webmaster|Administrador');
        $persona = Persona::find(Auth::user()->persona_id);

        if($administrador){

            if($request->input('ftp')){
                $fecha_registro = time();

                DB::beginTransaction();
                try{

                    $ftp = DB::connection('sqlsrv_j')->table('scj_ftp')
                                    ->where('id', 1)
                                    ->update([
                                        'ruta' => $request->ftp,
                                        'user_update' => $persona->numero_documento,
                                        'updated_at' => date('Ymd H:i:s',$fecha_registro)
                                    ]);
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Configuración guardada correctamente.";
                } catch (\Exception $e) {
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Error al actualizar en la Base de Datos.";
                }
                
                return $response;
            } else{
                if($request->input('ruta')){
                    $ruta = DB::connection('sqlsrv_j')->table('scj_ftp')
                                ->first();
                    $response["ruta"] = $ruta;
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Configuración cargada correctamente.";
                    return $response;
                } else{
                    $usuario = $this->getUser();
                    $user_roles = $this->getRoles();
            
                    return view('repositorio\jurisprudencia\config',compact('user_roles', 'usuario'));
                }
            }
        } else{
            return abort(403);
        }
    }
}
