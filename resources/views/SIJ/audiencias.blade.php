@extends('layouts.usuario')

@section('content')
	<sij-audiencias :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}, app:{{$app}} }"></sij-audiencias>
@endsection