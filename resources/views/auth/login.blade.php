<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('public/image/logo.png') }}">
    <title>Sistemas Intranet Corte Superior de Justicia de Arequipa</title>
    <meta name="description" content="Corte Superior de Justicia de Arequipa, sistemas internos de la institución">

    <link rel="stylesheet" type="text/css" href="{{ asset('public/iof/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/iof/css/fontawesome-all.min.css') }}">
</head>
<style>
    body{
        /* background: -webkit-linear-gradient(-135deg, #c850c0, #4158d0); */
        width: 100%;
        min-height: 100vh;
        display: -webkit-box;
        display: -webkit-flex;
        display: -moz-box;
        display: -ms-flexbox;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        padding: 15px;
        background: linear-gradient(-275deg, #660600, #999999);
    }
    .js-tilt{will-change: transform;transform: perspective(300px) rotateX(0deg) rotateY(0deg);}
    .login-block{float:left;width:100%;padding : 50px 0;}
    .banner-sec{background-size:cover; border-radius: 0 10px 10px 0; padding:0;}
.container{background:#fff; border-radius: 10px; box-shadow:15px 20px 0px rgba(0,0,0,0.1);}
.carousel-inner{border-radius:0 10px 10px 0;}
.carousel-item{max-height: 420px}
.carousel-caption{text-align:left; left:0%; top:20px;}
.carousel-indicators{top:15px; left:60%; }
.carousel-indicators li{height: 10px; background-color:#b50000 }
.carousel-indicators .active{height: 12px; background-color:#989898 }

.login-sec{padding: 30px 30px; min-height: 420px; max-height: 500px}
.login-sec .copy-text{
    position:absolute;
    width:70%; bottom:0px; font-size:10px; text-align:center;}
.login-sec .copy-text i{color:#b50000;}
.login-sec .copy-text a{color:#b50000;}
.login-sec h2{margin-bottom:30px; font-weight:800; font-size:30px; color: #b50000;}
.login-sec h2:after{content:" "; width:100px; height:5px; background:#989898; display:block; margin-top:20px; border-radius:3px; margin-left:auto;margin-right:auto}
.btn-login{background: #DE6262; color:#fff; font-weight:600;}
.banner-text{width:70%; position:absolute; top:-20px; padding-left:12px;  padding-top: 10px; background: rgba(100, 100, 100, 0.6);border-radius:0 10px 10px 0;}
.banner-text h2{color:rgb(255, 255, 255); font-weight:400; font-size: 24px}
.banner-text h2:after{content:" "; width:100px; height:5px; background:#FFF; display:block; margin-top:10px; border-radius:3px;}
.banner-text p{color:#fff;}
.logo{display:block;margin:auto;}
.slider-fullscreen-image {
    height: 100%;
    background: transparent !important;
}
.mbr-slider.slide .container {
    overflow: hidden;
    padding: 0;
}
.carousel-item .container.container-slide {
    position: initial;
    width: auto;
    min-height: 0;
}
.carousel-item .container-slide {
    text-align: center;
}
.image_wrapper {
    height: 420px;
    width: 100%;
    position: relative;
    vertical-align: middle;
    text-align: center;
    display: table-cell;
}
.image_wrapper img {
    width: 100%;
    
}
.alert{
    margin-bottom: 0rem;
}
.form-group {
    margin-bottom: 5px;
}

</style>
<body >



<section class="login-block">
    <div class="container">
        <div class="row">
            <div class="col-md-4 login-sec">
                <div class="col-md-12 row">
                    <div class="login100-pic js-tilt col-sm-3 col-md-4">
                        <img src='{{url('public/image/logo_csjp.png')}}' class="logo" width="80px" height="90px">
                    </div>
                    <div class="col-md-8 col-sm-6">
                        <h4 class="text-center">Corte Superior de Justicia de Arequipa</h4>
                    </div>
                </div>
                <form method="POST" class="login-form col-md-12" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    @error('username')
                    <div class="alert alert-danger alert-dismissible" style="font-size: 12px;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ $message }}
                    </div>
                    @enderror
                    <div class="form-group">
                        <label for="username" class="col-form-label text-md-right font-weight-bold">Usuario :</label>
                        <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autofocus placeholder="Usuario">
                    </div>
                    <div class="form-group">
                        <label for="password" class="col-form-label text-md-right font-weight-bold">Contraseña :</label><br>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Contraseña">
                    </div>
                    <div class="form-button">
                        <button id="submit" type="submit" class="ibtn btn btn-success float-right"><i class="fa fa-sign-in-alt"></i> Ingresar</button>
                    </div>
                </form>
                <div class="copy-text">Desarrollado <i class="fa fa-user"></i> por <a href="http://csjArequipa.pe">Área de Innovación y Desarrollo</a></div>
            </div>
            <div class="col-md-8 banner-sec">
                <div id="carouselExampleIndicators" class="mbr-slider slide carousel" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                        <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                        <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                    </ol>
                    <div class="carousel-inner" role="listbox">
                        <div class="carousel-item slider-fullscreen-image active">
                            <div class="container container-slide">
                                <div class="image_wrapper">
                                    <div class="carousel-caption d-none d-md-block">
                                        <div class="banner-text">
                                            <h2>Corte Superior de Justicia de Arequipa</h2>
                                            <p></p>
                                        </div>	
                                    </div>
                                    <img src="public/image/fachadacorte.jpg" alt="First slide">
                                </div>
                            </div>
                            
                        </div>
                        
                        
                    </div>	   
                
                </div>
            </div>
        </div>
    </div>
</section>


   

    <!-- Modal -->
<script type="text/javascript" src="{{ asset('public/iof/js/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('public/iof/js/bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('public/iof/js/tilt.jquery.min.js') }}"></script>


<script>
		$('.js-tilt').tilt({
			scale: 1.1
		})
	
</script>

</body>
</html>

