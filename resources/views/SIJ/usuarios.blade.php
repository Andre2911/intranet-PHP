@extends('layouts.usuario')

@section('content')
	<sij-usuario :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}, app:{{$app}} }"></sij-usuario>
@endsection