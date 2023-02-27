@extends('layouts.usuario')

@section('content')

	<personal-licencias-tipos :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"/>
@endsection