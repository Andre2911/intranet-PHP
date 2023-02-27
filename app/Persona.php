<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Plaza;

class Persona extends Model{
    //
    use Notifiable;

    protected $table = 'persona';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tipo_documento_id',
        'numero_documento',
        'ape_paterno',
        'ape_materno',
        'nombre',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'telefono',
        'celular',
        'colegiatura',
        'casilla',
        'email',
        'estatus',
        'persona_padre',
        'persona_madre',
        'observacion',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function padre(){
        return $this->belongsTo('App\Persona', 'persona_padre');
    }

    public function madre(){
        return $this->belongsTo('App\Persona', 'persona_madre');
    }
    public function tipo_documento(){
        return $this->belongsTo('App\TipoDocumento', 'tipo_documento_id');
    }

    public function plaza_fisica(){
        return $this->belongsToMany(Plaza::class, 'persona_has_plaza_fun', 'persona_id', 'op_plaza_fun_id');
    }

}
