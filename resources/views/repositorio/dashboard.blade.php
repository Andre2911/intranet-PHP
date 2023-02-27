@extends('layouts.usuario')

@section('content')

	<repositorio-dashboard :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></repositorio-dashboard>

@endsection