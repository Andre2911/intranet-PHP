@extends('layouts.usuario')

@section('content')
	<sij-dashboard :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}, app:{{$app}} }"></sij-dashboard>
@endsection