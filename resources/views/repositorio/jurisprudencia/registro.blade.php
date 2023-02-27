@extends('layouts.usuario')

@section('content')

	<jurisprudencia-registro :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></jurisprudencia-registro>
@endsection