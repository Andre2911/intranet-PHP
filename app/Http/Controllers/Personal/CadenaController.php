<?php
namespace App\Http\Controllers\Personal;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Persona;
use App\User;
use Illuminate\Support\Facades\Auth;

class CadenaController extends Controller
{
    public function index(Request $request)
    {
        $usuario = User::find(Auth::user()->id);
        $response=null;

        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal.administrador') ){

            if($request->input('crear')  && $request->input('anio') && $request->input('mes')){

                $response = null;
                $persona = Persona::find(Auth::user()->persona_id);

                $mes = ($request->mes < 10) ? '0'.$request->mes: $request->mes;
                $dateStringIni = $request->anio.'-'.$mes.'-01';
                
                $firstDateOfMonth = date("Y-m-d", strtotime($dateStringIni));
                $lastDateOfMonth = date("Y-m-t", strtotime($dateStringIni));

                $plazas_cadena = DB::table('op_plazas_tit')
                                    ->select('op_plazas_tit.id', 'op_plazas_tit.c_plaza', 'op_plazas_tit.nombre_plaza',
                                        DB::raw("CONCAT(dbo.view_op_oficinas.nombre_oficina, ' - ', dbo.view_op_oficinas.distrito) as nombre_oficina"), 
                                        'view_op_oficinas.id as oficina_id',
                                        'op_regimen.regimen_completo', 
                            
                                        'persona_has_plaza_tit.op_plaza_tit_id as persona_tit_plaza_id',
                                        'persona_tit.id as persona_tit_id',
                                        DB::raw("IIF(persona_tit.id IS NOT NULL, CONCAT(persona_tit.ap_paterno, ' ', persona_tit.ap_materno, ' ', persona_tit.nombres), null) as persona_tit"),
                                        
                                        'persona_has_plaza_fun.persona_id as persona_fun_id',
                                        DB::raw("IIF(persona_fun.id IS NOT NULL, CONCAT(persona_fun.ap_paterno, ' ', persona_fun.ap_materno, ' ', persona_fun.nombres), null) as persona_fun"),
                                        'plt_persona_fun.op_plaza_tit_id as persona_fun_plaza_id',
                                        DB::raw("motivo = 'Suplencia'"),
                                        DB::raw("fecha_ini = null"),
                                        DB::raw("fecha_fin = null"),

                                    )
                                    ->leftJoin('persona_has_plaza_tit', 'persona_has_plaza_tit.op_plaza_tit_id', 'op_plazas_tit.id')
                                    ->leftJoin('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                                    ->leftJoin('persona as persona_fun', 'persona_fun.id', 'persona_has_plaza_fun.persona_id')
                                    ->leftJoin('persona as persona_tit', 'persona_tit.id', 'persona_has_plaza_tit.persona_id')
                                    ->leftJoin('persona_has_plaza_tit as plt_persona_fun', 'plt_persona_fun.persona_id', 'persona_fun.id')
                                    ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                    ->join('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                                    ->whereNotIn('op_plazas_tit.op_regimen_id',[5,7])                        
                                    //->whereNULL('persona_has_plaza_tit.op_plaza_tit_id')
                                    //->whereRaw('persona_tit.id != persona_has_plaza_fun.persona_id')

                                    ->where('op_plazas_tit.activo', 1)
                                    ->get();
                

                $plazasLicencia = DB::table('op_lic_informes')
                                    ->select(   
                                            'op_plazas_tit.id',
                                            'op_plazas_tit.c_plaza',
                                            'op_plazas_tit.nombre_plaza',
                                            DB::raw("CONCAT(dbo.view_op_oficinas.nombre_oficina, ' - ', dbo.view_op_oficinas.distrito) as nombre_oficina"), 
                                            'view_op_oficinas.id as oficina_id',
                                            'op_regimen.regimen_completo', 
                                            
                                            
                                            DB::raw("persona_tit_plaza_id = null"),
                                            'persona.id as persona_tit_id',
                                            DB::raw("IIF(persona.id IS NOT NULL, CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres), null) as persona_tit"),
                                            
                                            /*DB::raw("persona_fun_id = null"),
                                            DB::raw("persona_fun = null"),
                                            DB::raw("persona_fun_plaza_id = null"),
                                            */
                                            'persona_has_plaza_fun.persona_id as persona_fun_id',
                                            DB::raw("IIF(persona_fun.id IS NOT NULL, CONCAT(persona_fun.ap_paterno, ' ', persona_fun.ap_materno, ' ', persona_fun.nombres), null) as persona_fun"),
                                            'plt_persona_fun.op_plaza_tit_id as persona_fun_plaza_id',

                                            DB::raw("motivo = 'Licencia'"),
                                            'op_lic_informes.fecha_ini',
                                            'op_lic_informes.fecha_fin'
                                            )
                                    ->join('persona', 'persona.numero_documento', 'op_lic_informes.personal_id')
                                    ->join('persona_has_plaza_tit', 'persona_has_plaza_tit.persona_id', 'persona.id')
                                    ->join('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_tit.op_plaza_tit_id')
                                    ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                                    ->join('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                                    ->leftJoin('persona_has_plaza_fun', 'persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                                    ->leftJoin('persona as persona_fun', 'persona_fun.id', 'persona_has_plaza_fun.persona_id')
                                    ->leftJoin('persona_has_plaza_tit as plt_persona_fun', 'plt_persona_fun.persona_id', 'persona_fun.id')

                                    ->orWhere(function($query) use ($firstDateOfMonth, $lastDateOfMonth)
                                    {
                                        $query->where('op_lic_informes.fecha_fin', '>', $firstDateOfMonth)
                                            ->where('op_lic_informes.fecha_fin', '<=', $lastDateOfMonth)
                                            ->whereNotNull(DB::raw("persona_has_plaza_tit.op_plaza_tit_id"))
                                            ->whereNotIn('op_plazas_tit.op_regimen_id',[5,7])
                                            ->where('op_lic_informes.sentido', 'PROCEDENTE')
                                            ->where('op_lic_informes.confirmada', '!=', -1)
                                            ->whereNULL('op_lic_informes.anio_vac')
                                            ->where('op_lic_informes.c_goce',0);
                                    })
                                    ->orWhere(function($query) use ($firstDateOfMonth, $lastDateOfMonth)
                                    {
                                        $query->where('op_lic_informes.fecha_ini', '>=', $firstDateOfMonth)
                                            ->where('op_lic_informes.fecha_ini', '<', $lastDateOfMonth)
                                            ->whereNotNull(DB::raw("persona_has_plaza_tit.op_plaza_tit_id"))
                                            ->whereNotIn('op_plazas_tit.op_regimen_id',[5,7])             
                                            ->whereNULL('op_lic_informes.anio_vac')
                                            ->where('op_lic_informes.sentido', 'PROCEDENTE')
                                            ->where('op_lic_informes.confirmada', '!=', -1)
                                            ->where('op_lic_informes.c_goce',0);
                                    })
                                    ->orWhere(function($query) use ($firstDateOfMonth, $lastDateOfMonth)
                                    {
                                        $query->where('op_lic_informes.fecha_ini', '<=', $firstDateOfMonth)
                                            ->where('op_lic_informes.fecha_fin', '>=', $lastDateOfMonth)
                                            ->whereNotNull(DB::raw("persona_has_plaza_tit.op_plaza_tit_id"))
                                            ->whereNotIn('op_plazas_tit.op_regimen_id',[5,7])               
                                            ->whereNULL('op_lic_informes.anio_vac')
                                            ->where('op_lic_informes.sentido', 'PROCEDENTE')
                                            ->where('op_lic_informes.confirmada', '!=', -1)
                                            ->where('op_lic_informes.c_goce',0);
                                    })
                                    ->get();
                
                $plazas_cadena = $plazas_cadena->merge($plazasLicencia);
                
                DB::beginTransaction();

                try{
                    if(!empty($plazas_cadena)){
                        $cadena = $this->nested($plazas_cadena, null);
                    }
                    if(!empty($cadena)){
                        DB::table('op_cadena_tmp')->truncate();
                        $this->saveCadena($cadena, $request->anio, $request->mes, $persona->numero_documento);
                    }
                    DB::commit();

                    $response["statusBD"] = true;
                    $response["messageDB"] = "Cadena periodica creada correctamente";
                } catch (\Exception $e) {
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
                }

                if($response["statusBD"]){

                    $cadena_new = DB::table('op_cadena_tmp')
                                        ->select('op_cadena_tmp.*',
                                            'plaza_tit.c_plaza',
                                            'plaza_tit.nombre_plaza',
                                            DB::raw("CONCAT(dbo.view_op_oficinas.nombre_oficina, ' - ', dbo.view_op_oficinas.distrito) as nombre_oficina"), 

                                            'op_regimen.regimen_completo', 
                                            DB::raw("IIF(persona_tit.id IS NOT NULL, CONCAT(persona_tit.ap_paterno, ' ', persona_tit.ap_materno, ' ', persona_tit.nombres), null) as persona_tit"),
                                            
                                            DB::raw("IIF(persona_fun.id IS NOT NULL, CONCAT(persona_fun.ap_paterno, ' ', persona_fun.ap_materno, ' ', persona_fun.nombres), null) as persona_fun"),
                                            DB::raw("motivo = tipo"),
                                            DB::raw("fecha_ini = null"),
                                            DB::raw("fecha_fin = null"),
                                        )
                                        ->leftJoin('persona as persona_tit', 'persona_tit.id', 'op_cadena_tmp.persona_tit_id')
                                        ->leftJoin('persona as persona_fun', 'persona_fun.id', 'op_cadena_tmp.persona_fun_id')
                                        ->join('op_plazas_tit as plaza_tit', 'plaza_tit.id', 'op_cadena_tmp.plaza_id')
                                        ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_cadena_tmp.oficina_id')
                                        ->join('op_regimen', 'op_regimen.id', 'plaza_tit.op_regimen_id')
                                        ->get();
                    
                    $cadena_plana = DB::table('op_cadena_tmp')
                                            ->select('op_cadena_tmp.*',
                                            'plaza_tit.c_plaza',
                                            'plaza_tit.nombre_plaza',
                                            DB::raw("CONCAT(dbo.view_op_oficinas.nombre_oficina, ' - ', dbo.view_op_oficinas.distrito) as nombre_oficina"), 

                                            'op_regimen.regimen_completo', 
                                            DB::raw("IIF(persona_tit.id IS NOT NULL, CONCAT(persona_tit.ap_paterno, ' ', persona_tit.ap_materno, ' ', persona_tit.nombres), null) as persona_tit"),
                                            
                                            DB::raw("IIF(persona_fun.id IS NOT NULL, CONCAT(persona_fun.ap_paterno, ' ', persona_fun.ap_materno, ' ', persona_fun.nombres), null) as persona_fun"),
                                            DB::raw("motivo = tipo"),
                                            DB::raw("fecha_ini = null"),
                                            DB::raw("fecha_fin = null"),
                                        )
                                        ->leftJoin('persona as persona_tit', 'persona_tit.id', 'op_cadena_tmp.persona_tit_id')
                                        ->leftJoin('persona as persona_fun', 'persona_fun.id', 'op_cadena_tmp.persona_fun_id')
                                        ->join('op_plazas_tit as plaza_tit', 'plaza_tit.id', 'op_cadena_tmp.plaza_id')
                                        ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_cadena_tmp.oficina_id')
                                        ->join('op_regimen', 'op_regimen.id', 'plaza_tit.op_regimen_id')
                                        ->get();

                    $cadena_view = $this->nested($cadena_new, null);
                    return [
                        'c_plana' => $cadena_plana,
                        'cadena' => $cadena_view,
                        'periodos' => [], 
                        'response' => $response
                    ];
                } else{
                    return ['cadena' => [],
                    'c_plana' => [],
                    'periodos' => [], 
                    'response' => $response
                ];
                }


            
            } else if($request->input('consultar')  && $request->input('anio') && $request->input('mes')){
                $cadena_new = DB::table('op_cadena_tmp')
                                        ->select('op_cadena_tmp.*',
                                            'plaza_tit.c_plaza',
                                            'plaza_tit.nombre_plaza',
                                            DB::raw("CONCAT(dbo.view_op_oficinas.nombre_oficina, ' - ', dbo.view_op_oficinas.distrito) as nombre_oficina"), 

                                            'op_regimen.regimen_completo', 
                                            DB::raw("IIF(persona_tit.id IS NOT NULL, CONCAT(persona_tit.ap_paterno, ' ', persona_tit.ap_materno, ' ', persona_tit.nombres), null) as persona_tit"),
                                            
                                            DB::raw("IIF(persona_fun.id IS NOT NULL, CONCAT(persona_fun.ap_paterno, ' ', persona_fun.ap_materno, ' ', persona_fun.nombres), null) as persona_fun"),
                                            DB::raw("motivo = tipo"),
                                            DB::raw("fecha_ini = null"),
                                            DB::raw("fecha_fin = null"),
                                        )
                                        ->leftJoin('persona as persona_tit', 'persona_tit.id', 'op_cadena_tmp.persona_tit_id')
                                        ->leftJoin('persona as persona_fun', 'persona_fun.id', 'op_cadena_tmp.persona_fun_id')
                                        ->join('op_plazas_tit as plaza_tit', 'plaza_tit.id', 'op_cadena_tmp.plaza_id')
                                        ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_cadena_tmp.oficina_id')
                                        ->join('op_regimen', 'op_regimen.id', 'plaza_tit.op_regimen_id')
                                        ->get();
                    
                    $cadena_plana = DB::table('op_cadena_tmp')
                                            ->select('op_cadena_tmp.*',
                                            'plaza_tit.c_plaza',
                                            'plaza_tit.nombre_plaza',
                                            DB::raw("CONCAT(dbo.view_op_oficinas.nombre_oficina, ' - ', dbo.view_op_oficinas.distrito) as nombre_oficina"), 

                                            'op_regimen.regimen_completo', 
                                            DB::raw("IIF(persona_tit.id IS NOT NULL, CONCAT(persona_tit.ap_paterno, ' ', persona_tit.ap_materno, ' ', persona_tit.nombres), null) as persona_tit"),
                                            
                                            DB::raw("IIF(persona_fun.id IS NOT NULL, CONCAT(persona_fun.ap_paterno, ' ', persona_fun.ap_materno, ' ', persona_fun.nombres), null) as persona_fun"),
                                            DB::raw("motivo = tipo"),
                                            DB::raw("fecha_ini = null"),
                                            DB::raw("fecha_fin = null"),
                                        )
                                        ->leftJoin('persona as persona_tit', 'persona_tit.id', 'op_cadena_tmp.persona_tit_id')
                                        ->leftJoin('persona as persona_fun', 'persona_fun.id', 'op_cadena_tmp.persona_fun_id')
                                        ->join('op_plazas_tit as plaza_tit', 'plaza_tit.id', 'op_cadena_tmp.plaza_id')
                                        ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_cadena_tmp.oficina_id')
                                        ->join('op_regimen', 'op_regimen.id', 'plaza_tit.op_regimen_id')
                                        ->get();

                    $cadena_view = $this->nested($cadena_new, null);
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Cadena periodica cargada correctamente";

                    return [
                        'c_plana' => $cadena_plana,
                        'cadena' => $cadena_view,
                        'periodos' => [], 
                        'response' => $response
                    ];
            
            }else if($request->input('init')){
                $periodos = DB::table('op_cadena')
                                    ->select('anio', 'mes')
                                    ->groupBy('anio','mes')
                                    ->get();

                $last_periodo = DB::table('op_cadena_tmp')
                                    ->select('anio', 'mes')
                                    ->groupBy('anio','mes')
                                    ->get();

                return ['cadena' => [], 'c_plana' => [], 'periodos' => $periodos, 'last_periodo' => $last_periodo];
            } else{
                abort(403);
            }
        }
        return abort('403');
    }

    public function store(Request $request){
        if($request->input('creartmp')){
            dd($request->creartmp);
        }
    }

    public function update(Request $request, $id){
        $response = null;
        $persona = Persona::find(Auth::user()->persona_id);
        $fecha_registro = time();

        if($request->input('plaza')){

            if($request->accion == 'unlink'){
                DB::beginTransaction();
                try{
                    $cadena = DB::table('op_cadena_tmp')
                                ->where('op_cadena_tmp.id', $id)
                                ->first();
                    
                    if($cadena){
                        $this->unlink($cadena->persona_fun_plaza_id);
                        $query = DB::table('op_cadena_tmp')
                                    ->where('op_cadena_tmp.id', $id)
                                    ->update([
                                        'persona_fun_plaza_id' => null,
                                        'persona_fun_id' => null,
                                        'user_update' => $persona->numero_documento,
                                        'updated_at' => date('Ymd H:i:s',$fecha_registro)
                                        
                                    ]);
                    }

                    DB::commit();
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Cadena temporal actualizada correctamente";
                } catch (\Exception $e) {
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Error al actualizar en la Base de Datos.";
                }

            } else if($request->accion == 'updateNodo'){
                $old_nodo = DB::table('op_cadena_tmp')
                                ->where('op_cadena_tmp.id', $id)
                                ->first();
                
                if($old_nodo){
                    /******** ULTIMO NUDO */
                    if($old_nodo->persona_fun_plaza_id == null){
                        DB::beginTransaction();
                        try{
                            $update_nodo = DB::table('op_cadena_tmp')
                                            ->where('op_cadena_tmp.id', $id)
                                            ->update([
                                                'persona_fun_plaza_id' => $request->plaza['persona_fun_plaza_id'],
                                                'persona_fun_id' => $request->plaza['persona_fun_id'],
                                                'user_update' => $persona->numero_documento,
                                                'updated_at' => date('Ymd H:i:s',$fecha_registro)
                                            ]);

                        /************** ENCARGATURA O SUPLENCIA */
                            
                            if($request->plaza['persona_fun_plaza_id'] != null){
                                $plaza = DB::table('op_plazas_tit')
                                                ->where('id', $request->plaza['persona_fun_plaza_id'])
                                                ->first();

                                $new_nodo = DB::table('op_cadena_tmp')
                                                ->insert([
                                                    'anio' => $request->plaza['anio'],
                                                    'mes' => $request->plaza['mes'],
                                                    'plaza_id' => $request->plaza['persona_fun_plaza_id'],
                                                    'oficina_id' => $plaza->op_oficinaf_id,
                                                    'persona_tit_plaza_id' => $request->plaza['persona_fun_plaza_id'],
                                                    'persona_fun_plaza_id' => null,
                                                    'persona_tit_id' => $request->plaza['persona_fun_id'],
                                                    'persona_fun_id' => null,
                                                    'tipo' =>  $request->plaza['motivo'],
                                                    'user_update' => $persona->numero_documento,
                                                ]);
                            }

                            DB::commit();

                            $response["statusBD"] = true;
                            $response["messageDB"] = "Nodo agregado correctamente";
                        } catch (\Exception $e) {
                            DB::rollback();
                            $response["statusBD"] = false;
                            $response["messageDB"] = "Error al actualizar en la Base de Datos.";
                        }

                    } else{

                        /*********** REEMPLAZO DE NODO */
                        DB::beginTransaction();
                        try{
                            $update_nodo = DB::table('op_cadena_tmp')
                                            ->where('op_cadena_tmp.id', $id)
                                            ->update([
                                                'persona_fun_plaza_id' => $request->plaza['persona_fun_plaza_id'],
                                                'persona_fun_id' => $request->plaza['persona_fun_id'],
                                                'user_update' => $persona->numero_documento,
                                                'updated_at' => date('Ymd H:i:s',$fecha_registro)
                                            ]);

                        /************** REEMPLAZAMOS ENCARGATURA */
                            
                            if($request->plaza['persona_fun_plaza_id'] != null){
                                $plaza = DB::table('op_plazas_tit')
                                                ->where('id', $request->plaza['persona_fun_plaza_id'])
                                                ->first();

                                $update_nodo2 = DB::table('op_cadena_tmp')
                                                ->where('persona_tit_plaza_id', $old_nodo->persona_fun_plaza_id)
                                                ->update([
                                                    'plaza_id' => $request->plaza['persona_fun_plaza_id'],
                                                    'oficina_id' => $plaza->op_oficinaf_id,
                                                    'persona_tit_plaza_id' => $request->plaza['persona_fun_plaza_id'],
                                                    'persona_tit_id' => $request->plaza['persona_fun_id'],
                                                    'user_update' => $persona->numero_documento,
                                                ]);
                            }

                            DB::commit();

                            $response["statusBD"] = true;
                            $response["messageDB"] = "Nodo agregado correctamente";
                        } catch (\Exception $e) {
                            DB::rollback();
                            $response["statusBD"] = false;
                            $response["messageDB"] = "Error al actualizar en la Base de Datos.";
                        }

                    }

                }
            }

            if($response["statusBD"]){
                $cadena_new = DB::table('op_cadena_tmp')
                                    ->select('op_cadena_tmp.*',
                                        'plaza_tit.c_plaza',
                                        'plaza_tit.nombre_plaza',
                                        DB::raw("CONCAT(dbo.view_op_oficinas.nombre_oficina, ' - ', dbo.view_op_oficinas.distrito) as nombre_oficina"), 

                                        'op_regimen.regimen_completo', 
                                        DB::raw("IIF(persona_tit.id IS NOT NULL, CONCAT(persona_tit.ap_paterno, ' ', persona_tit.ap_materno, ' ', persona_tit.nombres), null) as persona_tit"),
                                        
                                        DB::raw("IIF(persona_fun.id IS NOT NULL, CONCAT(persona_fun.ap_paterno, ' ', persona_fun.ap_materno, ' ', persona_fun.nombres), null) as persona_fun"),
                                        DB::raw("motivo = tipo"),
                                        DB::raw("fecha_ini = null"),
                                        DB::raw("fecha_fin = null"),
                                    )
                                    ->leftJoin('persona as persona_tit', 'persona_tit.id', 'op_cadena_tmp.persona_tit_id')
                                    ->leftJoin('persona as persona_fun', 'persona_fun.id', 'op_cadena_tmp.persona_fun_id')
                                    ->join('op_plazas_tit as plaza_tit', 'plaza_tit.id', 'op_cadena_tmp.plaza_id')
                                    ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_cadena_tmp.oficina_id')
                                    ->join('op_regimen', 'op_regimen.id', 'plaza_tit.op_regimen_id')
                                    ->get();
                
                $cadena_plana = DB::table('op_cadena_tmp')
                                        ->select('op_cadena_tmp.*',
                                        'plaza_tit.c_plaza',
                                        'plaza_tit.nombre_plaza',
                                        DB::raw("CONCAT(dbo.view_op_oficinas.nombre_oficina, ' - ', dbo.view_op_oficinas.distrito) as nombre_oficina"), 

                                        'op_regimen.regimen_completo', 
                                        DB::raw("IIF(persona_tit.id IS NOT NULL, CONCAT(persona_tit.ap_paterno, ' ', persona_tit.ap_materno, ' ', persona_tit.nombres), null) as persona_tit"),
                                        
                                        DB::raw("IIF(persona_fun.id IS NOT NULL, CONCAT(persona_fun.ap_paterno, ' ', persona_fun.ap_materno, ' ', persona_fun.nombres), null) as persona_fun"),
                                        DB::raw("motivo = tipo"),
                                        DB::raw("fecha_ini = null"),
                                        DB::raw("fecha_fin = null"),
                                    )
                                    ->leftJoin('persona as persona_tit', 'persona_tit.id', 'op_cadena_tmp.persona_tit_id')
                                    ->leftJoin('persona as persona_fun', 'persona_fun.id', 'op_cadena_tmp.persona_fun_id')
                                    ->join('op_plazas_tit as plaza_tit', 'plaza_tit.id', 'op_cadena_tmp.plaza_id')
                                    ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_cadena_tmp.oficina_id')
                                    ->join('op_regimen', 'op_regimen.id', 'plaza_tit.op_regimen_id')
                                    ->get();

                $cadena_view = $this->nested($cadena_new, null);

                return [
                    'c_plana' => $cadena_plana,
                    'cadena' => $cadena_view,
                    'periodos' => [], 
                    'response' => $response
                ];
            } else{
                return [
                    'response' => $response
                ];
            }
        }
    }


    public function nested($rows = array(), $plazapadre = null)
    {
        $nodo = array();

        if(!empty($rows))
        {
            foreach($rows as $row)
            {
                if($row->persona_tit_plaza_id == $plazapadre)
                {   
                    if ($row->persona_fun_plaza_id != null) {

                        $row->children = array();
                        $row->children = $this->nested($rows, $row->persona_fun_plaza_id);
                    }
                    array_push($nodo, $row);  
                }
            }
        }
        return $nodo;
    }

    public function saveCadena($rows, $anio, $mes, $persona){
        $nodo = array();

        if(!empty($rows))
        {
            foreach($rows as $row)
            {
                if(isset($row->children))
                {   
                    $this->saveCadena($row->children, $anio, $mes, $persona);
                }
                $registra_cadena = DB::table('op_cadena_tmp')
                                    ->insert([
                                        'anio' => $anio,
                                        'mes' => $mes,
                                        'plaza_id' => $row->id,
                                        'oficina_id' => $row->oficina_id,
                                        'persona_tit_plaza_id' => $row->persona_tit_plaza_id,
                                        'persona_fun_plaza_id' => $row->persona_fun_plaza_id,
                                        'persona_tit_id' => $row->persona_tit_id,
                                        'persona_fun_id' => $row->persona_fun_id,
                                        'tipo' =>  $row->motivo,
                                        'user_update' => $persona,
                                    ]);
            }
        }
        //return $nodo;
    }

    public function unlink($plazapadre)
    {
        if($plazapadre != null)
        {
            $plaza = DB::table('op_cadena_tmp')
                        ->where('persona_tit_plaza_id', $plazapadre)
                        ->first();
            
            if($plaza){
                if($plaza->persona_fun_plaza_id != null){
                    $this->unlink($plaza->persona_fun_plaza_id);
                }
                $delelePlaza = DB::table('op_cadena_tmp')
                                ->where('persona_tit_plaza_id', $plazapadre)
                                ->delete();
            }
        }
    }

}



?>