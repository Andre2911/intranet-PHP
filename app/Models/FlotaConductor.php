<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FlotaConductor extends Model{
    //
    use Notifiable;

    protected $table = 'guv_conductor';
    protected $primaryKey = 'conductor_id';

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_users',
        'brevete',
        'categoria',
        'activo',
    ];

}
    