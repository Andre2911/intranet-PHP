@extends('layouts.usuario')

@section('content')
	<stat-dependencias :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></stat-dependencias>
@endsection