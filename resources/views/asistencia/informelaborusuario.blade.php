@extends('layouts.usuario')

@section('content')

	<asistencia-reporte-labor :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-reporte-labor>
@endsection