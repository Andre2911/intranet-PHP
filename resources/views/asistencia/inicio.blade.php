@extends('layouts.usuario')

@section('content')

	<asistencia-component :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-component>
@endsection