<?php

namespace App\Http\Controllers;
use App\Facultad;
use Exception;
use App\Permiso;
use App\PermisoxTipoUsuario;
use App\TipoTutoria;
use App\TipoUsuario;
use App\Usuario;
use Illuminate\Http\Request;

class TipoUsuarioController extends Controller
{
    //Lista to-do
    public function index()
    {
        try {
            $tiposUsuario = TipoUsuario::where('estado','act')->get();
            $resp = array();
            foreach ($tiposUsuario as $item) {
                if($item->nombre != "Admin"){
                    $item->programa;
                    $item->permisos;
                    array_push($resp,$item);
                }
            }
            return response()->json($resp);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Lista por ID
    public function show($id)
    {
        try {
            $tipoUsuario = TipoUsuario::findOrFail($id);
            return response()->json($tipoUsuario);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Inserta
    public function store(Request $request)
    {
        try {
            $tipoUsuario = new TipoUsuario();
            $tipoUsuario->descripcion = $request->descripcion;
            $tipoUsuario->usuario_creacion = $request->usuario_creacion;
            $tipoUsuario->usuario_actualizacion = $request->usuario_actualizacion;
            $tipoUsuario->estado = 'act';
            $tipoUsuario->nombre = $request->nombre;
            $tipoUsuario->id_programa = $request->id_programa;
            $tipoUsuario->save();
            return response()->json($tipoUsuario,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Actualiza
    public function update(Request $request, $id)
    {
        try {
            $tipoUsuario = TipoUsuario::findOrFail($id);
            $tipoUsuario->update($request->all());
            return response()->json($tipoUsuario,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Eliminar
    public function destroy(Request $request,$id)
    {
        try {
            $tipoUsuario = TipoUsuario::findOrFail($id);
            $tipoUsuario->usuario_actualizacion = $request->usuario_actualizacion;
            $tipoUsuario->estado = 'eli';
            $tipoUsuario->save();
            $tipoUsuario->usuarios()->detach();
            return response()->json(null,204);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }


    public function listarRol(Request $request)
    {
        try {
            $id_programa=$request->id_programa;
            $tipoUsuario = TipoUsuario::find($request->id_tipoUsuario);
            $usua=$tipoUsuario->usuarios()->where('usuario_x_programa.id_programa',$id_programa)->get();
            return response()->json($usua);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function permisos($id)
    {
        try {
            $tipoUsuario = TipoUsuario::findOrFail($id);
            return response()->json([
                'nombre' => $tipoUsuario->nombre,
                'permisos' => $tipoUsuario->permisos,
                ]);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function modPermisos(Request $request){
        $tipoUs = TipoUsuario::where('nombre',$request->nombre)->first();
        if($tipoUs) {
            $permisos = $tipoUs->permisos;
            $nomArr = array();
            foreach ($permisos as $permiso) {
                array_push($nomArr, $permiso->nombre);
            }
            foreach ($request->cambios as $cambio) {
                $perm = Permiso::where('nombre', $cambio['nombre'])->first();
                $index = array_search($cambio['nombre'], $nomArr);
                if ($index === false) {
                    if ($cambio['estado'] === 'activo') {
                        $perm->tipoUsuarios()->attach($tipoUs->id_tipo_usuario);
                    }
                } else {
                    if ($cambio['estado'] === 'inactivo') {
                        $perm->tipoUsuarios()->detach($tipoUs->id_tipo_usuario);
                    }
                }
            }
            return response()->json('success');
        }
        else{
            $this->store($request);
            $tipoUs = TipoUsuario::where('nombre',$request->nombre)->first();
            foreach ($request->cambios as $cambio) {
                $perm = Permiso::where('nombre', $cambio['nombre'])->first();
                if ($cambio['estado'] === 'activo') {
                    $perm->tipoUsuarios()->attach($tipoUs->id_tipo_usuario);
                }
            }
            return response()->json('success');

        }
    }



    public function tiposAdmin(){
        try {
            $tipos = TipoUsuario::where('estado','act')->get();
            foreach ($tipos as $tipo) {
                $tipo->programa;
            }
            return response()->json($tipos);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function tiposFacultad(Request $request){
        try {
            $resp = array();
            $tipos = TipoUsuario::where('estado','act')->get();
            foreach ($tipos as $tipo) {
                $tipo->programa;
                if($tipo->programa['id_facultad']==$request->id_facultad || $tipo->programa['id_programa']==1){
                    if($tipo['nombre']!= 'Admin' && $tipo['id_tipo_usuario']!= 2) array_push($resp,$tipo);
                }
            }
            return response()->json($resp);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function tiposPrograma(Request $request){
        try {
            $resp = array();
            $tipos = TipoUsuario::where('estado','act')->get();
            $facu = Facultad::where('id_facultad',$request->id_facultad)->first();
            foreach ($tipos as $tipo) {
                //echo gettype($tipo)."\n";
                $tipo->programa;
                if($tipo->programa['id_programa']==$request->id_programa || $tipo->programa['id_programa']==1 || $tipo->programa['nombre']==$facu->nombre){
                    if($tipo['id_programa']!= '1' || ($tipo['id_programa']== '1' && ($tipo['nombre']== "Tutor" || $tipo['nombre']== "Alumno"))) {
                        $permisos=$tipo->permisos()->pluck('permiso.id_permiso');
                        //return response()->json($permisos);
                        $esTutor=0;
                        $esAlumno=0;
                        foreach ($permisos as $perm){
                            if($perm==12)$esAlumno=1;//12 es el id del permiso especial a alumno cargado en base de datos
                            if($perm==21)$esTutor=1;//21 es el id del permiso especial a tutor cargado en base de datos
                        }
                        $tipo->esTutor=$esTutor;
                        $tipo->esAlumno=$esAlumno;
                        array_push($resp,$tipo);
                    }
                }

            }
            return response()->json($resp);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

}
