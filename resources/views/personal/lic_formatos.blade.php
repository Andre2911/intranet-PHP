@extends('layouts.usuario')

@section('content')

	<personal-licencias-formatos :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"/>
@endsection