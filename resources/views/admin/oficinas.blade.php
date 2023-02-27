@extends('layouts.usuario')

@section('content')
	<admin-oficinas-component :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></admin-oficinas-component>
@endsection