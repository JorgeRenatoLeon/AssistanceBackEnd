<?php

namespace App\Http\Controllers;

use App\Compromiso;
use Illuminate\Http\Request;

class CompromisoController extends Controller
{
    //Inserta
    public function store(Request $request)
    {
        try {
            $compromiso = new Compromiso();
            $compromiso->id_plan_accion=$request->id_plan_accion;
            $compromiso->nombre=$request->nombre;
            $compromiso ->estado='act';
            $compromiso->save();
            return response()->json($compromiso,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Actualiza
    public function update(Request $request, $id)
    {
        try {
            $compromiso=Compromiso::findOrFail($id);
            $compromiso->nombre=$request->nombre;
            $compromiso->estado=$request->estado;
            $compromiso->save();
            return response()->json($compromiso,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Eliminar
    public function destroy($id)
    {
        try {
            $compromiso=PlanAccion::findOrFail($id);
            $compromiso->estado='eli';
            $compromiso->save();
            return response()->json(200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
}
