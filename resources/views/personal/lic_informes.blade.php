@extends('layouts.usuario')

@section('content')

	<personal-licencias-informes :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"/>
@endsection