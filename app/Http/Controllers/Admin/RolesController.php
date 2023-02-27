<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use App\Rol;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;

class RolesController extends Controller{
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
                    $tabla = Rol::where(function ($query) use ($request) {
                                $query->where(DB::raw("CONCAT(name, ' ',description)"),'like','%'.$request->search.'%');
                            })
                            ->orderBy('id','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }else{
                    $tabla = Rol::orderBy('id','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }
    
                    
                if ($tabla == NULL){ abort('404'); }
    
                $response['roles'] = $tabla;
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
                $admin = Rol::create(['name' => $request->name, 'guard_name' => 'web', 'description' => $request->description]);

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
                $query = DB::table('roles')
                        ->where('id', $id)
                        ->update([
                                'name' => $request->name,
                                'description' => $request->description,
                        ]);
                DB::commit(); 
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos del ROL registrados con exito.";
            
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

}
