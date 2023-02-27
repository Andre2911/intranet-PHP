<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Comunidad extends Model{
    //
    use Notifiable;

    protected $table = 'sojp_comunidades';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'ubigeo_id',
        'n_comunidad',
        'user_update'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
  

    public function ubigeo(){
        return $this->belongsTo('App\Models\Ubigeo', 'ubigeo_id');
    }
}
