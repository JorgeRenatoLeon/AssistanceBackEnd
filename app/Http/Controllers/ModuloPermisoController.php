<?php

namespace App\Http\Controllers;
use Exception;
use App\Permiso;
use Illuminate\Http\Request;
use App\ModuloPermiso;

class ModuloPermisoController extends Controller
{
    public function index()
    {
        try {
            $moduloPermiso = ModuloPermiso::where('estado', 'act')->get();
            return response()->json($moduloPermiso);
        }catch(Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $moduloPermisoNuevo = ModuloPermiso::create([
                'nombre' => $request->nombre,
                'estado' => 'act',
                'usuario_creacion' => $request->usuario_creacion,
                'usuario_actualizacion' => $request->usuario_actualizacion,
            ]);
            $moduloPermisoNuevo->save();
            return response()->json($moduloPermisoNuevo);
        }catch(Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
        $moduloPermiso=ModuloPermiso::findOrFail($id);
        return response()->json($moduloPermiso);
        }catch(Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{
        $moduloPermiso = ModuloPermiso::findOrFail($id);
        $moduloPermiso->nombre=$request->nombre;
        $moduloPermiso->usuario_actualizacion=$request->usuario_actualizacion;
        $moduloPermiso->estado=$request->estado;
        $moduloPermiso->save();
        return response()->json($moduloPermiso);
        }catch(Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
        $moduloPermiso = ModuloPermiso::findOrFail($id);
        $moduloPermiso->estado='eli';
        $moduloPermiso->save();
        return response()->json($moduloPermiso);
        }catch(Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function listarTodoPermisos()
    {
        $perms = array();
        $moduloPermiso = ModuloPermiso::where('estado', 'act')->get();
        foreach ($moduloPermiso as $item) {
            $perms[$item->id_modulo_permiso]['nombre'] = $item->nombre;
            $perms[$item->id_modulo_permiso]['permisos'] = array();
        }
        $permisos = Permiso::all();
        foreach ($permisos as $permiso) {
            if($permiso->moduloPermiso) {
                array_push($perms[$permiso->moduloPermiso->id_modulo_permiso]['permisos'], [
                    'nombre'=>$permiso->nombre,
                    'descripcion'=>$permiso->descripcion,
                ]);
            }
        }
        return $perms;
    }
}
