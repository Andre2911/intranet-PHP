<?php
namespace App\Http\Controllers\Personal;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\User;
use Illuminate\Support\Facades\Auth;

class FilesController extends Controller
{
    public function index(Request $request)
    {
        if($request->input('init')){
            $sedes = DB::table('sedes')
                            ->select('sedes.ubigeo_id', 'n_distrito')
                            ->join('ubigeos', 'ubigeos.ubigeo_id', 'sedes.ubigeo_id')
                            ->distinct()
                            ->get();

            $files = DB::table('opbl_academico')
                            ->select('persona.id',
                                    'plazafisica.nombre_plaza as nombre_plazafisica', 'plazatitular.nombre_plaza as nombre_plazatitular',
                                    DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                                    DB::raw("CONCAT(oficinafisica.nombre_oficina, ' [', oficinafisica.distrito, ']') as nombre_oficina")

                                )
                            ->leftjoin('persona', 'persona.id', 'opbl_academico.persona_id')
                            ->leftjoin('opbl_cargos', 'opbl_cargos.persona_id', 'opbl_academico.persona_id')
                            ->leftjoin('opbl_especialidades', 'opbl_especialidades.persona_id', 'opbl_academico.persona_id')
                            ->leftjoin('opbl_sedes', 'opbl_sedes.persona_id', 'opbl_academico.persona_id')
                            
                            ->leftjoin('persona_has_plaza_tit','persona_has_plaza_tit.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazatitular', 'plazatitular.id', 'persona_has_plaza_tit.op_plaza_tit_id')
                            ->leftjoin('persona_has_plaza_fun','persona_has_plaza_fun.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            ->leftjoin('view_op_oficinas as oficinafisica', 'oficinafisica.id', 'plazafisica.op_oficinaf_id')
                            ->distinct()
                            ->get();


            $response["files"] = $files;
            $response["sedes"] = $sedes;
            $response["statusBD"] = true;
            $response["messageDB"] = "Datos cargados correctamente";            

            return $response;
        } 

        if($request->input('personal_id')){
            $personal = Persona::with('opbl_academico', 'opbl_especialidad', 'opbl_cargo', 'opbl_sede')
                            ->select('persona.*',
                                'plazafisica.nombre_plaza as nombre_plazafisica', 'plazatitular.nombre_plaza as nombre_plazatitular',
                                DB::raw("CONCAT(oficinafisica.nombre_oficina, ' [', oficinafisica.distrito, ']') as nombre_oficina")
                                
                                )
                            ->leftjoin('persona_has_plaza_tit','persona_has_plaza_tit.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazatitular', 'plazatitular.id', 'persona_has_plaza_tit.op_plaza_tit_id')

                            ->leftjoin('persona_has_plaza_fun','persona_has_plaza_fun.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            
                            ->leftjoin('view_op_oficinas as oficinafisica', 'oficinafisica.id', 'plazafisica.op_oficinaf_id')
                            ->where('persona.id', $request->personal_id)
                            ->first();
            $response["personal"] = $personal;
            $response["statusBD"] = true;
            $response["messageDB"] = "Datos cargados correctamente";            
            return $response;
                       
        }
        
    }

    public function store(Request $request){
        if(isset($request->cargo_id) || isset($request->especialidad_id) || isset($request->sede_id)){

            $b_cargo = ($request->cargo_id == null)? false: true;
            $b_especialidad = ($request->especialidad_id == null)? false: true;
            $b_sede = ($request->sede_id == null)? false: true;


            $files = DB::table('opbl_academico')
                            ->select('persona.id',
                                    'plazafisica.nombre_plaza as nombre_plazafisica', 'plazatitular.nombre_plaza as nombre_plazatitular',
                                    DB::raw("CONCAT(persona.ap_paterno, ' ', persona.ap_materno, ' ', persona.nombres) as persona"),
                                    DB::raw("CONCAT(oficinafisica.nombre_oficina, ' [', oficinafisica.distrito, ']') as nombre_oficina")

                                )
                            ->leftjoin('persona', 'persona.id', 'opbl_academico.persona_id')
                            ->leftjoin('opbl_cargos', 'opbl_cargos.persona_id', 'opbl_academico.persona_id')
                            ->leftjoin('opbl_especialidades', 'opbl_especialidades.persona_id', 'opbl_academico.persona_id')
                            ->leftjoin('opbl_sedes', 'opbl_sedes.persona_id', 'opbl_academico.persona_id')
                            

                            ->leftjoin('persona_has_plaza_tit','persona_has_plaza_tit.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazatitular', 'plazatitular.id', 'persona_has_plaza_tit.op_plaza_tit_id')

                            ->leftjoin('persona_has_plaza_fun','persona_has_plaza_fun.persona_id', 'persona.id')
                            ->leftjoin('op_plazas_tit as plazafisica', 'plazafisica.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                            
                            ->leftjoin('view_op_oficinas as oficinafisica', 'oficinafisica.id', 'plazafisica.op_oficinaf_id')
                            
                            ->when($b_cargo, function($query) use ($request){
                                return $query->where('opbl_cargos.op_cargo_id', $request->cargo_id);
                            })
                            ->when($b_especialidad, function($query) use ($request){
                                return $query->where('opbl_especialidades.especialidad_id', $request->especialidad_id);
                            })
                            ->when($b_sede, function($query) use ($request){
                                return $query->where('opbl_sedes.ubigeo_id', $request->sede_id);
                            })
                            ->distinct()
                            ->get();

            $response["files"] = $files;
            $response["statusBD"] = true;
            $response["messageDB"] = "Datos cargados correctamente";            

            return $response;

        }
    }
}