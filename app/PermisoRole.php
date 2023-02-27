<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PermisoRole extends Model{
    //
    use Notifiable;

    protected $table = 'permission_role';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'permission_id',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function permisos(){
        return $this->belongsTo('App\Permiso', 'permission_id');
    }

    public function roles(){
        return $this->belongsTo('App\Rol', 'role_id');
    }

}
