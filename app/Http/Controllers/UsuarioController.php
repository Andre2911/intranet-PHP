<?php

namespace App\Http\Controllers;

use App\Cargo;
use App\Persona;
use App\RoleUser;
use App\TipoDocumento;
use Illuminate\Http\Request;
use Auth;
use App\Rol;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PDF;


class UsuarioController extends Controller{

    protected $titulo = 'Usuario';
    protected $url_modulo = 'mantenimiento/usuario';
    protected $permiso_show    = 'usuario.ver';
    protected $permiso_create  = 'usuario.crear';
    protected $permiso_edit    = 'usuario.editar';
    protected $permiso_delete  = 'usuario.eliminar';

    protected $mensajes = [
        'f_nombre.required'             => 'El nombre es requerido.',
        'f_nombre.unique'               => 'El nombre ya esta en uso.',
        'f_abreviatura.required'        => 'La abreviatura es requerida.',
        'f_abreviatura.unique'          => 'La abreviatura esta en uso.',
        'f_estado.required'             => 'El estado es requerido.',
        'f_estado.digits'               => 'El estado es incorrecto.',
        'f_estado.numeric'              => 'El estado es incorrecto.',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

        if ( $this->cerrojo_rol() == 1 ){

            if ( auth()->user()->hasPermissionTo( $this->permiso_show ) ){

                $tabla = User::with('persona',
                    'user_role')
                    ->orderBy('id','desc')
                    ->paginate(config('global.numero_x_paginas'));

                if ($request->has('b_columna') && $request->has('b_texto') ) {
                    $col = 'id';
                    if ($request->b_columna == 'username'){ $col = 'username'; }

                    $tabla = User::with('persona',
                        'user_role')
                        ->where($col,'like','%'.$request->b_texto.'%')
                        ->paginate(config('global.numero_x_paginas'));
                }

                $user = $this->data_user();
                $titulo = $this->titulo;
                $sub_titulo = 'Listado de Usuarios';
                $url_modulo = $this->url_modulo;
                $permiso_crear = auth()->user()->hasPermissionTo($this->permiso_create);
                $form_busqueda = $request->all();

                return view('usuario.index',compact('user',
                    'titulo',
                    'sub_titulo',
                    'url_modulo',
                    'tabla',
                    'form_busqueda',
                    'permiso_crear'));

            }
            return abort('403');
        }
        return abort('403');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){

        if ( $this->cerrojo_rol() == 1 ){

            if (auth()->user()->hasPermissionTo($this->permiso_create)) {

                $user = $this->data_user();
                $titulo = $this->titulo;
                $sub_titulo = 'Crear un nuevo usuario';

                $roles = Rol::select('id','name')
                    ->get();

                $cargos = Cargo::select('id','nombre')
                    ->get();

                $lista_tipo_documento = TipoDocumento::select('id','sigla')
                    ->get();
                $url_modulo = $this->url_modulo;

                return view('usuario.create',compact('user',
                    'titulo',
                    'sub_titulo',
                    'roles',
                    'cargos',
                    'lista_tipo_documento',
                    'url_modulo'));

            }
            return abort('403');

        }
        return abort('403');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){

        if ( $this->cerrojo_rol() == 1 ){

            if (auth()->user()->hasPermissionTo($this->permiso_create)) {

                $persona = Persona::select('id')->where('numero_documento','=',$request->f_numero_documento)->get();

                if ($persona->count() > 0){
                    $request->request->add(['persona_id' => $persona[0]->id]);
                }elseif ($persona->count() == 0){
                    $request->request->add(['persona_id' => '']);
                }

                $request->validate([
                    'f_cargo' => 'required|exists:cargo,id|numeric',
                    'f_numero_documento' => 'required|exists:persona,numero_documento|numeric',
                    'persona_id' => 'required|unique:users,persona_id',
                    'f_rol' => 'required|exists:roles,id|numeric',
                    'f_email' =>  'nullable|email|unique:users,email',
                    'f_usuario' =>  'required|unique:users,username',
                    'f_contrasena' =>  'required|min:6|max:25',
                    'f_estado' =>  'required|digits:1|numeric',
                ],$this->mensajes);

                $form_data = array(
                    'cargo_id' => $request->f_cargo,
                    'persona_id' => $request->persona_id,
                    'username' => mb_strtolower($request->f_usuario,'utf-8' ),
                    'password' => bcrypt($request->f_contrasena),
                    'email' => $request->f_email,
                    'estado' => $request->f_estado,
                );

                $reg = User::create($form_data);

                $form_data_2 = array(
                    'role_id' => $request->f_rol,
                    'user_id' => $reg->id,
                );

                RoleUser::create($form_data_2);

                return redirect($this->url_modulo)
                    ->with('success', 'Se guardo correctamente el registro '.$reg->username.'.');

            }
            return abort('403');

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

        if ( $this->cerrojo_rol() == 1 ){

            if (auth()->user()->hasPermissionTo($this->permiso_show)) {

                $titulo = $this->titulo;
                $user = $this->data_user();
                $item = User::where('id','=',$id)
                    ->with('persona.tipo_documento')
                    ->first();

                if ($item == NULL){ abort('404'); }

                $url_modulo = $this->url_modulo;
                $permiso_editar = auth()->user()->hasPermissionTo($this->permiso_edit);
                $permiso_eliminar = auth()->user()->hasPermissionTo($this->permiso_delete);

                return view('usuario.show',compact('titulo',
                    'user',
                    'item',
                    'url_modulo',
                    'permiso_editar',
                    'permiso_eliminar'));

            }
            return abort('403');

        }
        return abort('403');

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){

        if ( $this->cerrojo_rol() == 1 ){

            if (auth()->user()->hasPermissionTo($this->permiso_edit)) {

                $item = User::where('id','=',$id)
                    ->first();

                $roles = Rol::select('id','name')->get();
                $user_rol = RoleUser::where('user_id','=',$id)->first();
                $cargos = Cargo::select('id','nombre')->get();

                if ($item == NULL){ abort('404'); }

                $user = $this->data_user();
                $titulo = $this->titulo;
                $sub_titulo = 'Editar usuario ';
                $url_modulo = $this->url_modulo;

                return view('usuario.edit',compact('titulo',
                    'sub_titulo',
                    'user',
                    'roles',
                    'user_rol',
                    'cargos',
                    'item',
                    'url_modulo'));

            }
            return abort('403');

        }
        return abort('403');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

        if ( $this->cerrojo_rol() == 1 ){

            if (auth()->user()->hasPermissionTo($this->permiso_edit)) {

                $persona = Persona::select('id')->where('numero_documento','=',$request->f_numero_documento)->first();

                if ($persona->count() > 0){
                    $request->request->add(['persona_id' => $persona->id]);
                }elseif ($persona->count() == 0){
                    $request->request->add(['persona_id' => '']);
                }

                $request->validate([
                    'f_cargo' => 'required|exists:cargo,id|numeric',
                    'f_numero_documento' => 'required|exists:persona,numero_documento|numeric',
                    'persona_id' => 'required|unique:users,persona_id,'.$id,
                    'f_rol' => 'required|exists:roles,id|numeric',
                    'f_email' =>  'nullable|email|unique:users,email,'.$id,
                    'f_usuario' =>  'required|unique:users,username,'.$id,
                    'f_estado' =>  'required|digits:1|numeric',
                ],$this->mensajes);

                $form_data = array(
                    'cargo_id' => $request->f_cargo,
                    'persona_id' => $request->persona_id,
                    'username' => mb_strtolower($request->f_usuario,'utf-8' ),
                    'email' => $request->f_email,
                    'estado' => $request->f_estado,
                );

                if (User::whereId($id)->update($form_data)){
                    return redirect($this->url_modulo)
                        ->with('success', 'Se actualizo correctamente el registro.');
                }else{
                    return redirect($this->url_modulo)
                        ->with('error', 'Ocurrio un error al actualizar el registro.');
                }

            }
            return abort('403');

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

                $data = User::findOrFail($id);

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

    public function password($id){
        if ( $this->cerrojo_rol() == 1 ){

            $item = User::where('id','=',$id)
                ->with('persona.tipo_documento')
                ->first();

            if ($item == NULL){ abort('404'); }

            $pass = Str::random(12);

            $form_data = array(
                'password' => Hash::make($pass),
            );

            if (User::whereId($id)->update($form_data)){

                $data = [
                    "user" => $item,
                    "password" => $pass
                    ];

                $pdf = PDF::loadView('reporte.password', $data);
                return $pdf->stream('document.pdf');
            }
        }
        return abort('403');
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

    public function cerrojo_rol(){
        foreach ( Rol::select('id','slug')->get() as $rol ){
            if (auth()->user()->hasRole($rol->slug)){ return 1; }
        }
        return 0;
    }

}
