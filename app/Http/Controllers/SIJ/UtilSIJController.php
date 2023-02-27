<?php

namespace App\Http\Controllers\SIJ;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

Use App\Imports\PersonaImport;
Use App\Exports\PersonaExport;

use Auth;
use App\User;
use App\Persona;
use App\Models\DataRepo;

class UtilSIJController extends Controller
{

    public function index(Request $request){
        $usuario = User::find(Auth::user()->id);
        $response=null;
        
        if ( $usuario->hasAnyRole('Webmaster|Administrador|SIJ.usuario|SIJ.supervisor') ){
            if($request->input('list_personal_sup')){
                $persona = Persona::find($usuario->persona_id);

                $oficina = DB::table('op_oficinas')
                        ->select('op_oficinas.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                        ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                        ->where('persona.id', $persona->id)
                        ->first();
                
                $tabla = Persona::select('numero_documento',
                            DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno, ' ', persona.nombres) as nombrecompleto"), 
                        )
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                        ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                        ->join('asistencias_parent', 'asistencias_parent.op_oficinab_id', 'plazafisica.op_oficinaf_id')
                        ->where('asistencias_parent.op_oficinaa_id ', $oficina->id)
                        ->get();
    
                $tabla2 = Persona::select('numero_documento',
                            DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno, ' ', persona.nombres) as nombrecompleto"), 
                        )
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                        ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                        ->where('plazafisica.op_oficinaf_id ', $oficina->id)
                        ->get();

                $tabla3 = [];
                foreach ($tabla as $row){
                    array_push($tabla3, $row);
                }
                foreach ($tabla2 as $row){
                    array_push($tabla3, $row);
                }

                $response['personal'] = $tabla3;
                return $response;
            } else if($request->input('list_personal')){
                $tabla = Persona::select('numero_documento',
                                DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno, ' ', persona.nombres) as nombrecompleto"), 
                                )
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->get();
    
                $response['personal'] = $tabla;
                return $response;
            } else if($request->input('listar_instancias')){
                $dateini = $request->dateini;
                $instancias = DB::connection('sqlsrv_s')
                            ->table('v_especialidadesrepo as res')
                            ->select(
                                    DB::raw("CONCAT(RTRIM(i.c_provincia), '_', i.c_instancia) as id_instancia"),
                                    DB::raw("x_nom_provincia = (CASE WHEN i.c_provincia = '14' THEN 'CHUCUITO'
                                                            WHEN  i.c_provincia = '15' THEN 'SAN ROMAN'
                                                            WHEN  i.c_provincia = '16' THEN 'AZANGARO'
                                                            WHEN  i.c_provincia = '17' THEN 'AZANGARO'
                                                            WHEN  i.c_provincia = '18' THEN 'SAN ANTONIO DE PUTINA'
                                                            WHEN  i.c_provincia = '19' THEN 'AZANGARO'
                                                            WHEN  i.c_provincia = '20' THEN 'MELGAR'
                                                            WHEN  i.c_provincia < '15' THEN RTRIM(p.x_nom_provincia)
                                                            END )"),
                                        'i.c_sede', 'i.c_distrito', 'i.c_provincia', 'x_desc_sede', 
                                        'i.c_org_jurisd', 'i.c_org_jurisd', 'i.c_instancia', 
                                        'x_nom_instancia',  'res.c_especialidad'
                                    )
                            ->join('instancia as i', function($join) use($dateini){
                                        $join->on('res.c_provincia', '=', 'i.c_provincia');
                                        $join->on('res.c_distrito', '=', 'i.c_distrito');
                                        $join->on('res.c_instancia', '=', 'i.c_instancia');
                            })
                            ->join('provincia as p', function($join){
                                $join->on('p.c_provincia', '=', 'i.c_provincia');
                                $join->on('p.c_distrito', '=', 'i.c_distrito');
                            })
                            ->join('sede as s', 's.c_sede', 'i.c_sede')

                            //->join('sorgano_jurisdiccional as o', 'o.c_org_jurisd','i.c_org_jurisd')
                            
                            
                            ->whereIN('res.n_anio_est', array(substr($request->dateini, 0, 4), substr($request->datefin, 0, 4)))
                            ->distinct()
                            ->get();
                $response['status'] = 200;
                $response['data'] = $instancias;
                return $response;
            }
        }
        
        return abort('403');
    }
    public function store(Request $request){
        $usuario = User::find(Auth::user()->id);
        $response=null;

        if ( $usuario->hasAnyRole('Webmaster|Administrador|SIJ.usuario|SIJ.supervisor') ){
            if($request->input('listarVarEst')){
                $pc_var_id = array('1','2','3','4','5','6','7','8','9','89','10','11','12','50','51','88','52','53');
                $variables = DataRepo::select(
                                            'c_estadistica',
                                            'var_id',                    
                                            'var_des',
                                            'var_ord',
                                            'var_letra',
                                            'var_des_det',
                                            DB::raw("CONCAT('[', c_estadistica, '] - [', var_id , '] - [',  var_letra, '] ', var_des) as var_detalle"),
                                        )
                                        ->whereIN('var_id', $pc_var_id)
                                        ->where(function ($query) use ($request) {
                                            if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                                $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                                $query->WhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                            } else{
                                                $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                                $query->orWhereRaw(DB::raw("(n_anio_est > ".substr($request->dateini, 0, 4)." AND n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                                $query->orWhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                            }
                                            
                                        })
                                        ->whereNotNull('n_unico')
                                        ->whereIN(DB::raw("CONCAT(RTRIM(c_provincia), '_', c_instancia)"), $request->instancias)
                                        ->distinct()
                                        ->orderBy('c_estadistica')
                                        ->orderBy('var_id')
                                        ->get();

                $actosProcesales = DataRepo::select(
                                            DB::raw("CONCAT(c_estadistica, '_', var_id, '_', c_acto_procesal) as id_acto"),
                                            'c_estadistica',
                                            'var_letra',
                                            'x_desc_acto_procesal',
                                            DB::raw("CONCAT('[', c_estadistica, '] - [', var_letra, '] ', x_desc_acto_procesal) as acto_p_detalle")
                                        )
                                        ->whereIN('var_id', $pc_var_id)
                                        ->where(function ($query) use ($request) {
                                            if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                                $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                                $query->WhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                            } else{
                                                $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                                $query->orWhereRaw(DB::raw("(n_anio_est > ".substr($request->dateini, 0, 4)." AND n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                                $query->orWhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                            }
                                            
                                        })
                                        ->whereNotNull('n_unico')
                                        ->whereIN(DB::raw("CONCAT(RTRIM(c_provincia), '_', c_instancia)"), $request->instancias)
                                        ->distinct()
                                        ->get();

                $especialidades = DataRepo::select('c_especialidad')
                                        ->whereIN('var_id', $pc_var_id)
                                        ->whereIN(DB::raw("CONCAT(RTRIM(c_provincia), '_', c_instancia)"), $request->instancias)
                                        ->where(function ($query) use ($request) {
                                            if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                                $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                                $query->WhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                            } else{
                                                $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                                $query->orWhereRaw(DB::raw("(n_anio_est > ".substr($request->dateini, 0, 4)." AND n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                                $query->orWhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                            }
                                            
                                        })
                                        ->whereNotNull('n_unico')
                                        ->distinct()
                                        ->get();


                $response['statusDB'] = true;
                $response['variables'] = $variables;
                $response['actosProcesales'] = $actosProcesales;
                $response['especialidades'] = $especialidades;
                return $response;
            } 
            if ($request->input('promediosD')){
                $pc_var_id = array('1','2','3','4','5','6','7','8','9','89','10','11','12','50','51','88','52','53');
                $especialidades = $request->especialidades;
                $promedios = DataRepo::select(
                            'repo_det_est.n_dependencia', 'repo_det_est.c_instancia', 'repo_det_est.c_provincia',
                            //DB::raw("CONCAT('[', repo_det_est.c_estadistica, '] - [', repo_det_est.var_letra, '] ', repo_det_est.var_des) as var_detalle"),
                            //'repo_det_est.var_id', 
                            //DB::raw("CONCAT(x_nom_instancia, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as x_nom_instancia"), 
                            'x_nom_instancia',
                            DB::raw("TIEMPO_DIAS_ING_VS_ACTO  = ROUND(AVG(CAST(DATEDIFF(DAY, fecha_max, f_registro)  AS FLOAT )), 0)"),
                            DB::raw("TIEMPO_DIAS_LAB_ING_VS_ACTO  = ROUND(AVG(CAST(DATEDIFF(DAY, fecha_max, f_registro) - DATEDIFF(WEEK, fecha_max, f_registro)*2  AS FLOAT )), 0)"),
                            DB::raw("COUNT(*) as CANTIDAD")
                        )
                        ->join('instancia', function($join){
                            $join->on('instancia.c_instancia', '=','repo_det_est.c_instancia');
                            $join->on('instancia.c_provincia', '=','repo_det_est.c_provincia');
                        })
                        ->join('m_oficina', function($join){
                            $join->on('m_oficina.n_dependencia', '=','repo_det_est.n_dependencia');
                            $join->on('m_oficina.idanoproc', '=','repo_det_est.n_anio_est');
                            })
                        ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                        /*->leftjoin('repo_plazo_proceso as pp', function($join){
                            $join->on('pp.c_proceso', '=','repo_det_est.c_proceso');
                            $join->on('pp.c_org_jurisd', '=','instancia.c_org_jurisd');
                            $join->on('pp.c_especialidad', '=','repo_det_est.c_sub_especialidad_sij');
                        })*/
                        //->whereIN('var_id', $pc_var_id)
                        ->whereNotNull('repo_det_est.n_unico')
                        ->whereIN(DB::raw("CONCAT(RTRIM(repo_det_est.c_provincia), '_', repo_det_est.c_instancia)"), $request->instancias)
                        ->whereIN('repo_det_est.var_id', $request->varProm) 
                        ->when (($request->especialidades != null), function ($query) use ($especialidades)
                        {
                            return $query->whereIN('repo_det_est.c_especialidad', $especialidades) ;
                        })
                        //->whereBetween('f_registro', ['2021-11-26T00:00:00', '2021-11-26T23:59:59'])
                        ->where(function ($query) use ($request) {
                            if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                $query->WhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                            } else{
                                $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                $query->orWhereRaw(DB::raw("(n_anio_est > ".substr($request->dateini, 0, 4)." AND n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                $query->orWhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                            }
                            
                        })
                        ->groupBy('repo_det_est.n_dependencia', 'repo_det_est.c_instancia', 'repo_det_est.c_provincia', 'x_nom_instancia'
                        //DB::raw("CONCAT('[', repo_det_est.c_estadistica, '] - [', repo_det_est.var_letra, '] ', repo_det_est.var_des)"), 'repo_det_est.var_id'
                        )
                        ->orderBy('x_nom_instancia')
                        ->get();

                $promedios_e = DataRepo::select(
                                    'repo_det_est.n_dependencia', 'repo_det_est.c_instancia',
                                    DB::raw("CONCAT('[', repo_det_est.c_estadistica, '] - [', repo_det_est.var_id, '] - [', repo_det_est.var_letra, '] ', repo_det_est.var_des) as var_detalle"),
                                    'repo_det_est.var_id', 
                                    //DB::raw("CONCAT(x_nom_instancia, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as nom_dependencia"), 
                                    'x_nom_instancia',
                                    DB::raw("TIEMPO_DIAS_ING_VS_ACTO  = ROUND(AVG(CAST(DATEDIFF(DAY, fecha_max, f_registro)  AS FLOAT )), 0)"),
                                    DB::raw("TIEMPO_DIAS_LAB_ING_VS_ACTO  = ROUND(AVG(CAST(DATEDIFF(DAY, fecha_max, f_registro) - DATEDIFF(WEEK, fecha_max, f_registro)*2  AS FLOAT )), 0)"),
                                    DB::raw("COUNT(*) as CANTIDAD")
                                )
                                ->join('instancia', function($join){
                                    $join->on('instancia.c_instancia', '=','repo_det_est.c_instancia');
                                    $join->on('instancia.c_provincia', '=','repo_det_est.c_provincia');
                                })
                                ->join('m_oficina', function($join){
                                    $join->on('m_oficina.n_dependencia', '=','repo_det_est.n_dependencia');
                                    $join->on('m_oficina.idanoproc', '=','repo_det_est.n_anio_est');
                                    })
                                ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                                ->whereNotNull('repo_det_est.n_unico')
                                ->whereIN(DB::raw("CONCAT(RTRIM(repo_det_est.c_provincia), '_', repo_det_est.c_instancia)"), $request->instancias)
                                ->whereIN('repo_det_est.var_id', $request->varProm) 
                                ->when (($request->especialidades != null), function ($query) use ($especialidades)
                                {
                                    return $query->whereIN('repo_det_est.c_especialidad', $especialidades) ;
                                })
                                ->where(function ($query) use ($request) {
                                    if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                        $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                        $query->WhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                    } else{
                                        $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                        $query->orWhereRaw(DB::raw("(n_anio_est > ".substr($request->dateini, 0, 4)." AND n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                        $query->orWhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                    }
                                    
                                })
                                ->groupBy('repo_det_est.n_dependencia', 'repo_det_est.c_instancia', 'x_nom_instancia',
                                DB::raw("CONCAT('[', repo_det_est.c_estadistica, '] - [', repo_det_est.var_id, '] - [', repo_det_est.var_letra, '] ', repo_det_est.var_des)"), 'repo_det_est.var_id'
                                )
                                ->orderBy('x_nom_instancia')
                                ->orderBy('var_id')

                                ->get();


                $promedios_pro = DataRepo::select(
                                        'repo_det_est.c_proceso','proceso_maestro.x_desc_proceso',
                                        DB::raw("TIEMPO_DIAS_ING_VS_ACTO  = ROUND(AVG(CAST(DATEDIFF(DAY, fecha_max, f_registro)  AS FLOAT )), 0)"),
                                        DB::raw("TIEMPO_DIAS_LAB_ING_VS_ACTO  = ROUND(AVG(CAST(DATEDIFF(DAY, fecha_max, f_registro) - DATEDIFF(WEEK, fecha_max, f_registro)*2  AS FLOAT )), 0)"),
                                        DB::raw("COUNT(*) as CANTIDAD")
                                    )
                                    ->join('proceso_maestro', 'proceso_maestro.c_proceso', 'repo_det_est.c_proceso')
                                    ->whereNotNull('repo_det_est.n_unico')
                                    ->whereIN(DB::raw("CONCAT(RTRIM(repo_det_est.c_provincia), '_', repo_det_est.c_instancia)"), $request->instancias)
                                    ->whereIN('repo_det_est.var_id', $request->varProm) 
                                    ->when (($request->especialidades != null), function ($query) use ($especialidades)
                                    {
                                        return $query->whereIN('repo_det_est.c_especialidad', $especialidades) ;
                                    })
                                    ->where(function ($query) use ($request) {
                                        if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                            $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                            $query->WhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                        } else{
                                            $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                            $query->orWhereRaw(DB::raw("(n_anio_est > ".substr($request->dateini, 0, 4)." AND n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                            $query->orWhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                        }
                                        
                                    })
                                    ->groupBy('proceso_maestro.x_desc_proceso', 'repo_det_est.c_proceso' )
                                    ->orderBy('repo_det_est.c_proceso')
                                    ->get();

                $promedios_proE = DataRepo::select(
                                        'repo_det_est.c_instancia', 'repo_det_est.c_proceso','proceso_maestro.x_desc_proceso',
                                        'x_nom_instancia',
                                        DB::raw("TIEMPO_DIAS_ING_VS_ACTO  = ROUND(AVG(CAST(DATEDIFF(DAY, fecha_max, f_registro)  AS FLOAT )), 0)"),
                                        DB::raw("TIEMPO_DIAS_LAB_ING_VS_ACTO  = ROUND(AVG(CAST(DATEDIFF(DAY, fecha_max, f_registro) - DATEDIFF(WEEK, fecha_max, f_registro)*2  AS FLOAT )), 0)"),
                                        DB::raw("COUNT(*) as CANTIDAD")
                                    )
                                    ->join('instancia', function($join){
                                        $join->on('instancia.c_instancia', '=','repo_det_est.c_instancia');
                                        $join->on('instancia.c_provincia', '=','repo_det_est.c_provincia');
                                    })
                                    ->join('m_oficina', function($join){
                                        $join->on('m_oficina.n_dependencia', '=','repo_det_est.n_dependencia');
                                        $join->on('m_oficina.idanoproc', '=','repo_det_est.n_anio_est');
                                        })
                                    ->join('proceso_maestro', 'proceso_maestro.c_proceso', 'repo_det_est.c_proceso')
                                    ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                                    ->whereNotNull('repo_det_est.n_unico')
                                    ->whereIN(DB::raw("CONCAT(RTRIM(repo_det_est.c_provincia), '_', repo_det_est.c_instancia)"), $request->instancias)
                                    ->whereIN('repo_det_est.var_id', $request->varProm) 
                                    ->when (($request->especialidades != null), function ($query) use ($especialidades)
                                    {
                                        return $query->whereIN('repo_det_est.c_especialidad', $especialidades) ;
                                    })
                                    ->where(function ($query) use ($request) {
                                        if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                            $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                            $query->WhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                        } else{
                                            $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                            $query->orWhereRaw(DB::raw("(n_anio_est > ".substr($request->dateini, 0, 4)." AND n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                            $query->orWhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                        }
                                        
                                    })
                                    ->groupBy('proceso_maestro.x_desc_proceso', 'repo_det_est.c_instancia', 'x_nom_instancia','repo_det_est.c_proceso' )
                                    ->orderBy('x_nom_instancia')
                                    ->orderBy('repo_det_est.c_proceso')
                                    ->get();

                $response['statusDB'] = true;
                $response['promedios'] = $promedios;
                $response['promedios_e'] = $promedios_e;
                $response['promedios_pro'] = $promedios_pro;
                $response['promedios_proe'] = $promedios_proE;
                return $response;
            } 
            if ($request->input('listarExpedientes')){
                $pc_var_id = array('1','2','3','4','5','6','7','8','9','89','10','11','12','50','51','88','52','53');
                $especialidades = $request->especialidades;
                $expedientes = DataRepo::select(
                                    'repo_det_est.c_provincia', 'repo_det_est.c_instancia', 'repo_det_est.c_provincia',
                                    'repo_det_est.x_formato', 'x_nom_instancia', 'repo_det_est.x_sesp',
                                    'repo_det_est.x_desc_acto_procesal',
                                    DB::raw("SUBSTRING(CAST(repo_det_est.f_registro AS VARCHAR), 1, 19) as f_registro"), 
                                    DB::raw("SUBSTRING(CAST(repo_det_est.fecha_max AS VARCHAR), 1, 19) as fecha_max"), 
                                    DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as nom_dependencia"), 
                                    DB::raw("TIEMPO_DIAS_ING_VS_ACTO  = DATEDIFF(DAY, fecha_max, f_registro)"),
                                    DB::raw("TIEMPO_DIAS_LAB_ING_VS_ACTO  = DATEDIFF(DAY, fecha_max, f_registro) - DATEDIFF(WEEK, fecha_max, f_registro)*2"),
                                )
                                
                                ->where(function ($query) use ($request) {
                                    if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                        $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                        $query->WhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                    } else{
                                        $query->whereRaw(DB::raw("(n_anio_est = ".substr($request->dateini, 0, 4)." AND n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                        $query->orWhereRaw(DB::raw("(n_anio_est > ".substr($request->dateini, 0, 4)." AND n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                        $query->orWhereRaw(DB::raw("(n_anio_est = ".substr($request->datefin, 0, 4)." AND n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                    }
                                    
                                })
                                ->join('instancia', function($join){
                                    $join->on('instancia.c_instancia', '=','repo_det_est.c_instancia');
                                    $join->on('instancia.c_provincia', '=','repo_det_est.c_provincia');
                                })
                                ->join('m_oficina', function($join){
                                    $join->on('m_oficina.n_dependencia', '=','repo_det_est.n_dependencia');
                                    $join->on('m_oficina.idanoproc', '=','repo_det_est.n_anio_est');
                                    })
                                ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                                ->whereNotNull('repo_det_est.n_unico')
                                ->where(DB::raw("RTRIM(repo_det_est.c_provincia)"), rtrim($request->provincia, " "))
                                ->where('repo_det_est.c_instancia', $request->instancia) 
                                ->where('repo_det_est.var_id', $request->var_id) 
                                ->when (($request->especialidades != null), function ($query) use ($especialidades)
                                {
                                    return $query->whereIN('repo_det_est.c_especialidad', $especialidades) ;
                                })
                                ->orderBy(DB::raw("DATEDIFF(DAY, fecha_max, f_registro)"), 'desc')
                                ->get();

                $response['statusDB'] = true;
                $response['expedientes'] = $expedientes;
                return $response;
            }
        }
    }


    public function importExcelPersona(Request $request){

        if(isset($request->file)){
            $file = $request->file;

            $collection = Excel::toCollection(new PersonaImport, $file);
            $hoy = time();


            foreach ($collection[0] as $persona) {
                $url = 'http://172.17.176.17/apisybase/index.php/expediente/buscarExpedientesPersonaJEE?lc_x_doc_id='.$persona[2].'&lc_x_ape_nombres='.str_replace(' ', '%20', $persona[1]).'';
                $consulta = $this->consultaAPI($url);
                Storage :: append($persona[3].$hoy.".txt", $persona[2]."|Cantidad:|".sizeof($consulta['data'].".|Expedientes:|".$consulta['data']));
                $persona[4] = $consulta['data'];
            }


            $export = new PersonaExport($collection[0]);
        
            return Excel::store($export, 'personas'.time().'.xlsx');

            /*$response["collection"] = $collection[0][0];
            $response["statusDB"] = true;
            $response["messageDB"] = "Datos actualizados con éxito.";
            return $response;*/
        } else{
            return abort('403');
        }
    }


    public function adminvista($vista){

        $usuario = DB::table('users')
                            ->select('persona.numero_documento','persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
                            ->join('persona', 'persona.id', 'users.persona_id')
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

        if (sizeof($user_roles_db)>0) {
            foreach($user_roles_db as $role){
                array_push($user_roles, utf8_encode($role->name));
            }
        }

        $user_roles = json_encode($user_roles);

        $app = json_encode( ['url' => env('APP_URL'), 'server' => env('APP_SERVER'), 'apisybase' => env('APP_SYBASE')]);

        if (view()->exists('SIJ/'.$vista)){
            return view('SIJ/'.$vista, compact('user_roles', 'usuario', 'app'));
        } else{
            return abort('404');
        }
    }

    public function consultaAPI($url){
        if($url){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: php-curl'));
            curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
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