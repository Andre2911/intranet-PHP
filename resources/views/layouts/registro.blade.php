<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title> CSJ Puno :: Sistemas Internos</title>
    <meta name="description" content="Mesa de Partes Virtual de la Corte Superior de Justicia de Puno">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ url('public/image/logo.png') }}">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/iof/css/fontawesome-all.min.css') }}">

    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">

    <style>
        .pestana-no-seleccionada {
            color: #0077BB;
            font-weight: bold;
            text-align: center;
            text-align: center;
            border: 1px solid #0077BB;
            padding: 5px;
            cursor: pointer;
        }
        .pestana {
            color: #FFFFFF;
            font-weight: bold;
            text-align: center;
            text-align: center;
            border: 1px solid #0077BB;
            padding: 5px;
            background-color: #008ABB;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div id="right-panel" class="right-panel">
        <div class="content " id="app">
            <div class="animated fadeIn">
                @yield('content')
            </div>
                
        </div>
    </div><!-- /#right-panel -->

<!-- Right Panel -->

<!-- Scripts -->

</body>
<script type="text/javascript" src="{{ asset('public/iof/js/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('public/iof/js/bootstrap.min.js') }}"></script>
<script>
   function soloNumeros(e){
      var key = window.Event ? e.which : e.keyCode
      return ((key >= 48 && key <= 57) || (key==8) || (key==35) || (key==34) || (key==46));
    }

    $('#modaldigito').on('shown.bs.modal', function () {
        console.log('modal abierto')
    })

    function clickLnkDniAzul() {
        $("#divDniAzul").removeClass("pestana-no-seleccionada");
        $("#divDniAzul").addClass("pestana");
        $("#divDniElectronico").removeClass("pestana");
        $("#divDniElectronico").addClass("pestana-no-seleccionada");
        $("#imgDni").attr("src", "https://www.sunat.gob.pe/a/js/irdeducciones/img/dniverificacion.png");
    }

    function clickLnkDniElectronico() {
        $("#divDniAzul").removeClass("pestana");
        $("#divDniAzul").addClass("pestana-no-seleccionada");
        $("#divDniElectronico").removeClass("pestana-no-seleccionada");
        $("#divDniElectronico").addClass("pestana");
        $("#imgDni").attr("src", "https://www.sunat.gob.pe/a/js/irdeducciones/img/electronicoverificacion.png");
    }

</script>
</html>