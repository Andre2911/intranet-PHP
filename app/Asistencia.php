<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model{
    //
    protected $table = 'asistencias';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fecha',
        'user_id',
        'hora_inicio',
        'refrigerio_ini',
        'refrigerio_fin',
        'hora_fin',
        'hora_inicio2',
        'hora_fin2',
        'observación',
        'ip_registro',
        'ip_registro2'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    public function persona(){
        return $this->belongsTo('App\User', 'user_id');
    }

    public function actividades(){
        return $this->hasMany('App\AsistenciaActividad','asistencia_id');
        //return $this->belongsToMany('App\AsitenciaActividad', 'permission_role', 'role_id', 'permission_id');
    }
}
