@extends('layouts.usuario')

@section('content')

	<dashboard-mantenimiento :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></dashboard-mantenimiento>
@endsection