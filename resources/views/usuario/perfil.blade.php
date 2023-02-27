@extends('layouts.usuario')

@section('content')

	<usuario-perfil :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></usuario-perfil>
@endsection