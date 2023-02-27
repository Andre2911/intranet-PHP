<?php

namespace App\Http\Controllers\Personal;

use App\Plaza;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;

class PlazasController extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

        $usuario = User::find(Auth::user()->id);
        $response=null;

        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal.administrador|Personal.licencias') ){

            if($request->input('listar')){
                if($request->input('search') && $request->search != null){
                    $tabla = Plaza::with('oficinaCap', 'oficinaCaf', 'regimen','ocupadoCap', 'ocupadoCaf')
                    //$tabla = Plaza::
                            ->select('op_plazas_tit.*')
                            ->where(function ($query) use ($request) {
                                $query->orWhere('persona_titular.numero_documento', 'like','%'.$request->search.'%');
                                $query->orWhere('persona_fisica.numero_documento', 'like','%'.$request->search.'%');
                                $query->orWhere(DB::raw("CONCAT(persona_titular.ap_paterno,' ',persona_titular.ap_materno, ' ',persona_titular.nombres)"),'like','%'.$request->search.'%');
                                $query->orWhere(DB::raw("CONCAT(persona_fisica.ap_paterno,' ',persona_fisica.ap_materno, ' ',persona_fisica.nombres)"),'like','%'.$request->search.'%');
                                $query->orWhere(DB::raw("CONCAT(op_plazas_tit.nombre_plaza,' ',oficinafisica.nombre_oficina)"),'like','%'.$request->search.'%');
                                $query->orWhere(DB::raw("CONCAT(op_plazas_tit.nombre_plaza,' ',op_plazas_tit.c_plaza)"),'like','%'.$request->search.'%');
                            })
                            
                            ->leftjoin('persona_has_plaza_tit','persona_has_plaza_tit.op_plaza_tit_id', 'op_plazas_tit.id')
                            ->leftjoin('persona as persona_titular', 'persona_titular.id', 'persona_has_plaza_tit.persona_id')

                            ->leftjoin('persona_has_plaza_fun','persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                            ->leftjoin('persona as persona_fisica', 'persona_fisica.id', 'persona_has_plaza_fun.persona_id')
                            ->leftjoin('view_op_oficinas as oficinafisica', 'oficinafisica.id', 'op_plazas_tit.op_oficinaf_id')
                            ->when(isset( $request->vacias ) && $request->vacias === 'true', function ($query) 
                            {
                                return $query->whereNULL( 'persona_fisica.numero_documento');
                            })
                            ->where('op_plazas_tit.activo', 1)
                            ->orderBy('op_plazas_tit.id','asc')
                            ->paginate($request->perpage);

                }else{
                    if(isset($request->vacias) && $request->vacias === 'true'){
                        $tabla = Plaza::with('oficinaCap', 'oficinaCaf','regimen','ocupadoCap', 'ocupadoCaf')
                                ->select('op_plazas_tit.*')
                                ->leftjoin('persona_has_plaza_tit','persona_has_plaza_tit.op_plaza_tit_id', 'op_plazas_tit.id')
                                ->leftjoin('persona as persona_titular', 'persona_titular.id', 'persona_has_plaza_tit.persona_id')

                                ->leftjoin('persona_has_plaza_fun','persona_has_plaza_fun.op_plaza_fun_id', 'op_plazas_tit.id')
                                ->leftjoin('persona as persona_fisica', 'persona_fisica.id', 'persona_has_plaza_fun.persona_id')
                                ->leftjoin('view_op_oficinas as oficinafisica', 'oficinafisica.id', 'op_plazas_tit.op_oficinaf_id')
                                ->whereNULL('persona_fisica.numero_documento')
                                ->where('op_plazas_tit.activo', 1)
                                ->orderBy('op_plazas_tit.id','asc')
                                ->paginate(1000);
                    } else{
                        $tabla = Plaza::with('oficinaCap', 'oficinaCaf','regimen','ocupadoCap', 'ocupadoCaf')
                        ->orderBy('op_plazas_tit.id','asc')
                        ->paginate($request->perpage);
                    }
                   
                }
    
                    
                if ($tabla == NULL){ abort('404'); }
    
                $response['personas'] = $tabla;
                return $response;
            } else if($request->input('consultar')){
                $persona = Persona::where('id', $request->persona)->first();

                $response['persona'] = $persona;
                return $response;
            } else if($request->input('fisicasDisp')){
                $plazas = DB::table('view_op_plazasf_disp')
                        ->get();
                $response['plazas'] = $plazas;
                return $response;
            } 
            else if($request->input('tablas')){
                $oficinas = DB::table('view_op_oficinas')
                        ->select('view_op_oficinas.id', DB::raw("CONCAT(view_op_oficinas.nombre_oficina, ' [',parent.nombre_oficina, '] - ', view_op_oficinas.distrito) as nombre_oficina"))
                        ->join('op_oficinas as parent', 'parent.id', 'view_op_oficinas.parent_id')
                        ->where('view_op_oficinas.have_personal', 1)
                        ->get();
                $cargos = DB::table('op_cargos')->select('id', 'nombre_cargo')->get();
                $regimenes = DB::table('op_regimen')->select('id', 'regimen_completo')->get();

                $response['oficinas'] = $oficinas;
                $response['cargos'] = $cargos;
                $response['regimenes'] = $regimenes;
                return $response;
            } else if($request->input('documentos')){
                $documentos = TipoDocumento::get();

                $response['documentos'] = $documentos;
                return $response;
            }


            return abort('404');
        }
        return abort('403');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $usuario = User::find(Auth::user()->id);
        $response=null;
        $regimen_plaza_fisico = null;


        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal.administrador|Personal.licencias') ){
            $response = null;
            DB::beginTransaction();
            try{
                //VERIFICAMOS LA PLAZA FISICA
                if(isset($request->ocupado_caf) && sizeof($request->ocupado_caf) != 0){
                    $persona_plaza_tit = DB::table('persona_has_plaza_tit')
                                                ->where('persona_id', $request->ocupado_caf[0]['id'])
                                                ->first();
                            
                    if(!isset($request->ocupado_cap) || sizeof($request->ocupado_cap) == 0){
                        if (in_array($request->op_regimenf_id*1, array(3, 7)) && $persona_plaza_tit == null) {
                            $regimen_plaza_fisico = $request->op_regimenf_id;
                        } else{
                            if($persona_plaza_tit && in_array($request->op_regimenf_id*1, array(4, 6)) ){
                                $regimen_plaza_fisico = $request->op_regimenf_id;
                            } else{
                                $response["statusBD"] = false;
                                $response["messageDB"] = "Verifique el Regimen Laboral *728(F), CAS(N), 276(C)";
                            }
                        }
                    } else{
                        //Cuando el trabajador tiene la misma plaza nominal y fisica 728(I), 276(N), CAS ganador

                        if ( $request->ocupado_cap[0]['id'] == $request->ocupado_caf[0]['id']) {
                            if ($request->op_regimenf_id == $request->op_regimen_id) {
                                if (in_array($request->op_regimen_id*1, array(1, 2, 5))) {
                                    $regimen_plaza_fisico = $request->op_regimenf_id;
                                } else{
                                    $response["statusBD"] = false;
                                    $response["messageDB"] = "Verifique el Regimen Laboral *728(I), 276(N), CAS ganador";
                                }
                            } else{
                                $regimen_plaza_fisico = $request->op_regimen_id;
                            }
                        } else{
                            //Cuando el trabajador que ocupa la plaza fisica tiene una plaza nominal distinta 276(C), 728(E)
                            $consultaPlazaTit = DB::table('persona_has_plaza_tit')
                                        ->where('persona_id', $request->ocupado_caf[0]['id'])
                                        ->first();

                                if($consultaPlazaTit){
                                    if (in_array($request->op_regimenf_id*1, array(6, 4))) {
                                        $regimen_plaza_fisico = $request->op_regimenf_id;
                                    } else{
                                        $response["statusBD"] = false;
                                        $response["messageDB"] = "Verifique el Regimen Laboral *728(E), 276(C)";
                                    }
                                } else{
                                    if (in_array($request->op_regimenf_id*1, array(3, 7, 6))) {
                                        $regimen_plaza_fisico = $request->op_regimenf_id;
                                    } else{
                                        $response["statusBD"] = false;
                                        $response["messageDB"] = "Verifique el Regimen Laboral *728(F), CAS(N), 276(C)";
                                    }
                                }
                        }
                    }

                    if($regimen_plaza_fisico == null){
                        return $response;
                    }
                }

                $plaza = Plaza::create([
                                'c_plaza' => $request->c_plaza,
                                'op_cargo_id' => $request->op_cargo_id,
                                'nombre_plaza' => mb_strtoupper($request->nombre_plaza,'utf-8' ),
                                'op_oficina_id' => ($request->op_oficina_id)? $request->op_oficina_id : null,
                                'order_cap' => $request->order_cap,
                                'op_regimen_id' => ($request->op_regimen_id)? $request->op_regimen_id : null,
                                'op_oficinaf_id' => ($request->op_oficinaf_id)? $request->op_oficinaf_id : null,
                                'order_caf' => $request->order_caf,
                                'op_regimenf_id' => $regimen_plaza_fisico,
                                'jefe_area' => (isset($request->jefe_area))? $request->jefe_area : 0,
                                'activo' => ($request->activo)? $request->activo : 0,
                                'observacion' => ($request->observacion)? $request->observacion : null,
                                'user_update' => Auth::user()->id
                        ]);


                if(!isset($request->ocupado_cap) || sizeof($request->ocupado_cap) == 0){
                    
                } else{
                    if(isset($request->ocupado_cap[0]['id'])){

                        $deletenominal = DB::table('persona_has_plaza_tit')
                                            ->where('persona_id', $request->ocupado_cap[0]['id'])
                                            ->delete();

                        $registranominal = DB::table('persona_has_plaza_tit')
                                            ->insert([
                                                    'persona_id' => $request->ocupado_cap[0]['id'],
                                                    'op_plaza_tit_id' => $plaza->id,
                                            ]);
                    } else{
                        DB::rollback();
                        $response["statusBD"] = false;
                        $response["messageDB"] = "Error al actualizar en la Base de Datos.";
                    }
                }

                if(sizeof($request->ocupado_caf) == 0){
                    
                } else{
                    if(isset($request->ocupado_caf[0]['id'])){

                        $deletefisica = DB::table('persona_has_plaza_fun')
                                            ->where('persona_id', $request->ocupado_caf[0]['id'])
                                            ->delete();

                        $registrafisica= DB::table('persona_has_plaza_fun')
                                            ->insert([
                                                    'persona_id' => $request->ocupado_caf[0]['id'],
                                                    'op_plaza_fun_id' => $plaza->id,
                                            ]);
                    } else{
                        DB::rollback();
                        $response["statusBD"] = false;
                        $response["messageDB"] = "Error al actualizar en la Base de Datos.";
                    }
                }
                
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos de la Plaza actualizados con exito.";
            
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
            
        }
        return abort('403');
        
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        $usuario = User::find(Auth::user()->id);
        $response=null;
        $regimen_plaza_fisico = null;


        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal.administrador|Personal.licencias') ){
            $response = null;
            DB::beginTransaction();
            try{
                //VERIFICAMOS LA PLAZA FISICA
                if(sizeof($request->ocupado_caf) != 0){
                    $persona_plaza_tit = DB::table('persona_has_plaza_tit')
                                                ->where('persona_id', $request->ocupado_caf[0]['id'])
                                                ->first();
                            
                    if(sizeof($request->ocupado_cap) == 0){
                        if (in_array($request->op_regimenf_id*1, array(3, 7)) && $persona_plaza_tit == null) {
                            $regimen_plaza_fisico = $request->op_regimenf_id;
                        } else{
                            if($persona_plaza_tit && in_array($request->op_regimenf_id*1, array(4, 6)) ){
                                $regimen_plaza_fisico = $request->op_regimenf_id;
                            } else{
                                $response["statusBD"] = false;
                                $response["messageDB"] = "Verifique el Regimen Laboral *728(F), CAS(N), 276(C)";
                            }
                        }
                    } else{
                        //Cuando el trabajador tiene la misma plaza nominal y fisica 728(I), 276(N), CAS ganador

                        if ( $request->ocupado_cap[0]['id'] == $request->ocupado_caf[0]['id']) {
                            if ($request->op_regimenf_id == $request->op_regimen_id) {
                                if (in_array($request->op_regimen_id*1, array(1, 2, 5))) {
                                    $regimen_plaza_fisico = $request->op_regimenf_id;
                                } else{
                                    $response["statusBD"] = false;
                                    $response["messageDB"] = "Verifique el Regimen Laboral *728(I), 276(N), CAS ganador";
                                }
                            } else{
                                $regimen_plaza_fisico = $request->op_regimen_id;
                            }
                        } else{
                            //Cuando el trabajador que ocupa la plaza fisica tiene una plaza nominal distinta 276(C), 728(E)
                            $consultaPlazaTit = DB::table('persona_has_plaza_tit')
                                        ->where('persona_id', $request->ocupado_caf[0]['id'])
                                        ->first();

                                if($consultaPlazaTit){
                                    if (in_array($request->op_regimenf_id*1, array(6, 4))) {
                                        $regimen_plaza_fisico = $request->op_regimenf_id;
                                    } else{
                                        $response["statusBD"] = false;
                                        $response["messageDB"] = "Verifique el Regimen Laboral *728(E), 276(C)";
                                    }
                                } else{
                                    if (in_array($request->op_regimenf_id*1, array(3, 7, 6))) {
                                        $regimen_plaza_fisico = $request->op_regimenf_id;
                                    } else{
                                        $response["statusBD"] = false;
                                        $response["messageDB"] = "Verifique el Regimen Laboral *728(F), CAS(N), 276(C)";
                                    }
                                }
                        }
                    }

                    if($regimen_plaza_fisico == null){
                        return $response;
                    }
                }


                


                $query = DB::table('op_plazas_tit')
                        ->where('id', $id)
                        ->update([
                                'c_plaza' => $request->c_plaza,
                                'op_cargo_id' => $request->op_cargo_id,
                                'nombre_plaza' => mb_strtoupper($request->nombre_plaza,'utf-8' ),
                                'op_oficina_id' => ($request->op_oficina_id)? $request->op_oficina_id : null,
                                'order_cap' => $request->order_cap,
                                'op_regimen_id' => ($request->op_regimen_id)? $request->op_regimen_id : null,
                                'op_oficinaf_id' => ($request->op_oficinaf_id)? $request->op_oficinaf_id : null,
                                'order_caf' => $request->order_caf,
                                'op_regimenf_id' => $regimen_plaza_fisico,
                                'jefe_area' => (isset($request->jefe_area))? $request->jefe_area : null,
                                'activo' => ($request->activo)? $request->activo : 0,
                                'observacion' => ($request->observacion)? $request->observacion : null,
                                'user_update' => Auth::user()->id
                        ]);


                if(sizeof($request->ocupado_cap) == 0){
                    $deletenominal = DB::table('persona_has_plaza_tit')
                                        ->where('op_plaza_tit_id', $id)
                                        ->delete();
                } else{
                    if(isset($request->ocupado_cap[0]['id'])){

                        $deletenominal = DB::table('persona_has_plaza_tit')
                                            ->where('op_plaza_tit_id', $id)
                                            ->orWhere('persona_id', $request->ocupado_cap[0]['id'])
                                            ->delete();

                        $registranominal = DB::table('persona_has_plaza_tit')
                                            ->insert([
                                                    'persona_id' => $request->ocupado_cap[0]['id'],
                                                    'op_plaza_tit_id' => $id,
                                            ]);
                    } else{
                        DB::rollback();
                        $response["statusBD"] = false;
                        $response["messageDB"] = "Error al actualizar en la Base de Datos.";
                    }
                }

                if(sizeof($request->ocupado_caf) == 0){
                    
                    $deletefisica = DB::table('persona_has_plaza_fun')
                                        ->where('op_plaza_fun_id', $id)
                                        ->delete();
                } else{
                    if(isset($request->ocupado_caf[0]['id'])){

                        $deletefisica = DB::table('persona_has_plaza_fun')
                                            ->where('op_plaza_fun_id', $id)
                                            ->orWhere('persona_id', $request->ocupado_caf[0]['id'])
                                            ->delete();

                        $registrafisica= DB::table('persona_has_plaza_fun')
                                            ->insert([
                                                    'persona_id' => $request->ocupado_caf[0]['id'],
                                                    'op_plaza_fun_id' => $id,
                                            ]);
                    } else{
                        DB::rollback();
                        $response["statusBD"] = false;
                        $response["messageDB"] = "Error al actualizar en la Base de Datos.";
                    }
                }
                
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos de la Plaza actualizados con exito.";
            
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.";
            }
            return $response;
            
        }
        return abort('403');
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){

        if ( $this->cerrojo_rol() == 1 ){

            if (auth()->user()->hasPermissionTo($this->permiso_delete)) {

                $data = Persona::findOrFail($id);

                if ($data->delete()) {
                    return redirect($this->url_modulo)
                        ->with('success', 'Se elimino correctamente el registro.');
                };

                return redirect($this->url_modulo)
                    ->with('error', 'No se pudo eliminar correctamente el registro.');

            }
            return abort('403');

        }
        return abort('403');

    }

    public function numero_documento($num_doc){

        if( mb_strlen($num_doc) > 7 && is_numeric($num_doc) ){

            $data = Persona::select('id',
                'numero_documento',
                'ape_paterno',
                'ape_materno',
                'nombre')
                ->where('numero_documento','=',$num_doc)
                ->get();

            if ($data->count() > 0){
                echo '<script>
                        document.getElementById("f_persona").value = "'.$data[0]->nombre.' '.$data[0]->ape_paterno.' '.$data[0]->ape_materno.'";
                      </script>';
            }elseif($data->count() == 0){
                echo '<a href="'.url('mantenimiento/persona/create').'" target="_blank"><button type="button" class="btn btn-success" style="margin: 2px;">Registrar nueva persona</button></a>
                        <script>
                            document.getElementById("f_persona").value = "";
                        </script>';
            }
        }else{
            echo '<p class="text-red">Número de documento incorrecto.</p>';
        }
    }

    public function data_user(){
        $tmp_user = User::select('id',
            'persona_id',
            'username',
            'estado')
            ->where('id','=',Auth::id())
            ->with('persona:id,ape_paterno,ape_materno,nombre')
            ->first();
        if ($tmp_user == NULL){ abort('404'); }
        return $tmp_user;
    }


}
