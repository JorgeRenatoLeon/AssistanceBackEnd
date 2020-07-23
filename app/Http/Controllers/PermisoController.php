<?php

namespace App\Http\Controllers;
use Exception;
use App\Permiso;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    //Lista todo
    public function index()
    {
        try {
            return response()->json(Permiso::all());
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Lista por ID
    public function show($id)
    {
        try {
            $permiso = Permiso::findOrFail($id);
            return response()->json($permiso);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Inserta
    public function store(Request $request)
    {
        try {
            $permiso = new Permiso();
            $permiso->id_modulo_permiso = $request->id_modulo_permiso;
            $permiso->nombre = $request->nombre;
            $permiso->descripcion = $request->descripcion;
            //$permiso->fecha_creacion = $request->fecha_creacion;
            //$permiso->fecha_actualizacion = $request->fecha_actualizacion;
            $permiso->estado = 'act';
            $permiso->usuario_creacion = $request->usuario_creacion;
            $permiso->usuario_actualizacion = $request->usuario_actualizacion;
            $permiso->save();
            return response()->json($permiso,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Actualiza
    public function update(Request $request, $id)
    {
        try {
            $permiso = Permiso::findOrFail($id);
            $permiso->id_modulo_permiso = $request->id_modulo_permiso;
            $permiso->nombre = $request->nombre;
            $permiso->descripcion = $request->descripcion;
            //$permiso->fecha_creacion = $request->fecha_creacion;
            //$permiso->fecha_actualizacion = $request->fecha_actualizacion;
            $permiso->estado = $request->estado;
            $permiso->usuario_creacion = $request->usuario_creacion;
            $permiso->usuario_actualizacion = $request->usuario_actualizacion;
            $permiso->save();
            return response()->json($permiso,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Eliminar
    public function destroy($id)
    {
        try {
            $permiso = Permiso::findOrFail($id);
            $permiso->estado = 'eli';
            $permiso->save();
            return response()->json(null,204);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
}
