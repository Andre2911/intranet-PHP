<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Plaza;
use App\Cargo;

class Persona extends Model{
    //
    use Notifiable;

    protected $table = 'persona';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tipo_documento_id',
        'numero_documento',
        'ap_paterno',
        'ap_materno',
        'nombres',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'telefono',
        'celular',
        'email',
        'fecha_ingreso',
        'estatus',
        'observacion',
    ];

    //protected $visible = ['persona_id', 'numero_documento','ap_paterno', 'ap_materno', 'nombres', 'opbl_academico', 'plaza_fisica', 'opbl_especialidad', 'opbl_cargo', 'opbl_sede'];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function tipo_documento(){
        return $this->belongsTo('App\TipoDocumento', 'tipo_documento_id');
    }

    public function plaza_fisica(){
        return $this->belongsToMany(Plaza::class, 'persona_has_plaza_fun', 'persona_id', 'op_plaza_fun_id');
    }

    public function opbl_academico(){
        return $this->hasMany('App\Models\OpblAcademico', 'persona_id');
        
    }
    
    public function opbl_especialidad(){
        return $this->hasMany('App\Models\OpblEspecialidad', 'persona_id');
    }

    public function opbl_cargo(){
        return $this->belongsToMany(Cargo::class, 'opbl_cargos', 'persona_id', 'op_cargo_id');
    }

    public function opbl_sede(){
        return $this->belongsToMany('App\Models\Ubigeo', 'opbl_sedes', 'persona_id', 'ubigeo_id');
    }
}
