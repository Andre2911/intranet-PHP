<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Plaza extends Model{
    //
    use Notifiable;

    protected $table = 'op_plazas_tit';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'c_plaza',
        'op_cargo_id',
        'nombre_plaza',
        'op_oficina_id',
        'order_cap',
        'op_regimen_id',
        'op_oficinaf_id',
        'order_caf',
        'op_regimenf',
        'jefe_area',
        'op_plaza_ji',
        'activo',
        'observacion'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function oficinaCap(){
        return $this->belongsTo('App\VOficina', 'op_oficina_id');
    }
    public function oficinaCaf(){
        return $this->belongsTo('App\VOficina', 'op_oficinaf_id');
    }

    public function regimen(){
        return $this->belongsTo('App\Regimen', 'op_regimen_id');
    }

    public function ocupadoCap(){
        //return $this->belongsToMany('App\Persona', 'persona', 'op_plaza_tit_id', 'role_id');
        return $this->belongsToMany('App\Persona', 'persona_has_plaza_tit', 'op_plaza_tit_id', 'persona_id');
    }

    public function ocupadoCaf(){
        return $this->belongsToMany('App\Persona', 'persona_has_plaza_fun', 'op_plaza_fun_id', 'persona_id');
    }


}
