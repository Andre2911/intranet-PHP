@extends('layouts.usuario')

@section('content')

	<repositorio-laboral-registro :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></repositorio-laboral-registro>
@endsection