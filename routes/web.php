<?php
use App\Mobile_Detect;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Route::get('/', function () { return view('welcome'); });
//Auth::routes();
//Route::get('/home', 'HomeController@index')->name('home');

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/registro', 'Auth\RegisterController@showRegistrationForm')->name('registro');
Route::post('/registro', 'Auth\RegisterController@registrar')->name('registro');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/qrcode', 'HomeController@qrCode');


Route::get('/consultaDNI/{dni}', 'ToolController@getDNI');
Route::get('/consultaDNIu/{dni}', 'ToolController@getDNIu');
Route::get('/consultaDNIi/{dni}', 'ToolController@getDNIi'); //consulta completa


Route::get('/', function () {
    return redirect('dashboard');
});

Route::get('/home', function () {
    return redirect('inicio');
})->name('home');

Route::group(['middleware' => ['auth'] ], function () {
    //Inicio
    Route::get('dashboard', 'Dashboard@index');
});

Route::group(['prefix' => 'sys' ], function () {
    Route::get('home', function(){
        return redirect('inicio');
    });
    return redirect('inicio');
});

/*--------------------------------------------------------------------------
| Rutas Modulo ASISTENCIA ESTADO DE EMERGENCIA
|--------------------------------------------------------------------------*/


Route::group(['middleware' => ['auth'], 'prefix' => 'asistencia' ], function () {
    Route::get('/', function(){
        return redirect('asistencia/inicio');
    });
    Route::get('reporte', function(){
        return view('asistencia/reporte');
    ;});
    Route::get('/reporte/listpersonal','Asistencias\AsistenciaController@Listapersonal');
    Route::get('/reporte/listLaborRemota','Asistencias\AsistenciaController@ListalaborRemota');
    Route::get('/reporte/listLabor/{fecha}','Asistencias\AsistenciaController@Listalabor');
    Route::post('/reporte/regularizaActividades/{asistenciaID}',"Asistencias\AsistenciaController@RegularizaLabor");
    Route::post('/doexcepcion',"Asistencias\AsistenciaController@Excepcion");
    Route::put('/doexcepcion/{oficinaID}',"Asistencias\AsistenciaController@Excepcion");
    Route::get('/buscar', function () { return view('intranet\repositorio\buscar');});

    Route::apiResource('/asistenciaApi',"Asistencias\AsistenciaController");
    Route::apiResource('/oficinasApi', "Personal\OficinaController");
    Route::apiResource('/userLockApi',"Asistencias\UserLockController");
    Route::apiResource('personaApi', 'Admin\PersonaController');

    Route::get('reporteasistencia/{fecha}', 'Asistencias\AsistenciaController@reporteDiaPDF')->name('reporteasistenciadia.pdf');
    Route::get('reporteinformedia2/{fecha}', 'Asistencias\AsistenciaController@reporteInformeDia2')->name('reporteinformedia2.pdf');
    Route::get('reporteinformeusuario/{usuario}/{finicio}/{ffin}', 'Asistencias\AsistenciaController@reporteUsuario')->name('reporteusurio.pdf');
    Route::post('/getAnexosTrabajadores',"Asistencias\AsistenciaController@getMetasTrabajadores");
    Route::post('/getAsistenciaUser',"Asistencias\AsistenciaController@getAsistencia");
    Route::post('/updateAsistenciaUser',"Asistencias\AsistenciaController@updateAsistencia");
    Route::post('/createAsistenciaUser',"Asistencias\AsistenciaController@createAsistencia");


    /********** METAS */
    Route::apiResource('/metasApi', "Asistencias\MetasController");
    Route::post('/doparents',"Asistencias\MetasController@Parents");
    Route::put('/doparents/{oficinaID}',"Asistencias\MetasController@Parents");
    Route::post('/setActivity',"Asistencias\MetasController@Activity");
    Route::put('/setActivity/{actividadID}',"Asistencias\MetasController@Activity");

    Route::post('/setMetasXMes',"Asistencias\MetasController@MetasMes");
    Route::post('/getMetasXMes',"Asistencias\MetasController@getMetasMes");
    Route::post('/openMetasXMes',"Asistencias\MetasController@openMetasMes");
    Route::post('/getMetasTrabajadores',"Asistencias\MetasController@getMetasTrabajadores");
    Route::post('/getMetasTrabajadoresXls',"Asistencias\MetasController@getMetasTrabajadoresXLS");

    Route::get('SIJgetMetas', 'Asistencias\ConsultaController@consultaMeta');
    Route::put('/asistenciaMetas/{asistenciaID}',"Asistencias\AsistenciaController@salidaMetas");
    
    Route::get('/getFeriados',"Asistencias\AsistenciaController@getFeriado");
    Route::post('/setFeriado',"Asistencias\AsistenciaController@Feriado");
    Route::put('/setFeriado/{feriadoID}',"Asistencias\AsistenciaController@Feriado");

    Route::get('/getConfiguracion',"Asistencias\AsistenciaController@getConfiguracion");
    Route::post('/setConfiguracion',"Asistencias\AsistenciaController@Configuracion");

    Route::get('anexo04/{usuario}/{anio}/{mes}', 'Asistencias\MetasController@anexo04')->name('anexo04.pdf');
    Route::post('anexo04/{usuario}/{anio}/{mes}', 'Asistencias\MetasController@anexo04')->name('anexo04.pdf');
    Route::get('vbanexo04/{usuario}/{filename_anexo}', 'Asistencias\MetasController@vbanexo04')->name('vbanexo04.pdf');

    Route::get('{vista?}',  'Asistencias\AsistenciaController@adminvista');

});



