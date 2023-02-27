@extends('layouts.usuario')

@section('content')
	<admin-personas-component :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></admin-persona-component>
@endsection