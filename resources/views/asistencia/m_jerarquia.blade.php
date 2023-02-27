@extends('layouts.usuario')

@section('content')

	<asistencia-jerarquia :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-jerarquia>
@endsection