<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Caffeinated\Shinobi\Concerns\HasRolesAndPermissions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable{
    //
    use Notifiable;
    use HasRoles;

    //use HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'persona_id',
        'username',
        'password',
        'estado',
        'email',
    ];


    protected $dateFormat = 'Y-d-m H:i:s.v'; // o el formato que te sirva
    protected $guard_name = 'web';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function persona(){
        return $this->belongsTo('App\Persona', 'persona_id');
    }

    /*public function rol_user(){
        return $this->belongsTo('App\RoleUser', 'id');
    }*/

    public function user_role(){
        return $this->belongsToMany('App\Rol', 'model_has_role', 'user_id', 'role_id');
    }

}
