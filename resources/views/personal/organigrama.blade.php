@extends('layouts.usuario')

@section('content')

	<organigrama-personal :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></organigrama-personal>
@endsection