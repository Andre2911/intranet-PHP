<?php

namespace App\Http\Controllers\Personal;

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

        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal') ){

            if($request->input('listar')){
                if($request->input('search') && $request->search != null){
                    $tabla = Oficina::with('parent')->where(function ($query) use ($request) {
                                $query->where('nombre_oficina','like','%'.$request->search.'%');
                            })
                            ->orderBy('id','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }else{
                    $tabla = Oficina::with('parent')->orderBy('id','asc')
                            ->paginate(config('global.numero_x_paginas'));
                }
    
                    
                if ($tabla == NULL){ abort('404'); }
    
                $response['oficinas'] = $tabla;
                return $response;
            } else if($request->input('parents')){
                $tabla = DB::table('view_op_oficinas')->select('id', DB::raw("CONCAT(nombre_oficina, ' - ', distrito) as nombre_oficina"))
                                ->orderBy('id','asc')->get();
                if ($tabla == NULL){ abort('404'); }
    
                $response['oficinas'] = $tabla;
                return $response;
            } else if ($request->input('tipo') != '') {
                $oficinas = DB::table('op_oficinas')
                                ->select('id', DB::raw("CONCAT('',nombre_oficina, '[', id, ']') as name"), 'parent_id', 'have_childrens as childrens', 'have_personal')
                                ->where('activo',1)
                                ->get();
    
                $personal = DB::table('persona')
                                    ->select('persona.id', 
                                        DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as name"),
                                        'op_plazas_tit.op_oficinaf_id as parent_id',
                                        /*'persona.email as correo',
                                        'op_oficinas.nombre_oficina as oficina',
                                        'op_plazas_tit.nombre_plaza',
                                        */
                                        'op_plazas_tit.jefe_area'
                                        
                                    )
                                    ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                    ->join('op_plazas_tit','op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                    ->join('op_oficinas','op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                               ->orderBy('op_plazas_tit.order_caf')
                               ->get();

                /*$gerente = DB::table('persona')
                                    ->select('persona.id', 
                                         DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as name"),
                                        'op_plazas_tit.op_oficinaf_id as parent_id',
                                        'users.pers_correo',
                                        'op_oficinas.of_nombre as oficina',
                                        'op_cargofun.cargofun_det',
                                        'op_cargofun.jefe_area'
                                    )
                                    ->join('op_cargofun','users.pers_cargofun2', 'op_cargofun.cargofun_id')
                                    ->join('op_oficinas','op_oficinas.of_id', 'op_cargofun.oficina_id')
                               ->where('is_admin', '!=', 1)
                               ->orderBy('op_cargofun.order_caf')
                               ->get();
    
                //return $oficinas;
                $personal = $gerente->merge($personal);
                */  
                //return $this->nested($oficinas, 0, $personal);
                return ['organigrama' => $this->nested($oficinas, 0, $personal), 'personal' => $personal];
            } else if ($request->input('filtro_org') != '') {
                $oficinas = DB::table('op_oficinas')
                                ->select('id', DB::raw("CONCAT('',nombre_oficina, '[', id, ']') as name"), 'parent_id', 'have_childrens as childrens', 'have_personal')
                                ->where('activo',1)
                                ->get();
    
                $personal = DB::table('persona')
                                    ->select('persona.id', 
                                        DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres) as name"),
                                        'op_plazas_tit.op_oficinaf_id as parent_id',
                                        'op_plazas_tit.jefe_area'
                                        
                                    )
                                    ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                                    ->join('op_plazas_tit','op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                                    ->join('op_oficinas','op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                               ->orderBy('op_plazas_tit.order_caf')
                               ->where(DB::raw("CONCAT(ap_paterno,' ',ap_materno, ', ', nombres)"),'like', '%'.$request->filtro_org.'%')
                               ->get();

                return ['organigrama' => $this->nested($oficinas, 0, $personal)];
            } else if($request->input('listarfull')){
                $oficinas = DB::table('view_op_oficinas')
                            //->join('ubigeos','ubigeos.ubigeo_id', 'sedes.ubigeo_id')
                            ->where('have_personal',1)
                            ->get();
                return ['oficinas' => $oficinas,];    
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

        if ( $usuario->hasAnyRole('Webmaster|Administrador|Personal.administrador') ){
            $response = null;
            DB::beginTransaction();
            try{
                $query = DB::table('op_oficinas')
                        ->where('id', $id)
                        ->update([
                            'parent_id' => ($request->input('parent_id'))? $request->parent_id:0, 
                            'nombre_oficina' => $request->nombre_oficina, 
                            'have_personal' => ($request->input('have_personal'))? $request->have_personal:0, 
                            'show_cap' => ($request->input('show_cap'))? $request->show_cap:0, 
                            'show_caf' => ($request->input('show_caf'))? $request->show_caf:0, 
                            'user_update' => Auth::user()->id
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
        $usuario = User::find(Auth::user()->id);
        $response=null;
        if ($usuario->hasAnyRole('Webmaster|Administrador') ){

            DB::beginTransaction();
            try{
                $data = Oficina::findOrFail($id);

                if ($data->delete()) {
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


    public function excOrganigrama(Request $request){

        $oficinas = DB::table('op_oficinas')
                                ->select('op_oficinas.id', 'op_oficinas.nombre_oficina', 'op_oficinas.parent_id', 'op_oficinas.have_childrens as childrens', 'op_oficinas.have_personal', 'distrito')
                                ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_oficinas.id')
                                ->where('activo',1)
                                ->get();
    
        $personal = DB::table('persona')
                            ->select('persona.id', 'persona.numero_documento',
                                DB::raw("CONCAT(ap_paterno,' ',ap_materno, ' ', nombres) as name"),
                                'plaza_fun.op_oficinaf_id as parent_id',
                                /*'persona.email as correo',
                                'op_oficinas.nombre_oficina as oficina',
                                'op_plazas_tit.nombre_plaza',
                                */
                                'plaza_fun.jefe_area',
                                'plaza_fun.nombre_plaza as nombre_plaza_fun',
                                'plaza_tit.nombre_plaza as nombre_plaza_tit',
                                'op_regimen.regimen_base'
                                
                            )
                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                            ->join('op_plazas_tit as plaza_fun','plaza_fun.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            ->leftjoin('persona_has_plaza_tit', 'persona_has_plaza_tit.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plaza_tit', 'plaza_tit.id', 'persona_has_plaza_tit.op_plaza_tit_id')
                            ->leftjoin('op_regimen','op_regimen.id', 'plaza_fun.op_regimen_id')
                            
                            ->join('op_oficinas','op_oficinas.id', 'plaza_fun.op_oficinaf_id')
                        ->orderBy('plaza_fun.order_caf')
                        ->get();

        //dd($oficinas);

        echo '
        <!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">

    <title>CAF</title>
    <style type="text/css"> 
        thead tr th { 
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #ffffff;
        }
    
        .table-responsive { 
            height:200px;
            overflow:scroll;
        }
    </style>
  </head>
  <body>
    <div class="container">
    <form action="'.$request->getBasePath().'/personal/ficheroExcel" method="post" id="FormularioExportacion">
        <button class="form-control botonExcel" ><p>Exportar a Excel<img src="'.$request->getBasePath().'/public/image/MS Excel-32.png" /></p></button>
        <input type="hidden" id="datos_a_enviar" name="datos_a_enviar" />
    </form>
        <table class="table table-hover table-bordered" id="Exportar_a_Excel" border="1">
            <thead class="thead-dark">
                <tr class="table-dark">
                    <th class="header" scope="col">DNI</th>
                    <th class="header" scope="col">Apellidos y Nombres</th>
                    <th class="header" scope="col">Regimen Laboral</th>
                    <th class="header" scope="col">Encargatura/Suplencia (CAF)</th>
                    <th class="header" scope="col">Cargo Titular</th>
                    <th class="header" scope="col">Distrito</th>
                </tr>
            </thead>
            <tbody>'.
            $this->nestedTable($oficinas, 0, $personal).
            '</tbody>
        </table>
        
    </div>
    </body>
</html>
<script type="text/javascript" src="'.$request->getBasePath().'/public/iof/js/jquery.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $(".botonExcel").click(function(event) {
      $("#datos_a_enviar").val( $("<div>").append( $("#Exportar_a_Excel").eq(0).clone()).html() );
      $("#FormularioExportacion").submit();
    });
  });
</script>';
    }

    public function nested($rows = array(), $parent_id = 0, $personal = null)
    {
        $nodo = array();

        if(!empty($rows))
        {
            foreach($rows as $row)
            {
                
                if($row->parent_id == $parent_id)
                {
                    //if ($row->childrens && $row->have_personal) {
                    if ($row->childrens) {
                        
                        $row->children = array();

                        //$row->children = $this->nested($rows, $row->id, $personal);
                        
                        foreach ($personal as $elemento) {
                            if ($elemento->parent_id == $row->id) {
                                array_push($row->children, ['id' => $elemento->id,'name' => $elemento->name, 'jefe' => $elemento->jefe_area] );
                            }
                        }

                        
                        $hijo = $this->nested($rows, $row->id, $personal);
                        for ($i = 0; $i < sizeof($hijo); $i++) { 
                            array_push($row->children,  $hijo[$i]);    
                        }
                    }
                    else if ($row->childrens && !$row->have_personal) {
                        $row->children = array();
                        $row->children = $this->nested($rows, $row->id, $personal);
                    } else{                        
                            $row->children = array();
                            foreach ($personal as $elemento) {
                                if ($elemento->parent_id == $row->id) {
                                    array_push($row->children, ['id' => $elemento->id,'name' => $elemento->name, 'jefe' => $elemento->jefe_area] );
                                }
                            }
                    }
                    array_push($nodo, $row);
                    //print_r($row);
                }
            }
        }
        return $nodo;
    }

    public function nestedTable($rows = array(), $parent_id = 0, $personal = null)
    {
        $html = "";

        if(!empty($rows))
        {
            foreach($rows as $row)
            {
                
                if($row->parent_id == $parent_id)
                {
                    if ($row->childrens && !$row->have_personal) {

                        if($row->parent_id != 3 && $row->parent_id != 4 && $row->parent_id != 0 ){
                            $html.= '<tr class="table-secondary" style="font-weight: bold;">
                                        <td colspan="6" class="table-primary" style="text-align: center;">
                                            <h4>'.$row->nombre_oficina.'</h4>
                                        </td>
                                    </tr>';
                        } else if($row->parent_id != 3 && $row->parent_id != 4){
                            $html.= '<td colspan="6" class="table-secondary" style="text-align: center;"><h3>'.$row->nombre_oficina.'</h3></td>';
                        }

                        $html .= $this->nestedTable($rows, $row->id, $personal);

                    }
                    else if ($row->childrens) {
                        
                            if($row->parent_id != 0){
                                $html.= '<tr><td colspan="6" class="table-info" style="text-align: center;"><b>'.$row->nombre_oficina.'</b></td></tr>';
                            } else{
                                $html.= '<tr><td colspan="6" class="table-secondary" style="text-align: center;">'.$row->nombre_oficina.'</td></tr>';
                            }

                        foreach ($personal as $elemento) {
                            if ($elemento->parent_id == $row->id) {
                                
                                $html.= ($elemento->jefe_area)? '<tr style="font-weight:600">' : '<tr>';
                                $html.= '<td>'.$elemento->numero_documento.'</td>
                                <td>'.$elemento->name.'</td>
                                <td>'.$elemento->regimen_base.'</td>
                                <td>'.$elemento->nombre_plaza_fun.'</td>
                                <td>'.$elemento->nombre_plaza_tit.'</td>
                                <td>'.$row->distrito.'</td></tr>';
                                //array_push($row->children, ['id' => $elemento->id,'name' => $elemento->name, 'jefe' => $elemento->jefe_area] );
                            }
                        }
                        
                        $html.= $this->nestedTable($rows, $row->id, $personal);
                       /* for ($i = 0; $i < sizeof($hijo); $i++) { 
                            //array_push($row->children,  $hijo[$i]);    
                        }*/
                    }
                     else{                        
                        $have_personal = false;
                        foreach ($personal as $elemento) {
                            if ($elemento->parent_id == $row->id) {
                                $have_personal = true;
                            }
                        }
                        if($have_personal){
                            $html.= '<tr><td colspan="6" class="table-info" style="text-align: center;"><b>'.$row->nombre_oficina.'</b></td></tr>';
                        }
        

                        foreach ($personal as $elemento) {
                            if ($elemento->parent_id == $row->id) {

                                $html.= ($elemento->jefe_area)? '<tr style="font-weight:600">' : '<tr>';
                                $html.= '<td>'.$elemento->numero_documento.'</td>
                                        <td>'.$elemento->name.'</td>
                                        <td>'.$elemento->regimen_base.'</td>
                                        <td>'.$elemento->nombre_plaza_fun.'</td>
                                        <td>'.$elemento->nombre_plaza_tit.'</td>
                                        <td>'.$row->distrito.'</td></tr>';
                            }
                        }
                        $html.= '<tr><td colspan="6" class="table-secondary"></td></tr>';
                    }
                    //array_push($nodo, $row);
                }
            }
        }
        return $html;
    }

    public function ficheroExcel(Request $request){
        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-type:   application/x-msexcel; charset=utf-8");
        header("Content-Disposition: attachment; filename=CuadroFisico.xls"); 
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false); 

        $oficinas = DB::table('op_oficinas')
                                ->select('op_oficinas.id', 'op_oficinas.nombre_oficina', 'op_oficinas.parent_id', 'op_oficinas.have_childrens as childrens', 'op_oficinas.have_personal', 'distrito')
                                ->join('view_op_oficinas', 'view_op_oficinas.id', 'op_oficinas.id')
                                ->where('activo',1)
                                ->get();
    
        $personal = DB::table('persona')
                            ->select('persona.id', 'persona.numero_documento',
                                DB::raw("CONCAT(ap_paterno,' ',ap_materno, ' ', nombres) as name"),
                                'plaza_fun.op_oficinaf_id as parent_id',
                                /*'persona.email as correo',
                                'op_oficinas.nombre_oficina as oficina',
                                'op_plazas_tit.nombre_plaza',
                                */
                                'plaza_fun.jefe_area',
                                'plaza_fun.nombre_plaza as nombre_plaza_fun',
                                'plaza_tit.nombre_plaza as nombre_plaza_tit',
                                'op_regimen.regimen_base'
                                
                            )
                            ->join('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'persona.id')
                            ->join('op_plazas_tit as plaza_fun','plaza_fun.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            ->leftjoin('persona_has_plaza_tit', 'persona_has_plaza_tit.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plaza_tit', 'plaza_tit.id', 'persona_has_plaza_tit.op_plaza_tit_id')
                            ->leftjoin('op_regimen','op_regimen.id', 'plaza_fun.op_regimen_id')
                            
                            ->join('op_oficinas','op_oficinas.id', 'plaza_fun.op_oficinaf_id')
                        ->orderBy('plaza_fun.order_caf')
                        ->get();

                        
        echo utf8_decode('<table class="table table-hover table-bordered" id="Exportar_a_Excel" border="1">
        <thead class="thead-dark">
            <tr class="table-dark">
                <th class="header" scope="col">DNI</th>
                <th class="header" scope="col">Apellidos y Nombres</th>
                <th class="header" scope="col">Regimen Laboral</th>
                <th class="header" scope="col">Encargatura/Suplencia (CAF)</th>
                <th class="header" scope="col">Cargo Titular</th>
                <th class="header" scope="col">Distrito</th>
            </tr>
        </thead>
        <tbody>'.
        $this->nestedTable($oficinas, 0, $personal).
        '</tbody>
    </table>');

    }
}
