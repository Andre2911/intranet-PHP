<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Especialidad extends Model{
    //
    use Notifiable;

    protected $table = 'especialidad';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'descripcion',
        'abreviatura',
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
