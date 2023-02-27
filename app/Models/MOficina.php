<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MOficina extends Model{
    //
    use Notifiable;

    protected $table = 'm_oficina';
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
        'n_dependencia',
        'mofidescri',
        'idubicodigo',
        'idanoproc'
    ];

}
