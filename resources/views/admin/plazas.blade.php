@extends('layouts.usuario')

@section('content')
	<admin-plazas-component :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></admin-plazas-component>
@endsection