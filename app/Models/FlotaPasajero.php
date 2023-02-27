<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FlotaPasajero extends Model{
    //
    use Notifiable;

    protected $table = 'guv_pasajeros';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'evento_id',
        'user_id',
    ];

}
    