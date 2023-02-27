@extends('layouts.base')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>&nbsp;</h1>
            <ol class="breadcrumb">
                <li class="active"> <i class="fa fa-angle-right"></i> {{ $titulo }} </li>
                <li class="active"> {{ $item->username }} </li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="box box-primary">
                        <div class="box-body box-profile">
                            <h3 class="profile-username text-center">{{ $item->username }}</h3>
                            <ul class="list-group list-group-unbordered">

                                <li class="list-group-item">
                                    <b>Cargo :</b>
                                    <a class="pull-right">{{ $item->cargo->nombre }}</a>
                                </li>

                                <li class="list-group-item">
                                    <b>Tipo Documento :</b>
                                    <a class="pull-right">{{ $item->persona->tipo_documento->sigla }}</a>
                                </li>

                                <li class="list-group-item">
                                    <b>Número de Documento :</b>
                                    <a class="pull-right" href="{{ url('persona/'.$item->persona->id) }}" title="Ver mas detalles" style="cursor: pointer;">
                                        {{ $item->persona->numero_documento }}
                                    </a>
                                </li>

                                <li class="list-group-item">
                                    <b>Nombres y Apellidos :</b>
                                    <a class="pull-right" href="{{ url('persona/'.$item->persona->id) }}" title="Ver mas detalles" style="cursor: pointer;">
                                        {{ $item->persona->nombre.' '.$item->persona->ape_paterno.' '.$item->persona->ape_materno }}
                                    </a>
                                </li>

                                <li class="list-group-item">
                                    <b>Usuario :</b>
                                    <a class="pull-right">{{ $item->username }}</a>
                                </li>

                                <li class="list-group-item">
                                    <b>Email :</b>
                                    <a class="pull-right">{{ $item->email }}</a>
                                </li>

                                <li class="list-group-item">
                                    <b>Estado :</b>
                                    <a class="pull-right">
                                        @if ( $item->estado == 1 )
                                            <span class="label label-success">Activo</span>
                                        @elseif ( $item->estado == 0 )
                                            <span class="label label-warning">Inactivo</span>
                                        @endif
                                    </a>
                                </li>

                                <li class="list-group-item">
                                    <b>Fecha de creación :</b>
                                    <a class="pull-right">{{ $item->created_at }}</a>
                                </li>

                                <li class="list-group-item">
                                    <b>Fecha de actualización :</b>
                                    <a class="pull-right">{{ $item->updated_at }}</a>
                                </li>
                            </ul>

                            <div class="form-group">
                                <label for="exampleInputFile">Descripción :</label>
                                <p class="help-block">{{ $item->description }}</p>
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="col-3 col-sm-3 col-md-3 col-lg-12 col-xl-12"></div>
                            <div class="col-3 col-sm-3 col-md-3 col-lg-12 col-xl-12">
                                @if( $permiso_editar == 1 )
                                    <a href="{{ url($url_modulo.'/'.$item->id.'/edit') }}" class="btn btn-warning btn-block">{{config('global.sis_btn_editar')}}</a>
                                @endif
                            </div>
                            <div class="col-3 col-sm-3 col-md-3 col-lg-12 col-xl-12">
                                @if( $permiso_eliminar == 1 )
                                    <form name="form_delete" id="form_delete" action="{{ route('usuario.destroy', $item->id)}}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-block" type="submit"><b>{{config('global.sis_btn_eliminar')}}</b></button>
                                    </form>
                                @endif
                            </div>
                            <div class="col-3 col-sm-3 col-md-3 col-lg-12 col-xl-12"></div>
                        </div>

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12"><br></div>

                        <div class="row">
                            <a href="{{ url($url_modulo) }}">
                                <button type="button" class="btn btn-block btn-default"> <i class="fa fa-arrow-left"></i> {{config('global.sis_btn_atras')}} </button>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    <script type="text/javascript">
    </script>

@endsection
