<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sistemas Corte Superior de Justicia de Puno</title>
    <meta name="description" content="Corte Superior de Justicia de Puno, sistemas internos de la institución">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <link rel="apple-touch-icon" href="{{ $logo }}">
    <link rel="shortcut icon" href="{{ $logo }}">
    <link href="{{ asset('public/css/pdfstyle.css') }}" rel="stylesheet" type='text/css'>
    <link href="{{ asset('public/css/fontOpenSans.css') }}" rel='stylesheet' type='text/css'>
    <link href="{{ asset('public/css/Roboto.css') }}" rel="stylesheet" type='text/css'>
    <style>
        .clearfix:after {
  content: "";
  display: table;
  clear: both;
}

body {
  position: relative;
  width: 100%;  
  margin: 0 auto; 
  color: #001028;
  background: #FFFFFF; 
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
  font-size: 12px; 
}

header {
  padding: 0px 0;
}

#membrete{
  width: 100%;
  display: block;
}

#logo {
  margin-bottom: 10px;
  float: left;
  width: 8%;
  display: inline-block;
}

#logo img {
  width: 60px;
}

#company {
  float: left;
  top: 0px;
  text-align: left;
  display: block;
  width: 29%;
}

#company h3{
  margin: 0px;
  font-size: 12px;
}
#company h4{
  margin: 0px
}

#titulo {
  display: block;
  float: left;
  width:44%;
  border-top: 1px solid  #5D6975;
  border-bottom: 1px solid  #5D6975;
  color: #5D6975;
  font-size: 2em;
  line-height: 1.4em;
  font-weight: normal;
  text-align: center;
  margin: 10px 0 10px 0;
}

#project {
  float: right;
  width: 35%;
  display: block;
}
#project-block {
  float: right;
}

#project span {
  color: #5D6975;
  text-align: right;
  width: 52px;
  margin-right: 10px;
  display: inline-block;
  font-size: 0.8em;
}


#project div,
#company div {
  white-space: nowrap;        
}

main{
	margin: 10px;
	max-width: 100%;
}

table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  margin-bottom: 20px;
}

table tr:nth-child(2n-1) td {
  background: #FFFFFF;
}

table th,
table td {
  /*text-align: center;*/
}

table th {
  padding: 5px 20px;
  color: #2A3642;
  border-bottom: 1px solid #C1CED9;
  white-space: nowrap;        
  font-weight: normal;
  font-size: 14px;
}

table .service,
table .desc {
  text-align: left;
}
table tr{
  text-align: left;
}

table td {
  /*padding: 10px;*/
  font-size: 14px;
  vertical-align: top;
}

table td.service,
table td.desc {
  vertical-align: top;
}

table td.unit,
table td.qty,
table td.total {
  font-size: 1.2em;
}

table td.grand {
  border-top: 1px solid #5D6975;;
}

#notices .notice {
  color: #5D6975;
  font-size: 1.2em;
}

footer {
  color: #5D6975;
  width: 100%;
  height: 10px;
  position: absolute;
  bottom: 0;
  border-top: 1px solid #C1CED9;
  padding: 8px 0;
  text-align: center;
}

a {
  background-color: transparent;
  /* 1 */
  -webkit-text-decoration-skip: objects;
  /* 2 */
}

a {
  /*color: #039be5;*/
  color: #880000;
  text-decoration: none;
  -webkit-tap-highlight-color: transparent;
}

.selectX{
  background-color: #000;
  font-weight: bolder;
}

.selectMotivo{
  
  font-weight: bolder;
}

#boleta{
  font-size: 15px;
}

.box-dark{
  background-color: #DDD;
  border: 1px solid  #5D6975;
  text-align: center;
  padding: 8px 0;
}

.box-dark-t{
  background-color: #DDD;
  border: 1px solid  #5D6975;
  text-align: center;
  padding: 3px 0;
}

.box{
  border: 1px solid  #5D6975;
}
.box-t {
  border: 1px solid  #5D6975;
}
.t-left{
  text-align: left;
}

.t-center{
  text-align: center;
}
.t-right{
  text-align: right;
}

.col-s100 {
  width: 100%;
  
  padding: 3px 0;
}
.col-s90 {
  width: 90%;
  
  padding: 3px 0;
}
.col-s80 {
  width: 80%;
  
  padding: 3px 0;
}
.col-s70 {
  width: 70%;
  
  padding: 3px 0;
}
.col-s60 {
  width: 60%;
  
  padding: 3px 0;
}
.col-s50 {
  width: 50%;
  
  padding: 3px 0;
}
.col-s40 {
  width: 40%;
  
  padding: 3px 0;
}
.col-s30 {
  width: 30%;
  
  padding: 3px 0;
}
.col-s20 {
  width: 20%;
  
  padding: 3px 0;
}

.col-s10 {
  width: 10%;
  
  padding: 3px 0;
}

.left{
  float: left
}
#cargos th{
  font-weight: bold;
  font-size: 11px;
}

#cargos td{
  font-size: 0.75em;
  vertical-align: middle;
}

#cargos tbody td div{
  height: 55px;
  vertical-align: middle;
}

    </style>
</head>
<body>
    <header class="clearfix">
        <div id="membrete">
            <div id="logo" class="col s3">
                <img src="{{$logo}}" width="30px">
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
                <div><span>INTRANET</span> Sistema de Boletas de Permiso</div>
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
                    Copyright © 2019-2021 <a href="{{url('/sys/home')}}">Area de Informática - Unidad de Innovación y Desarrollo.</a> All rights reserved.
                </div>
            </div>
        </div>
    </footer>

</div><!-- /#right-panel -->

<!-- Right Panel -->

<!-- Scripts -->
</body>
</html>
