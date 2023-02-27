<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Repositorio extends Model{

    protected $connection = 'sqlsrv_j';

    protected $table = 'scj_resoluciones';
    protected $primaryKey = 'id_ingreso';

    public $timestamps = false;
    protected $fillable = [
        'anio_res',
        'mes_res',
        'dia_res',
        'exp_idcompleto',
        'exp_idunico',
        'exp_incidencia',
        'exp_f_descargo',
        'r_archivoword',
        'r_archivopdf',
        'crawlerpdf',
        'c_especialidad',
        'c_materia',
        'c_acto_procesal',
        'x_formato',
        'asunto',
        'txt_demandante',
        'txt_demandado',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
  
}
