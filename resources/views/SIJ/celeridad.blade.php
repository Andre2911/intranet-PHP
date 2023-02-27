@extends('layouts.usuario')

@section('content')
	<sij-celeridad :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}, app:{{$app}} }"></sij-celeridad>
@endsection

