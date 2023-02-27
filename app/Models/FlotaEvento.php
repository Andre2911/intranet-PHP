<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Models\FlotaPasajero;
use App\Persona;

class FlotaEvento extends Model{
    //
    use Notifiable;

    protected $table = 'guv_eventos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $timestamps = false;

    protected $fillable = [
        'motivo',
        'destino',
        'descripcion',
        'startTime',
        'endTime',
        'sede_origen',
        'isalldayevent',
        'color',
        'vehiculo_id',
        'pasajeros',
        'estado',
        'km_partida',
        'km_retorno',
        'n_documento',
        'r_archivo',
        'solicitante',
        'programador',
        'conductor',
        'last_user',
        'ip_registro',
        'mac_registro',
        'host_registro',
    ];

    public function pasajeros_list(){
        return $this->hasMany('App\Models\FlotaPasajero', 'evento_id');
    }

    public function sede(){
        return $this->hasOne('App\Models\FlotaSede', 'sede_id', 'sede_origen');
    }

    public function solicita(){
        return $this->hasOne('App\Models\Persona', 'numero_documento', 'solicitante');

    }

    public function pasajeros_listFull(){
        $pasajeros = $this->hasMany('App\Models\FlotaPasajero', 'evento_id');
        $pasajeros->getQuery()
                        ->select('guv_pasajeros.*', 
                            DB::raw("CONCAT(pasajero.ap_paterno,' ',pasajero.ap_materno, ', ', pasajero.nombres, ' [', IIF(view_op_oficinas.nombre_oficina is null, '', CONCAT(view_op_oficinas.nombre_oficina, ' - ', view_op_oficinas.distrito)), ']') as pasajero"), 
                        )
                        ->join('persona as pasajero', 'pasajero.numero_documento', 'guv_pasajeros.user_id')
                        ->leftJoin('persona_has_plaza_fun', 'persona_has_plaza_fun.persona_id', 'pasajero.id')
                        ->leftJoin('op_plazas_tit', 'op_plazas_tit.id', 'persona_has_plaza_fun.op_plaza_fun_id')
                        ->leftJoin('view_op_oficinas', 'view_op_oficinas.id', 'op_plazas_tit.op_oficinaf_id')
                        ->get();
        return $pasajeros;
    }

}
    