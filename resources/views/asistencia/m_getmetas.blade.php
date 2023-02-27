@extends('layouts.usuario')

@section('content')

	<asistencia-metas-trabajadores :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-metas-trabajadores>
@endsection