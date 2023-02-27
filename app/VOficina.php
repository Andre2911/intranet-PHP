<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class VOficina extends Model{
    //
    use Notifiable;

    protected $table = 'view_op_oficinas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        
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

}
