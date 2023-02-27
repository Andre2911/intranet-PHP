@extends('layouts.usuario')

@section('content')
	<personal-files :data-user="{modulos:{{$user_roles}}, usuario:{{$usuario}}}"></personal-files>
@endsection