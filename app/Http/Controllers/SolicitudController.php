<?php

namespace App\Http\Controllers;

use App\Programa;
use App\TipoTutoria;
use Exception;
use App\RegistroAlumno;
use Carbon\Carbon;
use App\Solicitud;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    //Lista
    public function index()
    {
        try {
            $solicitudes = Solicitud::all();
            foreach ($solicitudes as $solicitud) {
                $solicitud->usuarioSol;
                $solicitud->usuarioRem;
                $solicitud->cita;
            }
            return response()->json($solicitudes);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Lista por ID
    public function show($id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);
            return response()->json($solicitud);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Inserta
    public function store(Request $request)
    {
        try {
            if($request->tipo_solicitud = 'Cita'){
                $antSol= Solicitud::where('id_solicitante',$request->id_solicitante)
                    ->where('id_remitente',$request->id_remitente)
                    ->where('tipo_solicitud',$request->tipo_solicitud)
                    ->where('id_programa',$request->id_programa)
                    ->first();
                if($antSol && $antSol->estado == 'act'){
                    return response()->json(['status'=>'error','mensaje'=>'Tiene una solicitud pendiente con el tutor'],201);
                }
                else{
                    if($antSol && $antSol->id_cita == $request->id_cita) return response()->json(['status'=>'error','mensaje'=>'Ya se realizó dicha solicitud'],201);
                    else{
                        if($antSol){
                            $antSol->id_cita = $request->id_cita;
                            $antSol->motivo = $request->motivo;
                            $antSol->estado = 'act';
                            $antSol->usuario_actualizacion = $request->usuario_actualizacion;
                            $antSol->save();
                            return response()->json($antSol,201);
                        }
                    }
                }
            }
            $solicitud = new Solicitud();
            $solicitud->id_solicitante = $request->id_solicitante;
            $solicitud->id_remitente = $request->id_remitente;
            $solicitud->tipo_solicitud = $request->tipo_solicitud;
            $solicitud->estado = 'act';
            $solicitud->descripcion = $request->descripcion;
            $solicitud->motivo = $request->motivo;
            $solicitud->id_programa = $request->id_programa;
            $solicitud->id_cita = $request->id_cita;
            $solicitud->usuario_creacion = $request->usuario_creacion;
            $solicitud->id_usuario_relacionado = $request->id_usuario_relacionado;
            $solicitud->save();
            return response()->json($solicitud,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Actualiza
    public function update(Request $request, $id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);
            $solicitud->update($request->all());
            return response()->json($solicitud,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Eliminar
    public function destroy(Request $request)
    {
        try {
            $solicitud = Solicitud::where('id_solicitante',$request->id_solicitante)->where('id_remitente',$request->id_remitente)->first();
            $solicitud->estado = 'eli';
            $solicitud->usuario_actualizacion = $request->usuario_actualizacion;;
            $solicitud->save();
            return response()->json(null,204);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }


    //Listar mis Solicitudes
    public function listarSol(Request $request)
    {
        try {
            $solicitudes = Solicitud::where('id_remitente',$request->id)->where('id_programa',$request->id_programa)->where('estado','act')->get();
            foreach ($solicitudes as $solicitud) {
                $solicitud->usuarioSolicitante = $solicitud->usuarioSol;
                $solicitud->usuarioRemitente = $solicitud->usuarioRem;
                $solicitud->usuarioRelacionado = $solicitud->usuarioRel;
                $solicitud->cita;
                if($solicitud->cita) $solicitud->cita->disponibilidad;
            }
            return response()->json($solicitudes);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function solicitudTutor(Request $request)
    {
        try {
            //revisar si esta habilitado
            $habilitado = Solicitud::where('estado','act')->where('id_solicitante',$request->id_solicitante)
                ->where('tipo_solicitud','Tutor')->where('id_programa',$request->id_programa)->first();
            $habilitados = Solicitud::where('estado','eli')->where('id_solicitante',$request->id_solicitante)
                ->where('tipo_solicitud','Tutor')->where('id_programa',$request->id_programa)->first();
            $tutorSol = Solicitud::where('estado','eli')->where('id_solicitante',$request->id_solicitante)
                ->where('tipo_solicitud','Tutor')->where('id_programa',$request->id_programa)
                ->where('id_usuario_relacionado',$request->id_tutor)->first();
            if($habilitado){
                return response()->json(['habilitado'=>'No','mensaje'=>'Tiene una solicitud pendiente'],200);
            }else if($habilitados){
                if($tutorSol){
                    $idtt = TipoTutoria::where('nombre',$request->motivo)->first()->id_tipo_tutoria;
                    $tutor = RegistroAlumno::where('id_alumno',$request->id_solicitante)->where('id_tutor',$habilitados['id_usuario_relacionado'])->where('id_tipo_tutoria',$idtt)->where('estado','act')->first();
                    if($tutor){
                        return response()->json(['habilitado'=>'No','mensaje'=>'Ya se respondió su solicitud'],200);
                    }
                    else{
                        //buscar al coordinador de programa de ese id_programa
                        $programa = Programa::find($request->id_programa);
                        $datos['coordinador']=$programa->belongsToMany('App\Usuario','usuario_x_programa','id_programa','id_usuario')->withPivot('id_tipo_usuario')->where('usuario_x_programa.id_tipo_usuario',3)->get();
                        //id_remitente= id_coordinador
                        $id_remitente=$datos['coordinador'][0]['id_usuario'];
                        //id_usuario_creacion=id_solicitante
                        Solicitud::where('estado','eli')
                            ->where('id_solicitante',$request->id_solicitante)
                            ->where('tipo_solicitud','Tutor')
                            ->where('id_programa',$request->id_programa)
                            ->update([
                                'id_remitente'=>$id_remitente,
                                'estado'=>'act',
                                'id_usuario_relacionado'=>$request->id_tutor,
                                'usuario_actualizacion'=>$request->id_solicitante,
                                'motivo'=>$request->motivo]);
                        return response()->json(['habilitado'=>'Si','mensaje'=>'Si se encuentra habilitado'],200);
                    }
                }
                else{
                    Solicitud::where('estado','eli')->where('id_solicitante',$request->id_solicitante)
                        ->where('tipo_solicitud','Tutor')->where('id_programa',$request->id_programa)
                        ->update([
                            'id_usuario_relacionado'=>$request->id_tutor,
                            'estado'=>'act',
                            'usuario_actualizacion'=>$request->id_solicitante,
                            'motivo'=>$request->motivo]);
                    return response()->json(['habilitado'=>'Si','mensaje'=>'Si se encuentra habilitado'],200);
                }

            }else{
                //buscar al coordinador de programa de ese id_programa
                $programa = Programa::find($request->id_programa);
                $datos['coordinador']=$programa->belongsToMany('App\Usuario','usuario_x_programa','id_programa','id_usuario')->withPivot('id_tipo_usuario')->where('usuario_x_programa.id_tipo_usuario',3)->get();
                //id_remitente= id_coordinador
                $id_remitente=$datos['coordinador'][0]['id_usuario'];
                //id_usuario_creacion=id_solicitante
                $solicitud = new Solicitud();
                $solicitud->id_solicitante = $request->id_solicitante;
                $solicitud->id_remitente = $id_remitente;
                $solicitud->tipo_solicitud = 'Tutor';
                $solicitud->estado = 'act';
                $solicitud->descripcion = 'Solicitud para tener un tutor asignado';
                $solicitud->usuario_creacion = $request->id_solicitante;
                $solicitud->motivo=$request->motivo;
                $solicitud->id_usuario_relacionado=$request->id_tutor;
                $solicitud->id_programa=$request->id_programa;
                $solicitud->save();
                return response()->json(['habilitado'=>'Si','mensaje'=>'Si se encuentra habilitado'],200);
            }
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function habilitado(Request $request)
    {
        try{
            $habilitado = Solicitud::where('estado','act')
                ->where('id_solicitante',$request->id_usuario)
                ->where('tipo_solicitud',$request->tipo_solicitud)
                ->where('id_programa',$request->id_programa)
                ->first();

            $habilitados = Solicitud::where('estado','eli')
                ->where('id_solicitante',$request->id_usuario)
                ->where('tipo_solicitud',$request->tipo_solicitud)
                ->where('id_programa',$request->id_programa)
                ->first();

            if($habilitado){
                return response()->json(['habilitado'=>'No','mensaje'=>'Tiene una solicitud pendiente'],200);
            }
            else if($habilitados){
                if($request->tipo_solicitud == 'Tutor'){
                    $tutor = RegistroAlumno::where('id_alumno',$request->id_usuario)->where('id_tutor',$habilitados['id_usuario_relacionado'])->where('estado','act')->first();
                    if($tutor){
                        return response()->json(['habilitado'=>'No','mensaje'=>'Ya se respondió su solicitud'],200);
                    }
                    else{
                        return response()->json(['habilitado'=>'Si','mensaje'=>'Si se encuentra habilitado'],200);
                    }
                }
            }
            else{
                return response()->json(['habilitado'=>'Si','mensaje'=>'Si se encuentra habilitado'],200);
            }
        }
        catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
}
