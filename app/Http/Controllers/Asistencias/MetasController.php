<?php

namespace App\Http\Controllers\Asistencias;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Auth;
use App\User;
use App\Persona;
use App\Asistencia;
use App\Models\AsistenciasMeta;
use App\PDF_MC_Table;
use setasign\Fpdi\Fpdi;

//require_once $_SERVER['DOCUMENT_ROOT'].'intranet/app/fpdf/fpdf.php';


class MetasController extends Controller
{

    public function index(Request $request){
        if($request->input('parents')){
            $tabla = DB::table('asistencias_parent')
                        ->select('asistencias_parent.id',
                                'of_A.nombre_oficina as nombre_oficina_A',
                                //'of_B.nombre_oficina as nombre_oficina_B',
                                 DB::raw("CONCAT(of_B.nombre_oficina, ' [',parent.nombre_oficina, ']') as nombre_oficina_B"),
                                'of_A.distrito as distrito_A',
                                'of_B.distrito as distrito_B'
                            )
                        ->join('view_op_oficinas as of_A', 'of_A.ID', 'asistencias_parent.op_oficinaa_id')
                        ->join('view_op_oficinas as of_B', 'of_B.ID', 'asistencias_parent.op_oficinab_id')
                        ->join('op_oficinas as parent', 'parent.id', 'of_B.parent_id')
                        ->orderBy('asistencias_parent.id','desc')
                        ->paginate(5000);

            $oficinas = DB::table('view_op_oficinas')
                            ->select('view_op_oficinas.id', DB::raw("CONCAT(view_op_oficinas.nombre_oficina, ' [',parent.nombre_oficina, '] - ', view_op_oficinas.distrito) as nombre_oficina"))
                            ->join('op_oficinas as parent', 'parent.id', 'view_op_oficinas.parent_id')
                            ->where('view_op_oficinas.have_personal', 1)
                            ->get();

            $response['oficinas'] = $oficinas;
            $response['parents'] = $tabla;
            return $response;
        } else if($request->input('metas')){
            $usuario = User::find(Auth::user()->id);
            if(!$usuario){
                return abort(403);
            }
            $persona = Persona::find($usuario->persona_id);
            $fecha_registro = time();
            $response = null;

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
                if($jefe_supervisor->p_ext_a != null || ($jefe_supervisor->p_ext_a == null && $jefe_supervisor->p_prop_b == null)){

                    $oficina = DB::table('op_oficinas')
                            ->select('op_oficinas.id')
                            ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                            ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                            ->where('persona.id', $persona->id)
                            ->first();
                                    
                    $tabla = DB::table('asistencias_metas')
                                ->select('asistencias_metas.*', 
                                            DB::raw("CASE WHEN (select parent_id FROM asistencias_metas_area where asistencias_metas_area.asistencias_metas_id = [asistencias_metas].id and asistencias_metas_area.parent_id = ".$oficina->id.") IS NULL THEN 0 ELSE 1 END AS b_area"
                                            ))
                                ->whereIN('asistencias_metas.parent_id',[0, $oficina->id])
                                ->where('asistencias_metas.activo', 1)
                                ->paginate(5000);

                    $oficinas_h = [];

                    if($jefe_supervisor->p_ext_a != null ){
                        $oficinas_h = DB::table('users')
                                            ->select('oficina_b.*', 'view_op_oficinas.*')
                                            ->join('persona', 'persona.id', 'users.persona_id')
                                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                            ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                            ->join('op_oficinas as oficina_a', 'oficina_a.id', 'op_plazas_tit.op_oficinaf_id')
                                            ->join('asistencias_parent', 'asistencias_parent.op_oficinaa_id', 'oficina_a.id')
                                            ->join('op_oficinas as oficina_b', 'oficina_b.id', 'asistencias_parent.op_oficinab_id')
                                            ->leftjoin('view_op_oficinas', 'view_op_oficinas.ID',  'oficina_b.id')
                                            ->where('users.id', Auth::user()->id)
                                            ->where('op_plazas_tit.jefe_Area', 1)
                                            ->orderBy('view_op_oficinas.distrito')
                                            ->get();
            
                        $oficina_p = DB::table('users')
                                            ->select('oficina_a.*', 'view_op_oficinas.*')
                                            ->join('persona', 'persona.id', 'users.persona_id')
                                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                            ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                            ->join('op_oficinas as oficina_a', 'oficina_a.id', 'op_plazas_tit.op_oficinaf_id')
                                            ->leftjoin('view_op_oficinas', 'view_op_oficinas.ID',  'oficina_a.id')
                                            ->where('users.id', Auth::user()->id)
                                            ->where('op_plazas_tit.jefe_Area', 1)
                                            ->get();
                        $oficinas_h = $oficinas_h->merge($oficina_p);

                    } else if($jefe_supervisor->p_ext_a == null && $jefe_supervisor->p_prop_b == null){
                        $oficinas_h = DB::table('users')
                                            ->select('op_oficinas.*')
                                            ->join('persona', 'persona.id', 'users.persona_id')
                                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                            ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                            ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                            ->where('users.id', Auth::user()->id)
                                            ->where('op_plazas_tit.jefe_Area', 1)
                                            ->get();
                    }
                
                    $response['oficinas_h'] = $oficinas_h;
                    $response['metas'] = $tabla;
                }
            } 
            return $response;
        } else if($request->input('metas_area')){
            $usuario = User::find(Auth::user()->id);
            if(!$usuario){
                return abort(403);
            }
            $persona = Persona::find($usuario->persona_id);
            $fecha_registro = time();
            $anio = date('Y', $fecha_registro);
            //$mes = date('m', strtotime('2021-09-01'));//');//$fecha_registro);
            $mes = date('m', $fecha_registro);


            $oficina = DB::table('op_oficinas')
                            ->select('op_oficinas.id')
                            ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                            ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                            ->where('persona.id', $persona->id)
                            ->first();


            /********** OFICINA AREA */
            $oficina_a = DB::table('asistencias_parent')
                                ->select('asistencias_parent.op_oficinaa_id')
                                ->where('asistencias_parent.op_oficinab_id', $oficina->id)
                                ->first();

            $jefe_supervisor = DB::table('persona')
                                ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres')
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                ->leftjoin('asistencias_parent as p_externo', 'p_externo.op_oficinaa_id', 'op_oficinas.id')
                                ->where('op_plazas_tit.jefe_area', 1)
                                ->where(function ($query) use ($oficina) {
                                    $query->where('p_externo.op_oficinab_id', $oficina->id);
                                    $query->orWhere('p_externo.op_oficinaa_id', $oficina->id);
                                    
                                })
                                ->first();

                            
            $tabla = DB::table('asistencias_metas')
                        ->select('asistencias_metas.*',
                                DB::raw("CONCAT(asistencias_metas.actividad, CASE WHEN asistencias_metas.b_sij = 1 THEN ' [SIJ]' ELSE '' END) AS actividad")
                            )
                        ->join('asistencias_metas_area', 'asistencias_metas_area.asistencias_metas_id', 'asistencias_metas.id')
                        ->where('asistencias_metas_area.parent_id', ($oficina_a)? $oficina_a->op_oficinaa_id : $oficina->id)
                        ->where('asistencias_metas.activo', 1)
                        ->paginate(5000);

            $metasmes = DB::table('asistencias_meta_mes')
                        ->select('asistencias_meta_mes.anio',
                            DB::raw("COUNT(*) as cantidad_metas"),
                            DB::raw("IIF( SUM(CAST(jefe_vb AS INT)) > 0  , 1,0) as jefe_vb"),
                            DB::raw("IIF( ( (asistencias_meta_mes.anio = ".$anio." AND asistencias_meta_mes.mes < ".$mes.") OR (asistencias_meta_mes.anio < ".$anio.")  ), 1,0) as enable_anexo"),
                            DB::raw("PERIODO                     = (CASE 
                            WHEN asistencias_meta_mes.mes=1 THEN 'Enero'
                            WHEN asistencias_meta_mes.mes=2 THEN 'Febrero'
                            WHEN asistencias_meta_mes.mes=3 THEN 'Marzo'
                            WHEN asistencias_meta_mes.mes=4 THEN 'Abril'
                            WHEN asistencias_meta_mes.mes=5 THEN 'Mayo'
                            WHEN asistencias_meta_mes.mes=6 THEN 'Junio'
                            WHEN asistencias_meta_mes.mes=7 THEN 'Julio'
                            WHEN asistencias_meta_mes.mes=8 THEN 'Agosto'
                            WHEN asistencias_meta_mes.mes=9 THEN 'Setiembre'
                            WHEN asistencias_meta_mes.mes=10 THEN 'Octubre'
                            WHEN asistencias_meta_mes.mes=11 THEN 'Noviembre'
                            WHEN asistencias_meta_mes.mes=12 THEN 'Diciembre' END)"),
                            'asistencias_meta_mes.mes',
                            DB::raw("(SELECT TOP 1 filename_anexo FROM asistencias_metas_anexo WHERE asistencias_metas_anexo.user_id = asistencias_meta_mes.user_id AND asistencias_metas_anexo.anio = asistencias_meta_mes.anio AND asistencias_metas_anexo.mes = asistencias_meta_mes.mes )  as anexo"),

                            DB::raw("(SELECT TOP 1 IIF(filename_anexo IS NOT NULL, IIF(supervisor_vb = 0, 1, 2), 0) FROM asistencias_metas_anexo WHERE asistencias_metas_anexo.user_id = asistencias_meta_mes.user_id AND asistencias_metas_anexo.anio = asistencias_meta_mes.anio AND asistencias_metas_anexo.mes = asistencias_meta_mes.mes )  as c_anexo"),
                        )
                        ->where('asistencias_meta_mes.user_id', $persona->numero_documento)
                        ->groupBy('anio', 'mes', 'user_id')
                        ->paginate(5000);

            $response['oficina'] = $oficina->id;
            $response['jefe_supervisor'] = $jefe_supervisor;
            $response['metas'] = $tabla;
            $response['metasmes'] = $metasmes;
            return $response;
        } else if($request->input('personal')){
            $personal = DB::table('persona')
                                ->select('persona.ap_paterno', 
                                        'persona.ap_materno',
                                        'persona.nombres',
                                        'persona.id',
                                        'op_plazas_tit.nombre_plaza',   
                                        'op_regimen.regimen_base'    
                                )
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->join('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimenf_id')
                                ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                ->where('op_oficinas.id', $request->ofID)
                                //->where('op_plazas_tit.jefe_Area', 0)
                                ->get();
            
            $response['personal'] = $personal;
            return $response;

        }
    }

    public function Parents(Request $request, $id = null){

        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }
        $persona = Persona::find($usuario->persona_id);
        $fecha_registro = time();

        if($request->input('of_A_id')){
            /****** CONSULTAR SI LA RELACION FUE REGISTRADA Y ACTIVA */
            $excepcion_DB = DB::table('asistencias_parent')
                                ->where('op_oficinaa_id', $request->of_A_id)
                                ->where('op_oficinab_id', $request->of_B_id)
                                ->first();
            DB::beginTransaction();
            try{
                if($excepcion_DB){
                    $response["statusBD"] = false;
                    $response["messageDB"] = "La relación de Jefe Inmediato ya está registrado en la Base de Datos.";
                } else{
                    $excepcion = DB::table('asistencias_parent')
                                        ->insert([
                                            'op_oficinaa_id'=> $request->of_A_id,
                                            'op_oficinab_id'=> $request->of_B_id,
                                            'user_update' => $persona->numero_documento,
                                        ]);   
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Relación registrada con exito.";
                }

               
        
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
        }

        if($id != null){
            $excepcion_UPDATE = DB::table('asistencias_parent')
                                        ->where('id', $id)
                                        ->delete();

            $response["statusBD"] = true;
            $response["messageDB"] = "Relación eliminada con exito.";
            return $response;

        }

        return abort(403);
    }

    public function Activity(Request $request, $id = null){

        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }
        $persona = Persona::find($usuario->persona_id);
        $fecha_registro = time();

        $oficina = DB::table('op_oficinas')
                        ->select('op_oficinas.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                        ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                        ->where('persona.id', $persona->id)
                        ->first();

        if($request->input('actividad')){
            DB::beginTransaction();
            try{
                if(isset($request->actividad['id']) && $request->actividad['id'] > 0){
                    $actividad = DB::table('asistencias_metas')
                                        ->where('id', $request->actividad['id'])
                                        ->update([
                                            'actividad'=> $request->actividad['actividad'],
                                            'activo'=> $request->actividad['activo'],
                                            'user_update' => $persona->numero_documento,
                                            'updated_at' => date('Ymd H:i:s',$fecha_registro),
                                        ]);   
                    if($actividad && $request->actividad['b_area']*1 == true){
                        $deleteOld = DB::table('asistencias_metas_area')
                                            ->where('parent_id', $oficina->id)
                                            ->where('asistencias_metas_id', $request->actividad['id'])
                                            ->delete();

                        $actividad_parent = DB::table('asistencias_metas_area')
                                                    ->insert([
                                                        'parent_id' => $oficina->id,
                                                        'asistencias_metas_id' => $request->actividad['id']
                                                    ]);
                    } else if($request->actividad['b_area']*1 == false){
                        $deleteOld = DB::table('asistencias_metas_area')
                                            ->where('parent_id', $oficina->id)
                                            ->where('asistencias_metas_id', $request->actividad['id'])
                                            ->delete();
                    }
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Actividad actualizada con exito.";
                } else{
                    $actividad = AsistenciasMeta::create([
                                            'actividad'=> $request->actividad['actividad'],
                                            'parent_id'=> $oficina->id,
                                            'user_update' => $persona->numero_documento,
                                        ]);
                    if($actividad){
                        $actividad_parent = DB::table('asistencias_metas_area')
                                                    ->insert([
                                                        'parent_id' => $oficina->id,
                                                        'asistencias_metas_id' => $actividad->id
                                                    ]);
                    }
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Actividad registrada con exito.";
                }
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        }

        if($id != null){
            $actividad = DB::table('asistencias_metas')
                                        ->where('id', $id)
                                        ->update([
                                            'activo'=> 0,
                                            'user_update' => $persona->numero_documento,
                                            'updated_at' => date('Ymd H:i:s',$fecha_registro),
                                        ]);   
            $response["statusBD"] = true;
            $response["messageDB"] = "Meta y/o activdad eliminada con exito.";
            return $response;

        }

        return abort(403);
    }

    public function MetasMes(Request $request, $id = null){
        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }
        $persona = Persona::find($usuario->persona_id);

        $oficina = DB::table('op_oficinas')
                        ->select('op_oficinas.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                        ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                        ->where('persona.id', $persona->id)
                        ->first();

        if($request->input('trabajador_id')){

            DB::beginTransaction();
            try{

                $oficina_t = DB::table('op_oficinas')
                                ->select('op_oficinas.id')
                                ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                                ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                                ->where('persona.numero_documento', $request->trabajador_id)
                                ->first();

                $deleteOld = DB::table('asistencias_meta_mes')
                                ->where('anio', $request->periodo['anio'])
                                ->where('mes', $request->periodo['mes'])
                                ->where('user_id', $request->trabajador_id)
                                ->where('jefe_vb', 0)
                                ->delete();

                foreach ($request->actividades as $actividad) {
                    $query = DB::table('asistencias_meta_mes')
                                    ->insert([
                                        'anio' => $request->periodo['anio'],
                                        'mes' => $request->periodo['mes'],
                                        'meta_id' => $actividad['id'],
                                        'user_id' => $request->trabajador_id,
                                        'oficinaf_id' => $oficina_t->id,
                                        'meta_cantidad' => $actividad['meta_cantidad'],
                                        'user_update' => $persona->numero_documento,
                                        'jefe_vb' => 1,
                                        'jefe_id' => $persona->numero_documento,
                                    ]);
                }
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Actividades registradas con exito.";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }


            return $response;

        
        } else if($request->input('actividades')){
            DB::beginTransaction();
            try{
                $deleteOld = DB::table('asistencias_meta_mes')
                                ->where('anio', $request->periodo['anio'])
                                ->where('mes', $request->periodo['mes'])
                                ->where('user_id', $persona->numero_documento)
                                ->where('oficinaf_id', $oficina->id)
                                ->where('jefe_vb', 0)
                                ->delete();

                foreach ($request->actividades as $actividad) {
                    $query = DB::table('asistencias_meta_mes')
                                    ->insert([
                                        'anio' => $request->periodo['anio'],
                                        'mes' => $request->periodo['mes'],
                                        'meta_id' => $actividad['id'],
                                        'user_id' => $persona->numero_documento,
                                        'oficinaf_id' => $oficina->id,
                                        'meta_cantidad' => $actividad['meta_cantidad'],
                                        'user_update' => $persona->numero_documento
                                    ]);
                }
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Actividades registradas con exito.";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }


            return $response;

        }
    }

    public function getMetasMes(Request $request, $id = null){
        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }
        if($request->input('user_id')){
            $persona = Persona::where('numero_documento', $request->user_id)->first();
        } else{
            $persona = Persona::find($usuario->persona_id);
        }

        $oficina = DB::table('op_oficinas')
                        ->select('op_oficinas.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                        ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                        ->where('persona.id', $persona->id)
                        ->first();

        $metasmes = DB::table('asistencias_meta_mes')
                        ->select('asistencias_meta_mes.*',
                                DB::raw("CONCAT(asistencias_metas.actividad, CASE WHEN asistencias_metas.b_sij = 1 THEN ' [SIJ]' ELSE '' END) AS actividad"),
                                DB::raw("(SELECT SUM(meta_cantidad) FROM asistencias_actividades JOIN asistencias ON asistencias.id = asistencias_actividades.asistencia_id WHERE asistencias.user_id = asistencias_meta_mes.user_id AND meta_anio = ".$request->periodo['anio']." AND meta_mes = ".$request->periodo['mes']."AND asistencias_actividades.meta_id = asistencias_meta_mes.meta_id ) as avance"),
                                
                                'asistencias_meta_mes.meta_id as id'

                                
                                )
                        ->join('asistencias_metas', 'asistencias_meta_mes.meta_id', 'asistencias_metas.id')
                        
                        ->where('asistencias_meta_mes.anio', $request->periodo['anio'])
                        ->where('asistencias_meta_mes.mes', $request->periodo['mes'])
                        ->where('asistencias_meta_mes.user_id', $persona->numero_documento)
                       // ->where('oficinaf_id', $oficina->id)
                        ->get();
        
        $last_periodo = DB::table('asistencias_meta_mes')
                                ->select('asistencias_meta_mes.anio', 'asistencias_meta_mes.mes')
                                ->where('user_id', $persona->numero_documento)
                                //->where('oficinaf_id', $oficina->id)
                                ->orderBy('anio', 'desc')
                                ->orderBy('mes', 'desc')
                                ->first();
        
        $response["last_periodo"] = $last_periodo;
        $response["metasmes"] = $metasmes;
        $response["statusBD"] = true;
        $response["messageDB"] = "Datos cargados correctamente";
        
        return $response;
    }

    public function openMetasMes(Request $request, $id = null) {
        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }
        if($request->input('trabajador_id')){

            DB::beginTransaction();
            try{
                $oficina_t = DB::table('op_oficinas')
                            ->select('op_oficinas.id')
                            ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                            ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                            ->where('persona.numero_documento', $request->trabajador_id)
                            ->first();


                $updateMetas = DB::table('asistencias_meta_mes')
                                ->where('anio', $request->periodo['anio'])
                                ->where('mes', $request->periodo['mes'])
                                ->where('user_id', $request->trabajador_id)
                                ->update([
                                    'jefe_vb' => 0,
                                    'oficinaf_id' => $oficina_t->id,
                                ]);

                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Operación realizada con éxito";

            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }

        } else{
            $response["statusBD"] = false;
            $response["messageDB"] = "Hubo un error al procesar la operación";
        }

        return $response;

    }


    public function getMetasTrabajadores(Request $request, $id = null){
        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }

        $persona = Persona::find($usuario->persona_id);
        $fecha_registro = time();
        $response = null;

        $jefe_supervisor = DB::table('users')
                            ->select('username', 'p_externo.op_oficinaa_id as p_ext_a', 'p_propio.op_oficinaa_id as p_prop_b')
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                            ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                            ->leftjoin('asistencias_parent as p_externo', 'p_externo.op_oficinaa_id', 'op_oficinas.id')
                            ->leftjoin('asistencias_parent as p_propio', 'p_propio.op_oficinab_id', 'op_oficinas.id')
                            ->where('users.id', Auth::user()->id)
                            ->where('op_plazas_tit.jefe_area', 1)
                            ->first();


        if($jefe_supervisor){
            if($jefe_supervisor->p_ext_a != null || ($jefe_supervisor->p_ext_a == null && $jefe_supervisor->p_prop_b == null)){

                $oficina = DB::table('op_oficinas')
                        ->select('op_oficinas.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                        ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                        ->where('persona.id', $persona->id)
                        ->first();
                                
                $tabla = DB::table('asistencias_metas')
                            ->select('asistencias_metas.*', 
                                        DB::raw("CASE WHEN (select parent_id FROM asistencias_metas_area where asistencias_metas_area.asistencias_metas_id = [asistencias_metas].id and asistencias_metas_area.parent_id = ".$oficina->id.") IS NULL THEN 0 ELSE 1 END AS b_area"
                                        ))
                            ->whereIN('asistencias_metas.parent_id',[0, $oficina->id])
                            ->where('asistencias_metas.activo', 1)
                            ->paginate(5000);

                $oficinas_h = [];

                if($jefe_supervisor->p_ext_a != null ){
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
                                        ->where('asistencias_parent.op_oficinaa_id',$oficina->id)
                                        ->where('op_plazas_tit.jefe_area', 0)
                                        ->orderby('persona.ap_paterno')
                                        ->get();
        
                    $oficina_p = DB::table('persona')
                                        ->select('persona.numero_documento', 'persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'view_op_oficinas.*',
                                        DB::raw("CONCAT(view_op_oficinas.nombre_oficina, ' [',parent.nombre_oficina, '] - ', view_op_oficinas.distrito) as nombre_oficina"),
                                        DB::raw("(SELECT TOP 1 jefe_vb from asistencias_meta_mes where asistencias_meta_mes.user_id = persona.numero_documento AND anio=".$request->periodo['anio']." AND mes = ".$request->periodo['mes']." ) as metames"),
                                        DB::raw("CONCAT((SELECT SUM(meta_cantidad) FROM asistencias_actividades JOIN asistencias ON asistencias.id = asistencias_actividades.asistencia_id WHERE asistencias.user_id = persona.numero_documento AND meta_anio = ".$request->periodo['anio']." AND meta_mes = ".$request->periodo['mes']."), ' / ', (SELECT SUM(meta_cantidad) FROM asistencias_meta_mes WHERE asistencias_meta_mes.user_id = persona.numero_documento AND asistencias_meta_mes.anio=".$request->periodo['anio']." AND asistencias_meta_mes.mes = ".$request->periodo['mes']." )) as avance"),
                                        DB::raw("(SELECT TOP 1 filename_anexo FROM asistencias_metas_anexo WHERE asistencias_metas_anexo.user_id = persona.numero_documento AND asistencias_metas_anexo.anio = ".$request->periodo['anio']." AND asistencias_metas_anexo.mes = ".$request->periodo['mes']." )  as anexo"),
                                        DB::raw("(SELECT TOP 1 supervisor_vb FROM asistencias_metas_anexo WHERE asistencias_metas_anexo.user_id = persona.numero_documento AND asistencias_metas_anexo.anio = ".$request->periodo['anio']." AND asistencias_metas_anexo.mes = ".$request->periodo['mes']." ) as enable_anexo")

                                        )
                                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                        ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                        ->join('op_oficinas as oficina_a', 'oficina_a.id', 'op_plazas_tit.op_oficinaf_id')
                                        ->leftjoin('view_op_oficinas', 'view_op_oficinas.ID',  'oficina_a.id')
                                        ->join('op_oficinas as parent', 'parent.id', 'view_op_oficinas.parent_id')
                                        ->where('oficina_a.id', $oficina->id)
                                        ->where('op_plazas_tit.jefe_area', 0)
                                        ->orderby('persona.ap_paterno')
                                        ->get();

                    $oficinas_h = $oficinas_h->merge($oficina_p);

                } else if($jefe_supervisor->p_ext_a == null && $jefe_supervisor->p_prop_b == null){
                    $oficinas_h = DB::table('users')
                                        ->select('op_oficinas.*')
                                        ->join('persona', 'persona.id', 'users.persona_id')
                                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                        ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                        ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                        ->where('users.id', Auth::user()->id)
                                        ->where('op_plazas_tit.jefe_area', 1)
                                        ->get();
                }

                
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos cargados correctamente";
            
                $response['oficinas_h'] = $oficinas_h;
                $response['metas'] = $tabla;
            }
        } 
        return $response;
    }

    public function getMetasTrabajadoresXLS(Request $request, $id = null){
        $usuario = User::find(Auth::user()->id);
        if(!$usuario){
            return abort(403);
        }

        $persona = Persona::find($usuario->persona_id);
        $fecha_registro = time();
        $response = null;

        $jefe_supervisor = DB::table('users')
                            ->select('username', 'p_externo.op_oficinaa_id as p_ext_a', 'p_propio.op_oficinaa_id as p_prop_b')
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                            ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            ->join('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                            ->leftjoin('asistencias_parent as p_externo', 'p_externo.op_oficinaa_id', 'op_oficinas.id')
                            ->leftjoin('asistencias_parent as p_propio', 'p_propio.op_oficinab_id', 'op_oficinas.id')
                            ->where('users.id', Auth::user()->id)
                            ->where('op_plazas_tit.jefe_area', 1)
                            ->first();

        $reporte = [];
        if($jefe_supervisor){
            if($jefe_supervisor->p_ext_a != null || ($jefe_supervisor->p_ext_a == null && $jefe_supervisor->p_prop_b == null)){

                $oficina = DB::table('op_oficinas')
                        ->select('op_oficinas.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.op_oficinaf_id', 'op_oficinas.id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                        ->join('persona', 'persona.id', 'persona_has_plaza_fun.persona_id')
                        ->where('persona.id', $persona->id)
                        ->first();
                
                /************  REPORTE EXCEL TRABAJADORES METAS */

                $reporte = DB::table('asistencias_meta_mes')
                ->select('numero_documento', 'ap_paterno', 'ap_materno', 'nombres',
                        'view_op_oficinas_all.nombre_oficina', 'distrito', 
                        'anio', 'mes', 'meta_cantidad',
                        DB::raw("CONCAT(asistencias_metas.actividad, CASE WHEN asistencias_metas.b_sij = 1 THEN ' [SIJ]' ELSE '' END) AS actividad"),
                        DB::raw("IIF(((select SUM(asistencias_actividades.meta_cantidad) FROM asistencias_actividades 
                        JOIN asistencias ON asistencias.id = asistencias_actividades.asistencia_id
                        where 
                        asistencias.user_id = asistencias_meta_mes.user_id
                        AND asistencias_actividades.meta_anio = [asistencias_meta_mes].anio 
                        AND asistencias_actividades.meta_mes = [asistencias_meta_mes].mes
                        AND asistencias_actividades.meta_id = [asistencias_meta_mes].meta_id
                        
                        )) is NULL,0 , ((select SUM(asistencias_actividades.meta_cantidad) FROM asistencias_actividades 
                        JOIN asistencias ON asistencias.id = asistencias_actividades.asistencia_id
                        where 
                        asistencias.user_id = asistencias_meta_mes.user_id
                        AND asistencias_actividades.meta_anio = [asistencias_meta_mes].anio 
                        AND asistencias_actividades.meta_mes = [asistencias_meta_mes].mes
                        AND asistencias_actividades.meta_id = [asistencias_meta_mes].meta_id
                        
                        ))) as meta_avance")
                        )
                ->join('persona', 'persona.numero_documento', 'asistencias_meta_mes.user_id')
                ->join('asistencias_parent', 'asistencias_parent.op_oficinab_id', 'asistencias_meta_mes.oficinaf_id')
                ->leftJoin('asistencias_metas', 'asistencias_metas.id', 'asistencias_meta_mes.meta_id')
                ->leftJoin('view_op_oficinas_all', 'view_op_oficinas_all.id', 'asistencias_meta_mes.oficinaf_id')
                ->where('asistencias_parent.op_oficinaa_id', $oficina->id)
                ->where('anio', $request->periodo['anio'])
                ->where('mes', $request->periodo['mes'])
                ->orderBy('numero_documento')
                ->get();
            }
        }
        
        $response["statusBD"] = true;
        $response["messageDB"] = "Datos cargados correctamente";
    
        $response['reporte'] = $reporte;
        return $response;
    }

    public function anexo04(Request $request, $usuario, $anio, $mes)
    { 
        
        //header("Content-Disposition:attachment;filename='downloaded.pdf'");

       // header('Content-type: application/pdf');

        $response = null;

        $evaluado = DB::table('persona')
                        ->select('persona.*', 'view_op_oficinas_all.nombre_oficina', 'view_op_oficinas_all.distrito',
                            'parent.nombre_oficina as parent_oficina', 'op_plazas_tit.nombre_plaza', 'op_regimen.regimen_base'
                        )
                        ->join('asistencias_meta_mes', 'asistencias_meta_mes.user_id', 'persona.numero_documento')
                        ->join('view_op_oficinas_all', 'view_op_oficinas_all.id', 'asistencias_meta_mes.oficinaf_id')
                        ->join('asistencias_actividades', function($join)
                            {
                                $join->on('asistencias_actividades.meta_mes', '=', 'asistencias_meta_mes.mes');
                                $join->on('asistencias_actividades.meta_anio','=','asistencias_meta_mes.anio');
                            })
                        ->join('asistencias', function($join)
                            {
                                $join->on('asistencias.id', '=', 'asistencias_actividades.asistencia_id');
                                $join->on('asistencias.user_id','=','persona.numero_documento');
                            })
                        ->join('op_plazas_tit','op_plazas_tit.id', 'asistencias.op_plaza_id')
                        ->join('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                        ->leftjoin('op_oficinas as parent', 'parent.id', 'view_op_oficinas_all.parent_id')

                        ->where('persona.numero_documento', $usuario)
                        ->where('asistencias_meta_mes.anio', $anio)
                        ->where('asistencias_meta_mes.mes', $mes)
                        ->where('asistencias_actividades.meta_anio', $anio)
                        ->where('asistencias_actividades.meta_mes', $mes)
                        ->whereRaw("YEAR(asistencias.fecha) =".$anio)
                        ->whereRaw("MONTH(asistencias.fecha) =".$mes)
                        ->distinct()
                        ->first();

        $evaluador = DB::table('asistencias_meta_mes')
                        ->select('persona.*', 'op_plazas_tit.nombre_plaza', 'op_regimen.regimen_base', 'view_op_oficinas_all.nombre_oficina')
                        ->join('persona', 'persona.numero_documento', 'asistencias_meta_mes.jefe_id')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                        ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                        ->join('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                        ->join('view_op_oficinas_all', 'view_op_oficinas_all.id', 'op_plazas_tit.op_oficinaf_id')
                        ->where('asistencias_meta_mes.user_id', $usuario)
                        ->where('asistencias_meta_mes.anio', $anio)
                        ->where('asistencias_meta_mes.mes', $mes)
                        ->distinct()
                        ->first();
        if(!$evaluado){
            return abort(403);
        }

        if(!$evaluador){
            return abort(403);
        }

        $pdf = new PDF_MC_Table();
        // T�tulos de las columnas
        $pdf->SetFont('Arial','',14);
        $pdf->setFillColor(230,230,230); 


        $pdf->AddPage();
        // Logo
        $pdf->Cell(80);
        $pdf->Image(public_path().'../../public/image/logo.png',10,8,10);
        // Arial bold 15
        $pdf->SetFont('Arial','B',11);
        // T�tulo
        $pdf->SetX(20);
        $pdf->Cell(10,0,'PODER JUDICIAL');

        $pdf->SetFont('Arial','',11);

        $pdf->SetX(20);
        $pdf->Cell(10,10,'Corte Superior de Justicia de Arequipa');
       
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(80);
        $pdf->Cell(0,10,utf8_decode('ANEXO 04'));
        $pdf->Ln(1);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(30);
        $pdf->Cell(0,20,utf8_decode('FORMATO DE EVALUACIÓN DEL CUMPLIMIENTO DEL TRABAJO'));

        $pdf->Ln(16);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(1);
        $pdf->Cell(190,4,"1. INFORMACION GENERAL",1,1,'L',1);
        
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(1);
        $pdf->Cell(80,4,utf8_decode("Corte/Consejo Ejecutivo/ Órgano:"),1,1,'L',0);
        $pdf->Ln(-4);
        $pdf->Cell(81);
        $pdf->Cell(110,4,utf8_decode("CORTE SUPERIOR DE JUSTICIA DE AREQUIPA"),1,1,'L',0);

        $pdf->Cell(1);
        $pdf->Cell(80,4,utf8_decode("Nombre de la Unidad Orgánica u Dependencia :"),1,1,'L',0);
        $pdf->Ln(-4);
        $pdf->Cell(81);
        $pdf->MultiCell(110,4,utf8_decode($evaluado->nombre_oficina." [".$evaluado->parent_oficina."] - ".$evaluado->distrito),1,1,0,0);

        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(1);
        $pdf->Cell(190,4,"2. DATOS DEL EVALUADO Y EVALUADOR",1,1,'L',1);
        
        $pdf->SetFont('Arial','B',9);


        $datef = $anio."-".$mes."-01";

        $pdf->Cell(1);
        $pdf->Cell(75,8,"PERIODO DE EVALUACION",1,1,'L',1);
        $pdf->Ln(-8);
        $pdf->Cell(76);
        $pdf->Cell(20,4,"INICIO",1,1,'C',1);
        $pdf->Cell(76);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(20,4,date("d/m/Y", strtotime($anio."-".$mes."-01")),1,1,'C',0);

        $pdf->SetFont('Arial','B',9);

        $pdf->Ln(-8);
        $pdf->Cell(96);
        $pdf->Cell(20,4,"FIN",1,1,'C',1);
        $pdf->Cell(96);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(20,4, date("t/m/Y", strtotime($datef)),1,1,'C',0);

        $pdf->Ln(-8);
        $pdf->Cell(116);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(55,8, utf8_decode("REUNIÓN DE FIJACIÓN DE METAS"),1,1,'L',1);

        $pdf->SetFont('Arial','B',9);

        $pdf->Ln(-8);
        $pdf->Cell(171);
        $pdf->Cell(20,4,"FECHA",1,1,'C',1);
        $pdf->Cell(171);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(20,4, date("d/m/Y", strtotime($anio."-".$mes."-01")) ,1,1,'C',0);


        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(1);
        $pdf->Cell(40,4,utf8_decode("DESCRIPCIÓN"),1,1,'L',1);
        $pdf->Ln(-4);
        $pdf->Cell(41);
        $pdf->Cell(75,4,utf8_decode("DATOS DE LA PERSONA A EVALUAR"),1,1,'C',1);

        $pdf->Ln(-4);
        $pdf->Cell(116);
        $pdf->Cell(75,4,utf8_decode("DATOS DEL EVALUADOR"),1,1,'C',1);


        $pdf->Cell(1);

        
        
        // Anchuras de las columnas
        $w = array(40,75,75);
        $pdf->SetFont('Arial','',9);

        $pdf->SetWidths($w);

        $header = array(utf8_decode("Apellidos y nombres:"), utf8_decode($evaluado->ap_paterno." ".$evaluado->ap_materno." ".$evaluado->nombres),
            utf8_decode($evaluador->ap_paterno." ".$evaluador->ap_materno." ".$evaluador->nombres));
        $pdf->RowT($header, 8);
        
        $pdf->Cell(1);
        $header = array(utf8_decode("Documento de Identidad:"), $evaluado->numero_documento, $evaluador->numero_documento);
        $pdf->RowT($header, 8);

        $pdf->Cell(1);
        $header = array(utf8_decode("Genero:"), 
        ($evaluado->sexo == 0)? 'Femenino': (($evaluado->sexo == 1)? 'Masculino': ''), 
        ($evaluador->sexo == 0)? 'Femenino': (($evaluador->sexo == 1)? 'Masculino': '') );
        $pdf->RowT($header, 8);

        $pdf->Cell(1);
        $header = array(utf8_decode("Categoría Ocupacional:"), '', '');
        $pdf->RowT($header, 8);

        $pdf->Cell(1);
        $header = array(utf8_decode("Nombre del Cargo:"), $evaluado->nombre_plaza, $evaluador->nombre_plaza);
        $pdf->RowT($header, 8);

        $pdf->Cell(1);
        $header = array(utf8_decode("Régimen Laboral:"), $evaluado->regimen_base, $evaluador->regimen_base);
        $pdf->RowT($header, 8);

        $pdf->Cell(1);
        $header = array(utf8_decode("Órgano o Unidad Orgánica:"), utf8_decode(mb_strtoupper($evaluado->nombre_oficina)), utf8_decode(mb_strtoupper($evaluador->nombre_oficina)));
        $pdf->RowT($header, 8);


        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(1);
        $pdf->Cell(190,4,"3. EVALUACION DEL CUMPLIMIENTO DE METAS FIJADAS",1,1,'L',1);



        $w = array(70,70,25,25);
        $pdf->SetFont('Arial','',8);

        $pdf->SetWidths($w);

        $pdf->Cell(1);
        $header = array(utf8_decode("DESCRIPCION DE LA ACTIVIDAD"), utf8_decode("PRODUCTOS ALCANZADOS POR EL TELETRABAJADOR"), utf8_decode("NIVELES DE LOGRO ESPERADO(%)"), utf8_decode("NIVEL DE LOGRO ALCANZADO(%)"));
        $pdf->RowB($header, 8);


        $dias_remoto = DB::table("asistencias")
                            ->whereRaw("YEAR(asistencias.fecha) =".$anio)
                            ->whereRaw("MONTH(asistencias.fecha) =".$mes)
                            ->where("asistencias.user_id", $usuario)
                            ->where("asistencias.tipo", 2)
                            ->get()->count();

        $dias_remoto_c = DB::table("asistencias")
                            ->whereRaw("YEAR(asistencias.fecha) =".$anio)
                            ->whereRaw("MONTH(asistencias.fecha) =".$mes)
                            ->where("asistencias.user_id", $usuario)
                            ->where("asistencias.tipo", 1)
                            ->whereNotNULL("asistencias.turno")
                            ->get()->count();

        $dias_asistencia = DB::table("asistencias")
                            ->whereRaw("YEAR(asistencias.fecha) =".$anio)
                            ->whereRaw("MONTH(asistencias.fecha) =".$mes)
                            ->where("asistencias.user_id", $usuario)
                            ->get()->count();

        $metas = DB::table("asistencias_meta_mes")
                            ->select(
                                'asistencias_meta_mes.*',
                                'asistencias_metas.actividad',
                                DB::raw("(SELECT SUM(asistencias_actividades.meta_cantidad) FROM asistencias_actividades JOIN asistencias ON asistencias.id = asistencias_actividades.asistencia_id WHERE asistencias.user_id = asistencias_meta_mes.user_id AND asistencias.tipo = 2 AND meta_anio = ".$anio." AND meta_mes = ".$mes."AND asistencias_actividades.meta_id = asistencias_meta_mes.meta_id ) as avance"),
                                DB::raw("(SELECT SUM(asistencias_actividades.meta_cantidad) FROM asistencias_actividades JOIN asistencias ON asistencias.id = asistencias_actividades.asistencia_id WHERE asistencias.user_id = asistencias_meta_mes.user_id AND (asistencias.tipo = 1 AND asistencias.turno IS NOT NULL) AND meta_anio = ".$anio." AND meta_mes = ".$mes."AND asistencias_actividades.meta_id = asistencias_meta_mes.meta_id ) as avance_complementario")
                            )
                            ->join('asistencias_metas', 'asistencias_metas.id', 'asistencias_meta_mes.meta_id')
                            ->where("asistencias_meta_mes.user_id", $usuario)
                            ->where("asistencias_meta_mes.mes", $mes)
                            ->where("asistencias_meta_mes.anio", $anio)
                            ->get();

        foreach ($metas as $meta) {
            /*$meta->anexo4 = floor( $meta->meta_cantidad / $dias_asistencia * $dias_remoto);
            if($meta->anexo4*1 == 0){
                $meta->anexo4 == 1;
            }
            //$meta->anexo4 += floor( ($meta->meta_cantidad / $dias_asistencia * $dias_remoto_c)/8*3);
            $meta->anexo4 += floor( ($meta->meta_cantidad / $dias_asistencia * $dias_remoto_c));
            */
            $meta->anexo4 = $meta->meta_cantidad*1;
            if($meta->anexo4*1 == 0){
                $meta->anexo4 == 1;
            }
        }
        
        $sumatoria = 0;
        $n_metas = 0;
        foreach ($metas as $meta) {
            if(($meta->avance != null || $meta->avance_complementario != null) && $meta->anexo4 > 0){
                //$avance_real = round($meta->avance*1 + (($meta->avance_complementario*1)/8*3));
                $avance_real = round($meta->avance*1 + (($meta->avance_complementario*1)));
                $pdf->Cell(1);
                $header = array(utf8_decode($meta->actividad), utf8_decode($avance_real." ".$meta->actividad), utf8_decode($meta->anexo4." - 100%"), utf8_decode( round(($avance_real*1 / $meta->anexo4*1)*100 ))."%" );
                $pdf->RowT($header, 8);
                $n_metas++;
                $sumatoria += round(($avance_real*1 / $meta->anexo4*1)*100 );
            }
    
        }


        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(1);
        $pdf->Cell(165,4,"RESULTADO FINAL - PROMEDIO DE NIVEL DE LOGROS",1,1,'R',1);
        $pdf->Ln(-4);
        $pdf->Cell(166);
        if($sumatoria > 0){
            $pdf->Cell(25,4,round($sumatoria/$n_metas)."%",1,1,'R',0);

        } else{
            $pdf->Cell(25,4,"",1,1,'R',0);
        }

        $pdf->Cell(1);
        $pdf->Cell(190,4,"",1,1,'R',1);

        $pdf->Cell(1);
        $pdf->Cell(190,4,"4. OBSERVACIONES Y/O RECOMENDACIONES",1,1,'L',1);


        $pdf->Cell(1);
        if($request->input('observaciones')){
            $pdf->SetFont('Arial','',8);
            $pdf->MultiCell(190,5,utf8_decode($request->observaciones),1,1,0,0);
        } else{
            $pdf->Cell(190,20,"",1,1,'L',0);
        }
        $pdf->Cell(1);
        $pdf->Cell(190,4,"5. SUSCRIPCION ",1,1,'L',1);

        $pdf->SetFont('Arial','',8);

        $pdf->Cell(1);
        $pdf->MultiCell(40,5,"CONFORMIDAD DEL EVALUADO",1,1,'L',1);
        $pdf->Ln(-10);
        $pdf->Cell(41);


        $pdf->SetFont('Arial','B',9);

        if($request->input('generate')){
            $pdf->MultiCell(55,5,"CONFORME: ".utf8_decode($evaluado->ap_paterno." ".$evaluado->ap_materno." ".$evaluado->nombres),1,1,0,0);
        } else{
            $pdf->MultiCell(55,10,"",1,1,0,0);
        }

        $pdf->SetFont('Arial','',8);

        if($request->input('generate')){

            $pdf->Ln(-10);
            $pdf->Cell(96);
            $pdf->MultiCell(40,5,"CONFORMIDAD DEL EVALUADOR",1,1,'L',1);
            $pdf->Ln(-10);
            $pdf->Cell(136);
            $pdf->MultiCell(55,10, utf8_decode(""),1,1,0,0);
            
        } else{
            $pdf->Ln(-10);
            $pdf->Cell(96);
            $pdf->MultiCell(40,5,"CONFORMIDAD DEL EVALUADOR",1,1,'L',1);
            $pdf->Ln(-10);
            $pdf->Cell(136);
            $pdf->Cell(55,10,"",1,1,'L',0);
        }
        

        //$pdf->Row(array())

        $pdf->Setx(-1);
        // Arial italic 8
      
        $pdf->Cell(1);
       // $pdf->Image(public_path().'../../public/image/footer.png',35,280,150);

       if($request->input('generate')){
            
            DB::beginTransaction();
            try{

                $b_anexo = DB::table('asistencias_metas_anexo')
                                ->where('user_id', $usuario)
                                ->where('mes', $mes)
                                ->where('anio', $anio)
                                ->first();
                
                if(!$b_anexo){
                    $pdf->Output('F','storage/anexo04/'.$usuario.'_'.$anio.'_'.$mes.'.pdf',false);

                    $anexo = DB::table('asistencias_metas_anexo')
                                    ->insert([
                                        'anio'=> $anio,
                                        'mes'=> $mes,
                                        'user_id' => $usuario,
                                        'filename_anexo' => $usuario.'_'.$anio.'_'.$mes.'.pdf',
                                        //'observacion' => $persona->numero_documento,
                                    ]);   
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Anexo 04 generado correctamente";
                } else{
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Anexo 04 generado anteriormente";
                }

                
        
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
        
            return $response;

        } else{
            $pdf->Output('I','Anexo '.$usuario.'_'.$anio.'_'.$mes.'.pdf',false);
        }
        //$pdf->Output('F','Anexo '.$usuario.'_'.$anio.'-'.$mes.'.pdf',false);

    }

    public function vbanexo04(Request $request, $usuario, $filename_anexo)
    { 
        require_once $_SERVER['DOCUMENT_ROOT'].'/intranet/app/fpdf/fpdf.php';

        $anexo = DB::table('asistencias_metas_anexo')
                            ->where('user_id', $usuario)
                            ->where('filename_anexo', $filename_anexo)
                            ->where('supervisor_vb', false)
                            ->first();
        $fecha_registro = time();

        $usuariovb = User::find(Auth::user()->id);
        if(!$usuariovb){
            return abort(403);
        }
        $persona = Persona::find($usuariovb->persona_id);

        $response = null;
        if($anexo){

                // initiate FPDI
            $pdf = new Fpdi();
            // add a page
            //$pdf->AddPage();
            // set the source file
            //$pdf->setSourceFile(public_path().'/../storage/sgd/'.$documento->filename);
            $pageCount = $pdf->setSourceFile(public_path().'/../storage/anexo04/'.$filename_anexo);
            // import page 1
            //$tplIdx = $pdf->importPage(1);

            for ($pageNo = 1; $pageNo <= $pageCount ; $pageNo++) {
                $tplIdx = $pdf->importPage($pageNo);
            
                // add a page
                $pdf->AddPage();
                //$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
            
                // use the imported page and place it at position 10,10 with a width of 100 mm
                $pdf->useTemplate($tplIdx, 0, 0, 210);


                
                // now write some text above the imported page
                $pdf->SetFont('Arial','B',10);
                $pdf->SetTextColor(0, 0, 0);

                $pdf->SetXY(150, 240);
                $pdf->MultiCell(40,5,utf8_decode('VB°: '.$persona->ap_paterno.' '.$persona->ap_materno.' '.$persona->nombres),0,0,0,0);

                $pdf->SetFont('Arial','',10);
                $pdf->SetTextColor(100, 100, 100);


                $pdf->SetXY(10, 276);
                $pdf->Write(0, 'Este documento se encuentra en nuestros servidores https://csjarequipa.pj.gob.pe/');


                $image = \QrCode::format('png')
                         ->merge('public/image/logo_.png', 0.2, true)
                         ->size(500)->errorCorrection('H')
                         ->generate('A http://172.28.206.57/intranet/storage/anexo04/'.$filename_anexo);

                $dataURI = base64_encode($image);

                $TEMPIMGLOC = 'tempimg.png';


                if( $image!==false )
                {
                    if( file_put_contents($TEMPIMGLOC,$image)!==false )
                    {
                        $pdf->SetXY(180, 276);
                        //$pdf->Image('qr.png',160,256,40);
                        $pdf->Image('http://127.0.0.1/intranet/'.$TEMPIMGLOC, 160, 256, 30, 30, "png");
                
                    }
                }
            }
            
            
                

          //  $pdf->Output('F', 'generated.pdf');

            DB::beginTransaction();

            try{
                $pdf->Output('F','storage/anexo04/'.$filename_anexo,false);
                $anexo_f = DB::table('asistencias_metas_anexo')
                                ->where('user_id', $usuario)
                                ->where('filename_anexo', $filename_anexo)
                                ->update([
                                    'supervisor_id' => $persona->numero_documento,
                                    'supervisor_vb' => true,
                                    'fecha_vb' => date('Ymd H:i:s', $fecha_registro)
                                    //'observacion' => $persona->numero_documento,
                                ]);
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Anexo firmado correctamente";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }

         
        } else{
            $response["statusBD"] = true;
            $response["messageDB"] = "Anexo 04 firmado anteriormente";
        }
        return $response;
    }
}