<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Corte Superior de Justicia de Arequipa :: Sistemas Internos </title>
    <meta name="description" content="Corte Superior de Justicia de Arequipa">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ url('public/image/logo.png') }}">

   <!--  <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="https://i.imgur.com/QRAUqs9.png"> -->

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!--<link rel="stylesheet" href="{{ asset('/css/colorpicker.css') }}">-->
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    <link href="{{ asset('css/Roboto.css') }}" rel="stylesheet">

    
    <!--<link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">-->
    <link href="{{ asset('/css/materialdesignicons.min.css') }}" rel="stylesheet">
    <!--<link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">-->
    <!--<link href="{{ asset('/css/vuetify.min.css') }}" rel="stylesheet">-->
  
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">

    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/html5shiv/3.7.3/html5shiv.min.js"></script> -->

</head>

<body>

    <div id="loader-wrapper">
        <div id="loader"></div>
     
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
     
    </div>


    <div id="right-panel" class="right-panel">
        <div class="content " id="app">
            <div class="animated fadeIn">
                @yield('content')
            </div>
                
        </div>
    </div><!-- /#right-panel -->

<!-- Right Panel -->

<!-- Scripts -->
<script src="{{ asset('js/app.js?v=1.221019') }}"></script>

<script>
    $('body').toggleClass('loaded');
    $('#loader-wrapper').hide()
</script>
</body>
</html>