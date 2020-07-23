<?php

namespace App\Http\Controllers;
use Exception;
use App\Facultad;
use App\Http\Controllers\Controller;
use App\Programa;
use App\UnidadApoyo;
use Illuminate\Http\Request;

class UnidadApoyoController extends Controller
{
    public function index()
    {
        try {
            $unidades = UnidadApoyo::where('estado','act')->get();
            return response()->json($unidades);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function show($id)
    {
        try {
            $unidades = UnidadApoyo::findOrFail($id);
            $unidades->programas;
            return response()->json($unidades,200);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    public function store(Request $request)
    {
        try {
            $unidades =new UnidadApoyo();
            $unidades->nombre = $request->nombre;
            $unidades->nombre_contacto = $request->nombre_contacto;
            $unidades->correo_contacto = $request->correo_contacto;
            $unidades->telefono_contacto = $request->telefono_contacto;
            $unidades->estado = 'act';
            $unidades->usuario_creacion = $request->usuario_creacion;
            $unidades->save();
            if($request->tipo == 'Admin' && $request->general2){
                $unidades->programas()->attach(1);
            }
            else if (($request->tipo == 'Coordinador Facultad' || $request->tipo == 'Admin') && $request->general){
                $facu = Facultad::where('id_facultad',$request->id_facultad)->first();
                $programa = Programa::where('nombre',$facu->nombre)->first();
                $unidades->programas()->attach($programa->id_programa);
            }
            else{
                $unidades->programas()->attach($request->id_programa);
            }
            return response()->json($unidades,201);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $unidades = UnidadApoyo::findOrFail($id);
            $unidades->update($request->all());
            if($request->tipo == 'Admin' && $request->general2){
                $unidades->programas()->detach();
                $unidades->programas()->attach(1);
            }
            else if (($request->tipo == 'Coordinador Facultad' || $request->tipo == 'Admin') && $request->general){
                $facu = Facultad::where('id_facultad',$request->id_facultad)->first();
                $programa = Programa::where('nombre',$facu->nombre)->first();
                $unidades->programas()->detach();
                $unidades->programas()->attach($programa->id_programa);
            }
            else{
                if($request->id_programa){
                    $unidades->programas()->attach($request->id_programa);
                }
            }
            return response()->json($unidades,200);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $unidades = UnidadApoyo::findOrFail($id);
            if($request->tipoUsuario == 'Admin'){
                $unidades->programas()->detach();
            }
            elseif ($request->tipoUsuario == 'Coordinador Facultad'){
                $programas = Programa::where('id_facultad',$request->id_facultad)->get();
                foreach ($programas as $programa) {
                    $unidades->programas()->detach($programa->id_programa);
                }
            }
            else{
                $unidades->programas()->detach($request->id_programa);
            }
            $unidades->estado = 'eli';
            $unidades->save();
            return response()->json(null,204);
        } catch (ModelNotFoundException $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error: ', $e->getCode(), "\n";
            echo 'No existe el ID';
        }
    }

    public function uApoyoxPrograma(Request $request)
    {
        try {
           $programas = Programa::find($request->idProg);
           $listaProg = [];
           foreach ($programas->unidadApoyos as $unidades)
           {
               //echo $unidades->id_unidad_apoyo, "\n";
               $aux = UnidadApoyo::where([['id_unidad_apoyo', $unidades->id_unidad_apoyo], ['estado', 'act']])->get();
               if (count($aux))
                   array_push($listaProg,$aux);
           }
           return response()->json($listaProg,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function uApoyoconFacuProg(){
        try {
            $unidades = UnidadApoyo::where('estado','act')->get();
            foreach ($unidades as $unidade) {
                $unidade->programas;
            }
            return response()->json($unidades);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function uApoyodeFacu(Request $request){
        try {
            $resp = array();
            $unidades = UnidadApoyo::where('estado','act')->get();
            foreach ($unidades as $unidade) {
                $unidade->programas;
                foreach ($unidade->programas as $programa) {
                    if($programa['id_facultad']==$request->id_facultad || $programa['id_programa']==1){
                        array_push($resp,$unidade);
                        break;
                    }
                }
            }
            return response()->json($resp);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            echo 'Código de error:', $e->getCode(), "\n";
        }
    }

    public function uApoyodeProg(Request $request){
        try {
            $resp = array();
            $unidades = UnidadApoyo::where('estado','act')->get();
            $facu = Facultad::where('id_facultad',$request->id_facultad)->first();
            foreach ($unidades as $unidade) {
                $unidade->programas;
                foreach ($unidade->programas as $programa) {
                    if($programa['id_programa']==$request->id_programa || $programa['id_programa']==1 || $programa['nombre']==$facu->nombre){
                        array_push($resp,$unidade);
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
