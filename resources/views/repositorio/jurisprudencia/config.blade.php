@extends('layouts.usuario')

@section('content')

	<jurisprudencia-config :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></jurisprudencia-config>
@endsection