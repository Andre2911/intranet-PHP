<?php

namespace App\Http\Controllers\Personal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Auth;
use App\User;
use App\Persona;
//use QrCode;

class PersonalController extends Controller
{

    public function index(Request $request){

        $usuario = User::find(Auth::user()->id);
        $response=null;
        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal|Personal.licencias') ){

            if($request->input('listar')){
                if($request->input('search') && $request->search != null){
                    $tabla = Persona::with('tipo_documento')
                            ->select('persona.*', 'plazafisica.nombre_plaza as nombre_plazafisica', 'plazatitular.nombre_plaza as nombre_plazatitular', 
                                DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno, ' ', persona.nombres) as nombrecompleto"), 
                                DB::raw("CONCAT(oficinafisica.nombre_oficina, ' [', oficinafisica.distrito, ']') as nombre_oficina"))
                            ->where(function ($query) use ($request) {
                                $query->where(DB::raw("CONCAT(persona.numero_documento, ' ',persona.nombres,' ',persona.ap_paterno)"),'like','%'.$request->search.'%');
                                $query->orWhere(DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno)"),'like','%'.$request->search.'%');
                                $query->orWhere(DB::raw("CONCAT(plazafisica.nombre_plaza, ' ', oficinafisica.nombre_oficina)"),'like','%'.$request->search.'%');
                            })
                            ->leftjoin('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')

                            ->leftjoin('persona_has_plaza_tit', 'persona_has_plaza_tit.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazatitular', 'plazatitular.id', 'persona_has_plaza_tit.op_plaza_tit_id')
                            ->leftjoin('view_op_oficinas as oficinafisica', 'oficinafisica.id', 'plazafisica.op_oficinaf_id')
                            ->orderBy('persona.ap_paterno','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }else{
                    $tabla = Persona::with('tipo_documento')
                            ->select('persona.*', 'plazafisica.nombre_plaza as nombre_plazafisica', 'plazatitular.nombre_plaza as nombre_plazatitular',
                                DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno, ' ', persona.nombres) as nombrecompleto"), 
                                DB::raw("CONCAT(oficinafisica.nombre_oficina, ' [', oficinafisica.distrito, ']') as nombre_oficina"))
                            
                            ->leftjoin('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')

                            ->leftjoin('persona_has_plaza_tit', 'persona_has_plaza_tit.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazatitular', 'plazatitular.id', 'persona_has_plaza_tit.op_plaza_tit_id')
                            ->leftjoin('view_op_oficinas as oficinafisica', 'oficinafisica.id', 'plazafisica.op_oficinaf_id')
                            ->orderBy('persona.ap_paterno','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }
    
                    
                if ($tabla == NULL){ abort('404'); }
    
                $response['personas'] = $tabla;
                return $response;
            }
            else if($request->input('listarAll')){
                $tabla = Persona::select('numero_documento',
                                DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno, ' ', persona.nombres) as nombrecompleto"), 
                                )
                                ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                ->get();

                $response['personal'] = $tabla;
                return $response;
            } else if($request->input('consultar')){
                $persona = Persona::where('id', $request->persona)->first();

                $response['persona'] = $persona;
                return $response;
            }  else if($request->input('consultarOrg')){
                $persona = Persona::where('persona.id', $request->persona)
                                    ->select('persona.*',
                                                'op_plazas_tit.nombre_plaza as nombre_plaza_t',
                                                'op_regimen.regimen_completo as regimen',
                                                'op_oficinas.nombre_oficina as nombre_oficina_t',
                                                'op_plazas_fun.nombre_plaza as nombre_plaza_f',
                                                'op_regimenf.regimen_completo as regimenf',
                                                'op_oficinasf.nombre_oficina as nombre_oficina_f'
                                    )
                                    ->leftjoin('persona_has_plaza_tit', 'persona_has_plaza_tit.persona_id', 'persona.id')
                                    ->leftjoin('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_tit.op_plaza_tit_id')
                                    ->leftjoin('op_oficinas', 'op_oficinas.id', 'op_plazas_tit.op_oficina_id')
                                    ->leftjoin('op_regimen', 'op_regimen.id', 'op_plazas_tit.op_regimen_id')
                                    ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                    ->join('op_plazas_tit as op_plazas_fun', 'op_plazas_fun.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                    ->join('op_oficinas as op_oficinasf', 'op_oficinasf.id', 'op_plazas_fun.op_oficinaf_id')
                                    ->leftjoin('op_regimen as op_regimenf', 'op_regimenf.id', 'op_plazas_fun.op_regimen_id')
                                    ->first();

                $response['persona'] = $persona;
                return $response;
            } 
             else if($request->input('documentos')){
                $documentos = TipoDocumento::get();

                $response['documentos'] = $documentos;
                return $response;
            }


            return abort('404');
        } else if($request->input('listarAll')){
            $tabla = Persona::select('numero_documento',
                            DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno, ' ', persona.nombres) as nombrecompleto"), 
                            )
                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            ->get();

            $response['personal'] = $tabla;
            return $response;
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

        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal|Personal.licencias') ){
            $response = null;
            DB::beginTransaction();
            try{
                $query = DB::table('persona')
                        ->insert([
                                'tipo_documento_id' => $request->tipo_documento_id,
                                'numero_documento' => $request->numero_documento,
                                'ap_paterno' => mb_strtoupper($request->ap_paterno,'utf-8' ),
                                'ap_materno' => mb_strtoupper($request->ap_materno,'utf-8' ),
                                'nombres' => mb_strtoupper($request->nombres,'utf-8' ),
                                'fecha_nacimiento' => ($request->fecha_nacimiento)? $request->fecha_nacimiento : null,
                                'sexo' => ($request->sexo)? $request->sexo : null,
                                'direccion' => ($request->direccion)? $request->direccion : null,
                                'celular' => ($request->celular)? $request->celular : null,
                                'email' => ($request->email)? $request->email : null,
                                'escalafon' => ($request->escalafon)? $request->escalafon : null,
                                'fecha_ingreso' => ($request->fecha_ingreso)? $request->fecha_ingreso : null,
                                'user_update' => Auth::user()->id
                        ]);
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos de la Persona registrados con exito.";
            
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

        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal|Personal.licencias') ){
            $response = null;
            DB::beginTransaction();
            try{
                $query = DB::table('persona')
                        ->where('id', $id)
                        ->update([
                                'tipo_documento_id' => $request->tipo_documento_id,
                                'numero_documento' => $request->numero_documento,
                                'ap_paterno' => mb_strtoupper($request->ap_paterno,'utf-8' ),
                                'ap_materno' => mb_strtoupper($request->ap_materno,'utf-8' ),
                                'nombres' => mb_strtoupper($request->nombres,'utf-8' ),
                                'fecha_nacimiento' => ($request->fecha_nacimiento)? $request->fecha_nacimiento : null,
                                'sexo' => $request->sexo,
                                'direccion' => ($request->direccion)? $request->direccion : null,
                                'celular' => ($request->celular)? $request->celular : null,
                                'email' => ($request->email)? $request->email : null,
                                'escalafon' => ($request->escalafon)? $request->escalafon : null,
                                'fecha_ingreso' => ($request->fecha_ingreso)? $request->fecha_ingreso : null,
                                'user_update' => Auth::user()->id
                        ]);
                DB::commit(); 
                $response["statusBD"] = true;
                $response["sexo"] = $request->sexo;
                $response["messageDB"] = "Datos de la Persona registrados con exito.";
            
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





    public function adminvista($vista){

        $usuario = DB::table('users')
                            ->select('persona.ap_paterno', 'persona.ap_materno', 'persona.nombres', 'users.email')
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

        if (sizeof($user_roles_db)>0) {
            foreach($user_roles_db as $role){
                array_push($user_roles, utf8_encode($role->name));
            }
            $user_roles = json_encode($user_roles);
            //print_r($user_roles);

            if (view()->exists('personal/'.$vista)){
                return view('personal/'.$vista, compact('user_roles', 'usuario'));
            } else{
            return abort('404');
            }

        } else{
            $user_roles = json_encode($user_roles);

            return view('personal/'.$vista, compact('user_roles', 'usuario'));

        }
    }
}