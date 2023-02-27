@extends('layouts.usuario')

@section('content')
	<admin-roles-component :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></admin-roles-component>
@endsection