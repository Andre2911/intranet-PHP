@extends('layouts.base')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>&nbsp;</h1>
            <ol class="breadcrumb">
                <li class="active"> <i class="fa fa-angle-right"></i> {{ $titulo }} </li>
                <li class="active"> {{ $sub_titulo }} </li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">{{ $sub_titulo }}</h3>
                            <div class="box-tools">
                                <div class="input-group input-group-sm hidden-xs" style="width: 150px;"></div>
                            </div>
                        </div>
                        <div class="box-body table-responsive no-padding">
                            <section class="content">
                                <form action="{{ url($url_modulo) }}" method="POST" >
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">

                                            <div class="form-group">
                                                <label><i class="text-red">*</i>Cargo :</label>
                                                <select class="form-control" name="f_cargo" id="f_cargo">
                                                    <option disabled>- Seleccione una opción -</option>
                                                    @foreach($cargos as $cargo)
                                                        <option value="{{ $cargo->id }}" {{ $cargo->id == old('f_cargo') ? 'selected' : '' }}>{{ $cargo->nombre }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('f_cargo'))
                                                    <p class="text-red">{{ $errors->first('f_cargo') }}</p>
                                                @endif
                                            </div>

                                            <div class="form-group">
                                                <label><i class="text-red">*</i>Número de Documento :</label>
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control" id="f_numero_documento" name="f_numero_documento" value="{{ old('f_numero_documento') }}">
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info btn-flat" onclick ="buscar_documento();">Validar</button>
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($errors->has('f_numero_documento'))
                                                    <p class="text-red">{{ $errors->first('f_numero_documento') }}</p>
                                                @endif
                                                @if ($errors->has('persona_id'))
                                                    <p class="text-red">{{ $errors->first('persona_id') }}</p>
                                                @endif
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="resultado_documento" id="resultado_documento"></div>
                                            </div>

                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                <br>
                                            </div>

                                            <div class="form-group">
                                                <label><i class="text-red">*</i>Nombres y Apellidos :</label>
                                                <input type="text" class="form-control" id="f_persona" name="f_persona" value="{{ old('f_persona') }}" disabled >
                                            </div>

                                            <div class="form-group">
                                                <label><i class="text-red">*</i>Rol :</label>
                                                <select class="form-control" name="f_rol" id="f_rol">
                                                    <option disabled>- Seleccione una opción -</option>
                                                    @foreach($roles as $rol)
                                                        <option value="{{ $rol->id }}" {{ $rol->id == old('f_rol') ? 'selected' : '' }}>{{ $rol->name }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('f_rol'))
                                                    <p class="text-red">{{ $errors->first('f_rol') }}</p>
                                                @endif
                                            </div>

                                            <div class="form-group ">
                                                <label><i class="text-red">*</i>Email :</label>
                                                <input type="text" class="form-control" id="f_email" name="f_email" value="{{ old('f_email') }}" >
                                                @if ($errors->has('f_email'))
                                                    <p class="text-red">{{ $errors->first('f_email') }}</p>
                                                @endif
                                            </div>


                                            <div class="form-group ">
                                                <label><i class="text-red">*</i>Usuario :</label>
                                                <input type="text" class="form-control" id="f_usuario" name="f_usuario" value="{{ old('f_usuario') }}" >
                                                @if ($errors->has('f_usuario'))
                                                    <p class="text-red">{{ $errors->first('f_usuario') }}</p>
                                                @endif
                                            </div>

                                            <div class="form-group ">
                                                <label><i class="text-red">*</i>Contraseña :</label>
                                                <input type="text" class="form-control" id="f_contrasena" name="f_contrasena" value="{{ old('f_contrasena') }}" >
                                                @if ($errors->has('f_contrasena'))
                                                    <p class="text-red">{{ $errors->first('f_contrasena') }}</p>
                                                @endif
                                            </div>

                                            <div class="form-group">
                                                <label><i class="text-red">*</i> Estado:</label>
                                                <select class="form-control" name="f_estado" id="f_estado">
                                                    <option value="1" {{ '1' == old('f_estado') ? 'selected' : '' }}> Activo </option>
                                                    <option value="0" {{ '0' == old('f_estado') ? 'selected' : '' }}> Inactivo </option>
                                                </select>
                                                @if ($errors->has('f_estado'))
                                                    <p class="text-red">{{ $errors->first('f_estado') }}</p>
                                                @endif
                                            </div>

                                        </div>

                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" style="margin: 10px 0px 10px 0px;">
                                            <button type="submit" class="btn btn-success pull-right btn-block btn-sm">Guardar</button>
                                        </div>

                                    </div>
                                </form>

                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <p class="text-red">(*) Los datos son necesarios.</p>
                                </div>

                            </section>
                        </div>
                    </div>
                    <div class="box">
                        <a href="{{ url($url_modulo) }}">
                            <button type="button" class="btn btn-block btn-default"> <i class="fa fa-arrow-left"></i> {{config('global.sis_btn_atras')}} </button>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>

        function buscar_documento() {
            $.ajax({
                type:'GET',
                url: '{{ url('mantenimiento/persona/numero_documento') }}'+'/'+ $("#f_numero_documento").val(),
                beforeSend:function(){
                    $('#resultado_documento').html('Procesando...');
                },
                success:function(data){
                    $('#resultado_documento').html(data);
                },
                error:function(data){
                    $('#resultado_documento').html('Ocurrio un error.');
                }
            });
        }

        function registrar_persona() {
            $.ajax({
                type: 'POST',
                url: '{{ url('mantenimiento/persona/registrar') }}',
                data: $("#g_form_persona").serialize(),
                beforeSend:function(){
                    $('#resultado_persona').html('Procesando...');
                },
                success:function(data){
                    $('#resultado_persona').html(data);
                },
                error:function(data){
                    $('#resultado_persona').html('Ocurrio un error.');
                }
            });
        }
    </script>

@endsection
