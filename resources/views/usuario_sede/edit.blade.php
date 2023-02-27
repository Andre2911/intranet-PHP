@extends('layouts.base')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>&nbsp;</h1>
            <ol class="breadcrumb">
                <li class="active"> <i class="fa fa-angle-right"></i> {{ $titulo }} </li>
                <li class="active"> {{ $sub_titulo.'"'.$item->usuario->username.'"' }} </li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">{{ $sub_titulo.'"'.$item->usuario->username.'"' }}</h3>
                            <div class="box-tools">
                                <div class="input-group input-group-sm hidden-xs" style="width: 150px;"></div>
                            </div>
                        </div>
                        <div class="box-body table-responsive no-padding">
                            <section class="content">
                                <form method="POST" action="{{ url($url_modulo.'/'.$item->id) }}"  >
                                    @csrf
                                    @method('PATCH')
                                    <div class="row">
                                        <div class="col-md-12">

                                            <div class="form-group">
                                                <label><i class="text-red">*</i>Jurado</label>
                                                <select class="form-control select2" name="f_jurado" id="f_jurado">
                                                    <option>- Seleccione una opción -</option>
                                                    @foreach($jurados as $jurado)
                                                        <option value="{{ $jurado->id }}" {{ $jurado->id == $item->usuario_id ? 'selected' : '' }}>{{ $jurado->username }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('f_jurado'))
                                                    <p class="text-red">{{ $errors->first('f_jurado') }}</p>
                                                @endif
                                            </div>

                                            <div class="form-group">
                                                <label><i class="text-red">*</i>Criterio</label>
                                                <select class="form-control " name="f_criterio" id="f_criterio">
                                                    <option>- Seleccione una opción -</option>
                                                    @foreach($criterios as $criterio)
                                                        <option value="{{ $criterio->id }}" {{ $criterio->id == $item->criterio_id ? 'selected' : '' }}>{{ $criterio->desc_criterio }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('f_criterio'))
                                                    <p class="text-red">{{ $errors->first('f_criterio') }}</p>
                                                @endif
                                            </div>

                                        </div>

                                        <div class="col-xs-12">
                                            <button type="submit" class="btn btn-success pull-right btn-block btn-sm">{{config('global.sis_btn_actualizar')}}</button>
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
