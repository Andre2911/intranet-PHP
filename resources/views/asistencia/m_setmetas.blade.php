@extends('layouts.usuario')

@section('content')

	<asistencia-metas :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></asistencia-metas>
@endsection