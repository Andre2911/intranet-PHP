<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Instancia extends Model{
    //
    use Notifiable;

    protected $table = 'instancia';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = 'sqlsrv_s';

    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'c_distrito',
        'c_provincia',
        'c_instancia',
        'c_org_jurisd'
        ,'x_nom_instancia'
        ,'n_instancia'
        ,'x_corto'
        ,'c_sede'
        ,'c_ubigeo'
        ,'l_ind_baja'
        ,'n_instancia_id'
    ];

}
