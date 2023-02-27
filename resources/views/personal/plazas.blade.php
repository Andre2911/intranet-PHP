@extends('layouts.usuario')

@section('content')

	<plazas-personal :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></plazas-personal>
@endsection