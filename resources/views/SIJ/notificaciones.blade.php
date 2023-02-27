@extends('layouts.usuario')

@section('content')
	<sij-notificaciones :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}, app:{{$app}} }"></sij-notificaciones>
@endsection