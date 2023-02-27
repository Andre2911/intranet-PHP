@extends('layouts.usuario')

@section('content')

	<asistencia-metasxmes :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-metasxmes>
@endsection