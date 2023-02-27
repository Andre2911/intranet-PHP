<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OpblAcademico extends Model{
    //
    use Notifiable;

    protected $table = 'opbl_academico';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'persona_id',
        'tipo',
        'grado_profesional',
        'carrera',
        'universidad',
        'estado',
        'f_expedicion',
    ];


}
    