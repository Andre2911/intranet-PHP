@extends('layouts.usuario')

@section('content')

	<jurisprudencia-buscador :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></jurisprudencia-buscador>
@endsection