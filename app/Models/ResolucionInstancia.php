<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ResolucionInstancia extends Model{

    protected $connection = 'sqlsrv_j';

    protected $table = 'scj_resolucion_instancia';

    public $timestamps = false;
    protected $fillable = [
        'scj_resoluciones',
        'c_distrito',
        'c_provincia',
        'c_instancia',
        'c_org_jurisd',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
  
}
