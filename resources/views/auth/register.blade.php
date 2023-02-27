@extends('layouts.registro')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-danger mb-3">
                <div class="card-header row">
                    <div class="col-md-10">
                        <h2 class="text-danger">
                            Corte Superior de Justicia de Puno</br>
                            <small><b>Mesa de Partes Virtual :: {{ __('Registro de Usuario') }}</b></small>
                        </h2>
                    </div>
                    <div class="col-md-2">
                        <img src='{{url('public/image/logo.png')}}'>
                    </div>
                    
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('registro') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="tipo_doc" class="col-md-2 col-form-label text-md-right">{{ __('Documento:') }}</label>
                            <div class="col-md-3">
                                <select class="custom-select" id="tipo_doc" name="tipo_doc" value="{{ old('n_doc') }}" autocomplete="n_doc" autofocus>
                                    <option value="1">DNI</option>
                                    <option value="2">Carnet de Extranjeria</option>
                                </select>
                            </div>
                            <label for="n_doc" class="col-md-3 col-form-label text-md-right">{{ __('N° de Documento:  (usuario) ') }}</label>
                            <div class="col-md-3">
                                <input id="n_doc" type="text" class="form-control @error('n_doc') is-invalid @enderror" name="n_doc" value="{{ old('n_doc') }}" onkeypress="return soloNumeros(event);" maxlength="9" required autocomplete="n_doc" autofocus>
                                @error('n_doc')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="digito" class="col-md-3 offset-md-5 col-form-label text-md-right">{{ __('Digito de Verifcación: ') }}</label>
                            <div class="col-md-2">
                                <input id="digito" type="text" class="form-control @error('digito') is-invalid @enderror" name="digito" value="{{ old('digito') }}" onkeypress="return soloNumeros(event);" maxlength="1" required autocomplete="digito" autofocus>
                                @error('digito')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-2" style="display: flex; justify-content: space-between;">
                                <a class="btn btn-primary text-white" data-toggle="modal" data-target="#modaldigito">
                                    ?
                                </a>
                                <a class="btn" data-toggle="modal" data-target="#modaldigito">
                                    <small><i>Donde?</i></small>
                                </a>
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <label for="nombres" class="col-md-2 col-form-label text-md-right">{{ __('Nombres Completos:') }}</label>

                            <div class="col-md-10">
                                <input id="nombres" type="text" class="form-control @error('nombres') is-invalid @enderror" name="nombres" value="{{ old('nombres') }}" onkeyup="javascript:this.value=this.value.toUpperCase();" required autocomplete="nombres" autofocus>

                                @error('nombres')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <label for="ap_paterno" class="col-md-2 col-form-label text-md-right">{{ __('Apellido Paterno:') }}</label>

                            <div class="col-md-4">
                                <input id="ap_paterno" type="text" class="form-control @error('ap_paterno') is-invalid @enderror" name="ap_paterno" value="{{ old('ap_paterno') }}" required onkeyup="javascript:this.value=this.value.toUpperCase();" autocomplete="ap_paterno" autofocus>

                                @error('ap_paterno')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <label for="ap_materno" class="col-md-2 col-form-label text-md-right">{{ __('Apellido Materno:') }}</label>

                            <div class="col-md-4">
                                <input id="ap_materno" type="text" class="form-control @error('ap_materno') is-invalid @enderror" name="ap_materno" value="{{ old('ap_materno') }}" required onkeyup="javascript:this.value=this.value.toUpperCase();" autocomplete="ap_materno" autofocus>

                                @error('ap_materno')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="colegiatura" class="col-md-2 col-form-label text-md-right">{{ __('N° Colegiatura: ') }}</label>
                            <div class="col-md-2">
                                <input id="colegiatura" type="text" class="form-control @error('colegiatura') is-invalid @enderror" name="colegiatura" value="{{ old('colegiatura') }}" onkeypress="return soloNumeros(event);" maxlength="7" required autocomplete="colegiatura" autofocus>
                                @error('colegiatura')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <label for="casilla" class="col-md-2 col-form-label text-md-right">{{ __('Casilla Electrónica: ') }}</label>
                            <div class="col-md-2">
                                <input id="casilla" type="text" class="form-control @error('casilla') is-invalid @enderror" name="casilla" value="{{ old('casilla') }}"  maxlength="7" required autocomplete="colegiatura" autofocus>
                                @error('casilla')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>


                            <label for="solicitante" class="col-md-2 col-form-label text-md-right">{{ __('Tipo de Solicitante: ') }}</label>
                            <div class="col-md-2">
                                <select class="custom-select" id="solicitante" name="solicitante">
                                    <option value="1">Fiscalía</option>
                                    <option value="2">Defensoria Pública</option>
                                    <option value="3">Defensoria Privada</option>
                                </select>
                            </div>
                            
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-2 col-form-label text-md-right">{{ __('Correo Electrónico') }}</label>

                            <div class="col-md-4">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <label for="celular" class="col-md-2 col-form-label text-md-right">{{ __('N° de celular') }}</label>
                            <div class="col-md-3">
                                <input id="celular" type="text" class="form-control @error('celular') is-invalid @enderror" name="celular" value="{{ old('celular') }}" onkeypress="return soloNumeros(event);" maxlength="9" required autocomplete="celular" autofocus>

                                @error('celular')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="direccion" class="col-md-2 col-form-label text-md-right">{{ __('Dirección') }}</label>
                            <div class="col-md-10">
                                <input id="direccion" type="text" class="form-control @error('direccion') is-invalid @enderror" name="direccion" value="{{ old('direccion') }}" required autocomplete="direccion" autofocus>

                                @error('direccion')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-2 col-form-label text-md-right">{{ __('Contraseña (min: 8 caracteres)') }}</label>

                            <div class="col-md-4">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                       
                            <label for="password-confirm" class="col-md-2 col-form-label text-md-right">{{ __('Confirmar Contraseña') }}</label>

                            <div class="col-md-4">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-3 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i>
                                    {{ __('Registrar usuario') }}
                                </button>
                            </div>
                            <div class="col-md-3 offset-md-2">
                                <a href="{{url('login')}}"  class="btn btn-danger">
                                        <i class="fa fa-ban"></i>
                                        {{ __('Cancelar') }}
                            
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="modaldigito" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Datos del DNI</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="col-xs-12 col-md-5">
                <div class="marco">
                       <div class="row">
                           <div id="divDniAzul" onclick="clickLnkDniAzul()" class="col-xs-12 col-md-6 pestana">
                               Azul
                           </div>
                           <div id="divDniElectronico" onclick="clickLnkDniElectronico()" class="col-xs-12 col-md-6 pestana-no-seleccionada">
                               Electrónico
                           </div>
                       </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-12 text-center">
                            <img id="imgDni" src="https://www.sunat.gob.pe/a/js/irdeducciones/img/dniverificacion.png" class="img-responsive center-block">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary " data-dismiss="modal"><i class="fa fa-"></i>  Regresar</button>
        </div>
      </div>
    </div>
  </div>
@endsection
