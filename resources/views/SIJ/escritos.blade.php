@extends('layouts.usuario')

@section('content')
	<sij-escritos :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}, app:{{$app}} }"></sij-escritos>
@endsection

