<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Auth;
use App\Rol;
use App\TipoDocumento;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TipoDocumentoController extends Controller{

    protected $titulo = 'Tipo Documento';
    protected $url_modulo = 'mantenimiento/tipo_documento';
    protected $permiso_show    = 'tipo_documento.ver';
    protected $permiso_create  = 'tipo_documento.crear';
    protected $permiso_edit    = 'tipo_documento.editar';
    protected $permiso_delete  = 'tipo_documento.eliminar';

    protected $mensajes = [
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

        if ( $this->cerrojo_rol() == 1 ){

            if ( auth()->user()->hasPermissionTo( $this->permiso_show ) ){

                $tabla = TipoDocumento::orderBy('id','desc')
                    ->paginate(config('global.numero_x_paginas'));

                if ($request->has('b_columna') && $request->has('b_texto') ) {
                    $col = 'id';
                    if ($request->b_columna == 'nombre'){ $col = 'nombre'; }
                    if ($request->b_columna == 'sigla'){ $col = 'sigla'; }

                    $tabla = TipoDocumento::where($col,'like','%'.$request->b_texto.'%')
                        ->orderBy('id','desc')
                        ->paginate(config('global.numero_x_paginas'));
                }

                $user = $this->data_user();
                $titulo = $this->titulo;
                $sub_titulo = 'Listado de Tipos de Documentos';
                $url_modulo = $this->url_modulo;
                $permiso_crear = auth()->user()->hasPermissionTo($this->permiso_create);
                $form_busqueda = $request->all();

                return view('tipo_documento.index',compact('user',
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
                $sub_titulo = 'Crear un nuevo tipo de documento';
                $url_modulo = $this->url_modulo;

                return view('tipo_documento.create',compact('user',
                    'titulo',
                    'sub_titulo',
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

                $request->validate([
                    'f_nombre' => 'required|unique:tipo_documento,nombre',
                    'f_sigla' => 'required|unique:tipo_documento,sigla',
                    'f_digito' => 'required|numeric',
                ],$this->mensajes);

                $form_data = array(
                    'nombre' => mb_strtoupper($request->f_nombre,'utf-8' ),
                    'sigla' => mb_strtoupper($request->f_sigla,'utf-8' ),
                    'digito' => $request->f_digito,
                );

                $reg = TipoDocumento::create($form_data);

                return redirect($this->url_modulo)
                    ->with('success', 'Se guardo correctamente el registro '.$reg->nombre.'.');

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

                $item = TipoDocumento::where('id','=',$id)
                    ->first();

                if ($item == NULL){ abort('404'); }

                $url_modulo = $this->url_modulo;
                $permiso_editar = auth()->user()->hasPermissionTo($this->permiso_edit);
                $permiso_eliminar = auth()->user()->hasPermissionTo($this->permiso_delete);

                return view('tipo_documento.show',compact('titulo',
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

                $item = TipoDocumento::where('id','=',$id)
                    ->first();

                if ($item == NULL){ abort('404'); }

                $user = $this->data_user();
                $titulo = $this->titulo;
                $sub_titulo = 'Editar Tipo Documento ';
                $url_modulo = $this->url_modulo;

                return view('tipo_documento.edit',compact('titulo',
                    'sub_titulo',
                    'user',
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

                $request->validate([
                    'f_nombre' => 'required|unique:tipo_documento,nombre,'.$id,
                    'f_sigla' => 'required|unique:tipo_documento,sigla,'.$id,
                    'f_digito' => 'required|numeric',

                ],$this->mensajes);

                $form_data = array(
                    'nombre' => mb_strtoupper($request->f_nombre,'utf-8' ),
                    'sigla' => mb_strtoupper($request->f_sigla,'utf-8' ),
                    'digito' => $request->f_digito,
                );

                if (TipoDocumento::whereId($id)->update($form_data)){
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

                $data = TipoDocumento::findOrFail($id);

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
