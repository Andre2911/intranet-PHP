@extends('layouts.pdf_cargo_tramite')
@section('content')
@if($dataAsistencia)
<div class="row" id="boleta">
    <div class="col-s100" >
        <div class="col s12">
            <div class="col-s100 t-center"><b>{{$titulo}}</b></div>
        </div>
    </div>
    <div class="clearfix"/>
    <div class="col-s100" >
        <table id="cargos" width="100%">
            <thead>
               <tr>
                  <th class="box-dark-t" width="5%">N°</th>
                  <th class="box-dark-t" width="65%">Apellidos y nombres</th>
                  <th class="box-dark-t" width="10%">Hora Ingreso</th>
                  <th class="box-dark-t" width="10%">Hora Final</th>
                  <th class="box-dark-t" width="10%">Tipo de trabajo</th>
               </tr>
            </thead>
            <tbody>
                <?php $i = 1; ?>
                @foreach($dataAsistencia as $asistencia)
                    <tr>
                        <td class="box-t">{{$i++}}</td>
                        <td class="box-t">{{$asistencia->persona}}</td>
                        <td class="box-t">{{$asistencia->hora_inicio}}</td>
                        <td class="box-t">{{$asistencia->hora_fin}}</td>
                        <td class="box-t">{{ ($asistencia->tipo == 1)? 'Presencial': 'Remoto'}}</td>
                        
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
 
</div>
@else
<h1>No existe la boleta</h1>

@endif



@endsection