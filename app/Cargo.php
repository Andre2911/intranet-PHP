<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Cargo extends Model{
    //
    use Notifiable;

    protected $table = 'op_cargos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'c_regimen',
        'c_clase',
        'nombre_cargo',
        'sueldo',
        'user_update',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
