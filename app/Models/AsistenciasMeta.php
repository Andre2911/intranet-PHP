<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AsistenciasMeta extends Model{
    //
    use Notifiable;

    protected $table = 'asistencias_metas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $dateFormat = 'Y-d-m H:i:s.v'; // o el formato que te sirva

    protected $fillable = [
        'actividad',
        'b_sij',
        'activo',
        'parent_id',
        'user_update',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
  
}
