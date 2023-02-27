<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OpblSede extends Model{
    //
    use Notifiable;

    protected $table = 'opbl_sedes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'persona_id',
        'sedes_id',
    ];


}
    