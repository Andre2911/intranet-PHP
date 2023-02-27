<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use Illuminate\Support\Facades\DB;

class InicioController extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $usuario = User::find(Auth::user()->id);
        
        /*if ($usuario->hasAnyRole('usuario')){
            return redirect('/usuario/dashboard');
        }

        if ($usuario->hasAnyRole('Administrador')){
            return redirect('/admin/inicio');
        }

        if ($usuario->hasAnyRole('Webmaster')){
            return redirect('/admin/inicio');
        }
        */


        return redirect('dashboard');

        $data = [
        ];

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){}

    public function data_user(){

        $tmp_user = User::select('id',
            'persona_id',
            'username',
            'estado')
            ->where('id','=',Auth::id())
            ->with(
                'persona:id,ape_paterno,ape_materno,nombre')
            ->first();

        if ($tmp_user == NULL){ abort('404'); }

        return $tmp_user;

    }

}
