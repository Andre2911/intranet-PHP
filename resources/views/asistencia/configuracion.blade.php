@extends('layouts.usuario')

@section('content')

	<asistencia-configuracion :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-configuracion>
@endsection