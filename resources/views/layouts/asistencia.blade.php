<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sistemas Corte Superior de Justicia de Puno</title>
    <meta name="description" content="Ela Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <link rel="apple-touch-icon" href="{{ $logo }}">
    <link rel="shortcut icon" href="{{ $logo }}">
    <link href="{{ asset('public/css/pdfstyle.css') }}" rel="stylesheet">
    <link href="{{ asset('public/css/fontOpenSans.css') }}" rel='stylesheet' type='text/css'>
    <link href="{{ asset('public/css/Roboto.css') }}" rel="stylesheet">
    
</head>
<body>
    <header class="clearfix">
        <div id="membrete">
            <div id="logo" class="col s3">
                <img src="{{$logo}}" width="20px">
            </div>      
            <div id="company" class="clearfix">
                <h3>Poder Judicial</h3>
                <h3>Corte Superior de Justicia de Puno</h3>
                <h4>Gerencia de Administración Distrital</h4>
                <div><a mailto="informatica_puno@pj.gob.pe">informatica_puno@pj.gob.pe</a></div>
            </div>
        </div>
        <div id="project">
            <div id="project-block">
                <div><span>INTRANET</span> Módulo de Asistencia</div>
                <?php setlocale(LC_ALL,"es_ES");?>
                <div><span>Fecha</span> {{date("F j, Y, g:i a")}} </div>    
            </div>
        </div>
    </header>
    <main>

                @yield('content')
    
    </main>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="row">
                <div class="col-sm-6 text-right offset-sm-6">
                    Copyright © 2019-2021 <a href="{{url('/sys/home')}}">Area de Informática - Sub área de Innovación y Desarrollo.</a> All rights reserved.
                </div>
            </div>
        </div>
    </footer>

</div><!-- /#right-panel -->

<!-- Right Panel -->

<!-- Scripts -->
</body>
</html>
