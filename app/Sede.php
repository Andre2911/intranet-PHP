<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Sede extends Model{
    //
    use Notifiable;

    protected $table = 'sedes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'n_sede',
        'direccion',
        'ubigeo',
        'latitud',
        'longitud'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function ubigeo(){
        return $this->belongsToMany('App\Permiso', 'permission_role', 'role_id', 'permission_id');
    }

}
