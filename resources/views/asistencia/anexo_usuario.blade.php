@extends('layouts.usuario')

@section('content')

	<asistencia-reporte-anexo :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-reporte-anexo>
@endsection