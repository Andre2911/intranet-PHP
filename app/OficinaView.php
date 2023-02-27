<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OficinaView extends Model{
    //
    use Notifiable;

    protected $table = 'view_op_oficinas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $dateFormat = 'Y-d-m H:i:s.v'; // o el formato que te sirva
    protected $guard_name = 'web';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
