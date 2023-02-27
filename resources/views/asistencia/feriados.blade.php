@extends('layouts.usuario')

@section('content')

	<asistencia-feriados :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-feriados>
@endsection