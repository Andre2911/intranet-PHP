<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FlotaSede extends Model{
    //
    use Notifiable;

    protected $table = 'guv_sedes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'descripcion',
        'ubigeo',
    ];

}
    