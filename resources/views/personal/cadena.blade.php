@extends('layouts.usuario')

@section('content')
	<personal-cadena :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></personal-cadena>
@endsection