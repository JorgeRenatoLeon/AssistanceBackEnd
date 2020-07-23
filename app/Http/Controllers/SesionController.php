<?php

namespace App\Http\Controllers;


use App\Cita;
use App\Disponibilidad;
use App\Http\Controllers\Controller;
use App\MotivoConsulta;
use App\Permiso;
use App\Sesion;
use App\TipoUsuario;
use App\Usuario;
use App\UsuarioxPrograma;
use Couchbase\Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SesionController extends Controller
{
    public function index()
    {
        try{
            $sesiones = Sesion::where('estado','act')->get();
            return response()->json($sesiones);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function show($id)
    {
        try{
            $sesiones = Sesion::findOrFail($id);
            return response()->json($sesiones);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    public function store(Request $request)
    {
        try {
            $sesiones = new Sesion();
            $sesiones->id_sesion = $request->id_sesion;
            $sesiones->resultado = $request->resultado;
            $sesiones->estado = 'act';
            $sesiones->usuario_creacion = $request->usuario_creacion;
            $sesiones->usuario_actualizacion = $request->usuario_actualizacion;
            $sesiones->save();
            return response()->json($sesiones,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $sesiones = Sesion::findOrFail($id);
            $sesiones->resultado = $request->resultado;
            $sesiones->estado = $request->estado;
            $sesiones->usuario_creacion = $request->usuario_creacion;
            $sesiones->usuario_actualizacion = $request->usuario_actualizacion;
            $sesiones->save();
            return response()->json($sesiones,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function destroy($id)
    {
        try {
            $sesiones = Sesion::findOrFail($id);
            $sesiones->estado = 'eli';
            $sesiones->save();
            return response()->json(null,204);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Listar alumnos de un determinado programa
    public function alumnosPrograma(Request $request)
    {
        try {
            /*$alumnos = UsuarioxPrograma::select('id_usuario')->where([['id_programa',$request->idProg],
                ['id_tipo_usuario',$request->idTipoU],])->get();
            $len = count($alumnos);
            $listaAlum = [];
            $condciones=DB::table('valores')->where('tabla','CONDICION_ALUMNO')->get();
            for ($i = 0; $i < $len; $i++)
            {
                $aux = Usuario::where([['id_usuario', $alumnos[$i]->id_usuario], ['estado','act']])->get();
                if (count($aux) > 0){
                    foreach ($condciones as $condcione) {
                        if($condcione->abreviatura == $aux[0]->condicion_alumno){
                            $aux[0]->cond = $condcione->nombre;
                        }
                    }
                    array_push($listaAlum, $aux);
                }
            }
            return response()->json($listaAlum, 200);*/
            $usuarios = Permiso::select('usuario.id_usuario','usuario.codigo','usuario.nombre','usuario.apellidos',
                'usuario.correo','usuario.telefono','usuario.estado','usuario.imagen','usuario.sexo','usuario.notas',
                'usuario.condicion_alumno','usuario_x_programa.id_tipo_usuario','usuario_x_programa.id_programa')
                ->join('permiso_x_tipo_usuario','permiso.id_permiso','permiso_x_tipo_usuario.id_permiso')
                ->join('usuario_x_programa','permiso_x_tipo_usuario.id_tipo_usuario','usuario_x_programa.id_tipo_usuario')
                ->join('usuario','usuario_x_programa.id_usuario','usuario.id_usuario')
                ->where('permiso.nombre','like','Tutores', 'and')
                ->where('usuario.estado','act','and')
                ->where('usuario_x_programa.id_programa',$request->idProg)->get();
            return response($usuarios,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Crear cita y registrar asistencia para sesiones informales
    public function sesionInformalCitaAsist(Request $request)
    {
        try{
            //Creacion de la disponibilidad
            $disponibilidades = new Disponibilidad();
            $disponibilidades->id_usuario = $request->id_usuario;
            $disponibilidades->id_programa = $request->id_programa;
            $disponibilidades->fecha = $request->fecha;
            $disponibilidades->hora_inicio = $request->hora_inicio;
            $disponibilidades->estado = 'act';
            $disponibilidades->usuario_creacion = $request->usuario_creacion;
            $disponibilidades->usuario_actualizacion = $request->usuario_actualizacion;
            $disponibilidades->tipo_disponibilidad = 'oca'; //ocasional
            $disponibilidades->save();
            //Creacion de la cita
            $citas = new Cita();
            $citas->id_tipo_tutoria = $request->id_tipo_tutoria;
            $citas->id_disponibilidad = $disponibilidades->id_disponibilidad;
            //$citas->nota = $request->nota;
            $citas->estado = 'act';
            $citas->usuario_creacion = $request->usuario_creacion;
            $citas->usuario_actualizacion = $request->usuario_actualizacion;
            $citas->save();
            //echo $disponibilidades->id_disponibilidad, "\n";
            //echo $citas->id_cita, "\n";
            //Creacion de la sesion
            $sesiones = new Sesion();
            $sesiones->id_sesion = $citas->id_cita;
            $sesiones->resultado = $request->resultado;
            $sesiones->estado = 'act';
            $sesiones->usuario_creacion = $request->usuario_creacion;
            $sesiones->usuario_actualizacion = $request->usuario_actualizacion;
            $sesiones->save();
            //echo $sesiones->id_sesion, "\n";
            //Asistencia de todos los alumnos
            $len = count($request->idAlumnos); //idAlumnos es un arreglo de los IDs de los alumnos
            for ($i = 0; $i < $len; $i++)
            {
                $asistencia = Usuario::find($request->idAlumnos[$i]);
                $asistencia->citas()->attach($citas->id_cita, ['asistencia' => 'asi']);
            }
            //Motivos de la sesion
            $lenM = count($request->idMotivos); //idMotivos es una lista con todos los IDs de los motivos
            for ($j = 0; $j < $lenM; $j++)
            {
                $motivos = MotivoConsulta::find($request->idMotivos[$j]);
                $motivos->sesions()->attach($sesiones->id_sesion);
            }
            return response()->json($sesiones,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Tomar asistencia en sesiones formales
    public function sesionFormalAsistencia(Request $request)
    {
        try {
            $len = count($request->idAlumnos); //idAlumnos es un arreglo de los IDs de los alumnos
            for ($i = 0; $i < $len; $i++)
            {
                $asistencia = Usuario::find($request->idAlumnos[$i]);
                //Actualiza asistencia
                $asistencia->citas()->updateExistingPivot($request->id_cita, ['asistencia' => $request->asistencia]);
            }
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function registrarSesionFormal(Request $request)
    {
        try {
            //Creacion de la sesion
            $sesiones = new Sesion();
            $sesiones->id_sesion = $request->id_cita;
            $sesiones->resultado = $request->resultado;
            $sesiones->estado = 'act';
            $sesiones->usuario_creacion = $request->usuario_creacion;
            $sesiones->usuario_actualizacion = $request->usuario_actualizacion;
            $sesiones->save();
            //Asistencia de todos los alumnos
            $len = count($request->idAlumnos); //idAlumnos es un arreglo de los IDs de los alumnos
            for ($i = 0; $i < $len; $i++)
            {
                $asistencia = Usuario::find($request->idAlumnos[$i]);
                //Actualiza asistencia
                $asistencia->citas()->updateExistingPivot($request->id_cita, ['asistencia' => $request->asistencia[$i]]);
            }
            //Motivos de la sesion
            $lenM = count($request->idMotivos); //idMotivos es una lista con todos los IDs de los motivos
            for ($j = 0; $j < $lenM; $j++)
            {
                $motivos = MotivoConsulta::find($request->idMotivos[$j]);
                $motivos->sesions()->attach($sesiones->id_sesion);
            }
            return response()->json($sesiones,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /*//Listar usuarios de un programa con sus respectivos roles
    public function usuarioYRoldeunPrograma($idPrograma)
    {
        try {
            $usuarios = UsuarioxPrograma::select('id_usuario','id_tipo_usuario')->where('id_programa',$idPrograma)->get();
            $len = count($usuarios);
            $listaUsuario = [];
            $listaTipos = [];
            for ($i = 0; $i < $len; $i++)
            {
                $aux = Usuario::where([['id_usuario', $usuarios[$i]->id_usuario], ['estado','act']])->get();
                if (count($aux) > 0)
                {
                    array_push($listaUsuario, $aux);
                    $aux1 = TipoUsuario::where('id_tipo_usuario',$usuarios[$i]->id_tipo_usuario)->get();
                    if (count($aux1) > 0)
                        array_push($listaTipos, $aux1);
                }
            }
            $lista = [$listaUsuario,$listaTipos];
            return response()->json($lista,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }*/
}
