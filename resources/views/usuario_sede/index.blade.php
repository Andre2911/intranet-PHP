@extends('layouts.base')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>&nbsp;</h1>
            <ol class="breadcrumb">
                <li class="active"> <i class="fa fa-angle-right"></i> {{ $titulo }} </li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-xs-12">

                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title"> {{ $sub_titulo }} </h3>
                        </div>

                        <div class="box-body table-responsive no-padding">

                            @if( $permiso_crear == 1 )
                                <div class="col-xs-12" style="margin: 10px 0px 10px 0px;">
                                    <a href="{{ url($url_modulo.'/create') }}" class="btn btn-success btn-block"><b>{{ config('global.sis_btn_crear') }}</b></a>
                                </div>
                            @endif

                            @if ($message = Session::get('success'))
                                <div class="col-xs-12">
                                    <div class="alert alert-success">
                                        <p>{{ $message }}</p>
                                    </div>
                                </div>
                            @endif

                            @if ($message = Session::get('error'))
                                <div class="col-xs-12">
                                    <div class="alert alert-danger">
                                        <p>{{ $message }}</p>
                                    </div>
                                </div>
                            @endif

                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center sorting" style="cursor: pointer">
                                        ID<span id="id_icon"></span>
                                    </th>
                                    <th class="text-center sorting" style="cursor: pointer">
                                        GRUPO<span id="id_icon"></span>
                                    </th>
                                    <th class="text-center"> </th>
                                </tr>
                                </thead>
                                <tbody>

                                @if( count($tabla) > 0 )
                                    @foreach ($tabla as $t)
                                        <tr>
                                            <td>{{ $t->id }}</td>
                                            <td class="">{{ $t->usuario->username.' ['.$t->usuario->persona->ape_paterno.' '.$t->usuario->persona->ape_materno.', '.$t->usuario->persona->nombre.'] ' }}</td>
                                            <td class="">{{ $t->sede->n_sede }}</td>
                                            <td class="text-center">
                                                <a href="{{ url($url_modulo.'/'.$t->id) }}">
                                                    <button type="ver" class="btn btn-block btn-default btn-xs">{{ config('global.sis_btn_ver') }}</button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif( count($tabla) == 0 )
                                    <tr>
                                        <td colspan="5" align="center">
                                            {{ config('global.msj_sin_resultados')  }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="5" align="center">
                                        {!! $tabla->appends($form_busqueda)->links() !!}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
