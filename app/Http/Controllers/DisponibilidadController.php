<?php

namespace App\Http\Controllers;
use App\Programa;
use Exception;
use App\Cita;
use App\Disponibilidad;
use App\Sesion;
use App\TipoTutoria;
use App\Usuario;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisponibilidadController extends Controller
{
    public function index()
    {
        try {
            $disponibilidades = Disponibilidad::where('estado','act')->get();
            return response()->json($disponibilidades);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function show ($id)
    {
        try {
            $disponibilidades = Disponibilidad::findOrFail($id);
            return response()->json($disponibilidades);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    public function store(Request $request)
    {
        try {
            /*$aux = Disponibilidad::select('disponibilidad.*')->whereDate('fecha','=',$request->fecha,'and')
                ->where('hora_inicio',$request->hora_inicio)->get();
            if(count($aux))
                return "Ya existe esa disponibilidad";*/
            $disponibilidades = new Disponibilidad();
            $disponibilidades->id_usuario = $request->id_usuario;
            $disponibilidades->id_programa = $request->id_programa;
            $disponibilidades->fecha = $request->fecha;
            $disponibilidades->hora_inicio = $request->hora_inicio;
            $disponibilidades->estado = 'act';
            $disponibilidades->usuario_creacion = $request->usuario_creacion;
            $disponibilidades->usuario_actualizacion = $request->usuario_actualizacion;
            $disponibilidades->tipo_disponibilidad = $request->tipo_disponibilidad;
            $disponibilidades->hora_fin = $request->hora_fin;
            $disponibilidades->save();
            return response()->json($disponibilidades,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $disponibilidades = Disponibilidad::findOrFail($id);
            $disponibilidades->id_usuario = $request->id_usuario;
            $disponibilidades->id_programa = $request->id_programa;
            $disponibilidades->fecha = $request->fecha;
            $disponibilidades->hora_inicio = $request->hora_inicio;
            $disponibilidades->estado = $request->estado;
            $disponibilidades->usuario_creacion = $request->usuario_creacion;
            $disponibilidades->usuario_actualizacion = $request->usuario_actualizacion;
            $disponibilidades->tipo_disponibilidad = $request->tipo_disponibilidad;
            $disponibilidades->hora_fin = $request->hora_fin;
            $disponibilidades->save();
            return response()->json($disponibilidades,200);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    public function destroy($id)
    {
        try {
            $disponibilidades = Disponibilidad::findOrFail($id);
            $disponibilidades->estado = 'eli';
            $disponibilidades->save();
            return response()->json(null,204);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    //Disponibilidad semanal que se le muestra al alumno
    public function dispSemanalVistaAlumno(Request $request)
    {
        try {
            //Segun figma se mostrará la disponibilidad semanal LUNES - DOMINGO
            //entonces fechaIni (tipo date) debe ser la fecha del lunes de esa semana
            //y fechaFin (tipo date) debe ser la fecha del domingo de esa semana.
            $disponibilidades = Disponibilidad::where('id_usuario',$request->idUsuario,'and') //idUsuario es el id del tutor seleccionado
                ->where('id_programa',$request->idPrograma,'and')
                ->where('estado','act','and')
                ->whereDate('fecha','>=',$request->fechaIni,'and')
                ->whereDate('fecha','<=',$request->fechaFin)->get();
            $estadoDisp = [];
            $len = count($disponibilidades);
            for ($i = 0; $i < $len; $i++)
            {
                $aux = Cita::where([['id_disponibilidad',$disponibilidades[$i]->id_disponibilidad],['estado','act']])->get();

                if (count($aux)) {
                    $disponibilidades[$i]->alumno = Cita::where([['id_disponibilidad', $disponibilidades[$i]->id_disponibilidad], ['estado', 'act']])->first()->citaXUsuarios;
                    array_push($estadoDisp, 'o'); //ocupado
                }
                else
                    array_push($estadoDisp,'l'); //libre
            }
            $lista = [$disponibilidades,$estadoDisp];
            return response()->json($lista,200);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    //Para actualizar el color de la disponibilidad en el calendario de front
    public function dispElegidaPorAlumno(Request $request)
    {
        try {
            $rpta = 'l';
            $ocupado = Cita::where([['id_disponibilidad',$request->id_disponibilidad],['estado','act']])->get();
            if (count($ocupado))
                $rpta = 'o';
            return response()->json($rpta,200);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    //Disponibilidad semanal que se le muestra al tutor/coord.
    public function dispSemanalVistaTutor(Request $request)
    {
        try {
            //Segun figma se mostrará la disponibilidad semanal LUNES - DOMINGO
            //entonces fechaIni (tipo date) debe ser la fecha del lunes de esa semana
            //y fechaFin (tipo date) debe ser la fecha del domingo de esa semana.
            $disponibilidades = Disponibilidad::where('id_usuario',$request->idUsuario,'and') //idSsuario es el id del tutor seleccionado
                ->where('id_programa',$request->idPrograma,'and')
                ->where('estado','act','and')
                ->whereDate('fecha','>=',$request->fechaIni,'and')
                ->whereDate('fecha','<=',$request->fechaFin)->get();
            $estadoDisp = [];
            $listaTipoTutoria = [];
            $listaUsuarios = [];
            $len = count($disponibilidades);
            for ($i = 0; $i < $len; $i++)
            {
                $cita = Cita::where([['id_disponibilidad',$disponibilidades[$i]->id_disponibilidad],['estado','act']])->get();
                if (count($cita))
                {
                    $tipoTutoria = TipoTutoria::findOrFail($cita[0]->id_tipo_tutoria);
                    $citaAux = Cita::findOrFail($cita[0]->id_cita);
                    $alumno = $citaAux->citaXUsuarios()->get();
                    array_push($estadoDisp,'o'); //ocupado
                    array_push($listaTipoTutoria, $tipoTutoria);
                    array_push($listaUsuarios,$alumno);
                }
                else
                {
                    array_push($estadoDisp,'l'); //libre
                    array_push($listaTipoTutoria, 'l'); //libre
                    array_push($listaUsuarios,'l'); //libre
                }
            }
            $lista = [$disponibilidades,$estadoDisp,$listaTipoTutoria,$listaUsuarios];
            return response()->json($lista,200);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    //No se usa, solo para demostracion
    public function citaU(Request $request)
    {
        $cita = Cita::findOrFail($request->id);
        $aux= $cita->citaXUsuarios()->get();
        echo $aux, "\n";
        /*try {
            $cita = Cita::findOrFail($request->id);
            $usuario=$cita->citaXUsuarios()->get();
            return response()->json($usuario,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }*/
    }

    //Mostrar la cita ligada a una disponibilidad
    public function mostrarCita($id)
    {
        try {
            $disponibilidad=Disponibilidad::findOrFail($id);
            $cita=$disponibilidad->citas()->with('citaXUsuarios')->with('tipoTutoria')->get();
            $sesion=Sesion::where('id_sesion',$id)->with('motivoConsultas')->get();
            return response()->json([
                'cita'=>$cita,
                'sesion'=>$sesion,
            ],200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function mostrarCita2(Request $request)
    {
        try {
            $condciones=DB::table('valores')->where('tabla','CONDICION_ALUMNO')->get();
            $disponibilidad=Disponibilidad::findOrFail($request->idDisponibilidad);
            $disponibilidad2=Disponibilidad::findOrFail($request->idDisponibilidad);
            $cita=$disponibilidad->citas()->with('citaXUsuarios')->with('tipoTutoria')->get();
            $len = count($cita);
            for ($i = 0; $i < $len; $i++)
            {
                if($cita[$i]->estado == 'act')
                {
                    $rpta = [];
                    $sesion = Sesion::where('id_sesion',$cita[$i]->id_cita)->with('motivoConsultas')->get();
                    foreach ($condciones as $condcione) {
                        if($condcione->abreviatura == ($cita[$i]->citaXUsuarios)[0]->condicion_alumno){
                            $cita[$i]['cond'] = $condcione->nombre;
                        }
                    }
                    array_push($rpta,$cita[$i]);
                    if (count($sesion))
                        array_push($rpta,$sesion[0]);
                    else
                        array_push($rpta,'l');
                    return response()->json([$rpta,$disponibilidad2],200);
                }
            }
            return response()->json(['l',$disponibilidad2],200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Consultar si la disponibilidad ya existe
    public function consultaDisponibilidad(Request $request)
    {
        try {
            $disponibilidad = Disponibilidad::where('id_usuario',$request->idUsuario,'and')
                ->where('estado','act','and')
                ->whereDate('fecha','=',$request->fecha,'and')
                ->where('hora_inicio',$request->horaInicio)->get();
            $rpta = [];
            //return response()->json($disponibilidad,200);
            if (count($disponibilidad))
            {
                $programa = Programa::findOrFail($disponibilidad[0]->id_programa);
                array_push($rpta,$disponibilidad);
                array_push($rpta,$programa);
            }
            else
            {
                array_push($rpta,'l');
            }
            return response()->json($rpta,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function citasDeUnAlumno(Request $request)
    {
        try {
            $citaxUsuario = Sesion::select('cita_x_usuario.asistencia','sesion.resultado','sesion.estado as estadoSesion','tipo_tutoria.nombre as tipoTutoria','disponibilidad.fecha',
                'disponibilidad.hora_inicio', 'disponibilidad.hora_fin','disponibilidad.estado as dispEstado','disponibilidad.tipo_disponibilidad','disponibilidad.id_programa','usuario.id_usuario as idTutor',
                'usuario.estado as usuarioEstado','usuario.correo','usuario.nombre','usuario.apellidos','usuario.codigo as codigoTutor')
                ->join('cita_x_usuario','sesion.id_sesion','cita_x_usuario.id_cita')
                ->join('cita','cita_x_usuario.id_cita','cita.id_cita')
                ->join('disponibilidad','cita.id_disponibilidad','disponibilidad.id_disponibilidad')
                ->join('usuario','disponibilidad.id_usuario','usuario.id_usuario')
                ->join('tipo_tutoria','cita.id_tipo_tutoria','tipo_tutoria.id_tipo_tutoria')
                ->where('cita_x_usuario.id_usuario',$request->idAlumno,'and')
                ->where('disponibilidad.estado','act','and')
                ->whereDate('disponibilidad.fecha','>=',$request->fechaIni,'and')
                ->whereDate('disponibilidad.fecha','<=',$request->fechaFin)->get();
            return response()->json($citaxUsuario,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
}
