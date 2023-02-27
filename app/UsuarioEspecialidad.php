<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UsuarioEspecialidad extends Model{
    use Notifiable;

    protected $table = 'usuario_especialidad';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'usuario_id',
        'especialidad_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function usuario(){
        return $this->belongsTo('App\User', 'usuario_id');
    }

    public function sede(){
        return $this->belongsTo('App\Especialidad', 'especialidad_id');
    }

}
