@extends('layouts.usuario')

@section('content')

	<asistencia-excepcion :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-excepcion>
@endsection