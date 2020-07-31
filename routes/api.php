<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'web','auth:api'], function () {

    Route::get('/api/user', function (Request $request) {
        return $request->user();
    });

    Auth::routes();

    Route::get('/', function () {
        return view('welcome');
    });
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('auth/google', 'Auth\GoogleController@redirectToGoogle');
    Route::get('auth/google/callback', 'Auth\GoogleController@handleGoogleCallback');
    Route::get('auth/google/calendarioAnadir', 'Auth\GoogleController@calendarioAnadir');
    Route::post('/api/vuelogin', 'UsuarioController@vuelogin');
    Route::post('/api/vueregister', 'UsuarioController@vueregister');
    Route::post('/api/vuelogout', 'UsuarioController@vuelogout');
    Route::post('/api/vueuser', 'UsuarioController@vueuser');
    Route::post('/api/googlelogin', 'UsuarioController@googleLogin');
    Route::post('/api/googleregister', 'UsuarioController@googleregister');


    Route::group(['prefix' => 'api/institucion'], function() {
        Route::post('listarTodo','InstitucionController@index');
        Route::post('insertar','InstitucionController@store');
        Route::post('listar/{institucion}','InstitucionController@show');
        Route::post('modificar/{institucion}','InstitucionController@update');
        Route::post('eliminar/{institucion}','InstitucionController@destroy');
        Route::post('listarPorNombre','InstitucionController@listNombre');
        Route::post('subirLogo','InstitucionController@subirLogo');
    });

    Route::group(['prefix' => 'api/facultad'], function() {
        Route::post('listarTodo', 'FacultadController@index');
        Route::post('insertar', 'FacultadController@store');
        Route::post('listar/{facultad}', 'FacultadController@show');
        Route::post('modificar/{facultad}', 'FacultadController@update');
        Route::post('eliminar', 'FacultadController@destroy');
        Route::post('listarProgramas', 'FacultadController@listarProgramas');
        Route::post('cantProgramas/{facultad}', 'FacultadController@cantProgramas');
        Route::post('listFacuConCant', 'FacultadController@listFacuConCantid');
        Route::post('listFacuConCoordi', 'FacultadController@listFacuConCoordi');
        Route::post('coordinadorFacultad/{facultad}', 'FacultadController@coordinadorFacultad');
        Route::post('listarTodoPorNombre', 'FacultadController@listNombre');
        Route::post('coordinadoresPyF', 'FacultadController@coordinadores');
        Route::post('coordinadoresFacu', 'FacultadController@coordinadoresFacu');
        Route::post('coordinadoresProg', 'FacultadController@coordinadoresProg');
        Route::post('asignarCoordi', 'FacultadController@asignarCoordinadoresFacu');
        Route::post('verificarCod/{id?}', 'FacultadController@verificarCodigo');
        Route::post('verificarNom/{id?}', 'FacultadController@verificarNombre');
        Route::post('listarFacultades', 'FacultadController@listarFacultades');
        Route::post('listarProgramasDefault', 'FacultadController@listarProgramasDefault');
    });
    Route::group(['prefix' => 'api/programa'], function() {
        Route::post('listarTodo','ProgramaController@index');
        Route::post('insertarVariosPro','ProgramaController@store');
        Route::post('listar/{programa}','ProgramaController@show');
        Route::post('actualizarVariosPro','ProgramaController@update');
        Route::post('eliminarVariosPro','ProgramaController@destroy');
        Route::post('listarPorNombre','ProgramaController@listNombre');
        Route::post('usuarioPrograma/{id_programa}','ProgramaController@usuarioPrograma');
        Route::post('asignarCoordi','ProgramaController@asignarCoordinadoresProg');
        Route::post('coordinador/{programa}','ProgramaController@idCoordinador');
        Route::post('listarPorTipoTutoria/{programa}','ProgramaController@listTipoTutoria');
        Route::post('listarConCoord','ProgramaController@listConCoord');
        Route::post('listarConCoord/{id}','ProgramaController@listConCoord2');
        Route::post('verificarCod/{id?}', 'ProgramaController@verificarCodigo');
        Route::post('verificarNom/{id?}', 'ProgramaController@verificarNombre');
        Route::post('tutores', 'ProgramaController@tutores');
        Route::post('tutoresAsignar', 'ProgramaController@tutoresAsignar');
        Route::post('tutoresAlumno', 'ProgramaController@tutoresAlumno');
        Route::post('tutoresAlumnoPaginado', 'ProgramaController@tutoresAlumnoPaginado');
        Route::post('tutoresListar', 'ProgramaController@tutoresTodo');
        Route::post('alumnosProg', 'ProgramaController@alumnosProg');
        Route::post('asistenciaXTutores', 'ProgramaController@asistenciaXTutores');
        Route::post('asistenciaXPrograma', 'ProgramaController@asistenciaXPrograma');
        Route::post('cantAtendidos', 'ProgramaController@cantAtendidos');
        Route::post('citasXDia', 'ProgramaController@citasxdia');
        Route::post('citasXDiaTodos', 'ProgramaController@citasXDiaTodos');
        Route::post('facultadesProg', 'ProgramaController@listProgFacu');
        Route::post('tiposTutoriaAlumno', 'ProgramaController@tiposTutoriaAlumno');
        Route::post('tutoresAsignados', 'ProgramaController@tutoresAsignados');

        //Route::post('hola', 'ProgramaController@hola');
    });

    Route::group(['prefix' => 'api/TipoTutoria'],function() {
        Route::post('listarTodo/{idprograma}', 'TipoTutoriaController@index');
        Route::post('listarActivos/{idprograma}', 'TipoTutoriaController@listarActivos');
        Route::post('insertar', 'TipoTutoriaController@store');
        Route::post('mostrar/{tipoTutoria}', 'TipoTutoriaController@show');
        Route::post('modificar/{tipoTutoria}', 'TipoTutoriaController@update');
        Route::post('eliminar/{tipoTutoria}', 'TipoTutoriaController@destroy');
        Route::post('tutoresAsignados', 'TipoTutoriaController@TutoresXTipoTutoria');
        Route::post('eliminarTutor', 'TipoTutoriaController@eliTutorTipoTutoria');
        Route::post('asistenciaXTipoTutorias', 'TipoTutoriaController@asistenciaXTipoTutorias');
        Route::post('listaAlumnosConTT', 'TipoTutoriaController@listaAlumnosConTipoTutoriaYTutorAsignado');
        Route::post('tiposTutoriaPrograma', 'TipoTutoriaController@tiposTutoriaPrograma');
        Route::post('listaTutoresDeTipoTutoria', 'TipoTutoriaController@listaTutoresDeTipoTutoria');
        Route::post('tipoTutoriaNombre', 'TipoTutoriaController@tipoTutoriaNombre');
    });

    Route::group(['prefix' => 'api/ModuloPermiso'],function() {
        Route::post('listarTodo', 'ModuloPermisoController@index');
        Route::post('insertar', 'ModuloPermisoController@store');
        Route::post('listar/{moduloPermiso}', 'ModuloPermisoController@show');
        Route::post('modificar/{moduloPermiso}', 'ModuloPermisoController@update');
        Route::post('eliminar/{moduloPermiso}', 'ModuloPermisoController@destroy');
        Route::post('listarTodoPermisos', 'ModuloPermisoController@listarTodoPermisos');
    });


    Route::group(['prefix' => 'api/permisos'], function (){
        Route::post('listarTodo', 'PermisoController@index');
        Route::post('listar/{permiso}', 'PermisoController@show');
        Route::post('insertar','PermisoController@store');
        Route::post('modificar/{permiso}', 'PermisoController@update');
        Route::post('eliminar/{permiso}', 'PermisoController@destroy');
    });

    Route::group(['prefix' => 'api/tipoUsuarios'], function (){
        Route::post('listarTodo', 'TipoUsuarioController@index');
        Route::post('listarPrograma', 'TipoUsuarioController@listarRol');
        Route::post('listar/{tipoUsuario}', 'TipoUsuarioController@show');
        Route::post('insertar', 'TipoUsuarioController@store');
        Route::post('modificar/{tipoUsuario}', 'TipoUsuarioController@update');
        Route::post('eliminar/{tipoUsuario}', 'TipoUsuarioController@destroy');
        Route::post('listarPermisos/{id}', 'TipoUsuarioController@permisos');
        Route::post('modPermisos', 'TipoUsuarioController@modPermisos');
        Route::post('tiposAdmin', 'TipoUsuarioController@tiposAdmin');
        Route::post('tiposFacultad', 'TipoUsuarioController@tiposFacultad');
        Route::post('tiposPrograma', 'TipoUsuarioController@tiposPrograma');
    });

    Route::group(['prefix' => 'api/usuarios'], function (){
        Route::post('listarTodo', 'UsuarioController@index');
        Route::post('listar/{usuario}', 'UsuarioController@show');
        Route::post('insertar', 'UsuarioController@store');
        Route::post('modificar/{usuario}', 'UsuarioController@update');
        Route::post('eliminar/{usuario}', 'UsuarioController@destroy');
        Route::post('tipoUsuario', 'UsuarioController@tipoUsuario');
        Route::post('permisos', 'UsuarioController@permisos');
        Route::post('permisosProgramas', 'UsuarioController@programas');
        Route::post('eliUsuarioPrograma', 'UsuarioController@eliUsuarioPrograma');
        Route::post('busquedaPorNombre', 'UsuarioController@usuariosPorNombre');
        Route::post('usuarioProgramaRol/{usuario}', 'UsuarioController@usuarioProgramaRol');
        Route::post('updateTipoTutoria/{usuario}', 'UsuarioController@updateTipoTutoria');
        Route::post('nuevoPrograma/{usuario}', 'UsuarioController@nuevoPrograma');
        Route::post('subirNotas', 'UsuarioController@subirNotas');
        Route::post('masivo/{idprograma}', 'UsuarioController@masivo');
        Route::post('verificarUsuario', 'UsuarioController@verificarUsuario');
        Route::post('condAlumno', 'UsuarioController@condAlumno');
        Route::post('notas', 'UsuarioController@notas');
        Route::post('tiposTutoriasTutor', 'UsuarioController@tiposTutoriaTutor');
        Route::post('alumnoMasivo/{idprograma}', 'UsuarioController@alumnoMasivo');
        Route::post('tutoriaTutor', 'UsuarioController@tutoriaTutor');
        Route::post('subirFoto', 'UsuarioController@subirFoto');
        Route::post('modCondAlumnoMasivo/{idprograma}', 'UsuarioController@modCondAlumnoMasivo');
        Route::post('veificarCoordinador', 'UsuarioController@eliCoordinador');
        Route::post('recuperarContrasenaVal', 'UsuarioController@recuperarContrasenaVal');

        Route::post('datosBajoRendimiento', 'UsuarioController@datosBajoRendimiento');
        Route::post('datosAlumnosPlan', 'UsuarioController@datosAlumnosPlan');
        //prueba
        Route::post('testData', 'UsuarioController@testData');
    });

    Route::group(['prefix' => 'api/sesiones'], function (){
        Route::post('listarTodo', 'SesionController@index');
        Route::post('listar/{sesion}', 'SesionController@show');
        Route::post('insertar', 'SesionController@store');
        Route::post('modificar/{sesion}', 'SesionController@update');
        Route::post('eliminar/{sesion}', 'SesionController@destroy');
        Route::post('alumnoProg', 'SesionController@alumnosPrograma');
        Route::post('asistencia', 'SesionController@sesionInformalCitaAsist');
        //Route::post('usuarios', 'SesionController@usuarioYRoldeunPrograma');
        Route::post('asistenciaFormal', 'SesionController@sesionFormalAsistencia');
        Route::post('regSesionFormal', 'SesionController@registrarSesionFormal');
    });

    Route::group(['prefix' => 'api/citas'], function (){
        Route::post('listarTodo', 'CitaController@index');
        Route::post('listar/{cita}', 'CitaController@show');
        Route::post('insertar', 'CitaController@store');
        Route::post('modificar/{cita}', 'CitaController@update');
        Route::post('eliminar/{cita}', 'CitaController@destroy');
        Route::post('registrarCitaAlumno', 'CitaController@registrarCitaAlumno');
        Route::post('cancelarCita', 'CitaController@cancelarCita');
        Route::post('registrarCitaCoord', 'CitaController@registrarCitaCoord');
        Route::post('historcioAlumno', 'CitaController@histCitasAlumno');
        Route::post('editarCitaCoord', 'CitaController@editarCitaCoord');

        Route::post('listCitaAlu', 'CitaController@listCitaAlu');
        Route::post('listCitaTutor', 'CitaController@listCitaTutor');
    });

    Route::group(['prefix' => 'api/motivosConsulta'], function (){
        Route::post('listarTodo', 'MotivoConsultaController@index');
        Route::post('listar/{motivoCons}', 'MotivoConsultaController@show');
        Route::post('insertar', 'MotivoConsultaController@store');
        Route::post('modificar/{motivoCons}', 'MotivoConsultaController@update');
        Route::post('eliminar/{motivoCons}', 'MotivoConsultaController@destroy');
        Route::post('asistencia', 'MotivoConsultaController@asistencia');
    });

    Route::group(['prefix' => 'api/unidadesApoyo'], function (){
        Route::post('listarTodo', 'UnidadApoyoController@index');
        Route::post('listar/{unidadApoyo}', 'UnidadApoyoController@show');
        Route::post('insertar', 'UnidadApoyoController@store');
        Route::post('modificar/{unidadApoyo}', 'UnidadApoyoController@update');
        Route::post('eliminar/{unidadApoyo}', 'UnidadApoyoController@destroy');
        Route::post('unidadesxProg', 'UnidadApoyoController@uApoyoxPrograma');
        Route::post('unidadesAdmin', 'UnidadApoyoController@uApoyoconFacuProg');
        Route::post('unidadesFacultad', 'UnidadApoyoController@uApoyodeFacu');
        Route::post('unidadesPrograma', 'UnidadApoyoController@uApoyodeProg');
    });

    Route::group(['prefix' => 'api/solicitudes'], function (){
        Route::post('listarTodo', 'SolicitudController@index');
        Route::post('listar/{solicitud}', 'SolicitudController@show');
        Route::post('insertar','SolicitudController@store');
        Route::post('modificar/{solicitud}', 'SolicitudController@update');
        Route::post('eliminar', 'SolicitudController@destroy');
        Route::post('listarSol', 'SolicitudController@listarSol');
        Route::post('solicitudTutor', 'SolicitudController@solicitudTutor');
        Route::post('habilitado', 'SolicitudController@habilitado');
    });

    Route::group(['prefix' => 'api/disponibilidades'], function(){
        Route::post('listarTodo', 'DisponibilidadController@index');
        Route::post('listar/{disponibilidad}', 'DisponibilidadController@show');
        Route::post('insertar', 'DisponibilidadController@store');
        Route::post('modificar/{disponibilidad}', 'DisponibilidadController@update');
        Route::post('eliminar/{disponibilidad}', 'DisponibilidadController@destroy');
        Route::post('dispSemanalVistaAl','DisponibilidadController@dispSemanalVistaAlumno');
        Route::post('dispElegidaPorAl','DisponibilidadController@dispElegidaPorAlumno');
        Route::post('dispSemanalVistaTu','DisponibilidadController@dispSemanalVistaTutor');
        Route::post('citasDeUnAlumno','DisponibilidadController@citasDeUnAlumno');

        Route::post('mostrarCita/{disponibilidad}','DisponibilidadController@mostrarCita');
        Route::post('mostrarCita2','DisponibilidadController@mostrarCita2');
        //Route::post('prueba', 'DisponibilidadController@citaU');
        Route::post('consultarDisp','DisponibilidadController@consultaDisponibilidad');
    });

    Route::group(['prefix' => 'api/registros'], function() {
        Route::post('listarTodo','RegistroAlumnoController@index');
        Route::post('insertar','RegistroAlumnoController@store');
        Route::post('listar/{registros}','RegistroAlumnoController@show');
        Route::post('modificar/{registros}','RegistroAlumnoController@update');
        Route::post('eliminar','RegistroAlumnoController@destroy');
        Route::post('listarAlumnos','RegistroAlumnoController@listarAlumnosTutor');
        Route::post('asignadosXPrograma','RegistroAlumnoController@asignadosXPrograma');
        Route::post('asignadosXFacultad','RegistroAlumnoController@asignadosXFacultad');
        Route::post('asignadosXUniversidad','RegistroAlumnoController@asignadosXUniversidad');
        Route::post('cantAlumnosXTutores','RegistroAlumnoController@cantAlumnosXTutores');
        Route::post('cantAlumnosXPrograma','RegistroAlumnoController@cantAlumnosXPrograma');
        Route::post('cantAlumnosXFacultad','RegistroAlumnoController@cantAlumnosXFacultad');
    });

        Route::group(['prefix' => 'api/planAccion'], function() {
        Route::post('insertar','PlanAccionController@store');
        Route::post('modificar/{planAccion}','PlanAccionController@update');
        Route::post('eliminar/{planAccion}','PlanAccionController@destroy');
        Route::post('planAlumno','PlanAccionController@planAlumno');
    });
    Route::group(['prefix' => 'api/compromisos'], function() {
        Route::post('insertar','CompromisoController@store');
        Route::post('modificar/{compromisos}','CompromisoController@update');
        Route::post('eliminar/{compromisos}','CompromisoController@destroy');
    });

    Route::group(['prefix' => 'api/encuesta'], function () {
        Route::post('listarTodo','EncuestaController@index');
        Route::post('listar','EncuestaController@show');
        Route::post('insertar','EncuestaController@store');
        Route::post('modificar','EncuestaController@update');
        Route::post('eliminar','EncuestaController@destroy');
        Route::post('alumnosSesionXTutorXRangoFecha','EncuestaController@alumnosSesionxTutorxRangoFecha');
        Route::post('registrarEnvioEncuesta','EncuestaController@registrarEnvioEncuesta');
        Route::post('mostrarListaEncuestasAlAlumno','EncuestaController@mostrarListaEncuestasAlAlumno');
        Route::post('mostrarPreguntas','EncuestaController@mostrarPreguntas');
        Route::post('guardarRespuestas','EncuestaController@guardarRespuestas');
        Route::post('encuestaOmitida','EncuestaController@encuestaOmitida');
        Route::post('reporteEncuesta','EncuestaController@reporteEncuesta');
    });

});
