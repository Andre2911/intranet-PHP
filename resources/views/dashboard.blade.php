@extends('layouts.usuario')

@section('content')

	<dashboard-component :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></dashboard-component>

@endsection