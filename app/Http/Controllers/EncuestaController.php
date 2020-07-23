<?php

namespace App\Http\Controllers;

use App\Disponibilidad;
use App\Encuesta;
use App\Http\Controllers\Controller;
use App\PreguntasxAlumno;
use App\Usuario;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncuestaController extends Controller
{
    public function index()
    {
        try {
            $encuestas = Encuesta::where('estado','act')->get();
            return response()->json($encuestas,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function show(Request $request)
    {
        try {
            $encuestas = Encuesta::findOrFail($request->id);
            return response()->json($encuestas,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function store(Request $request)
    {
        try {
            $encuestas = new Encuesta();
            $encuestas->orden = $request->orden;
            $encuestas->pregunta = $request->pregunta;
            $encuestas->tipo = $request->tipo;
            $encuestas->estado = 'act';
            $encuestas->fecha_creacion = $request->fecha_creacion;
            $encuestas->fecha_actualizacion = $request->fecha_actualizacion;
            $encuestas->save();
            return response()->json($encuestas,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function update(Request $request)
    {
        try {
            $encuestas = Encuesta::findOrFail($request->id);
            $encuestas->orden = $request->orden;
            $encuestas->pregunta = $request->pregunta;
            $encuestas->tipo = $request->tipo;
            $encuestas->estado = $request->estado;
            $encuestas->fecha_creacion = $request->fecha_creacion;
            $encuestas->fecha_actualizacion = $request->fecha_actualizacion;
            $encuestas->save();
            return response()->json($encuestas,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function destroy(Request $request)
    {
        try {
            $encuestas = Encuesta::findOrFail($request->id);
            $encuestas->estado = 'eli';
            $encuestas->save();
            return response()->json(null,204);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    //Listar alumnos que tuvieron una sesion con determinado tutor en cierto rango de fecha
    public function alumnosSesionxTutorxRangoFecha(Request $request){
        try {
            $disponibilidades = Disponibilidad::select('usuario.*')
                ->join('cita','disponibilidad.id_disponibilidad','cita.id_disponibilidad')
                ->join('cita_x_usuario', 'cita.id_cita','cita_x_usuario.id_cita')
                ->join('usuario','cita_x_usuario.id_usuario','usuario.id_usuario')->distinct()
                ->where('disponibilidad.id_programa',$request->idPrograma,'and')
                ->where('disponibilidad.id_usuario',$request->idTutor,'and')
                ->where('cita_x_usuario.asistencia','asi')
                ->whereDate('disponibilidad.fecha','>=',$request->fechaIni,'and')
                ->whereDate('disponibilidad.fecha','<=',$request->fechaFin)->get();

            $alumnosFinal = array();
            foreach ($disponibilidades as $disponibilidad) {
                $encuestaPen = PreguntasxAlumno::select('usuario.*','preguntas_x_alumno.agrupador')
                    ->join('usuario','preguntas_x_alumno.id_tutor','usuario.id_usuario')//->distinct()
                    ->where('preguntas_x_alumno.id_tutor',$request->idTutor,'and')
                    ->where('preguntas_x_alumno.id_alumno',$disponibilidad['id_usuario'],'and')
                    ->where('preguntas_x_alumno.id_encuesta',1,'and')
                    ->where('preguntas_x_alumno.estado','pen')->first();
                if($encuestaPen==null){
                    $encuestaCom = PreguntasxAlumno::select('usuario.*','preguntas_x_alumno.agrupador')
                        ->join('usuario','preguntas_x_alumno.id_tutor','usuario.id_usuario')//->distinct()
                        ->where('preguntas_x_alumno.id_tutor',$request->idTutor,'and')
                        ->where('preguntas_x_alumno.id_alumno',$disponibilidad['id_usuario'],'and')
                        ->where('preguntas_x_alumno.id_encuesta',1,'and')
                        ->where('preguntas_x_alumno.fecha_creacion',$request->fechaFin)->first();
                    if ($encuestaCom == null) array_push($alumnosFinal,$disponibilidad);
                }
            }
            return response()->json($alumnosFinal);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    //Registro de envio de encusta al alumno
    public function registrarEnvioEncuesta(Request $request)
    {
        try {
            $preguntas = Encuesta::where('estado','act')->get();
            $lenPreg = count($preguntas);
            $lenAlum = count($request->id_alumnos); //Arreglo de IDs de los alumnos a los que se les enviará la encuesta
            $cantAlum = 0;
            $agrupador = PreguntasxAlumno::select('agrupador')->groupby('agrupador')
                ->orderby('agrupador','desc')->get();
            if (count($agrupador))
                $nuevoAgrupador = $agrupador[0]->agrupador + 1;
            else
                $nuevoAgrupador = 1;
            for ($j = 0; $j < $lenAlum; $j++)
            {
                for ($i = 0; $i < $lenPreg; $i++)
                {
                    $pregxAlum = new PreguntasxAlumno();
                    $pregxAlum->id_encuesta = $preguntas[$i]->id_encuesta;
                    $pregxAlum->id_tutor = $request->id_tutor;
                    $pregxAlum->id_alumno = $request->id_alumnos[$j];
                    $pregxAlum->respuesta = '';
                    $pregxAlum->estado = 'pen';
                    $pregxAlum->id_programa = $request->idPrograma;
                    $pregxAlum->fecha_creacion = $request->fecha_creacion;
                    $pregxAlum->fecha_actualizacion = $request->fecha_actualizacion;
                    $pregxAlum->agrupador = $nuevoAgrupador;
                    $pregxAlum->save();
                }
                $cantAlum = $cantAlum + 1;
            }
            return response()->json($cantAlum,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    //Muestra la lista de encuestas (por tutor) por constestar del alumno
    public function mostrarListaEncuestasAlAlumno(Request $request)
    {
        try {
            $tutores = PreguntasxAlumno::select('usuario.*','preguntas_x_alumno.agrupador')
                ->join('usuario','preguntas_x_alumno.id_tutor','usuario.id_usuario')//->distinct()
                //->where('preguntas_x_alumno.id_tutor',$request->idTutor,'and')
                ->where('preguntas_x_alumno.id_alumno',$request->idAlumno,'and')
                ->where('preguntas_x_alumno.id_encuesta',1,'and')
                ->where('preguntas_x_alumno.estado','pen')->get();
            return response()->json($tutores,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    //Mostrar preguntas
    public function mostrarPreguntas()
    {
        try {
            $preguntas = Encuesta::all();
            return response()->json($preguntas,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    //Guardar las respuestas de la encuesta y cambiar el estado
    //request->rpta es un arreglo con las respuestas
    //request->preg es un arreglo con los ids de las preguntas
    //request->agrupador, request->idTutor, request->idAlumno
    public function guardarRespuestas(Request $request)
    {
        try {
            $lenRptas = count($request->rpta);
            $array = [];
            for ($i = 0; $i < $lenRptas; $i++)
            {
                $pregxAlum = PreguntasxAlumno::where('id_encuesta',$request->preg[$i],'and')
                    ->where('id_tutor',$request->idTutor,'and')
                    ->where('id_alumno',$request->idAlumno,'and')
                    ->where('agrupador',$request->agrupador)->first();
                if($request->rpta[$i] == "Sí") $pregxAlum->respuesta = "1";
                elseif ($request->rpta[$i] == "No") $pregxAlum->respuesta = "0";
                else $pregxAlum->respuesta = strval($request->rpta[$i]);
                $pregxAlum->estado = 'con'; //contestado
                $pregxAlum->save();
                array_push($array,$pregxAlum);
            }
            return response()->json($array,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    //Modificar el estado de la encuesta cuando el alumno decide no responder
    public function encuestaOmitida(Request $request)
    {
        try {
            $preguntas = Encuesta::where('estado','act')->get();
            $lenPreg = count($preguntas);
            for ($i = 0; $i < $lenPreg; $i++)
            {
                $pregxAlum = PreguntasxAlumno::where('id_encuesta',$preguntas[$i]->id_encuesta,'and')
                    ->where('id_tutor',$request->idTutor,'and')
                    ->where('id_alumno',$request->idAlumno,'and')
                    ->where('agrupador',$request->agrupador)->first();
                $pregxAlum->estado = 'noc'; //no contestado
                $pregxAlum->save();
            }
            return response()->json(null,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function reporteEncuesta(Request $request)
    {
        try {
            //DB::enableQueryLog();
            $id_tutor=$request->id_tutor;
            $id_programa=$request->id_programa;
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin;

            $subquery = DB::table('preguntas_x_alumno AS p')
                ->select('id_encuesta','respuesta')
                ->whereBetween('p.fecha_actualizacion',[$ini,$fin])
                ->where('estado','=','con')
                ->whereIn('p.id_programa',$id_programa);
            /*if(count($id_tutor)>0){
                $subquery->whereIn('p.id_usuario',$id_tutor);
            };*/

            $query=Encuesta::select('encuesta.id_encuesta','valores.abreviatura','valores.nombre',
                DB::raw('count(respuesta) as total'))
                ->join('valores','valores.tabla','=','encuesta.tipo')
                ->leftjoinSub($subquery,'sub',function($join){
                    $join->on('valores.abreviatura','=','sub.respuesta');
                    $join->on('sub.id_encuesta','=','encuesta.id_encuesta');
                })
                ->groupBy('encuesta.id_encuesta','valores.abreviatura','valores.nombre')
                ->orderBy('encuesta.id_encuesta')
                ->orderBy('valores.abreviatura')->get();
            //print_r(DB::getQueryLog()[0]['query']);//prueba del query generado por laravel
            return response()->json($query,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }


}
