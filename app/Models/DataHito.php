<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class DataHito extends Model{
    //
    use Notifiable;

    protected $table = 'data_hitos';

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
        'f_registro',
        'f_ingreso',
        'l_ind_exp',
        'c_instancia',
        'n_dependencia',
        'n_anio_est',
        'n_mes_est',
        'var_id',
        'c_acto_procesal_hito',
        'x_formato',
        'n_funcion',
        'c_especialidad',
        'c_especialidad_fee',
        'c_proceso',
        'c_delito',
        'c_provincia',
        'c_distrito',
        'c_org_jurisd',
        'c_sede',
        'c_dni_mag',
        'c_dni_sec'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
  
}
