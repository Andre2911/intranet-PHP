<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Oficina extends Model{
    //
    use Notifiable;

    protected $table = 'op_oficinas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $dateFormat = 'Y-d-m H:i:s.v'; // o el formato que te sirva
    protected $guard_name = 'web';

    protected $fillable = [
        'parent_id',
        'nombre_oficina',
        'have_childrens',
        'have_personal',
        'sede_id',
        'bloque_nro',
        'dep_nro',
        'show_cap',
        'show_caf',
        'order_oficina',
        'activo',
        'user_update'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function parent(){
        return $this->belongsTo('App\Oficina', 'parent_id');
    }

    public function view(){
        return $this->belongsTo('App\OficinaView', 'id');
    }

}
