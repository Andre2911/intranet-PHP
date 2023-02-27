<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OpblCargo extends Model{
    //
    use Notifiable;

    protected $table = 'opbl_cargos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'persona_id',
        'cargos_id',
    ];


}
    