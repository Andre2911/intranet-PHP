@extends('layouts.usuario')

@section('content')
	<sij-resoluciones :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}, app:{{$app}} }"></sij-resoluciones>
@endsection