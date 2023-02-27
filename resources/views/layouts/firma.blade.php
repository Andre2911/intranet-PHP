<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Corte Superior de Justicia de Puno :: Sistemas Internos </title>
    <meta name="description" content="Corte Superior de Justicia de Puno">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ url('public/image/logo.png') }}">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">

    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="{{ asset('/css/materialdesignicons.min.css') }}" rel="stylesheet">
  
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <script type="text/javascript" src="https://dsp.reniec.gob.pe/refirma_invoker/resources/js/clientclickonce.js"></script>

    <script type="text/javascript">	
        //<![CDATA[
           /* window.addEventListener('getArguments', function (e) {					
                type = e.detail;					
                if(type === 'W'){
                    jsRefirmaWebParamWeb(); // llama a getArguments al terminar
                }else if(type === 'L'){
                    jsRefirmaWebParamLocal(); // llama a getArguments al terminar
                } 				    
            });
            function getArguments(){					
                dispatchEventClient('sendArguments', document.getElementById("view:frmSign:param").value);															
            }
            
            window.addEventListener('invokerOk', function (e) { 
                type = e.detail;		   
                if(type === 'W'){
                    jsRefirmaWebOk();	
                }else if(type === 'L'){
                    jsRefirmaWebOkLocal();	
                }	
                document.location.href='#signed';
            });
            
            window.addEventListener('invokerCancel', function (e) {    
                jsRefirmaWebCancel();	
            });
            //
            function signMobile(url){	
                setTimeout(function () {
                    PF('dlgMobile').show();     		            	
                }, 1500);			
                location.href = url;												
            }*/
        //]]>
    </script>
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
    <input type="hidden" id="argumentos" value="" />
    <div id="addComponent"></div>
    <a id="signedDocument" class="btn btn-info btn-block" href="#" role="button" style="display: none;">VER DOCUMENTO</a>
    

<!-- Right Panel -->

<!-- Scripts -->
<script src="{{ asset('js/app.js?v=0.8') }}"></script>
<script>
    $('body').toggleClass('loaded');
    $('#loader-wrapper').hide()

    ////////////////////////////////////REFIRMA////////////////////////////////////

    function iniciar_firma(){
        motivo = $("#f_motivo").val();
        if(document.getElementById("check_terminos_condiciones").checked){
            if( motivo.length > 0 && pagina > 0 && pos_x > 0 && pos_y > 0 ){ initInvoker('W') }
        }else{
            Toast.fire({
                type: 'error',
                title: ' Debe aceptar los términos condiciones.'
            });
        }
    }

    window.addEventListener('getArguments', function (e) {
        type = e.detail;
        if(type === 'W'){ data_firma_web(); }
    });

    function getArguments(){
        arg = document.getElementById("argumentos").value;
        dispatchEventClient('sendArguments', arg);
    }
/*
    window.addEventListener('invokerOk', function (e) {
        type = e.detail;
        if(type === 'W'){ firma_ok(); }
    });
*/
    window.addEventListener('invokerCancel', function (e) {
        cancelar_firma();
    });

    function data_firma_web(){
        var nombre_archivo = document.getElementById("filepdf").dataset.filename;
        var pagina = 1;
        var motivo = "En señal de conformidad";
        var pos_x = document.getElementById("filepdf").dataset.posx;
        var pos_y = document.getElementById("filepdf").dataset.posy;
        var expediente = document.getElementById("filepdf").dataset.expediente;
        var usuario = document.getElementById("filepdf").dataset.usuario;
        var votacionid = document.getElementById("filepdf").dataset.votacionid;
        var estado = document.getElementById("filepdf").dataset.estado;
       /* $.get("{{ url('/firmadigital/renombrar') }}", {}, function(data, status) {
*/
        var time = Date.now();
        var nuevo_documento = expediente + usuario +'_'+time.toString().substring(0,10)+'.pdf';

            $.post("{{ url('/firmadigital/validarvoto') }}", {
                type : "W",
                votacionid : votacionid,
                nuevo_documento : nuevo_documento,
                documento : nombre_archivo,
                estado : estado,
                pagina : pagina,
                motivo : motivo,
                pos_x : pos_x,
                pos_y: pos_y
            }, function(data, status) {
                //alert("Data: " + data + "\nStatus: " + status);
                document.getElementById("argumentos").value = data;
                getArguments();
            });

       /* });*/
    }
    /*function firma_ok(){
        /*Toast.fire({
            type: 'success',
            title: ' El documento fue firmado correctamente.'
        });*/
        /*$.post("{{ url('votosApi?update=true') }}", {
            votacionid : votacionid,
        }, function(data, status) {
            console.log('El documento fue firmado correctamente.')
        });
*/
      /*  console.log("firmado");
        document.getElementById("d_firmado").value=false;
        //$("#signedDocument").show();

    }

    function cancelar_firma(){
        Toast.fire({
            type: 'error',
            title: ' Ocurrio un error.'
        });
        console.log('El documento fue firmado correctamente.')

        document.getElementById("signedDocument").href="#";
    }
    */
</script>
</body>
</html>