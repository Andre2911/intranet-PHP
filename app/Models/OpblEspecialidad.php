<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OpblEspecialidad extends Model{
    //
    use Notifiable;

    protected $table = 'opbl_especialidades';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'persona_id',
        'especialidad_id',
    ];


}
    