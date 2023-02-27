<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Rol extends Model{
    //
    use Notifiable;

    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];
    protected $dateFormat = 'Y-d-m H:i:s.v'; // o el formato que te sirva

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function permiso(){
        return $this->belongsToMany('App\Permiso', 'permission_role', 'role_id', 'permission_id');
    }

}
