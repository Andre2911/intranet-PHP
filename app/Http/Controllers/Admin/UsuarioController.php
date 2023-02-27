<?php

namespace App\Http\Controllers\Admin;

use App\Persona;
use App\TipoDocumento;
use Illuminate\Http\Request;
use Auth;
use App\Rol;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller{
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
                    $tabla = User::with('persona', 'roles')
                            ->select('users.*')
                            ->where(function ($query) use ($request) {
                                $query->where(DB::raw("CONCAT(persona.numero_documento, ' ',persona.nombres,' ',persona.ap_paterno)"),'like','%'.$request->search.'%');
                                $query->orWhere(DB::raw("CONCAT(persona.ap_paterno,' ',persona.ap_materno)"),'like','%'.$request->search.'%');
                                $query->orWhere('roles.name','like','%'.$request->search.'%');
                            })
                            ->join('persona', 'persona.id', 'users.persona_id')
                            ->leftjoin('model_has_roles', 'model_has_roles.model_id', 'users.id')
                            ->leftjoin('roles', 'roles.id', 'model_has_roles.role_id')
                            ->distinct()
                            ->orderBy('users.id','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }else{
                    $tabla = User::with('persona', 'roles')
                            ->orderBy('id','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }
    
                    
                if ($tabla == NULL){ abort('404'); }
    
                $response['personas'] = $tabla;
                return $response;
            } else if($request->input('consultar')){
                $persona = Persona::where('id', $request->persona)->first();

                $response['persona'] = $persona;
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

        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal|Personal.licencias') ){
            $response = null;
            DB::beginTransaction();
            try{

                /******** CONSULTAMOS SI EL USUARIO YA EXISTE */
                $existe = User::where('username',$request->username)->first();

                if(!$existe){
                    $form_data = array(
                        'persona_id' => $request->persona_id,
                        'username' => mb_strtolower($request->username,'utf-8' ),
                        'password' => bcrypt($request->password),
                        'email' => ($request->email)? $request->email : null,
                        'estado' => 1,
                    );
    
                    $user_created = User::create($form_data);
    
                    if($request->input('roles')){
                        $roles = [];
                        foreach ($request->roles as $role) {
                            array_push($roles, $role['name']);
                        }
        
                        $user_created->syncRoles($roles);
                    }
                    
                    DB::commit(); 
                    $response["statusBD"] = true;
                    $response["messageDB"] = "Datos de la Persona registrados con exito.";
                } else{
                    DB::rollback();
                    $response["statusBD"] = false;
                    $response["messageDB"] = "El usuario ya existe";
                }
            }  
            catch (\Exception $e) {
                DB::rollback();
                $response["statusBD"] = false;
                $response["messageDB"] = "Error al registrar en la Base de Datos.";

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
                
                if ($request->input('password') != null && $request->password != '') {
                    $query = DB::table('users')
                        ->where('id', $id)
                        ->update([
                                'persona_id' => $request->persona_id,
                                'username' => $request->username,
                                'password' => bcrypt($request->password),
                                'email' => ($request->email)? $request->email : null,
                                'user_update' => Auth::user()->id
                        ]);
                } else{
                    $query = DB::table('users')
                        ->where('id', $id)
                        ->update([
                                'persona_id' => $request->persona_id,
                                'username' => $request->username,
                                'email' => ($request->email)? $request->email : null,
                                'user_update' => Auth::user()->id
                        ]);
                
                }
                DB::commit();

                ///CONSULTAMOS lOS ROLES  

                $userupdate= User::find($id);
                
                $roles = [];
                foreach ($request->roles as $role) {
                    array_push($roles, $role['name']);
                }

                $userupdate->syncRoles($roles);


                $response["roles"] = $roles;
                $response["statusBD"] = true;
                $response["messageDB"] = "Datos de la Persona registrados con exito.";
            
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

        
        return abort('403');

    }


}
