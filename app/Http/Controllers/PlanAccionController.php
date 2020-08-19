<?php

namespace App\Http\Controllers;

use App\Compromiso;
use App\PlanAccion;
use Illuminate\Http\Request;

class PlanAccionController extends Controller
{
    //Inserta
    public function store(Request $request)
    {
        try {
            $planAccion = new PlanAccion();
            $planAccion->id_tutor=$request->id_tutor;
            $planAccion->id_alumno=$request->id_alumno;
            $planAccion->nombre=$request->nombre;
            $planAccion->fecha_inicio=$request->fecha;
            $planAccion->descripcion=$request->descripcion;
            $planAccion->id_programa=$request->id_programa;
            $planAccion->usuario_creacion = $request->id_tutor;
            $planAccion->estado='act';
            $planAccion->save();

            $planAccion = PlanAccion::where('id_tutor',$request->id_tutor)->where('id_alumno',$request->id_alumno)->where('nombre',$request->nombre)->first();
            foreach ($request->compromisos as $compromiso) {
                $comp = new Compromiso();
                $comp->nombre = $compromiso['nombre'];
                $comp->estado = $compromiso['estado'];
                $comp->id_plan_accion = $planAccion->id_plan_accion;
                $comp->usuario_creacion = $request->id_tutor;
                $comp->save();
            }

            return response()->json($planAccion,200);

        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Actualiza
    public function update(Request $request, $id)
    {
        try {
            $planAccion=PlanAccion::findOrFail($id);

            if($request->mod){
                $planAccion->nombre = $request->mod['nombre'];
                $planAccion->descripcion = $request->mod['descripcion'];
                $planAccion->fecha_inicio=$request->mod['fecha_inicio'];
                $planAccion->save();
            }

            if($request->compromisos){
                foreach ($request->compromisos as $compromiso) {
                    $comp = new Compromiso();
                    $comp->nombre = $compromiso['nombre'];
                    $comp->estado = $compromiso['estado'];
                    $comp->id_plan_accion = $planAccion->id_plan_accion;
                    $comp->usuario_creacion = $request->id_tutor;
                    $comp->save();
                }
            }


            if($request->cambios){
                foreach ($request->cambios as $cambio) {
                    $comp = Compromiso::where('nombre',$cambio['nombre'])->where('id_plan_accion',$planAccion->id_plan_accion)->first();
                    $comp->estado = $cambio['estado'];
                    $comp->save();
                }
            }

            if($request->eliminados){
                foreach ($request->eliminados as $eliminado) {
                    $comp = Compromiso::where('nombre',$eliminado['nombre'])->where('id_plan_accion',$planAccion->id_plan_accion)->first();
                    $comp->estado = 'eli';
                    $comp->save();
                }
            }


            return response()->json($planAccion,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Eliminar
    public function destroy($id)
    {
        try {
            $planAccion=PlanAccion::findOrFail($id);
            $planAccion->estado='eli';
            $planAccion->save();
            return response()->json(200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //mostrarPlanAccion de un Alumno con un tutor
    public function planAlumno(Request $request)
    {
        try {
            $planAccion=PlanAccion::where('id_alumno',$request->id_alumno)->where('id_tutor',$request->id_tutor)->where('estado','act')->get();
            if($planAccion!=null){
                foreach ($planAccion as $item) {
                    $item->compromisos;
                }
            }
            return response()->json($planAccion,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

}
