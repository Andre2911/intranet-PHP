@extends('errors::illustrated-layout')

@section('code', '404')
@section('title', __('Not Found'))

@section('image')
    <div style="background-image: url({{ asset('/public/image/fachadacorte.jpg') }});" class="absolute pin  bg-no-repeat md:bg-left lg:bg-center">
    </div>
@endsection

@section('message', __($exception->getMessage() ?: 'Esta página no existe'))
