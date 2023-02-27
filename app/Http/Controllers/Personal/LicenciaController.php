<?php

namespace App\Http\Controllers\Personal;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Psr\Log\NullLogger;
use Illuminate\Support\Str;

use App\User;
use App\Persona;

class LicenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
    	if($request->input('tipos')){
            $licencias = DB::table('op_lic_licencias')
                        ->select('op_lic_licencias.*', 
                                DB::raw("CONCAT(tipo ,' ', descripcion, ' : ', solicitante) AS fulldescripcion")
                                )
                        ->get();
            foreach ($licencias as $licencia) {
                $licencia->c_goce = $licencia->c_goce*1;
                $licencia->s_goce = $licencia->s_goce*1;
                $licencia->a_vac = $licencia->a_vac*1;

            }
            return ['licencias' => $licencias];
        } else if($request->input('formatos')){
            $formatos = DB::table('op_lic_formatos')
                        ->select('op_lic_formatos.*',
                                'op_lic_licencias.tipo',
                                'op_lic_licencias.solicitante',
                        )
                        ->join('op_lic_licencias', 'op_lic_licencias.id', 'op_lic_formatos.licencia_tipo')
                        ->get();
            return ['formatos' => $formatos];
        } else if($request->input('init')){
            $informes = DB::table('op_lic_informes')
                        ->select('op_lic_informes.*',
                                DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as nombrecompleto"),
                                DB::raw("CONCAT(op_lic_informes.anio, '-',op_lic_informes.correlativo) as n_informe"),
                                DB::raw("CONCAT(std_tipodocumento.den_doc, '-',op_lic_informes.num_doc) as doc_completo"),
                                'op_lic_licencias.tipo',
                                'op_lic_licencias.solicitante',
                                'op_lic_licencias.descripcion'
                        )
                        ->join('op_lic_licencias', 'op_lic_licencias.id', 'op_lic_informes.licencia_tipo_id')
                        ->join('persona', 'persona.numero_documento', 'op_lic_informes.personal_id')
                        ->join('std_tipodocumento', 'std_tipodocumento.cod_doc', 'op_lic_informes.cod_doc')
                        ->orderBy('op_lic_informes.id')
                        ->paginate(isset($request->perpage)? $request->perpage:10);
                        

            $personal = DB::table('persona')
                     ->select('persona.numero_documento', DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as nombrecompleto"))
                     ->join('op_lic_informes', 'op_lic_informes.personal_id', 'persona.numero_documento')
                     ->where('persona.numero_documento','!=', '00000000')
                     ->orderBy('nombrecompleto')
                     ->distinct()->get();

            $personal_activo = DB::table('persona')
                    ->select('persona.numero_documento', DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as nombrecompleto" ))
                    ->leftjoin('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                    ->leftjoin('persona_has_plaza_tit', 'persona_has_plaza_tit.persona_id', 'persona.id')
                    ->orderBy('nombrecompleto')
                    ->orWhereNotNull('persona_has_plaza_fun.op_plaza_fun_id')
                    ->orWhereNotNull('persona_has_plaza_tit.op_plaza_tit_id')
                    ->distinct()->get();
           
            $licencias = DB::table('op_lic_licencias')
                        ->select('op_lic_licencias.*', 
                                DB::raw("CONCAT(tipo ,' ', descripcion, ' : ', solicitante) AS fulldescripcion")
                                )
                        ->get();
            
            foreach ($licencias as $licencia) {
                $licencia->c_goce = $licencia->c_goce*1;
                $licencia->s_goce = $licencia->s_goce*1;
                $licencia->a_vac = $licencia->a_vac*1;

            }

            $documentos = DB::table('std_tipodocumento')
                        ->get();

            $formatos = DB::table('op_lic_formatos')
                        ->get();

         
            return ['informes' => $informes, 'personal' => $personal, 'licencias' => $licencias, 'documentos' => $documentos, 'formatos' => $formatos, 'personal_activo'=>$personal_activo];
        } else if($request->input('informes')){
            $personal_id = null;
            $licencia_id = null;
            if(isset( $request->personal_id )){
                $personal_id = $request->personal_id ;
            }
            if(isset( $request->licencia_id )){
                $licencia_id = $request->licencia_id ;
            }

            $informes = DB::table('op_lic_informes')
                        ->select('op_lic_informes.*',
                                DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as nombrecompleto"),
                                DB::raw("CONCAT(op_lic_informes.anio, '-',op_lic_informes.correlativo) as n_informe"),
                                DB::raw("CONCAT(std_tipodocumento.den_doc, '-',op_lic_informes.num_doc) as doc_completo"),
                                'op_lic_licencias.tipo',
                                'op_lic_licencias.solicitante',
                                'op_lic_licencias.descripcion'
                        )
                        ->when(isset( $request->personal_id ), function ($query) use ($personal_id)
                        {
                            return $query->where( 'op_lic_informes.personal_id', $personal_id);
                        })
                        ->when(isset( $request->licencia_id ), function ($query) use ($licencia_id)
                        {
                            return $query->where( 'op_lic_informes.licencia_tipo_id', $licencia_id);
                        })
                        ->join('op_lic_licencias', 'op_lic_licencias.id', 'op_lic_informes.licencia_tipo_id')
                        ->join('persona', 'persona.numero_documento', 'op_lic_informes.personal_id')
                        ->join('std_tipodocumento', 'std_tipodocumento.cod_doc', 'op_lic_informes.cod_doc')
                        ->orderBy('op_lic_informes.id')
                        ->paginate(isset($request->perpage)? $request->perpage:10);
         
            return ['informes' => $informes];
        } else if($request->input('getDocumento')) {
            
            $plantilla = $request->input('formato');

            $informe = DB::table('op_lic_informes')
                        ->select('op_lic_informes.*',
                                DB::raw("CONCAT(ap_paterno,' ',ap_materno, ' ', nombres) as nombrecompleto"),
                                DB::raw("CONCAT(op_lic_informes.anio, '-',op_lic_informes.correlativo) as n_informe"),
                                DB::raw("CONCAT(std_tipodocumento.den_doc, '-',op_lic_informes.num_doc) as doc_completo"),
                                'op_lic_licencias.tipo',
                                'op_lic_licencias.solicitante',
                                'op_lic_licencias.descripcion',
                                'persona.escalafon',
                                'regimentitular.regimen_completo',
                                'std_tipodocumento.den_doc'
                        )
                        ->join('op_lic_licencias', 'op_lic_licencias.id', 'op_lic_informes.licencia_tipo_id')
                        ->join('persona', 'persona.numero_documento', 'op_lic_informes.personal_id')
                        ->join('std_tipodocumento', 'std_tipodocumento.cod_doc', 'op_lic_informes.cod_doc')
                        ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                        ->join('persona_has_plaza_tit', 'persona_has_plaza_tit.persona_id', 'persona.id')

                        ->join('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                        ->join('op_plazas_tit as plazatitular', 'plazatitular.id', 'persona_has_plaza_tit.op_plaza_tit_id')

                        ->join('op_regimen as regimenfisico','regimenfisico.id', 'plazafisica.op_regimen_id')
                        ->join('op_regimen as regimentitular','regimentitular.id', 'plazatitular.op_regimen_id')

                        ->where('op_lic_informes.id', $request->informe)
                        ->first();

            

            if ($informe != null) {
                $historial = DB::table('op_lic_informes')
                                ->select('op_lic_informes.n_dias')
                                ->join('op_lic_licencias', 'op_lic_licencias.id', 'op_lic_informes.licencia_tipo_id')
                                ->where('op_lic_informes.personal_id', $informe->personal_id)
                                ->where('op_lic_informes.anio_vac', $informe->anio_vac)
                                ->where('op_lic_licencias.a_vac', 1)
                                ->where('op_lic_informes.fecha_fin', '<=', $informe->fecha_fin)
                                ->get();

                $n_dias_adelanto = 0;

                if ($historial != null && sizeof($historial) > 0) {
                    foreach ($historial as $periodo) {
                        $n_dias_adelanto = $n_dias_adelanto + $periodo->n_dias;
                    }
                }

                $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];

                $phpWord = new TemplateProcessor(storage_path('licencias/'.$plantilla));

                $servidor           =   $informe->nombrecompleto;
                $doc_origen         =   $informe->doc_completo;
                $escalafon          =   $informe->escalafon;
                $regimen_servidor   =   $informe->regimen_completo;
                $fecha_doc_orig     =   $this->parseDate($informe->doc_fecha);
                $doc_tipo           =   $informe->doc_completo;
                $fecha_ini          =   $this->parseDate($informe->fecha_ini);
                $fecha_fin          =   $this->parseDate($informe->fecha_fin);
                $n_dias             =   $informe->n_dias;
                $periodo_vac        =   $this->getPeriodo($informe->fecha_ini, $informe->fecha_fin);
                $anio_vac           =   $informe->anio_vac;
                $fecha_informe      =   substr($informe->fecha_inf, 8,2)." de ".$meses[substr($informe->fecha_inf, 5,2)*1-1]." de ".substr($informe->fecha_inf, 0,4);
                

                $phpWord->setValue('servidor',$servidor);
                $phpWord->setValue('doc_origen',$doc_origen);
                $phpWord->setValue('escalafon',$escalafon);
                $phpWord->setValue('regimen_servidor',$regimen_servidor);
                $phpWord->setValue('fecha_doc_orig',$fecha_doc_orig);
                $phpWord->setValue('doc_tipo',$doc_tipo);
                $phpWord->setValue('fecha_ini',$fecha_ini);
                $phpWord->setValue('fecha_fin',$fecha_fin);
                $phpWord->setValue('n_dias',$n_dias);
                $phpWord->setValue('periodo_vac',$periodo_vac);
                $phpWord->setValue('año_vac',$anio_vac);
                $phpWord->setValue('año_sig',$anio_vac);
                $phpWord->setValue('n_dias_adelanto',$n_dias_adelanto);
                $phpWord->setValue('fecha_informe',$fecha_informe);

                $phpWord->saveAs(storage_path('inf_licencias/'.Auth::user()->id.'_'.$informe->n_informe.'.docx'));

                $pathtoFile = storage_path('inf_licencias/'.Auth::user()->id.'_'.$informe->n_informe.'.docx');
                return response()->download($pathtoFile);
                
            }
        } else if($request->input('historial')){
            if ($request->personal_id != null && $request->anio_vac != null) {
                $informes = DB::table('op_lic_informes')
                                ->select('op_lic_informes.*',
                                    'op_lic_licencias.tipo',
                                    'op_lic_licencias.solicitante',
                                    'op_lic_licencias.descripcion'
                                )
                                ->join('op_lic_licencias', 'op_lic_licencias.id', 'op_lic_informes.licencia_tipo_id')
                                ->where('op_lic_informes.personal_id', $request->personal_id)
                                ->where('op_lic_informes.anio_vac', $request->anio_vac)
                                ->where('op_lic_informes.sentido', 'PROCEDENTE')
                                ->where('op_lic_informes.confirmada','!=', 0)
                                ->get();

                $vacaciones = DB::table('op_lic_vacaciones')
                                ->select('op_lic_vacaciones.*',
                                    'op_lic_vacaciones.f_inicio as fecha_ini',
                                    'op_lic_vacaciones.f_fin as fecha_fin',
                                    DB::raw("DATEDIFF(day, op_lic_vacaciones.f_inicio, op_lic_vacaciones.f_fin)+1 AS n_dias"))
                                ->where('op_lic_vacaciones.persona_id', $request->personal_id)
                                ->where('op_lic_vacaciones.anio', $request->anio_vac)
                                ->get();

                return ['informes' => $informes, 'vacaciones'=> $vacaciones];
            } 
            
        } else if($request->input('list_personal')){

            $personal_vigente = DB::table('persona')
                        ->select(
                            'persona.id',
                            'persona.numero_documento',
                            'op_regimen.regimen_base',
                            'op_plazas_tit.id as plaza_tit_id',
                            'view_op_oficinas.id as oficinaf_id',
                            DB::raw("CONCAT(view_op_oficinas.nombre_oficina, '[', view_op_oficinas.distrito, ']') dependencia"),
                            DB::raw("CONCAT(ap_paterno,' ',ap_materno, ' ', nombres) as nombrecompleto")
                        )
                        ->leftjoin('persona_has_plaza_tit', 'persona_has_plaza_tit.persona_id', 'persona.id')
                        ->leftJoin('op_plazas_tit','op_plazas_tit.id', 'persona_has_plaza_tit.op_plaza_tit_id')
                        ->leftJoin('view_op_oficinas', 'view_op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                        ->leftJoin('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                        ->where('numero_documento', '!=','00000000')
                        ->get();

            return ['personal' => $personal_vigente];
        } else if($request->input('list_vacaciones')){

            $personal_vigente = DB::table('op_lic_vacaciones')
                        ->select(
                            'op_lic_vacaciones.id',
                            'op_lic_vacaciones.anio',
                            'op_lic_vacaciones.f_inicio',
                            'op_lic_vacaciones.f_fin',
                            'persona.numero_documento',
                            'op_plazas_tit.nombre_plaza',
                            DB::raw("DATEDIFF(day, op_lic_vacaciones.f_inicio, op_lic_vacaciones.f_fin)+1 AS DateDiff"),
                            'op_regimen.regimen_base',
                            DB::raw("CONCAT(view_op_oficinas.nombre_oficina, '[', view_op_oficinas.distrito, ']') dependencia"),
                            DB::raw("CONCAT(ap_paterno,' ',ap_materno, ' ', nombres) as nombrecompleto")
                        )
                        ->join('persona', 'persona.numero_documento', 'op_lic_vacaciones.persona_id')
                        ->leftJoin('op_plazas_tit','op_plazas_tit.id', 'op_lic_vacaciones.op_plaza_tit_id')
                        ->leftJoin('view_op_oficinas', 'view_op_oficinas.id', 'op_lic_vacaciones.op_oficinaf_id')
                        ->leftJoin('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                        ->where('numero_documento', '!=','00000000')
                        ->where('op_lic_vacaciones.anio', $request->anio)
                        ->get();

            return ['personal' => $personal_vigente];


        }
    }
    public function store(Request $request){
    	if ($request->input('savetipos')) {
            $response = null;
            DB::beginTransaction();
            try{
                $query = DB::table('op_lic_licencias')
                        ->insert([
                                'tipo' => $request->tipo,
                                'solicitante' => $request->solicitante,
                                'descripcion' => $request->descripcion,
                                'c_goce' => ($request->c_goce)? $request->c_goce : false,
                                's_goce' => ($request->s_goce)? $request->s_goce : false,
                                'a_vac' => ($request->a_vac)? $request->a_vac : false,
                                'user_update' => Auth::user()->id
                        ]);
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos del Tipo de Licencia registrados con exito.";
            
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
        } elseif ($request->input('updateformatos')) {
            $response = null;
            DB::beginTransaction();
            try{
                $file = $request->file;
                if ($file != null && $file != 'null') {
                    $fileName = Str::random(4).str_replace(' ', '_', $file->getClientOriginalName());
                    $request->file->move(storage_path('/licencias'), $fileName);    
                    $query = DB::table('op_lic_formatos')
                        ->where('id', $request->formato_id)
                        ->update([
                                'licencia_tipo' => $request->licencia_tipo,
                                'descripcion' => $request->descripcion,
                                'sentido' => $request->sentido,
                                'archivo' => $fileName,
                                'user_update' => Auth::user()->id
                        ]);    
                } else{
                    
                    $query = DB::table('op_lic_formatos')
                        ->where('id', $request->formato_id)
                        ->update([
                                'licencia_tipo' => $request->licencia_tipo,
                                'descripcion' => $request->descripcion,
                                'sentido' => $request->sentido,
                                'user_update' => Auth::user()->id
                        ]);    
                }
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos del Tipo de Licencia actualizados con exito.";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        } elseif ($request->input('saveformatos')) {
            $response = null;
            DB::beginTransaction();
            try{
                $file = $request->file;
                if ($file != null && $file != 'null') {
                    $fileName = Str::random(4).str_replace(' ', '_', $file->getClientOriginalName());
                    $request->file->move(storage_path('/licencias'), $fileName);    
                    $query = DB::table('op_lic_formatos')                        
                        ->insert([
                                'licencia_tipo' => $request->licencia_tipo,
                                'descripcion' => $request->descripcion,
                                'sentido' => $request->sentido,
                                'archivo' => $fileName,
                                'user_update' => Auth::user()->id
                        ]);    
                } else{
                    
                    $query = DB::table('op_lic_formatos')
                        ->insert([
                                'licencia_tipo' => $request->licencia_tipo,
                                'descripcion' => $request->descripcion,
                                'sentido' => $request->sentido,
                                'user_update' => Auth::user()->id
                        ]);    
                }
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos del Tipo de Licencia actualizados con exito.";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        } elseif ($request->input('saveinforme')) {
            $response = null;
            DB::beginTransaction();
            try{
                $fecha_registro = time();
                $num_reg = 1;

                $consulta_lastregistro = DB::table('op_lic_informes')
                                    ->select('correlativo')
                                    ->orderBy('correlativo', 'desc')
                                    ->first();
                if ($consulta_lastregistro != null) $num_reg = $consulta_lastregistro->correlativo*1 + 1;

                $query = DB::table('op_lic_informes')                        
                        ->insert([
                                'anio' =>  date('Y', $fecha_registro),
                                'correlativo' => $num_reg,
                                'licencia_tipo_id' => $request->licencia_tipo_id,
                                'personal_id' => $request->personal_id,
                                'cod_doc' => $request->cod_doc,
                                'num_doc' => $request->num_doc,
                                'doc_fecha' => $request->doc_fecha,
                                'fecha_ini' => $request->fecha_ini,
                                'fecha_fin' => $request->fecha_fin,
                                'fecha_inf' => $request->fecha_inf,
                                'n_dias' => $request->n_dias,
                                'anio_vac' => isset($request->anio_vac)? $request->anio_vac: null,
                                'c_goce' => isset($request->c_goce)? $request->c_goce: null,
                                'a_vac' => isset($request->anio_vac)? $request->anio_vac: null,
                                'formato_id' => $request->formato_id,
                                'sentido' => $request->sentido,
                                'archivo' => $request->archivo,
                                'user_update' => Auth::user()->id
                        ]);    

                
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Informe de Licencia registrado con exito.";
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        }
    }
    public function update(Request $request, $id){
    	if ($request->input('tipos')) {
            $response = null;
            DB::beginTransaction();
            try{
                $query = DB::table('op_lic_licencias')
                        ->where('id', $id)
                        ->update([
                                'tipo' => $request->tipo,
                                'solicitante' => $request->solicitante,
                                'descripcion' => $request->descripcion,
                                'c_goce' => ($request->c_goce)? $request->c_goce : false,
                                's_goce' => ($request->s_goce)? $request->s_goce : false,
                                'a_vac' => ($request->a_vac)? $request->a_vac : false,
                                'user_update' => Auth::user()->id
                        ]);
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos del Tipo de Licencia actualizados con exito.";
            
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
        } else if($request->input('informe')){
            if($request->fecha_resced || $request->confirmada != -1){
                DB::beginTransaction();
                try{
                    $query = DB::table('op_lic_informes')
                            ->where('id', $id)                        
                            ->update([
                                    'licencia_tipo_id' => $request->licencia_tipo_id,
                                    'personal_id' => $request->personal_id,
                                    'cod_doc' => $request->cod_doc,
                                    'num_doc' => $request->num_doc,
                                    'doc_fecha' => $request->doc_fecha,
                                    'fecha_ini' => $request->fecha_ini,
                                    'fecha_fin' => $request->fecha_fin,
                                    'fecha_inf' => $request->fecha_inf,
                                    
                                    'n_dias' => $request->n_dias,
                                    'anio_vac' => isset($request->anio_vac)? $request->anio_vac: null,
                                    'c_goce' => isset($request->c_goce)? $request->c_goce: null,
                                    'a_vac' => isset($request->anio_vac)? $request->anio_vac: null,
                                    'formato_id' => $request->formato_id,
                                    'sentido' => $request->sentido,
                                    'archivo' => $request->archivo,

                                    'fecha_resced' => $request->fecha_resced,
                                    'n_resolucionced' => $request->n_resolucionced,
                                    'confirmada' => $request->confirmada,

                                    'user_update' => Auth::user()->id
                            ]);    
    
                    
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Informe de Licencia actualizado con exito.";
                } catch (\Exception $e) {
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
                }
                return $response;
            }
            
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->input('tipos')) {
            $response = null;
            DB::beginTransaction();
            try{
                    $query = DB::table('op_lic_licencias')
                            ->where('id', $id)
                            ->delete();
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Actualizado correctamente.";

                }catch(\Exception $e){
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Error al eliminar de la Base de Datos.";
                }

                return $response;
           
        }
        if ($request->input('formatos')) {
            $response = null;
            DB::beginTransaction();
            try{
                $formato_used = DB::table('op_lic_informes')
                                ->select('formato_id')
                                ->where('formato_id', $id)
                                ->first();

                if(!$formato_used){
                    $query = DB::table('op_lic_formatos')
                        ->where('id', $id)
                        ->delete();
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Actualizado correctamente.";
                } else{
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "El formato esta siendo usado en algun informe, no se puede eliminar";
                }

                
            }catch(\Exception $e){
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al eliminar de la Base de Datos.";
            }

            return $response;
           
        }
    }

    public function getPeriodo ($fecha1, $fecha2) {

        $fecha_registro = time();
        $fecha1 = substr($fecha1, 0, 10);
        $fecha2 = substr($fecha2, 0, 10);
        $numeroDia = date('d', strtotime($fecha1));
        $numeroDia2 = date('d', strtotime($fecha2));

        $dia = date('l', strtotime($fecha1));
        $mes = date('F', strtotime($fecha1));
        $anio = date('Y', strtotime($fecha1));

        $dia2 = date('l', strtotime($fecha2));
        $mes2 = date('F', strtotime($fecha2));
        $anio2 = date('Y', strtotime($fecha2));

        $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
        $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

        //$nombredia = str_replace($dias_EN, $dias_ES, $dia);
        $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

        $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
        $nombreMes2 = str_replace($meses_EN, $meses_ES, $mes2);

        $periodo = '';

        if ($anio == $anio2 && $mes == $mes2 && $numeroDia == $numeroDia2) {
            $periodo = $numeroDia." de ".$nombreMes." de ".$anio;    
        } else if($anio == $anio2 && $mes == $mes2){
            $periodo = $numeroDia." al ".$numeroDia2." de ".$nombreMes."  ";
        } else if ($anio == $anio2) {
            $periodo = $numeroDia." de ".$nombreMes." al ".$numeroDia2." de ".$nombreMes2;   
        } else{
            $periodo = $numeroDia." de ".$nombreMes." de ".$anio." al ".$numeroDia2." de ".$nombreMes2." de ".$anio2;
        }

        if ($anio == $anio2 && $anio2 == date('Y', $fecha_registro)) {
            $periodo = $periodo." del presente año ";    
        }

        return $periodo;
    }

    public function parseDate($fecha){
        $fecha = substr($fecha, 0, 10);
        $dia = date('d', strtotime($fecha));
        $mes = date('m', strtotime($fecha));
        $anio = date('Y', strtotime($fecha));

        return $dia."/".$mes."/".$anio;
    }

  //  public function vacaciones(Request $request, $id){
    public function vacaciones(Request $request){
        $usuario = User::find(Auth::user()->id);
        $persona = Persona::find(Auth::user()->persona_id);
        $response = null;
        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal.licencias') ){
            if ($request->input('documento_r')) {
                $response = null;
                DB::beginTransaction();
                try{

                    foreach ($request->personal as $registro) {

                        $query = DB::table('op_lic_vacaciones')
                            ->insert([
                                    'persona_id' => $registro['dni'],
                                    'anio' => $request->anio_vac,
                                    'f_inicio' => $registro['f_inicio'],
                                    'f_fin' => $registro['f_final'],
                                    'documento' => $request->documento_r,
                                    'op_plaza_tit_id' => $registro['plaza_id'],
                                    'op_oficinaf_id' => $registro['oficina_id'],
                                    'user_update' => $persona->numero_documento
                            ]);
                    }
                    
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Datos de las vacaciones registrados con exito.";
                
                } catch (\Exception $e) {
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
                }
                return $response;
            } else if($request->id){
                $response = null;
                DB::beginTransaction();
                try{

                    $query = DB::table('op_lic_vacaciones')
                                    ->where('id', $id)
                                    ->delete();
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Datos de las vacaciones borrados con exito.";
                
                } catch (\Exception $e) {
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
                }
                return $response;
            }
        } else{
            return abort(403);
        }
    }
    
    public function delVacaciones(Request $request, $id){
        $usuario = User::find(Auth::user()->id);
        $persona = Persona::find(Auth::user()->persona_id);
        $response = null;
        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal.licencias') ){
            if($request->id){
                $response = null;
                DB::beginTransaction();
                try{

                    $query = DB::table('op_lic_vacaciones')
                                    ->where('id', $id)
                                    ->delete();
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Datos de las vacaciones borrados con exito.";
                
                } catch (\Exception $e) {
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
                }
                return $response;
            }
        }
        else{
            return abort(403);
        }
    }

}