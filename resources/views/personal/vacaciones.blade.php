@extends('layouts.usuario')

@section('content')
	<personal-vacaciones :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"/>
@endsection