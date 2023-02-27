@extends('layouts.usuario')

@section('content')

	<repositorio-laboral-buscador :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></repositorio-laboral-buscador>
@endsection