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
                                                <label><i class="text-red">*</i>usuarios</label>
                                                <select class="form-control select2" name="f_usuario" id="f_usuario">
                                                    <option>- Seleccione una opción -</option>
                                                    @foreach($usuarios as $usuario)
                                                        <option value="{{ $usuario->id }}" {{ $usuario->id == old('f_usuario') ? 'selected' : '' }}>
                                                            {{ $usuario->username.' ['.$usuario->persona->ape_paterno.' '.$usuario->persona->ape_materno.', '.$usuario->persona->nombre.'] ' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('f_usuario'))
                                                    <p class="text-red">{{ $errors->first('f_jurado') }}</p>
                                                @endif
                                            </div>

                                            <div class="form-group">
                                                <label><i class="text-red">*</i>Sedes</label>
                                                <select class="form-control" name="f_sede" id="f_sede">
                                                    <option>- Seleccione una opción -</option>
                                                    @foreach($sedes as $sede)
                                                        <option value="{{ $sede->id }}" {{ $sede->id == old('f_sede') ? 'selected' : '' }}>{{ $sede->n_sede }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('f_sede'))
                                                    <p class="text-red">{{ $errors->first('f_evento') }}</p>
                                                @endif
                                            </div>

                                            <div class="form-group">
                                                <label><i class="text-red">*</i>Especialidad</label>
                                                <select multiple class="form-control" name="f_especialidad[]" id="f_especialidad">
                                                    @foreach($especialidades as $especialidad)
                                                        <option value="{{ $especialidad->id }}" {{ $especialidad->id == old('f_especialidad') ? 'selected' : '' }}>{{ $especialidad->descripcion }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('f_especialidad'))
                                                    <p class="text-red">{{ $errors->first('f_evento') }}</p>
                                                @endif
                                            </div>

                                        </div>

                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" style="margin: 10px 0px 10px 0px;">
                                            <button type="submit" class="btn btn-success pull-right btn-block btn-sm">{{config('global.sis_btn_guardar')}}</button>
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
        $(function () {
            $('.select2').select2()
        });
    </script>
@endsection
