@extends('layouts.usuario')

@section('content')
	<sij-cupones :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}, app:{{$app}} }"></sij-cupones>
@endsection