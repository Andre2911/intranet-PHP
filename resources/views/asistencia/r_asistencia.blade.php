@extends('layouts.usuario')

@section('content')

	<asistencia-reporte-dia :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-reporte-dia>
@endsection