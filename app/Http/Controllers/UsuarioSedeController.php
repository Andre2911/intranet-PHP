<?php

namespace App\Http\Controllers;

use App\Sede;
use App\Especialidad;
use App\UsuarioSede;
use App\UsuarioEspecialidad;
use Auth;
use App\Evento;
use App\OrdenPresentacion;
use App\Participante;
use App\Rol;
use App\Serie;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class UsuarioSedeController extends Controller{

    protected $titulo = 'Jurado criterio';
    protected $url_modulo = 'mantenimiento/mpvusuario';
    protected $permiso_show = 'usuario_sede.ver';
    protected $permiso_create = 'usuario_sede.crear';
    protected $permiso_edit = 'usuario_sede.editar';
    protected $permiso_delete = 'usuario_sede.eliminar';

    protected $mensajes = [
        'f_nombre.unique' => 'El nombre ya esta en uso.',
        'f_nombre.required' => 'El nombre es requerido.',
        'f_slug.unique' => 'El slug ya esta en uso.',
        'f_slug.required' => 'El slug es requerido.',
        'f_permisos.required' => 'Debe seleccionar al menos un permiso.',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

        if ($this->cerrojo_rol() == 1) {

            if (auth()->user()->hasPermissionTo($this->permiso_show)) {

                $tabla = UsuarioSede::with('usuario','sede')
                    ->orderBy('id','desc')
                    ->paginate(config('global.numero_x_paginas'));

                if ($request->has('b_columna') && $request->has('b_texto')) {
                    $col = 'id';
                    if ($request->b_columna == 'name') {
                        $col = 'name';
                    }
                    if ($request->b_columna == 'slug') {
                        $col = 'slug';
                    }

                    $tabla = UsuarioSede::where($col, 'like', '%' . $request->b_texto . '%')
                        ->with('usuario','sede')
                        ->orderBy('id','desc')
                        ->paginate(config('global.numero_x_paginas'));
                }

                $user = $this->data_user();
                $titulo = $this->titulo;
                $sub_titulo = 'Listado de Usuario y Sede';
                $url_modulo = $this->url_modulo;
                $permiso_crear = auth()->user()->hasPermissionTo($this->permiso_create);
                $form_busqueda = $request->all();

                return view('usuario_sede.index', compact('user',
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

        if ($this->cerrojo_rol() == 1) {

            if (auth()->user()->hasPermissionTo($this->permiso_create)) {

                $user = $this->data_user();
                $titulo = $this->titulo;
                $sub_titulo = 'Crear Usuario Sede';
                $usuarios = User::with('persona')->get();
                $sedes = Sede::get();
                $especialidades = Especialidad::get();
                $url_modulo = $this->url_modulo;

                return view('usuario_sede.create', compact('user',
                    'titulo',
                    'sub_titulo',
                    'url_modulo',
                    'sedes',
                    'especialidades',
                    'usuarios'
                ));

            }
            return abort('403');

        }
        return abort('403');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){

        if ($this->cerrojo_rol() == 1) {

            if (auth()->user()->hasPermissionTo($this->permiso_create)) {

                $request->validate([
                    'f_usuario' => 'required|exists:users,id',
                    'f_sede' => 'required|exists:sede,id',
                ], $this->mensajes);

                $form_data = array(
                    'usuario_id' => $request->f_usuario,
                    'sede_id' => $request->f_sede
                );


                $deletesede = DB::table('usuario_sede')
                    ->where('usuario_id', $request->f_usuario)
                    ->delete();
                
                $deleteesp = DB::table('usuario_especialidad')
                    ->where('usuario_id', $request->f_usuario)
                    ->delete();

                $reg = UsuarioSede::create($form_data);

                foreach ($request->f_especialidad as $especialidad) {
                    $form_data2 = array(
                        'usuario_id' => $request->f_usuario,
                        'especialidad_id' => $especialidad
                    );

                    $reg = UsuarioEspecialidad::create($form_data2);
                }
                

                return redirect($this->url_modulo)
                    ->with('success', 'Se guardo correctamente el registro .');

            }
            //print_r($request);
            return abort('403');

        }
        return abort('403');

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){

        if ($this->cerrojo_rol() == 1) {

            if (auth()->user()->hasPermissionTo($this->permiso_show)) {

                $titulo = $this->titulo;
                $user = $this->data_user();

                $item = UsuarioSede::where('id', '=', $id)
                    ->with('usuario','sede')
                    ->first();

                if ($item == NULL) { abort('404'); }

                $url_modulo = $this->url_modulo;
                $permiso_editar = auth()->user()->hasPermissionTo($this->permiso_edit);
                $permiso_eliminar = auth()->user()->hasPermissionTo($this->permiso_delete);

                return view('usuario_sede.show', compact('titulo',
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
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){

        if ($this->cerrojo_rol() == 1) {

            if (auth()->user()->hasPermissionTo($this->permiso_edit)) {

                $item = UsuarioSede::where('id', '=', $id)
                    ->first();

                if ($item == NULL) {
                    abort('404');
                }

                $user = $this->data_user();
                $titulo = $this->titulo;
                $sub_titulo = 'Editar usuario sede ';
                $url_modulo = $this->url_modulo;
                $usuarios = User::with('persona')->get();
                $sedes = Sede::get();

                return view('usuario_sede.edit', compact('titulo',
                    'sub_titulo',
                    'user',
                    'item',
                    'usuarios',
                    'sedes',
                    'url_modulo'));

            }
            return abort('403');

        }
        return abort('403');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

        if ($this->cerrojo_rol() == 1) {

            if (auth()->user()->hasPermissionTo($this->permiso_edit)) {

                $request->validate([
                    'f_usuario' => 'required|exists:users,id',
                    'f_Sede' => 'required|exists:sede,id',
                ], $this->mensajes);

                $form_data = array(
                    'usuario_id' => $request->f_usuario,
                    'sede_id' => $request->d_sede
                );


                if (UsuarioSede::whereId($id)->update($form_data)) {
                    return redirect($this->url_modulo)
                        ->with('success', 'Se actualizo correctamente el registro.');
                } else {
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
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){

        if ($this->cerrojo_rol() == 1) {

            if (auth()->user()->hasPermissionTo($this->permiso_delete)) {

                $data = UsuarioSede::findOrFail($id);

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

    public function data_user()
    {
        $tmp_user = User::select('id',
            'persona_id',
            'username',
            'estado')
            ->where('id', '=', Auth::id())
            ->with('persona:id,ape_paterno,ape_materno,nombre')
            ->first();
        if ($tmp_user == NULL) {
            abort('404');
        }
        return $tmp_user;
    }

    public function cerrojo_rol()
    {
        foreach (Rol::select('id', 'slug')->get() as $rol) {
            if (auth()->user()->hasRole($rol->slug)) {
                return 1;
            }
        }
        return 0;

    }

}
