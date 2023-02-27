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
                            <h3 class="box-title"> Busqueda </h3>
                        </div>

                        <div class="box-body table-responsive no-padding">
                            <form action="{{ url( $url_modulo ) }}" method="GET">
                                @csrf
                                <div class="col-xs-12" style="margin: 10px 0px 10px 0px;">
                                    <div class="form-group">
                                        <label>Columna</label>
                                        <select class="form-control" id="b_columna" name="b_columna">
                                            <option value="username" {{ isset($form_busqueda['b_columna'])? $form_busqueda['b_columna'] == 'username' ? 'selected' : '' : '' }} >Username</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Texto:</label>
                                        <input type="text" class="form-control" id="b_texto" name="b_texto" value="{{ isset($form_busqueda['b_texto'])? $form_busqueda['b_texto'] : '' }}">
                                    </div>
                                    <button class="btn btn-info btn-block" id="buscar" name="buscar"><b>{{ config('global.sis_btn_buscar') }}</b></button>
                                </div>
                            </form>
                        </div>
                    </div>

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
                                            ROL<span id="id_icon"></span>
                                        </th>
                                        <th class="text-center sorting" style="cursor: pointer">
                                            USUARIO<span id="id_icon"></span>
                                        </th>
                                        <th class="text-center sorting" style="cursor: pointer">
                                            APELLIDOS Y NOMBRES<span id="id_icon"></span>
                                        </th>
                                        <th class="text-center sorting" style="cursor: pointer">
                                            ESTADO<span id="id_icon"></span>
                                        </th>
                                        <th class="text-center"> </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @if( count($tabla) > 0 )
                                        @foreach ($tabla as $t)
                                            <tr>
                                                <td class="text-center">{{ $t->id }}</td>
                                                <td>
                                                    @if($t->user_role)
                                                        {{ $t->user_role[0]->name }}
                                                    @endif
                                                </td>
                                                <td>{{ $t->username }}</td>
                                                <td>{{ $t->persona->ape_paterno.' '.$t->persona->ape_materno.', '.$t->persona->nombre }}</td>
                                                <td class="text-center">
                                                    @if ( $t->estado == 1 )
                                                        <span class="label label-success">Activo</span>
                                                    @elseif ( $t->estado == 0 )
                                                        <span class="label label-warning">Inactivo</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ url($url_modulo.'/'.$t->id) }}">
                                                        <button type="ver" class="btn btn-block btn-default btn-xs">{{ config('global.sis_btn_ver') }}</button>
                                                    </a>
                                                    <a href="{{ url($url_modulo.'/password/'.$t->id) }}" target="_blank">
                                                        <button type="ver" class="btn btn-block btn-default btn-xs"><i class="fa fa-key"></i>{{ config('global.sis_btn_pass') }}</button>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @elseif( count($tabla) == 0 )
                                        <tr>
                                            <td colspan="5" align="center">
                                                No se encontraron resultados en la busqueda :(
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
