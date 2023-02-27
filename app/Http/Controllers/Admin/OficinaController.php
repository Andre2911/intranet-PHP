<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use App\Oficina;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;

class OficinaController extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

        $usuario = User::find(Auth::user()->id);
        $response=null;

        if ( $usuario->hasAnyRole('Webmaster|Administrador') ){

            if($request->input('listar')){
                if($request->input('search') && $request->search != null){
                    $tabla = Oficina::with('parent', 'view')
                            ->select('op_oficinas.*')
                            ->where(function ($query) use ($request) {
                                $query->where('op_oficinas.nombre_oficina','like','%'.$request->search.'%');
                                $query->orWhere('op_oficinas.id', 'like',$request->search);
                                $query->orWhere('view_op_oficinas.distrito','like',$request->search);
                            })
                            //->where('op_oficinas.activo', 0)
                            ->leftjoin('view_op_oficinas', 'view_op_oficinas.id', 'op_oficinas.id')
                            ->orderBy('op_oficinas.id','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }else{
                    $tabla = Oficina::with('parent', 'view')
                            ->orderBy('id','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }
    
                    
                if ($tabla == NULL){ abort('404'); }
    
                $response['oficinas'] = $tabla;
                return $response;
            } else if($request->input('parents')){
                $tabla = DB::table('view_op_oficinas')
                                    ->select('view_op_oficinas.id', DB::raw("CONCAT(view_op_oficinas.nombre_oficina, ' [',parent.nombre_oficina, '] - ', view_op_oficinas.distrito) as nombre_oficina"))
                                    ->join('op_oficinas as parent', 'parent.id', 'view_op_oficinas.parent_id')
                                    ->orderBy('id','asc')
                                    ->get();

                $sedes = DB::table('sedes')
                                    ->select('cod_local',
                                    DB::raw("CONCAT('[', n_distrito, '] ', direccion, ' - ', descripcion) as local"))
                                    ->join('ubigeos', 'ubigeos.ubigeo_id', 'sedes.ubigeo_id')
                                    ->get();

                if ($tabla == NULL){ abort('404'); }
    
                $response['oficinas'] = $tabla;
                $response['sedes'] = $sedes;
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

        if ( $usuario->hasAnyRole('Webmaster|Administrador') ){
            $response = null;
            DB::beginTransaction();
            try {
                $admin = Oficina::create([
                    'parent_id' => ($request->input('parent_id'))? $request->parent_id:0, 
                    'nombre_oficina' => $request->nombre_oficina, 
                    'sede_id' => $request->sede_id, 
                    'have_childrens' => ($request->input('have_childrens'))? $request->have_childrens:0, 
                    'have_personal' => ($request->input('have_personal'))? $request->have_personal:0, 
                    'show_cap' => ($request->input('show_cap'))? $request->show_cap:0, 
                    'show_caf' => ($request->input('show_caf'))? $request->show_caf:0, 
                    'user_update' => Auth::user()->id
                    ]);

                DB::commit();
                $response["statusBD"] = true;
                $response["messageDB"] = "Registrado correctamente.";
            }
            catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al registrar el nuevo Rol en la Base de Datos.".$e;
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

        if ( $usuario->hasAnyRole('Webmaster|Administrador') ){
            $response = null;
            DB::beginTransaction();
            try{
                $query = DB::table('op_oficinas')
                        ->where('id', $id)
                        ->update([
                            'parent_id' => ($request->input('parent_id'))? $request->parent_id:0, 
                            'nombre_oficina' => $request->nombre_oficina, 
                            'sede_id' => $request->sede_id, 
                            'have_childrens' => ($request->input('have_childrens'))? $request->have_childrens:0, 
                            'have_personal' => ($request->input('have_personal'))? $request->have_personal:0, 
                            'show_cap' => ($request->input('show_cap'))? $request->show_cap:0, 
                            'show_caf' => ($request->input('show_caf'))? $request->show_caf:0, 
                            'activo' => ($request->input('activo'))? $request->activo:0, 
                            'user_update' => Auth::user()->id
                        ]);
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos de la dependencia actualizados con exito.";
            
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
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
        $usuario = User::find(Auth::user()->id);
        $response=null;
        if ($usuario->hasAnyRole('Webmaster|Administrador') ){

            DB::beginTransaction();
            try{
                $data = Oficina::findOrFail($id);

                if ($data->update(['activo' => 0])) {
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Datos de la dependencia eliminados con exito.";
                    DB::commit(); 

                } else{
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "Error al actualizar en la Base de Datos.";
                }
            } catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al actualizar en la Base de Datos.".$e;
            }
            return $response;
        }
        return abort('403');


    }

}
