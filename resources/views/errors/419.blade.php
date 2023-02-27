@extends('errors::illustrated-layout')

@section('code', '419')
@section('title', __('Forbidden'))

@section('image')
    <div style="background-image: url({{ asset('/public/image/fachadacorte.jpg') }});" class="absolute pin  bg-no-repeat md:bg-left lg:bg-center">
    </div>
@endsection

@section('message', __($exception->getMessage() ?: 'Sesión expirada, vuelva a ingresar'))
