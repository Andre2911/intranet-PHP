<?php

namespace App\Http\Controllers\SIJ;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

Use App\Imports\MetasImport;

use Auth;
use App\User;
use App\Models\DataHito;
use App\Models\DataMeta;
use App\Models\MOficina;


class StatController extends Controller
{

    public function index(Request $request){
        $usuario = User::find(Auth::user()->id);
        $response=null;

        if($request->input('init')){
            $filtros   = DB::connection('sqlsrv_s')->table('filtros_buscador')
                            ->where('n_anio_est', '>=', substr($request->dateini, 0, 4))
                            ->where('n_anio_est', '<=', substr($request->datefin, 0, 4))
                            ->get();

            $lastUpdate = DB::connection('sqlsrv_s')->table('data_hitos')
                                ->select('created_at')
                                ->orderBy('created_at', 'DESC')
                                ->first();

            $lastDate = DB::connection('sqlsrv_s')->table('data_hitos')
                                ->select('n_anio_est', 'n_mes_est')
                                ->groupBy('n_anio_est', 'n_mes_est')
                                ->orderBy('n_anio_est', 'desc')
                                ->orderBy('n_mes_est', 'desc')
                                ->first();

            $response['statusDB'] = true;
            $response['filtros'] = $filtros;
            $response['lastUpdate'] = $lastUpdate;
            $response['lastDate'] = $lastDate;
            return $response;
        } else if($request->input('jueces')){
            $anio = Date('Y');
            $jueces = DB::connection('sqlsrv_s')->table('data_hitos')
                                ->select('data_hitos.c_dni_mag as c_dni', 
                                    'persona.c_ape_paterno', 'persona.c_ape_materno',
                                    'persona.c_nombres', 'persona.f_nacimiento',
                                    'persona.b_sexo', 'persona.t_foto',
                                    'm_oficina.*',
                                )
                                ->leftJoin('persona', 'persona.c_dni', 'data_hitos.c_dni_mag')
                                ->leftJoin('m_oficina', function($join) use ($anio){
                                    $join->on('m_oficina.n_dependencia', '=', 'persona.n_dependencia');
                                    $join->on('m_oficina.idanoproc', DB::raw($anio));
                                })
                                ->distinct()
                                ->whereNOTNULL('data_hitos.c_dni_mag')
                                ->get();

            $response['jueces'] = $jueces;
            $response['statusDB'] = true;

            return $response;
        } else if($request->input('metas')){
            $data = DataMeta::where('n_anio_est', $request->anio_est)
                                ->select(
                                    'data_metas.*', 
                                    'm_oficina.mofidescri',
                                    'ubigeos.n_provincia',
                                    'ubigeos.n_distrito'
                                )
                                ->join('m_oficina', function($join){
                                    $join->on('m_oficina.n_dependencia', '=', 'data_metas.n_dependencia');
                                    $join->on('m_oficina.idanoproc', '=', 'data_metas.n_anio_est');
                                })
                                ->join('CSJPDATA.dbo.ubigeos', 'ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                                ->get();

            $response['metas'] = $data;
            $response['statusDB'] = true;
            return $response;
        } else if($request->input('m_oficinas')) {
            $data = MOficina::paginate(999999999);

            $response['data'] = $data;
            $response['statusDB'] = true;
            $response['messageDB'] = 'Cargado con éxito';
            return $response;
        }
        
        
        return abort('403');
    }
    public function store (Request $request){
        $response=null;
        
        if($request->input('anio') && $request->input('mes') && $request->input('data') && sizeof($request->data) > 0){

            DB::beginTransaction();
            try{

                $deleteOldData = DataHito::where('n_anio_est', $request->anio)
                                            ->where('n_mes_est', $request->mes)
                                            ->delete();

                foreach ($request->data as $dato) {
                    //if($dato['FECHA_INGRESO'] != null && $dato['COD_ESPECIALIDAD'] != null ){

                        $dataHito = DataHito::create([
                                    'f_registro' => ($dato['FECHA_REG_HITO'] != null)? date('Ymd H:i:s', strtotime($dato['FECHA_REG_HITO'])) : null,
                                    'f_ingreso' =>  ($dato['FECHA_INGRESO'] != null)? date('Ymd H:i:s', strtotime($dato['FECHA_INGRESO'])) : null,
                                    'l_ind_exp' =>  $dato['TIPO_INCIDENTE'],
                                    'c_instancia' => $dato['COD_INSTANCIA_SIJ'],
                                    'n_dependencia' => $dato['NUM_DEPENDENCIA'],
                                    'n_anio_est'    => $dato['ANIO_ESTADISTICA']*1,
                                    'n_mes_est'     => $dato['MES_ESTADISTICA']*1,
                                    'var_id'        => $dato['VAR_ID']*1,
                                    //'c_acto_procesal' => $dato['COD_TIPO_ACTO_PROCESAL'],
                                    'c_acto_procesal_hito' => $dato['COD_ACTO_PROCESAL_HITO'],
                                    'x_formato'     => $dato['NUM_EXPEDIENTE'],
                                    'n_funcion'     => $dato['NUM_FUNCION']*1,
                                    'c_especialidad' => $dato['COD_ESPECIALIDAD'],
                                    'c_especialidad_fee' => $dato['COD_ESPECIALIDAD_FEE'],
                                    'c_proceso  '   => $dato['COD_PROCESO'],
                                    'c_delito   '   => $dato['COD_DELITO'],
                                    'c_provincia'   => $dato['COD_PROV_JUDICIAL_SIJ'],
                                    'c_distrito'    => $dato['COD_DIST_JUDICIAL_SIJ'],
                                    'c_org_jurisd'  => $dato['COD_ORG_JURISDICCIONAL'],
                                    'c_sede'        => $dato['COD_SEDE_JUDICIAL'],
                                    'c_dni_mag'     => $dato['DNI_JUEZ'],
                                    'c_dni_sec'     => $dato['DNI_SECRETARIO'],
                                ]);
                    //}
                }

                DB::commit(); 

                $response["statusDB"] = true;
                $response["messageDB"] = "Datos registrados con éxito.";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusDB"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }


            

            return $response;
        }

        if($request->input('stat')){

            $f_sede = false;
            $f_org_jurisd = false;
            $f_especialidad = false;
            $f_tipo = false;
            $f_instancia = false;
            $f_juez = false;

            $sedes = null;
            $orgjurisd = null;
            $especialidades = null;
            $tipos = null;
            $jueces = null;

            if($request->input('sede') || $request->input('orgjurisd') || $request->input('especialidad') || $request->input('tipo') || $request->input('juez')){
                $f_sede = ($request->sede == null || sizeof($request->sede) == 0)? false: true;
                $f_org_jurisd = ($request->orgjurisd == null || sizeof($request->orgjurisd) == 0)? false: true;
                $f_especialidad = ($request->especialidad == null || sizeof($request->especialidad) == 0)? false: true;
                $f_tipo = ($request->tipo == null || sizeof($request->tipo) == 0)? false: true;
                $f_juez = ($request->juez == null || sizeof($request->juez) == 0)? false: true;

                $sedes = $request->sede;
                $orgjurisd = $request->orgjurisd;
                $especialidades = $request->especialidad;
                $tipos = $request->tipo;
                $jueces = $request->juez;
            }
            $instancias = $request->instancia;

            $sameYear = (substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4))? true:false;

            $stats1 = DB::connection('sqlsrv_s')->table('data_hitos')
                            ->select('n_anio_est', 'n_mes_est', DB::raw("n_anio_est*100+n_mes_est*1 as orden"), DB::raw('count(*) as total'))

                            ->when ($f_sede, function ($query) use ($sedes){
                                return $query->whereIN('data_hitos.c_sede', $sedes);
                            })
                            ->when ($f_org_jurisd, function ($query) use ($orgjurisd){
                                return $query->whereIN('data_hitos.c_org_jurisd', $orgjurisd);
                            })
                            ->when ($f_especialidad, function ($query) use ($especialidades){
                                return $query->whereIN('data_hitos.c_especialidad', $especialidades);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_juez, function ($query) use ($jueces){
                                return $query->whereIN('data_hitos.c_dni_mag', $jueces);
                            })
                            ->whereIN('data_hitos.c_instancia', $instancias)
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
                            ->groupBy('n_anio_est', 'n_mes_est',  DB::raw("n_anio_est*100+n_mes_est*1"))
                            ->orderBy('orden', 'asc')
                            ->get();

            $stats2 = DB::connection('sqlsrv_s')->table('data_hitos')
                            ->select('x_desc_acto_procesal',DB::raw('count(*) as total'))
                            ->join('acto_procesal_maestro', 'acto_procesal_maestro.c_acto_procesal', 'data_hitos.c_acto_procesal_hito')
                            
                            ->when ($f_sede, function ($query) use ($sedes){
                                return $query->whereIN('data_hitos.c_sede', $sedes);
                            })
                            ->when ($f_org_jurisd, function ($query) use ($orgjurisd){
                                return $query->whereIN('data_hitos.c_org_jurisd', $orgjurisd);
                            })
                            ->when ($f_especialidad, function ($query) use ($especialidades){
                                return $query->whereIN('data_hitos.c_especialidad', $especialidades);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_juez, function ($query) use ($jueces){
                                return $query->whereIN('data_hitos.c_dni_mag', $jueces);
                            })
                            ->whereIN('data_hitos.c_instancia', $instancias)
                            ->where(function ($query) use ($request) {
                                if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                    $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->dateini, 0, 4)." AND data_hitos.n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                    $query->WhereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->datefin, 0, 4)." AND data_hitos.n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                } else{
                                    $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->dateini, 0, 4)." AND data_hitos.n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                    $query->orWhereRaw(DB::raw("(data_hitos.n_anio_est > ".substr($request->dateini, 0, 4)." AND data_hitos.n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                    $query->orWhereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->datefin, 0, 4)." AND data_hitos.n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                }
                            })
                            ->groupBy('x_desc_acto_procesal')
                            ->orderBy('total', 'desc')
                            ->get();

            if($sameYear){
                $stats3 = DB::connection('sqlsrv_s')->table('data_hitos')
                            ->select(
                                DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as nom_dependencia"), 
                                'x_nom_instancia', 'm_provisional', DB::raw('count(*) as total'))
                            ->join('instancia', function($join){
                                $join->on('instancia.c_instancia', '=','data_hitos.c_instancia');
                                $join->on('instancia.c_provincia', '=','data_hitos.c_provincia');
                            })
                            ->join('m_oficina', function($join){
                                $join->on('m_oficina.n_dependencia', '=','data_hitos.n_dependencia');
                                $join->on('m_oficina.idanoproc', '=','data_hitos.n_anio_est');
                                })
                            ->join('data_metas', function($join){
                                    $join->on('data_metas.n_dependencia', '=','data_hitos.n_dependencia');
                                    $join->on('data_metas.n_anio_est', '=','data_hitos.n_anio_est');
                            })
                            ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')

                            ->when ($f_sede, function ($query) use ($sedes){
                                return $query->whereIN('data_hitos.c_sede', $sedes);
                            })
                            ->when ($f_org_jurisd, function ($query) use ($orgjurisd){
                                return $query->whereIN('data_hitos.c_org_jurisd', $orgjurisd);
                            })
                            ->when ($f_especialidad, function ($query) use ($especialidades){
                                return $query->whereIN('data_hitos.c_especialidad', $especialidades);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_juez, function ($query) use ($jueces){
                                return $query->whereIN('data_hitos.c_dni_mag', $jueces);
                            })
                            ->whereIN('data_hitos.c_instancia', $instancias)
                            ->where(function ($query) use ($request) {
                                if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                    $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->dateini, 0, 4)." AND data_hitos.n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                    $query->WhereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->datefin, 0, 4)." AND data_hitos.n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                } else{
                                    $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->dateini, 0, 4)." AND data_hitos.n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                    $query->orWhereRaw(DB::raw("(data_hitos.n_anio_est > ".substr($request->dateini, 0, 4)." AND data_hitos.n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                    $query->orWhereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->datefin, 0, 4)." AND data_hitos.n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                }
                            })
                            ->groupBy('x_nom_instancia' ,DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito)"), 'm_provisional')
                            ->orderBy('total', 'desc')
                            ->get();
            } else{
                $stats3 = DB::connection('sqlsrv_s')->table('data_hitos')
                            ->select(
                                'x_nom_instancia', 
                                DB::raw('count(*) as total'))
                            ->join('instancia', function($join){
                                $join->on('instancia.c_instancia', '=','data_hitos.c_instancia');
                                $join->on('instancia.c_provincia', '=','data_hitos.c_provincia');
                            })
                            
                            ->when ($f_juez, function ($query){
                                return $query->join('usuario', 'usuario.c_usuario', 'data_hitos.c_juez' );
                            })
                            
                            ->when ($f_sede, function ($query) use ($sedes){
                                return $query->whereIN('data_hitos.c_sede', $sedes);
                            })
                            ->when ($f_org_jurisd, function ($query) use ($orgjurisd){
                                return $query->whereIN('data_hitos.c_org_jurisd', $orgjurisd);
                            })
                            ->when ($f_especialidad, function ($query) use ($especialidades){
                                return $query->whereIN('data_hitos.c_especialidad', $especialidades);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_juez, function ($query) use ($jueces){
                                return $query->whereIN('data_hitos.c_dni_mag', $jueces);
                            })
                            ->whereIN('data_hitos.c_instancia', $instancias)
                            ->where(function ($query) use ($request) {
                                if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                    $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->dateini, 0, 4)." AND data_hitos.n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                    $query->WhereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->datefin, 0, 4)." AND data_hitos.n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                } else{
                                    $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->dateini, 0, 4)." AND data_hitos.n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                    $query->orWhereRaw(DB::raw("(data_hitos.n_anio_est > ".substr($request->dateini, 0, 4)." AND data_hitos.n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                    $query->orWhereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->datefin, 0, 4)." AND data_hitos.n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                }
                            })
                            ->groupBy('x_nom_instancia')
                            ->orderBy('total', 'desc')
                            ->get();

            }
                
            $stats4 = DB::connection('sqlsrv_s')->table('data_hitos')
                            ->select('data_hitos.c_dni_mag', 'persona.c_ape_paterno', 'persona.c_ape_materno', 'persona.t_foto', DB::raw('count(*) as total'))
                            ->when ($f_sede, function ($query) use ($sedes){
                                return $query->whereIN('data_hitos.c_sede', $sedes);
                            })
                            ->when ($f_org_jurisd, function ($query) use ($orgjurisd){
                                return $query->whereIN('data_hitos.c_org_jurisd', $orgjurisd);
                            })
                            ->when ($f_especialidad, function ($query) use ($especialidades){
                                return $query->whereIN('data_hitos.c_especialidad', $especialidades);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            
                            ->whereIN('data_hitos.c_instancia', $instancias)
                            ->where(function ($query) use ($request) {
                                if(substr($request->dateini, 0, 4) == substr($request->datefin, 0, 4)){
                                    $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->dateini, 0, 4)." AND data_hitos.n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                    $query->WhereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->datefin, 0, 4)." AND data_hitos.n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                } else{
                                    $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->dateini, 0, 4)." AND data_hitos.n_mes_est >= ".substr($request->dateini, 5, 2).")"));
                                    $query->orWhereRaw(DB::raw("(data_hitos.n_anio_est > ".substr($request->dateini, 0, 4)." AND data_hitos.n_anio_est < ".substr($request->datefin, 0, 4).")"));
                                    $query->orWhereRaw(DB::raw("(data_hitos.n_anio_est = ".substr($request->datefin, 0, 4)." AND data_hitos.n_mes_est <= ".substr($request->datefin, 5, 2).")"));
                                }
                            })
                            ->leftJoin('persona', 'persona.c_dni', 'data_hitos.c_dni_mag')
                            ->groupBy('data_hitos.c_dni_mag', 'persona.c_ape_paterno', 'persona.c_ape_materno', 'persona.t_foto')
                            ->orderBy('total', 'desc')
                            ->get();

            $response['statusDB'] = true;
            $response['stats1'] = $stats1;
            $response['stats2'] = $stats2;
            $response['stats3'] = $stats3;
            $response['stats4'] = $stats4;
            
            return $response;
        }

        if($request->input('tabla') && $request->tabla == 'moficina' && $request->input('data') && sizeof($request->data) > 0){
            DB::beginTransaction();
            try{
                $deleteOldData = MOficina::truncate();
                foreach ($request->data as $dato) {
                    //if($dato['FECHA_INGRESO'] != null && $dato['COD_ESPECIALIDAD'] != null ){
                    $dataHito = MOficina::create([
                        'n_dependencia' => $dato['nro_dependencia'],
                        'mofidescri' => $dato['mofidescri'],
                        'idubicodigo' => $dato['idubicodigo'],
                        'idanoproc' => $dato['idanoproc'],
                    ]);
                }
                DB::commit(); 
                $response["statusDB"] = true;
                $response["messageDB"] = "Datos registrados con éxito.";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusDB"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        }
        

        return abort('403');
    }

    public function update(Request $request, $id)
    {
        $response = null;
        if($request->c_dni){
            DB::beginTransaction();
            try{
                if(isset($request->b64_foto)){
                    $file = 'stat/jueces/'.$request->c_dni.'.jpg';
                    $decoded = base64_decode($request->b64_foto);
                    Storage::put($file, $decoded);
                }

                $registro = DB::connection('sqlsrv_s')->table('persona')
                                        ->where('c_dni', $request->c_dni)
                                        ->first();


                if(!$registro){
                    $juez = DB::connection('sqlsrv_s')->table('persona')
                                ->insert([
                                    'c_dni' => $request->c_dni,
                                    'c_ape_paterno' => $request->c_ape_paterno,
                                    'c_ape_materno' => $request->c_ape_materno,
                                    'c_nombres' => $request->c_nombres,
                                    'f_nacimiento' => $request->f_nacimiento,
                                    'b_sexo' => $request->b_sexo,
                                    't_foto' => 'stat/jueces/'.$request->c_dni.'.jpg',
                                    'n_dependencia' => isset($request->n_dependencia) ? $request->n_dependencia : null,
                                ]);
                } else{
                    $juez = DB::connection('sqlsrv_s')->table('persona')
                                ->where('c_dni', $request->c_dni)
                                ->update([
                                    'c_ape_paterno' => $request->c_ape_paterno,
                                    'c_ape_materno' => $request->c_ape_materno,
                                    'c_nombres' => $request->c_nombres,
                                    'f_nacimiento' => $request->f_nacimiento,
                                    'b_sexo' => $request->b_sexo,
                                    't_foto' => 'stat/jueces/'.$request->c_dni.'.jpg',
                                    'n_dependencia' => isset($request->n_dependencia) ? $request->n_dependencia : null,
                                ]);
                }


                
                DB::commit(); 
                $response["statusDB"] = true;
                $response["messageDB"] = "Datos actualizados con éxito.";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusDB"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }

            return $response;
        } else{
            abort('403');
        }
    }

    public function juzgadoJuez(Request $request){

        $anio = date("Y");

        $juzgados = DB::connection('sqlsrv_s')->table('persona')
                            ->select('m_oficina.n_dependencia',
                                DB::raw('count(*) as contador'),
                                DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as nom_dependencia"), 
                                
                            )
                            ->where('persona.c_dni', $request->c_dni)
                            ->join('usuario', 'usuario.c_dni', 'persona.c_dni')
                            ->join('data_hitos', 'data_hitos.c_juez', 'usuario.c_usuario')
                            ->join('m_oficina', function($join){
                                $join->on('m_oficina.n_dependencia', '=','data_hitos.n_dependencia');
                                $join->on('m_oficina.idanoproc', '=','data_hitos.n_anio_est');
                            })
                            ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                            ->where('m_oficina.idanoproc', $anio)
                            ->groupby('m_oficina.n_dependencia', DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito)"))
                            ->orderBy('contador', 'desc')
                            ->get();

        $response["juzgados"] = $juzgados;
        $response["statusDB"] = true;
        $response["messageDB"] = "Datos actualizados con éxito.";
        return $response;
    }

    public function importExcel(Request $request){

        if(isset($request->anio_est)){
            $file = $request->file;

            $deleteAnio = DataMeta::where('n_anio_est', $request->anio_est)
                                    ->delete();

            Excel::import(new MetasImport, $file);
            $response["statusDB"] = true;
            $response["messageDB"] = "Datos actualizados con éxito.";
            return $response;
        } else{
            return abort('403');
        }
    }

    public function juzgadoProduccion(Request $request){

        $f_sede = false;
        $f_org_jurisd = false;
        $f_especialidad = false;
        $f_tipo = false;
        $f_instancia = false;
        $f_juez = false;

        $sedes = null;
        $orgjurisd = null;
        $especialidades = null;
        $tipos = null;
        $jueces = null;

        if($request->input('sede') || $request->input('orgjurisd') || $request->input('especialidad') || $request->input('tipo') || $request->input('juez')){
            $f_sede = ($request->sede == null || sizeof($request->sede) == 0)? false: true;
            $f_org_jurisd = ($request->orgjurisd == null || sizeof($request->orgjurisd) == 0)? false: true;
            $f_especialidad = ($request->especialidad == null || sizeof($request->especialidad) == 0)? false: true;
            $f_tipo = ($request->tipo == null || sizeof($request->tipo) == 0)? false: true;
            $f_juez = ($request->juez == null || sizeof($request->juez) == 0)? false: true;

            $sedes = $request->sede;
            $orgjurisd = $request->orgjurisd;
            $especialidades = $request->especialidad;
            $tipos = $request->tipo;
            $jueces = $request->juez;
        }

        /******************* PARAMETROS */

        $dependencia = $request->n_dependencia;
        $anioConsulta = isset($request->anio)? $request->anio : Date('Y');

        $mesFinalDB = DB::connection('sqlsrv_s')->table('data_hitos')
                        ->select('n_mes_est')
                        ->where('data_hitos.n_anio_est', $anioConsulta)
                        ->groupBy('n_mes_est')
                        ->orderBy('n_mes_est', 'desc')
                        ->first();
        $mesFinal = $mesFinalDB->n_mes_est;

        
        $stats1 = DB::connection('sqlsrv_s')->table('data_hitos')
                            ->select('n_anio_est', 'n_mes_est', DB::raw("n_anio_est*100+n_mes_est*1 as orden"), DB::raw('count(*) as total'))

                            ->when ($f_sede, function ($query) use ($sedes){
                                return $query->whereIN('data_hitos.c_sede', $sedes);
                            })
                            ->when ($f_org_jurisd, function ($query) use ($orgjurisd){
                                return $query->whereIN('data_hitos.c_org_jurisd', $orgjurisd);
                            })
                            ->when ($f_especialidad, function ($query) use ($especialidades){
                                return $query->whereIN('data_hitos.c_especialidad', $especialidades);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_juez, function ($query) use ($jueces){
                                return $query->whereIN('data_hitos.c_dni_mag', $jueces);
                            })
                            ->where('data_hitos.n_dependencia', $dependencia)
                            ->where(function ($query) use ($mesFinal, $anioConsulta) {
                                $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".$anioConsulta." AND n_mes_est >= 1)"));
                                $query->WhereRaw(DB::raw("(data_hitos.n_anio_est = ".$anioConsulta." AND n_mes_est <= ".$mesFinal.")"));
                            })
                            ->groupBy('n_anio_est', 'n_mes_est',  DB::raw("n_anio_est*100+n_mes_est*1"))
                            ->orderBy('orden', 'asc')
                            ->get();

        $stats2 = DB::connection('sqlsrv_s')->table('data_hitos')
                            ->select('x_desc_acto_procesal',DB::raw('count(*) as total'))
                            ->join('acto_procesal_maestro', 'acto_procesal_maestro.c_acto_procesal', 'data_hitos.c_acto_procesal_hito')
                            
                            ->when ($f_sede, function ($query) use ($sedes){
                                return $query->whereIN('data_hitos.c_sede', $sedes);
                            })
                            ->when ($f_org_jurisd, function ($query) use ($orgjurisd){
                                return $query->whereIN('data_hitos.c_org_jurisd', $orgjurisd);
                            })
                            ->when ($f_especialidad, function ($query) use ($especialidades){
                                return $query->whereIN('data_hitos.c_especialidad', $especialidades);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_juez, function ($query) use ($jueces){
                                return $query->whereIN('data_hitos.c_dni_mag', $jueces);
                            })
                            ->where('data_hitos.n_dependencia', $dependencia)
                            ->where(function ($query) use ($mesFinal, $anioConsulta) {
                                $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".$anioConsulta." AND n_mes_est >= 1)"));
                                $query->WhereRaw(DB::raw("(data_hitos.n_anio_est = ".$anioConsulta." AND n_mes_est <= ".$mesFinal.")"));
                            })
                            ->groupBy('x_desc_acto_procesal')
                            ->orderBy('total', 'desc')
                            ->get();

        $stats3 = DB::connection('sqlsrv_s')->table('data_hitos')
                            ->select(
                                DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as nom_dependencia"), 
                                'x_nom_instancia', 'm_provisional', DB::raw('count(*) as total'), 'zona')
                            ->join('instancia', function($join){
                                $join->on('instancia.c_instancia', '=','data_hitos.c_instancia');
                                $join->on('instancia.c_provincia', '=','data_hitos.c_provincia');
                            })
                            ->join('m_oficina', function($join){
                                $join->on('m_oficina.n_dependencia', '=','data_hitos.n_dependencia');
                                $join->on('m_oficina.idanoproc', '=','data_hitos.n_anio_est');
                                })
                            ->join('data_metas', function($join){
                                    $join->on('data_metas.n_dependencia', '=','data_hitos.n_dependencia');
                                    $join->on('data_metas.n_anio_est', '=','data_hitos.n_anio_est');
                            })
                            ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                            ->when ($f_sede, function ($query) use ($sedes){
                                return $query->whereIN('data_hitos.c_sede', $sedes);
                            })
                            ->when ($f_org_jurisd, function ($query) use ($orgjurisd){
                                return $query->whereIN('data_hitos.c_org_jurisd', $orgjurisd);
                            })
                            ->when ($f_especialidad, function ($query) use ($especialidades){
                                return $query->whereIN('data_hitos.c_especialidad', $especialidades);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            ->when ($f_juez, function ($query) use ($jueces){
                                return $query->whereIN('data_hitos.c_dni_mag', $jueces);
                            })
                            ->where('data_hitos.n_dependencia', $dependencia)
                            ->where(function ($query) use ($mesFinal, $anioConsulta) {
                                $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".$anioConsulta." AND n_mes_est >= 1)"));
                                $query->WhereRaw(DB::raw("(data_hitos.n_anio_est = ".$anioConsulta." AND n_mes_est <= ".$mesFinal.")"));
                            })
                            ->groupBy('x_nom_instancia' ,DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito)"), 'm_provisional', 'zona')
                            ->orderBy('total', 'desc')
                            ->get();

        $stats4 = DB::connection('sqlsrv_s')->table('data_hitos')
                            ->select('data_hitos.c_dni_mag', 'persona.c_ape_paterno', 'persona.c_ape_materno', 'persona.t_foto', DB::raw('count(*) as total'))
                            ->when ($f_sede, function ($query) use ($sedes){
                                return $query->whereIN('data_hitos.c_sede', $sedes);
                            })
                            ->when ($f_org_jurisd, function ($query) use ($orgjurisd){
                                return $query->whereIN('data_hitos.c_org_jurisd', $orgjurisd);
                            })
                            ->when ($f_especialidad, function ($query) use ($especialidades){
                                return $query->whereIN('data_hitos.c_especialidad', $especialidades);
                            })
                            ->when ($f_tipo, function ($query) use ($tipos){
                                return $query->whereIN('data_hitos.l_ind_exp', $tipos);
                            })
                            
                            ->where('data_hitos.n_dependencia', $dependencia)
                            ->where(function ($query) use ($mesFinal, $anioConsulta) {
                                $query->whereRaw(DB::raw("(data_hitos.n_anio_est = ".$anioConsulta." AND n_mes_est >= 1)"));
                                $query->WhereRaw(DB::raw("(data_hitos.n_anio_est = ".$anioConsulta." AND n_mes_est <= ".$mesFinal.")"));
                            })
                            ->leftJoin('persona', 'persona.c_dni', 'data_hitos.c_dni_mag')
                            ->groupBy('data_hitos.c_dni_mag', 'persona.c_ape_paterno', 'persona.c_ape_materno', 'persona.t_foto')
                            ->orderBy('total', 'desc')
                            ->get();

        $lastUpdate = DB::connection('sqlsrv_s')->table('data_hitos')
                        ->select('created_at')
                        ->orderBy('created_at', 'DESC')
                        ->first();

        $lastDate = DB::connection('sqlsrv_s')->table('data_hitos')
                        ->select('n_anio_est', 'n_mes_est')
                        ->groupBy('n_anio_est', 'n_mes_est')
                        ->orderBy('n_anio_est', 'desc')
                        ->orderBy('n_mes_est', 'desc')
                        ->first();
        
        $response['statusDB'] = true;
        $response['stats1'] = $stats1;
        $response['stats2'] = $stats2;
        $response['stats3'] = $stats3;
        $response['stats4'] = $stats4;
        $response['lastUpdate'] = $lastUpdate;
        $response['lastDate'] = $lastDate;
        
        return $response;

    }


    public function perfiles(Request $request)
    {
        $usuario = User::find(Auth::user()->id);
        $juez = $usuario->hasAnyRole('Stat.juez');
        
        if ($juez) {
            $anio = date("Y");

            $juzgado = DB::connection('sqlsrv_s')->table('persona')
                            ->select('persona.*',
                                DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as nom_dependencia"), 
                            )
                            ->leftJoin('m_oficina', function($join) use ($anio){
                                $join->on('m_oficina.n_dependencia', '=', 'persona.n_dependencia');
                                $join->on('m_oficina.idanoproc', DB::raw($anio));
                            })
                            ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                            ->where('persona.c_dni', $usuario->username)
                            ->first();

            $juzgado = json_encode($juzgado);
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('stat/juzgado',compact('user_roles', 'usuario', 'juzgado'));

        } else{
            $usuario = $this->getUser();
            $user_roles = $this->getRoles();
            return view('stat/inicio',compact('user_roles', 'usuario'));
        }
    
    }

    public function juzgadosView(Request $request)
    {
        $usuario = User::find(Auth::user()->id);
        $anio = date('Y');

        $juzgados = DB::connection('sqlsrv_s')->table('m_oficina')
                            ->select('m_oficina.n_dependencia',
                                DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as nom_dependencia"), 
                                'm_oficina.idubicodigo'
                            )
                            ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                            ->join('data_hitos', function($join){
                                $join->on('m_oficina.n_dependencia', '=','data_hitos.n_dependencia');
                                $join->on('m_oficina.idanoproc', '=','data_hitos.n_anio_est');
                            })                            
                            ->where('m_oficina.idanoproc', $anio)
                            ->distinct()
                            ->orderBy('m_oficina.idubicodigo')
                            ->get();

        $juzgados = json_encode($juzgados);
        $usuario = $this->getUser();
        $user_roles = $this->getRoles();
        return view('stat/juzgados',compact('user_roles', 'usuario', 'juzgados'));
    }

    public function juzgados(Request $request)
    {
        $usuario = User::find(Auth::user()->id);
        $anio = $request->anio;
        $n_dependencia = $request->n_dependencia;

        $juzgado = DB::connection('sqlsrv_s')->table('m_oficina')
                            ->select('n_dependencia',
                                DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as nom_dependencia"), 
                            )
                            ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                            ->where('m_oficina.n_dependencia', $n_dependencia)
                            ->where('m_oficina.idanoproc', $anio)
                            ->first();

        $juzgado = json_encode($juzgado);

        $juzgados = DB::connection('sqlsrv_s')->table('m_oficina')
                            ->select('m_oficina.n_dependencia',
                                DB::raw("CONCAT(mofidescri, ' - ', CSJPDATA.dbo.ubigeos.n_distrito) as nom_dependencia"), 
                                'm_oficina.idubicodigo'
                            )
                            ->join('CSJPDATA.dbo.ubigeos', 'CSJPDATA.dbo.ubigeos.ubigeo_id', 'm_oficina.idubicodigo')
                            ->join('data_hitos', function($join){
                                $join->on('m_oficina.n_dependencia', '=','data_hitos.n_dependencia');
                                $join->on('m_oficina.idanoproc', '=','data_hitos.n_anio_est');
                            })                            
                            ->where('m_oficina.idanoproc', $anio)
                            ->distinct()
                            ->orderBy('m_oficina.idubicodigo')
                            ->get();
        $juzgados = json_encode($juzgados);

        $usuario = $this->getUser();
        $user_roles = $this->getRoles();
        return view('stat/juzgados',compact('user_roles', 'usuario', 'juzgado', 'juzgados'));
    
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

        $user_mr =  User::find(Auth::user()->id);
        if ($vista == 'dataLoad' ){
            if($user_mr->hasRole('Stat.administrador')){
                return view('stat/'.$vista, compact('user_roles', 'usuario', 'app'));
            } else{
                return abort('403');
            }

        } else if (view()->exists('stat/'.$vista)){
            return view('stat/'.$vista, compact('user_roles', 'usuario', 'app'));
        } else{
            return abort('404');
        }
        
    }

    public function getUser(){
        $usuario = DB::table('users')
                            ->select('persona.numero_documento', 'persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
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