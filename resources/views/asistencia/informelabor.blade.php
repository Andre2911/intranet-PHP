@extends('layouts.usuario')

@section('content')

	<asistencia-reporte-labores :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-reporte-labores>
@endsection