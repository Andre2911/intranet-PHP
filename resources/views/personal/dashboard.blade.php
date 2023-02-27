@extends('layouts.usuario')

@section('content')

	<crud-personal-component :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></crud-personal-component>
@endsection