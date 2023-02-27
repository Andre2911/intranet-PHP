@extends('layouts.usuario')

@section('content')

	<asistencia-reporte-fecha :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-reporte-fecha>
@endsection