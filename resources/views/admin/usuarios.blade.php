@extends('layouts.usuario')

@section('content')
	<admin-usuarios-component  :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></admin-usuarios-component>
@endsection