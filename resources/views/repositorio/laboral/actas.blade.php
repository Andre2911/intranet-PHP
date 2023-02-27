@extends('layouts.usuario')

@section('content')

	<repositorio-laboral-actas :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></repositorio-laboral-actas>
@endsection