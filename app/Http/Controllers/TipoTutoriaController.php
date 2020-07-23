<?php

namespace App\Http\Controllers;
use App\Disponibilidad;
use App\Programa;
use App\RegistroAlumno;
use App\TipoUsuario;
use App\Usuario;
use App\UsuarioXPrograma;
use Exception;
use Illuminate\Http\Request;
use App\TipoTutoria;
use Illuminate\Support\Facades\DB;

class TipoTutoriaController extends Controller
{
    //Listar tipos de tutorias de un programa, activos e inactivos
    public function index($id_programa)
    {
        try {
            $tipoTutorias = TipoTutoria::where([['id_programa', $id_programa], ['estado', '!=', 'eli']])->get();
            return response()->json($tipoTutorias);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Listar tipos de tutorias de un programa, solo activos
    public function listarActivos($id_programa)
    {
        try {
            $tipoTutorias = TipoTutoria::where([['id_programa', $id_programa], ['estado', 'act']])->get();
            return response()->json($tipoTutorias);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if ($request->tutor_fijo == 0) {
                $request->merge(['tutor_asignado' => null]);
            }
            $tipoTutoriaNuevo = TipoTutoria::create([
                'id_programa' => $request->id_programa,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'obligatorio' => $request->obligatorio,
                'individual' => $request->individual,
                'tutor_fijo' => $request->tutor_fijo,
                'tutor_asignado' => $request->tutor_asignado,
                'bajo_rendimiento' => $request->bajo_rendimiento,
                'usuario_creacion' => $request->usuario_creacion,
                'usuario_actualizacion' => $request->usuario_creacion,
                'estado' => 'act',
            ]);
            $tipoTutoriaNuevo->save();
            return response()->json($tipoTutoriaNuevo);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $tipotutoria = TipoTutoria::findOrFail($id);
            return response()->json($tipotutoria);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function tipoTutoriaNombre(Request $request)
    {
        try {
            $tipoTutorias = TipoTutoria::where([['nombre', $request->nombre], ['estado', '!=', 'eli'],['id_programa',$request->id_programa]])->first();
            return response()->json($tipoTutorias);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            if ($request->tutor_fijo == 0) {
                $request->merge(['tutor_asignado' => null]);
            }
            $tipoTutoria = TipoTutoria::findOrFail($id);
            $tipoTutoria->nombre = $request->nombre;
            $tipoTutoria->descripcion = $request->descripcion;

            $tipoTutoria->obligatorio = $request->obligatorio;
            $tipoTutoria->individual = $request->individual;
            $tipoTutoria->tutor_fijo = $request->tutor_fijo;
            $tipoTutoria->tutor_asignado = $request->tutor_asignado;
            $tipoTutoria->bajo_rendimiento = $request->bajo_rendimiento;

            $tipoTutoria->usuario_actualizacion = $request->usuario_actualizacion;
            $tipoTutoria->estado = $request->estado;
            $tipoTutoria->save();
            return response()->json($tipoTutoria);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $tipoTutoria = TipoTutoria::findOrFail($id);
            $tipoTutoria->usuario_actualizacion = $request->usuario_actualizacion;
            $tipoTutoria->estado = 'eli';
            $tipoTutoria->usuarios()->detach();
            RegistroAlumno::where('estado', 'act')->where('id_tipo_tutoria',$id)
                ->update(array('estado' => 'eli','usuario_actualizacion'=>$request->usuario_actualizacion));
            $tipoTutoria->save();
            return response()->json($tipoTutoria);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function tiposTutoriaPrograma(Request $request){
        try {
            $tipos = TipoTutoria::where('id_programa',$request->id_programa)->where('tutor_fijo',"1")->where('estado','act')->get();
            return response()->json($tipos,200);
        }
        catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Listar los tutores asignados a un tipo de tutoria
    public function TutoresXTipoTutoria(Request $request)
    {
        try {
            $tipoTutoria = TipoTutoria::findOrFail($request->id_programa);
            $tutores_asignados = $tipoTutoria->usuarios()->where('estado', '!=', 'eli')->get();
            return response()->json($tutores_asignados);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Eliminar un tutor del tipo de tutoria
    public function eliTutorTipoTutoria(Request $request)
    {
        try {
            $tipoTutoria = TipoTutoria::findOrFail($request->id_tipo_tutoria);
            $tipoTutoria->usuarios()->detach($request->id_tutor);
            return response()->json(['status' => 'Tutor Eliminado'], 200);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //lista la cantidad de atendidos por tipo de tutoría
    public function asistenciaXTipoTutorias(Request $request)
    {
        try {
            $ini = $request->fecha_ini;
            $fin = $request->fecha_fin . ' 23:59:59';
            $registro = Disponibilidad::selectRaw("count(cita_x_usuario.id_cita) as total ,tipo_tutoria.id_tipo_tutoria,tipo_tutoria.nombre as data")
                ->join('cita', 'cita.id_disponibilidad', '=', 'disponibilidad.id_disponibilidad')
                ->join('tipo_tutoria', 'tipo_tutoria.id_tipo_tutoria', '=', 'cita.id_tipo_tutoria')
                ->join('cita_x_usuario', 'cita.id_cita', '=', 'cita_x_usuario.id_cita')
                ->where('tipo_tutoria.id_programa', '=', $request->id_programa)
                ->where('disponibilidad.id_programa', '=', $request->id_programa)
                ->where('disponibilidad.estado', '=', 'act')
                ->where('cita.estado', '=', 'act')
                ->where('tipo_tutoria.estado', '=', 'act')
                ->where('cita_x_usuario.asistencia', '=', 'asi')
                ->whereIn('disponibilidad.id_usuario', $request->id_tutores)
                ->whereBetween('disponibilidad.fecha', [$ini, $fin])
                ->groupBy('tipo_tutoria.id_tipo_tutoria')
                ->orderBy('tipo_tutoria.nombre', 'ASC')
                ->get();
            return response()->json($registro, 200);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //lista a todos los alumnos que tienen tipo de tutoría con su tutor asignado
    public function listaAlumnosConTipoTutoriaYTutorAsignado(Request $request)
    {
        try {
//            $tutor_fijo = ['0','1'];
//            $registro['data'] = TipoTutoria::selectRaw("registro_alumno.id_tutor, tipo_tutoria.nombre as tipotutoria, usuario.*")
//                ->join('tipo_tutoria_x_usuario', 'tipo_tutoria_x_usuario.id_tipo_tutoria', '=', 'tipo_tutoria.id_tipo_tutoria')
//                ->join('usuario_x_programa', 'usuario_x_programa.id_usuario', '=', 'tipo_tutoria_x_usuario.id_usuario')
//                ->join('permiso_x_tipo_usuario', 'permiso_x_tipo_usuario.id_tipo_usuario', '=', 'usuario_x_programa.id_tipo_usuario')
//                ->join('usuario', 'usuario.id_usuario', '=', 'tipo_tutoria_x_usuario.id_usuario')
//                //->leftjoin('registro_alumno','registro_alumno.id_alumno','=','tipo_tutoria_x_usuario.id_usuario')
//                ->leftjoin('registro_alumno', function ($join) {
//                    $join->on('registro_alumno.id_alumno', '=', 'tipo_tutoria_x_usuario.id_usuario');
//                })
//                ->where('permiso_x_tipo_usuario.id_permiso', '=', 12)
//                ->where('usuario.estado','=','act')
//                //->where('registro_alumno.estado', '=', 'act') //si se pone esta condición solo listarará a los que si o si tienen tutor
//                    //pero hay un error cuando exista el historico, ya que no valido que esa asignación este activa
//                ->where('usuario_x_programa.id_programa', '=', $request->id_programa)
//                ->whereIn('tipo_tutoria.tutor_asignado', $tutor_fijo)
//                ->orderBy('usuario.codigo', 'ASC')
//                ->get();
//            //buscar el nombre de los tutores
//            if(!empty($registro['data'][0])){
//                foreach($registro['data'] as $data){
//                    if($data->id_tutor){
//                        $usuario=Usuario::selectRaw('usuario.nombre as nombre')->where('id_usuario','=',$data->id_tutor)->get();
//                        $data['nombreTutor']=$usuario[0]->nombre;
//                    }else{
//                        $data['nombreTutor']='No tiene tutor';
//                    }
//                }
//            }
            $tiposProg = TipoUsuario::where('id_programa',$request->id_programa)->get();
            $idFacu = Programa::where('nombre',$request->nomFacu)->first()->id_programa;
            $tiposFacu = TipoUsuario::where('id_programa',$idFacu)->get();
            $tiposGen = TipoUsuario::where('id_programa',1)->get();

            $tiposTotal = array();
            foreach ($tiposProg as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['nombre']=="Tutores"){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposFacu as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['nombre']=="Tutores"){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposGen as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['nombre']=="Tutores"){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }


            $alumnos = UsuarioxPrograma::
            selectRaw("*")
                ->join('usuario','usuario.id_usuario','=','usuario_x_programa.id_usuario')
                ->where('id_programa',$request->id_programa)
                ->where('usuario.nombre','ILIKE','%'.$request->nombre.'%');

            $alumnos = $alumnos->where(function($query) use ($tiposTotal)  {
                for ($i = 0; $i <= count($tiposTotal)-1; $i++) {
                    if($i==0) $query->where('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                    else $query->orWhere('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                }
            });

            $alumnos = $alumnos->get();
            $alumnosFinal = array();

            $condciones=DB::table('valores')->where('tabla','CONDICION_ALUMNO')->get();
            foreach ($alumnos as $alumno) {
                $alumno->usuario;
                $registros = RegistroAlumno::
                    where('id_alumno',$alumno->usuario['id_usuario'])
                    ->where('id_programa',$request->id_programa)
                    ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                    ->where('estado','act')->get();
                if(count($registros)>0){
                    foreach ($condciones as $condcione) {
                        if($condcione->abreviatura == $alumno->usuario['condicion_alumno']){
                            $alumno->usuario['cond'] = $condcione->nombre;
                        }
                    }
                    $tutoresFinal = array();
                    foreach ($registros as $ta) {
                        if($ta->id_tutor){
                            $tutorAsignadoM = Usuario::findOrFail($ta->id_tutor);
                            array_push($tutoresFinal, $tutorAsignadoM);
                        }
                    }
                    $alumno->usuario['tutoresAsignados'] = $tutoresFinal;
                    array_push($alumnosFinal,$alumno['usuario']);
                }
            }

            return response()->json($alumnosFinal, 200);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function listaTutoresDeTipoTutoria(Request $request)
    {
        try {
            $tiposProg = TipoUsuario::where('id_programa',$request->id_programa)->get();
            $idFacu = Programa::where('nombre',$request->nomFacu)->first()->id_programa;
            $tiposFacu = TipoUsuario::where('id_programa',$idFacu)->get();
            $tiposGen = TipoUsuario::where('id_programa',1)->get();

            $tiposTotal = array();
            foreach ($tiposProg as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposFacu as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposGen as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }


            $tutores = UsuarioxPrograma::
            selectRaw("*")
                ->join('usuario','usuario.id_usuario','=','usuario_x_programa.id_usuario')
                ->where('id_programa',$request->id_programa)
                ->where('usuario.nombre','ILIKE','%'.$request->nombre.'%');

            $tutores = $tutores->where(function($query) use ($tiposTotal)  {
                for ($i = 0; $i <= count($tiposTotal)-1; $i++) {
                    if($i==0) $query->where('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                    else $query->orWhere('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                }
            });

            $tutores = $tutores->get();
            $tutoresFinal = array();

            foreach ($tutores as $tutor) {
                $tutor->usuario;
                $si = false;
                foreach ($tutor['usuario']->tipoTutorias as $item) {
                    if($item['id_tipo_tutoria'] == $request->id_tipo_tutoria){
                        array_push($tutoresFinal,$tutor['usuario']);
                        break;
                    }
                }
            }

            return response()->json($tutoresFinal, 200);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
}
