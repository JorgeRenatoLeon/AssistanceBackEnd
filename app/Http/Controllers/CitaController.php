<?php

namespace App\Http\Controllers;
use App\Disponibilidad;
use Exception;
use App\Cita;
use App\Http\Controllers\Controller;
use App\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher;

class CitaController extends Controller
{
    public function index()
    {
        try{
            $citas = Cita::where('estado','act')->get();
            return response()->json($citas);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function show($id)
    {
        try{
            $citas = Cita::findOrFail($id);
            return response()->json($citas);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function store(Request $request)
    {
        try {
            $citas = new Cita();
            $citas->id_tipo_tutoria = $request->id_tipo_tutoria;
            $citas->id_disponibilidad = $request->id_disponibilidad;
            $citas->nota = $request->nota;
            $citas->estado = 'act';
            $citas->usuario_creacion = $request->usuario_creacion;
            $citas->usuario_actualizacion = $request->usuario_actualizacion;
            $citas->save();
            return response()->json($citas,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $citas = Cita::findOrFail($id);
            $citas->id_tipo_tutoria = $request->id_tipo_tutoria;
            $citas->id_disponibilidad = $request->id_disponibilidad;
            $citas->nota = $request->nota;
            $citas->estado = $request->estado;
            $citas->usuario_creacion = $request->usuario_creacion;
            $citas->usuario_actualizacion = $request->usuario_actualizacion;
            $citas->save();
            return response()->json($citas,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function destroy($id)
    {
        try {
            $citas = Cita::findOrFail($id);
            $citas->estado = 'eli';
            $citas->save();
            return response()->json(null, 204);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Registro de cita desde la vista del alumno
    public function registrarCitaAlumno(Request $request)
    {
        try {
            $citas = new Cita();
            $citas->id_tipo_tutoria = $request->id_tipo_tutoria;
            $citas->id_disponibilidad = $request->id_disponibilidad;
            //$citas->nota = $request->nota;
            $citas->estado = 'act';
            $citas->usuario_creacion = $request->usuario_creacion;
            $citas->usuario_actualizacion = $request->usuario_actualizacion;
            $citas->save();
            //Registrando cita_x_usuario
            $aux = Usuario::find($request->idUsuario); //idAlumno
            $aux->citas()->attach($citas->id_cita);
            return response()->json($citas,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //El tutor cancela la cita
    public function cancelarCita(Request $request)
    {
        try {
            $citas = Cita::where([['id_cita',$request->idCita],['id_disponibilidad',$request->idDisponibilidad]])->get();
            $citas[0]->estado = 'eli';
            $citas[0]->usuario_actualizacion = $request->usuario_actualizacion;
            //echo $citas[0]->id_cita, "\n";
            $citas[0]->save();
            return response()->json($citas,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //funcion para solo mostrar el tutor, la fecha , hora, asistencia; los detalles seran en otra funcion
    public function histCitasAlumno(Request $request)
    {
        try {
            $usuario = Usuario::findOrFail($request->id_usuario);
            $citas = $usuario->citas()->
            with('disponibilidad.usuario:id_usuario,nombre,apellidos')->get();
            $resp = array();
            if($citas!=null){
                foreach ($citas as $cita) {
                    if($cita!=null){
                        if($cita->estado == 'act'){
                            if($cita->sesion!=null) $cita->sesion->motivoConsultas;
                            $cita->tipoTutoria;
                            array_push($resp,$cita);
                        }
                    }
                }
            }
            return response()->json($resp,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Registro de cita desde la vista del Coordinador
    public function registrarCitaCoord(Request $request)
    {
        try {
            $cita = new Cita();
            $cita->id_tipo_tutoria = $request->id_tipo_tutoria;
            $cita->id_disponibilidad = $request->id_disponibilidad;
            //$citas->nota = $request->nota;
            $cita->estado = 'act';
            $cita->usuario_creacion = $request->usuario_creacion;
            $cita->usuario_actualizacion = $request->usuario_creacion;
            $cita->save();
            //Registrando cita_x_usuario
            $cita->citaXUsuarios()->attach($request->idUsuario);
            return response()->json($cita,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Editar la cita desde la vista del Coordinador
    //Se puede editar: los alumnos, el tipo de tutoria,
    public function editarCitaCoord(Request $request)
    {
        try {
            $cita=Cita::findOrFail($request->id_cita);

            $cita->id_tipo_tutoria = $request->id_tipo_tutoria;
            $cita->usuario_actualizacion = $request->usuario_actualizacion;
            $cita->save();
            $usuarios=$cita->citaXUsuarios()->get()->toArray();
            $ids_usuarios = array_column($usuarios, 'id_usuario');
            $usuarios_final = $request->id_usuario;
            //$ids_tutorias_actual = array_column($tutorias, 'id_tipo_tutoria');
            $eliminar = array_diff($ids_usuarios, $usuarios_final);
            $insertar = array_diff($usuarios_final, $ids_usuarios);
            if (count($insertar) > 0) {
                $cita->citaXUsuarios()->attach($insertar);
            }
            if (count($eliminar) > 0) {
                $cita->citaXUsuarios()->detach($eliminar);
            }
            return response()->json(['status'=>'Cambios realizados'],201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function listCitaTutor(Request $request)
    {
        try {
            $fecha_ini=$request->fecha_ini;
            $fecha_fin=$request->fecha_fin;
            $usuario=$request->id_usuario;
            $tipo=$request->tipo;
            $id_programa=$request->id_programa;

            $sub=DB::table('disponibilidad')->
            selectRaw('cita.id_cita,disponibilidad.id_disponibilidad,TO_CHAR(fecha :: DATE, \'dd/mm/yyyy\') as fecha,
            disponibilidad.hora_inicio,CASE
            when sum(case when asistencia=\'can\' or asistencia=\'sol\' then 1 else 0 end)>0 then \'Cancelada\'
            when fecha+hora_inicio>now() then \'Pendiente\'
            when fecha+hora_inicio<=now() then \'Realizada\' end as tipo_de_cita')->
            join('cita','cita.id_disponibilidad','=','disponibilidad.id_disponibilidad')->
            join('cita_x_usuario','cita.id_cita','=','cita_x_usuario.id_cita')->
            join('usuario','usuario.id_usuario','=','disponibilidad.id_usuario')->
            whereBetween('disponibilidad.fecha',[$fecha_ini,$fecha_fin])->
            where('disponibilidad.id_usuario','=',$usuario)->
            where('disponibilidad.id_programa','=',$id_programa)->
            groupBy('cita.id_cita','disponibilidad.id_disponibilidad')->
            orderBy('disponibilidad.fecha','desc')->
            orderBy('hora_inicio','desc');


            $citas=Cita::fromSub($sub,'subquery')->with('citaXUsuarios:cita_x_usuario.id_usuario,nombre,apellidos');
            if($tipo!=""){
                $citas->where('tipo_de_cita','=',$tipo);
            }
            $citas=$citas->paginate(10);

            if (is_null($citas[0])){
                return response()->json([],204);
            }else{
                //se retorna un array
                $datosFinal=['paginate' => [
                    'total'         => $citas->total(),
                    'current_page'  => $citas->currentPage(),
                    'per_page'      => $citas->perPage(),
                    'last_page'     => $citas->lastPage(),
                    'from'          => $citas->firstItem(),
                    'to'            => $citas->lastPage(),
                ],
                    'tasks'=> $citas
                ];
                //compact('datos')
                return response()->json($datosFinal);
            }
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function listCitaAlu(Request $request)
    {
        try {
            $fecha_ini=$request->fecha_ini;
            $fecha_fin=$request->fecha_fin;
            $usuario=$request->id_usuario;
            $tipo=$request->tipo;
            $id_programa=$request->id_programa;

            $sub=DB::table('cita')->
            selectRaw('cita.id_cita,cita_x_usuario.asistencia,disponibilidad.id_disponibilidad,disponibilidad.hora_inicio,
            usuario.nombre,usuario.apellidos,TO_CHAR(fecha :: DATE, \'dd/mm/yyyy\') as fecha,CASE
            when asistencia=\'can\' or asistencia=\'sol\' then \'Cancelada\'
            when fecha+hora_inicio>now() then \'Pendiente\'
            when fecha+hora_inicio<=now() then \'Realizada\' end as tipo_de_cita')->
            join('cita_x_usuario','cita.id_cita','=','cita_x_usuario.id_cita')->
            join('disponibilidad','disponibilidad.id_disponibilidad','=','cita.id_disponibilidad')->
            join('usuario','usuario.id_usuario','=','disponibilidad.id_usuario')->
            where('cita_x_usuario.id_usuario','=',$usuario)->
            whereBetween('disponibilidad.fecha',[$fecha_ini,$fecha_fin])->
            where('disponibilidad.id_programa','=',$id_programa)->
            orderBy('disponibilidad.fecha','desc')->
            orderBy('disponibilidad.hora_inicio','desc');


            $citas=Disponibilidad::fromSub($sub,'subquery')->selectRaw("*");
            if($tipo!=""){
                $citas->where('tipo_de_cita','=','$tipo');
            }
            $citas=$citas->paginate(10);
            if (is_null($citas[0])){
                return response()->json([],204);
            }else{
                //se retorna un array
                $datosFinal=['paginate' => [
                    'total'         => $citas->total(),
                    'current_page'  => $citas->currentPage(),
                    'per_page'      => $citas->perPage(),
                    'last_page'     => $citas->lastPage(),
                    'from'          => $citas->firstItem(),
                    'to'            => $citas->lastPage(),
                ],
                    'tasks'=> $citas
                ];
                //compact('datos')
                return response()->json($datosFinal);
            }
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }


}
