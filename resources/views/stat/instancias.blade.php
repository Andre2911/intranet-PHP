@extends('layouts.usuario')

@section('content')
	<stat-instancias :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></stat-instancias>
@endsection