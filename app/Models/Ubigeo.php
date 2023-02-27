<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Ubigeo extends Model{
    //
    use Notifiable;

    protected $table = 'ubigeos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $primaryKey = 'ubigeo_id';

    protected $fillable = [
        'ubigeo_id',
        'c_departamento',
        'n_departamento',
        'c_provincia',
        'n_provincia',
        'c_distrito',
        'n_distrito',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
  
}