/*--------------------------------------------------------------------------
| Rutas Modulo PERSONAL
|--------------------------------------------------------------------------*/
    Route::group(['middleware' => ['auth'], 'prefix' => 'personal'], function () {
        
        Route::get('/', function(){
            return redirect('personal/organigrama');
        });

        Route::apiResource('oficinasApi', "Personal\OficinaController");
        Route::apiResource('/personalApi', "Personal\PersonalController");
        Route::apiResource('/plazasApi', "Personal\PlazasController");
        Route::apiResource('personaApi', 'Admin\PersonaController');
        Route::apiResource('/licenciasApi', 'Personal\LicenciaController');
        Route::post('/registerVacaciones' , 'Personal\LicenciaController@vacaciones');
        Route::put('/deleteVacaciones/{id}' , 'Personal\LicenciaController@vacaciones');
        
        Route::apiResource('/cadenaApi', 'Personal\CadenaController');
        Route::get('/download/{file}' , 'Personal\LicenciaController@downloadFile');

        Route::get('/organigramaExc' , 'Personal\OficinaController@excOrganigrama');
        Route::post('/ficheroExcel' , 'Personal\OficinaController@ficheroExcel');

        Route::apiResource('/filespersonalApi', 'Personal\FilesController');


        Route::get('{vista?}',  'Personal\PersonalController@adminvista');
    });



/*--------------------------------------------------------------------------
| Rutas Modulo REPORTES SIJ
|--------------------------------------------------------------------------*/

Route::group(['middleware' => ['auth'], 'prefix' => 'utilsij'], function () {
            
    Route::get('/', function(){
        return redirect('utilsij/inicio');
    });

    Route::apiResource('utilSIJApi', "SIJ\UtilSIJController");
    Route::get('SIJgetRegUsuario', 'SIJ\ConsultaController@consultaXusuario');

    Route::get('{vista?}',  'SIJ\UtilSIJController@adminvista');

});

