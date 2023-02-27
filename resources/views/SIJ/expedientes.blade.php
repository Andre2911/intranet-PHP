@extends('layouts.usuario')

@section('content')
	<sij-expedientes :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}, app:{{$app}} }"></sij-expedientes>
@endsection