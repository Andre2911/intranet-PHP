@extends('layouts.base')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                <small>Inicio</small>
            </h1>
            <ol class="breadcrumb">
                <?php $dia = ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"]; ?>
                <?php $mes = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre']; ?>
                <li> {{ $dia[date('w')].', '.date('j').' de '.$mes[date('n')-1].' de '.date('Y').'.' }} </li>
            </ol>
        </section>

        <section class="content">
            <p>PLATAFORMA VIRTUAL DE MESA DE PARTES</p>
        </section>
    </div>
@endsection
