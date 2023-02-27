<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AsistenciaActividad extends Model
{
    //
    protected $table = 'asistencias_actividades';

    protected $primaryKey = 'id';

    public $timestamps = false;

}