/*--------------------------------------------------------------------------
| CONSULTAS SIJ
|--------------------------------------------------------------------------*/
//Route::apiResource('consultasSIJ', 'SIJ\ConsultaController');
Route::group(['middleware' => ['auth'], 'prefix' => 'consultasSIJ'], function () {

    Route::get('listarInstancias', 'SIJ\ConsultaController@listarInstancia');
    Route::get('listarInstanciasUser', 'SIJ\ConsultaController@listarInstanciasUser');
    Route::get('listarAudiencias', 'SIJ\ConsultaController@listarAudiencias');
    Route::get('getAudiencia', 'SIJ\ConsultaController@dataAudiencia');
    Route::get('getAudActa', 'SIJ\ConsultaController@dataAudActa');
    Route::get('listarEscritos', 'SIJ\ConsultaController@listarEscritos');
    Route::get('getEscrito', 'SIJ\ConsultaController@dataEscrito');
    Route::get('listarResolucionesrsij', 'SIJ\ConsultaController@listarResolucionesrsij');
    Route::get('getResolucion', 'SIJ\ConsultaController@dataResolucion');
    Route::get('listarResoluciones', 'SIJ\ConsultaController@listarResoluciones');
    Route::get('listarNotificaciones', 'SIJ\ConsultaController@listarNotificaciones');
    Route::get('listarCupones', 'SIJ\ConsultaController@listarCupones');
    
    
    Route::get('listarMOficinas', 'SIJ\ConsultaController@listarMOficinas');
        
    Route::get('listarExpedientes', 'SIJ\ConsultaController@listarExpedientes');
    Route::get('buscarCedulas', 'SIJ\ConsultaController@buscarCedulas');
    Route::get('buscarDigitalizados', 'SIJ\ConsultaController@buscarDigitalizados');
    
    Route::get('listarDataHitos', 'SIJ\ConsultaController@listarDataHitos');
    

    Route::get('reporteEscritos', 'SIJ\ConsultaController@reporteEscritos');
    Route::get('reporteDemandas', 'SIJ\ConsultaController@reporteDemandas');
    Route::get('reporteResoluciones', 'SIJ\ConsultaController@reporteResoluciones');
    Route::get('reporteAudiencias', 'SIJ\ConsultaController@reporteAudiencias');
    Route::get('reporteNotificaciones', 'SIJ\ConsultaController@reporteNotificaciones');

    
    Route::get('downloadFileFTP', 'SIJ\ConsultaController@downloadFileFTP');

});


/*--------------------------------------------------------------------------
| Rutas Modulo RESOLUCIONES JURISPRUDENCIA
|--------------------------------------------------------------------------*/
    Route::group(['middleware' => ['role:Webmaster|Administrador|Jurisprudencia.administrador|Jurisprudencia.usuario','auth'], 'prefix' => 'jurisprudencia'], function ()
    {
        Route::get('/', function(){
            return redirect('jurisprudencia/dashboard');
        });

        Route::get('/dashboard', 'Repositorio\JurisprudenciaController@index');
        Route::get('/buscar', 'Repositorio\JurisprudenciaController@buscador');
        Route::get('/config', 'Repositorio\JurisprudenciaController@config');
        Route::post('/config', 'Repositorio\JurisprudenciaController@config');
        Route::post('/filterDB', 'Repositorio\JurisprudenciaController@filterDB');

        Route::apiResource('/repositorioApi',"Repositorio\JurisprudenciaController");

    });

Route::group(['middleware' => ['auth','role:Webmaster|Administrador|Personal'], 'prefix' => 'admin' ], function () {

    Route::get('/', function(){
        return redirect('admin/inicio');
    });

    Route::get('inicio', 'Dashboard@admin');
    
    Route::apiResource('usuariosApi', 'Admin\UsuarioController');
    Route::apiResource('usuarioApi', 'UserInfoController');
    Route::apiResource('personaApi', 'Admin\PersonaController');
    Route::apiResource('rolesApi', 'Admin\RolesController');
    Route::apiResource('plazasApi', 'Admin\PlazasController');
    Route::apiResource('oficinasApi', 'Admin\OficinaController');

    Route::get('{vista?}',  'Dashboard@adminvista');

});


Route::group(['middleware' => ['auth'], 'prefix' => 'perfil' ], function () {
    Route::get('/', function(){
        return redirect('perfil/inicio');
    });

    Route::get('inicio', 'UserInfoController@perfil');
    Route::get('perfilProfesional', 'UserInfoController@perfilProfesional');
    Route::post('perfilProfesional', 'UserInfoController@perfilProfesional');

    Route::post('changePassword','UserInfoController@changePassword')->name('changePassword');

});

Route::group(['middleware' => ['auth']], function () {
    //Inicio
    Route::get('inicio', 'InicioController@index')->name('Inicio');
});

