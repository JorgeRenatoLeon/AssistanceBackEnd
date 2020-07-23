<?php

namespace App\Http\Controllers;
use App\Disponibilidad;
use Exception;
use App\Http\Controllers\Controller;
use App\MotivoConsulta;
use Illuminate\Http\Request;

class MotivoConsultaController extends Controller
{
    public function index()
    {
        try {
            return response()->json(MotivoConsulta::all());
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function show($id)
    {
        try {
            $motivoCons = MotivoConsulta::findOrFail($id);
            return response()->json($motivoCons);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function store(Request $request)
    {
        try {
            $motivoCons = new MotivoConsulta();
            $motivoCons->nombre = $request->nombre;
            $motivoCons->usuario_creacion = $request->usuario_creacion;
            $motivoCons->usuario_actualizacion = $request->usuario_actualizacion;
            $motivoCons->estado = 'act';
            $motivoCons->save();
            return response()->json($motivoCons,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $motivoCons = MotivoConsulta::findOrFail($id);
            $motivoCons->nombre = $request->nombre;
            $motivoCons->usuario_creacion = $request->usuario_creacion;
            $motivoCons->usuario_actualizacion = $request->usuario_actualizacion;
            $motivoCons->estado = $request->estado;
            $motivoCons->save();
            return response()->json($motivoCons,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function destroy($id)
    {
        try {
            $motivoCons = MotivoConsulta::findOrFail($id);
            $motivoCons->estado = 'eli';
            $motivoCons->save();
            return response()->json(null,204);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function asistencia(Request $request)
    {
        try {
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $consulta=Disponibilidad::selectRaw('count(cita_x_usuario.id_usuario) as total ,motivo_consulta.nombre as data')
                ->join('cita','cita.id_disponibilidad','=','disponibilidad.id_disponibilidad')
                ->join('sesion','sesion.id_sesion','=','cita.id_cita')
                ->join('cita_x_usuario','cita.id_cita','=','cita_x_usuario.id_cita')
                ->join('sesion_x_motivo_consulta','sesion_x_motivo_consulta.id_sesion','=','sesion.id_sesion')
                ->join('motivo_consulta','motivo_consulta.id_motivo_consulta','=','sesion_x_motivo_consulta.id_motivo_consulta')
                ->where('disponibilidad.id_programa','=',$request->id_programa)
                ->where('disponibilidad.estado','=','act')
                ->where('cita_x_usuario.asistencia','=','asi')
                ->where('motivo_consulta.estado','=','act')
                ->where('sesion.estado','=','act')
                ->whereIn('disponibilidad.id_usuario',$request->id_tutores)
                ->whereBetween('disponibilidad.fecha',[$ini,$fin])
                ->groupBy('motivo_consulta.id_motivo_consulta')
                ->orderBy('motivo_consulta.nombre','ASC')
                ->get();
            return response()->json($consulta);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
}
