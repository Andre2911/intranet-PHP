<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class DataRepo extends Model{
    //
    use Notifiable;

    protected $table = 'repo_det_est';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = 'sqlsrv_s';

    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    
    //protected $dateFormat = 'Ymd H:i:s.v'; // o el formato que te sirva

    protected $fillable = [
        'c_distrito'
        ,'c_provincia'
        ,'c_instancia'
        ,'n_dependencia'
        ,'n_instancia_id'
        ,'n_anio_est'
        ,'n_mes_est'
        ,'n_funcion'
        ,'var_id'
        ,'var_des'
        ,'var_letra'
        ,'c_estadistica'
        ,'var_ind'
        ,'var_des_det'
        ,'var_ord'
        ,'var_linea_inf'
        ,'n_var_id'
        ,'n_anio_exp'
        ,'n_num_exp'
        ,'l_ind_exp'
        ,'n_unico'
        ,'n_incidente'
        ,'c_acto_procesal'
        ,'x_desc_acto_procesal'
        ,'fecha_max'
        ,'cantidad_rep'
        ,'c_especialidad'
        ,'c_sub_especialidad'
        ,'f_registro'
        ,'edo_id'
        ,'edo_des_usu'
        ,'x_sesp'
        ,'c_proceso'
        ,'c_materia'
        ,'c_delito'
        ,'x_formato'
        ,'c_incidente'
        ,'c_item'
        ,'c_dni_mag'
        ,'x_magistrado'
        ,'c_dni_sec'
        ,'x_secretario'
        ,'c_sub_especialidad_sij'
        ,'l_ind_flagrancia'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
  
}
