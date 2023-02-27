@extends('layouts.usuario')

@section('content')

	<historico-asistencia-component :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></historico-asistencia-component>
@endsection