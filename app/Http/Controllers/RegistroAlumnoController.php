<?php

namespace App\Http\Controllers;

use App\Facultad;
use App\Http\Controllers\Controller;
use App\UsuarioXPrograma;
use Exception;
use App\RegistroAlumno;
use App\Programa;
use Illuminate\Http\Request;
use App\Usuario;
class RegistroAlumnoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $datos['registros']=RegistroAlumno::where('estado','act')->paginate(10);
            if (is_null($datos['registros'][0])){
                return response()->json([],204);
            }else{
                $datosFinal=['paginate' => [
                    'total'         => $datos['registros']->total(),
                    'current_page'  => $datos['registros']->currentPage(),
                    'per_page'      => $datos['registros']->perPage(),
                    'last_page'     => $datos['registros']->lastPage(),
                    'from'          => $datos['registros']->firstItem(),
                    'to'            => $datos['registros']->lastPage(),
                ],
                    'tasks'=> $datos['registros']
                ];
                return response()->json($datosFinal);
            }
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        try{
            if($request->id_tutor){
                if ($request->cambiar){//valida si ya existe ese registro
                    $regAnt = RegistroAlumno::where('id_programa',$request->id_programa)
                        ->where('id_tutor',$request->id_tutor)
                        ->where('id_alumno',$request->id_alumno)
                        ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                        ->first();
                    if($regAnt) {
                        RegistroAlumno::where('id_programa',$request->id_programa)
                            ->where('id_tutor',$request->id_tutor)
                            ->where('id_alumno',$request->id_alumno)
                            ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                            ->update([
                                'estado' => 'act',
                                'usuario_actualizacion' => $request->usuario_actualizacion
                            ]);
                        return response()->json([
                            'status' => 'success',
                            'mensaje'=> 'Se reasignó satisfactoriamente el tutor',
                            'registro' => $regAnt],200);
                    }
                    else{
                        $regAnterior = RegistroAlumno::
                        where('id_programa',$request->id_programa)
                            ->where('id_alumno',$request->id_alumno)
                            ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                            ->where('estado','act')->first();
                        if($regAnterior){//si existe alguna asignacion se actualiza
                            RegistroAlumno::where('id_programa',$request->id_programa)
                                ->where('id_alumno',$request->id_alumno)
                                ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                                ->where('estado','act')
                                ->update([
                                    'id_tutor' => $request->id_tutor,
                                    'usuario_actualizacion' => $request->usuario_actualizacion
                                ]);
                            return response()->json([
                                'status' => 'success',
                                'mensaje'=> 'Se reasignó satisfactoriamente el tutor'
                            ],200);
                        }
                        else{//si no existe ninguna asignacion se crea la asignacion
                            $reg = new RegistroAlumno();
                            $reg->id_tutor = $request->id_tutor;
                            $reg->id_programa = $request->id_programa;
                            $reg->id_alumno = $request->id_alumno;
                            $reg->id_tipo_tutoria = $request->id_tipo_tutoria;
                            $reg->usuario_creacion = $request->usuario_creacion;
                            $reg->usuario_actualizacion = $request->usuario_actualizacion;
                            $reg->estado = 'act';
                            $reg->save();
                            return response()->json([
                                'status' => 'success',
                                'mensaje'=> 'Se asignó satisfactoriamente el tutor',
                                'registro' => $reg],200);
                        }
                    }
                }
                else{
                    $regAnterior = RegistroAlumno::
                    where('id_programa',$request->id_programa)
                        ->where('id_alumno',$request->id_alumno)
                        ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                        ->where('estado','act')
                        ->first();
                    if($regAnterior){
                        $regAnterior->tutor;
                        if($regAnterior->tutor){
                            $regAnterior->tipoTutoria;
                            return response()->json([
                                'status' => 'error',
                                'mensaje'=>
                                    'Ya se encuentra asignado '.
                                    $regAnterior['tutor']['nombre'].
                                    ' '.$regAnterior['tutor']['apellidos'].
                                    ' como su tutor en el tipo de tutoría '.$regAnterior['tipoTutoria']['nombre'],
                                'registro' => $regAnterior],200);
                        }
                        else{
                            RegistroAlumno::where('id_programa',$request->id_programa)
                                ->where('id_alumno',$request->id_alumno)
                                ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                                ->where('estado','act')
                                ->update([
                                    'id_tutor' => $request->id_tutor,
                                    'usuario_actualizacion' => $request->usuario_actualizacion
                                ]);
                            return response()->json([
                                'status' => 'success',
                                'mensaje'=> 'Se asignó satisfactoriamente el tutor'
                            ],200);
                        }
                    }
                    else{
                        $regAnt = RegistroAlumno::
                        where('id_programa',$request->id_programa)
                            ->where('id_tutor',$request->id_tutor)
                            ->where('id_alumno',$request->id_alumno)
                            ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                            ->first();
                        if($regAnt){
                            RegistroAlumno::where('id_programa',$request->id_programa)
                                ->where('id_tutor',$request->id_tutor)
                                ->where('id_alumno',$request->id_alumno)
                                ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                                ->update([
                                    'estado' => 'act',
                                    'id_tipo_tutoria' => $request->id_tipo_tutoria,
                                    'usuario_actualizacion' => $request->usuario_actualizacion,
                                ]);
                            return response()->json([
                                'status' => 'success',
                                'mensaje'=> 'Se reasignó satisfactoriamente el tutor'
                            ],200);
                        }
                        else{
                            $reg = new RegistroAlumno();
                            $reg->id_tutor = $request->id_tutor;
                            $reg->id_programa = $request->id_programa;
                            $reg->id_alumno = $request->id_alumno;
                            $reg->id_tipo_tutoria = $request->id_tipo_tutoria;
                            $reg->usuario_creacion = $request->usuario_creacion;
                            $reg->usuario_actualizacion = $request->usuario_actualizacion;
                            $reg->estado = 'act';
                            $reg->save();
                            return response()->json([
                                'status' => 'success',
                                'mensaje'=> 'Se asignó satisfactoriamente el tutor',
                                'registro' => $reg],200);
                        }
                    }
                }
            }
            else{
                $regAnterior = RegistroAlumno::
                where('id_programa',$request->id_programa)
                    ->where('id_alumno',$request->id_alumno)
                    ->where('id_tipo_tutoria',$request->id_tipo_tutoria)
                    ->where('estado','act')
                    ->first();
                if($regAnterior){
                    return response()->json([
                        'status' => 'error',
                        'mensaje'=> 'Ya se encuentra asignado a dicho tipo de tutoría',
                        'registro' => $regAnterior],200);
                }
                else{
                    $reg = new RegistroAlumno();
                    $reg->id_programa = $request->id_programa;
                    $reg->id_alumno = $request->id_alumno;
                    $reg->id_tipo_tutoria = $request->id_tipo_tutoria;
                    $reg->usuario_creacion = $request->usuario_creacion;
                    $reg->usuario_actualizacion = $request->usuario_actualizacion;
                    $reg->estado = 'act';
                    $reg->save();
                    return response()->json([
                        'status' => 'success',
                        'mensaje'=> 'Se asignó satisfactoriamente el tutor',
                        'registro' => $reg],200);
                }
            }
        }
        catch (Exception $e){
            return response()->json(['e'=>$e,'Error capturado:' => 'El codigo ingresados ya existen']);
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
        try {
            $datos['registros'] = RegistroAlumno::find($id);
            return response()->json($datos['registros']);
        }catch(Exception $e) {
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
            $registro = RegistroAlumno::findOrFail($id);//depende con que id se va a buscar
            $registro->id_tutor=$request;
            $registro->id_alumno=$request;
            $registro->id_tipo_tutoria = $request->id_tipo_tutoria;
            $registro->usuario_creacion=$request;
            $registro->usuario_actualizacion=$request;
            $registro->estado='act';
            $registro->id_programa=$request;
            $registro->update();
            return response()->json([],200);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try{
            $id=$request->id_tutor;
            if($id==0){
                $registro = RegistroAlumno::where('id_programa','=',$request->id_programa)->where('id_alumno','=',$request->id_alumno)
                    ->where('id_tipo_tutoria','=',$request->id_tipo_tutoria)
                    ->where('estado','act')->update(['estado'=>'eli','usuario_actualizacion'=>$request->usuario_actualizacion]);
            }else{
                $registro = RegistroAlumno::where('id_programa','=',$request->id_programa)->where('id_alumno','=',$request->id_alumno)
                    ->where('id_tutor','=',$request->id_tutor)->where('id_tipo_tutoria','=',$request->id_tipo_tutoria)
                    ->where('estado','act')->update(['id_tutor'=>null,'usuario_actualizacion'=>$request->usuario_actualizacion]);
            }
            return response()->json([],200);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //listar alumnos de ese tutor
    public function listarAlumnosTutor(Request $request){
        try{
            $id_tuto=$request->id_tipo_tutoria;
            if($id_tuto==0){
                $registro = RegistroAlumno::selectRaw("tipo_tutoria.nombre as tipotutoria,tipo_tutoria.id_tipo_tutoria,  usuario.*")
                    ->join('usuario','usuario.id_usuario','=','registro_alumno.id_alumno')
                    ->join('tipo_tutoria','tipo_tutoria.id_tipo_tutoria','=','registro_alumno.id_tipo_tutoria')
                    ->where('usuario.estado','act')->where('registro_alumno.id_programa','=',$request->id_programa)
                    ->where('registro_alumno.id_tutor','=',$request->id_tutor)->where('registro_alumno.estado','act')
                    ->get();
                return response()->json($registro,200);
            }
            else{//alumnos asigandos a ese tipo de tutoria
                $registro = RegistroAlumno::selectRaw("tipo_tutoria.nombre as tipotutoria,tipo_tutoria.id_tipo_tutoria,  usuario.*")
                    ->join('usuario','usuario.id_usuario','=','registro_alumno.id_alumno')
                    ->join('tipo_tutoria','tipo_tutoria.id_tipo_tutoria','=','registro_alumno.id_tipo_tutoria')
                    ->where('registro_alumno.id_tipo_tutoria','=',$id_tuto)
                    ->where('usuario.estado','act')->where('registro_alumno.id_programa','=',$request->id_programa)
                    ->where('registro_alumno.id_tutor','=',$request->id_tutor)->where('registro_alumno.estado','act')
                    ->get();
                return response()->json($registro,200);
            }
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function asignadosXPrograma(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            //ver cuantos registro hay con fecha actualizacion null
            $reg['nulos']=RegistroAlumno::selectRaw("id_tutor")
                ->whereIn('id_programa',$request->id_programa)
                ->where('estado','act')
                ->whereNull('fecha_actualizacion')->get();
            if(!empty($reg['nulos'])){
                //se debe realizar consulta con la fecha de creación
                $regist['primero'] = RegistroAlumno::selectRaw("count(id_alumno) as Asignados")
                    ->whereIn('id_programa',$request->id_programa)
                    ->whereNull('fecha_actualizacion')
                    ->where('estado','act')
                    ->whereBetween('fecha_creacion',[$ini,$fin])->get();
            }
            $registro['primero'] = RegistroAlumno::selectRaw("count(id_alumno) as Asignados")
                ->whereIn('id_programa',$request->id_programa)
                ->where('estado','act')
                ->whereBetween('fecha_actualizacion',[$ini,$fin])->get();
            if(!empty($regist['primero'])){
                //debo sumar los valores porque hay ambos registros con fecha creacion y actualizacion
                $registro['primero'][0]['asignados']=$registro['primero'][0]['asignados'] + $regist['primero'][0]['asignados'];
            }
            else{//solo hay registros con fecha creación
                $registro['primero']=$regist['primero'];
            }
            $regis['segundo'] =UsuarioxPrograma::selectRaw("count(id_usuario) as noAsignados")
            ->whereIn('id_programa',$request->id_programa)->where('id_tipo_usuario',5)->get();
            if($regis['segundo'][0]['noasignados']>=$registro['primero'][0]['asignados']){
                $regis['segundo'][0]['noasignados']= $regis['segundo'][0]['noasignados'] - $registro['primero'][0]['asignados'];
            }else{
                $regis['segundo'][0]['noasignados']=$registro['primero'][0]['asignados'] - $regis['segundo'][0]['noasignados'];
            }
            $total=array_merge_recursive($registro['primero']->toArray(),$regis['segundo']->toArray());
            return response()->json($total,200);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function asignadosXFacultad(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $programas=array();
            //sacar los ids de los programas de esas facultades
            $ids['datos']=Programa::selectRaw("id_programa")->whereIn('id_facultad',$request->id_facultad)->where('estado','act')->get();
            foreach($ids['datos'] as $datos){
                array_push($programas,$datos['id_programa']);
            }
            //ver cuantos registro hay con fecha actualizacion null
            $reg['nulos']=RegistroAlumno::selectRaw("id_tutor")
                ->whereIn('id_programa',$programas)
                ->where('estado','act')
                ->whereNull('fecha_actualizacion')->get();
            if(!empty($reg['nulos'])){
                //se debe realizar consulta con la fecha de creación
                $regist['primero'] = RegistroAlumno::selectRaw("count(id_alumno) as Asignados")
                    ->whereIn('id_programa',$programas)
                    ->whereNull('fecha_actualizacion')
                    ->where('estado','act')
                    ->whereBetween('fecha_creacion',[$ini,$fin])->get();
            }
            $registro['primero'] = RegistroAlumno::selectRaw("count(id_alumno) as Asignados")
                ->whereIn('id_programa',$programas)
                ->where('estado','act')
                ->whereBetween('fecha_actualizacion',[$ini,$fin])->get();
            if(!empty($regist['primero'])){
                //debo sumar los valores porque hay ambos registros con fecha creacion y actualizacion
                $registro['primero'][0]['asignados']=$registro['primero'][0]['asignados'] + $regist['primero'][0]['asignados'];
            }
            else{//solo hay registros con fecha creación
                $registro['primero']=$regist['primero'];
            }
            $regis['segundo'] =UsuarioxPrograma::selectRaw("count(id_usuario) as noAsignados")
                ->whereIn('id_programa',$programas)->where('id_tipo_usuario',5)->get();
            if($regis['segundo'][0]['noasignados']>=$registro['primero'][0]['asignados']){
                $regis['segundo'][0]['noasignados']= $regis['segundo'][0]['noasignados'] - $registro['primero'][0]['asignados'];
            }else{
                $regis['segundo'][0]['noasignados']=$registro['primero'][0]['asignados'] - $regis['segundo'][0]['noasignados'];
            }
            $total=array_merge_recursive($registro['primero']->toArray(),$regis['segundo']->toArray());
            return response()->json($total,200);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function asignadosXUniversidad(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $programas=array();$facultades=array();
            //sacar todas los ids de las facultades
            $id['datos']=Facultad::selectRaw("id_facultad")->where('id_institucion',$request->id_institucion)->where('estado','act')->get();
            foreach($id['datos'] as $datos){
                array_push($facultades,$datos['id_facultad']);
            }
            //sacar los ids de los programas de esas facultades
            $ids['datos']=Programa::selectRaw("id_programa")->whereIn('id_facultad',$facultades)->where('estado','act')->get();
            foreach($ids['datos'] as $datos){
                array_push($programas,$datos['id_programa']);
            }
            $registro['primero'] = RegistroAlumno::selectRaw("count(id_alumno) as Asignados")
                ->whereIn('id_programa',$programas)
                ->where('estado','act')
                ->whereBetween('fecha_actualizacion',[$ini,$fin])->get();
            $regis['segundo'] =UsuarioxPrograma::selectRaw("count(id_usuario) as noAsignados")
                ->whereIn('id_programa',$programas)->where('id_tipo_usuario',5)->get();
            if($regis['segundo'][0]['noasignados']>=$registro['primero'][0]['asignados']){
                $regis['segundo'][0]['noasignados']= $regis['segundo'][0]['noasignados'] - $registro['primero'][0]['asignados'];
            }else{
                $regis['segundo'][0]['noasignados']=$registro['primero'][0]['asignados'] - $regis['segundo'][0]['noasignados'];
            }
            $total=array_merge_recursive($registro['primero']->toArray(),$regis['segundo']->toArray());
            return response()->json($total,200);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function cantAlumnosXTutores(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $registro['primero'] = RegistroAlumno::selectRaw("count(registro_alumno.id_alumno) as total, usuario.id_usuario, concat(usuario.nombre,' ',usuario.apellidos) as data")
                ->join('usuario','usuario.id_usuario','=','registro_alumno.id_tutor')->whereIn('registro_alumno.id_tutor',$request->id_tutores)
                ->where('usuario.estado','act')->where('registro_alumno.id_programa','=',$request->id_programa)
                ->where('registro_alumno.estado','act')
                ->whereBetween('registro_alumno.fecha_actualizacion',[$ini,$fin])
                ->groupBy('usuario.id_usuario')
                ->orderBy('usuario.nombre', 'ASC')
                ->get();
            //obtener la lista de todos los tutores
            $consulta2['datos']=Usuario::selectRaw("id_usuario, concat(nombre,' ',apellidos) as data")
                ->where('estado','=','act')
                ->whereIn('id_usuario',$request->id_tutores)
                ->orderBy('nombre', 'ASC')
                ->get();
            //verificar el tamaño del arreglo
            if(count($registro['primero'])!=count($consulta2['datos'])){
                //verificar que tutores tienen cantidad de alumnos y cuales no
                $i=0;
                foreach ($consulta2['datos'] as $tutores){
                    $cant=0;
                    $j=0;
                    foreach ($registro['primero'] as $datos){
                        if(($tutores->data==$datos->data)) {
                            $consulta2['datos'][$i]['total'] = $registro['primero'][$j]['total'];
                            $cant=1;
                            break;
                        }
                        $j++;
                    }
                    if($cant!=1){//significa que no tiene ninguno alumno
                        $consulta2['datos'][$i]['total']=0;
                    }
                    $i++;
                }
                return response()->json($consulta2['datos']);
            }
            return response()->json($registro['primero'],200);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function cantAlumnosXPrograma(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $registro['primero'] = RegistroAlumno::selectRaw("count(id_alumno) as total, programa.id_programa,programa.nombre as data")
                ->join('programa','programa.id_programa','=','registro_alumno.id_programa')
                ->whereIn('registro_alumno.id_programa',$request->id_programa)
                ->where('programa.estado','act')
                ->where('programa.id_facultad','=',$request->id_facultad)
                ->where('registro_alumno.estado','act')
                ->whereBetween('registro_alumno.fecha_actualizacion',[$ini,$fin])
                ->groupBy('programa.id_programa')
                ->orderBy('programa.nombre', 'ASC')->get();
            return response()->json($registro['primero'] ,200);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function cantAlumnosXFacultad(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $programas=array();
            //sacar los ids de los programas de esas facultades
            $ids['datos']=Programa::selectRaw("id_programa")->whereIn('id_facultad',$request->id_facultad)->where('estado','act')->get();
            foreach($ids['datos'] as $datos){
                array_push($programas,$datos['id_programa']);
            }
            $registro['primero'] = RegistroAlumno::selectRaw("count(id_alumno) as total, facultad.id_facultad,facultad.nombre as data ")
                ->join('programa','programa.id_programa','=','registro_alumno.id_programa')
                ->join('facultad','facultad.id_facultad','=','programa.id_facultad')
                ->whereIn('registro_alumno.id_programa',$programas)
                ->where('programa.estado','act')
                ->where('facultad.estado','act')
                ->where('registro_alumno.estado','act')
                ->whereBetween('registro_alumno.fecha_actualizacion',[$ini,$fin])
                ->groupBy('facultad.id_facultad')
                ->orderBy('facultad.nombre', 'ASC')->get();
            return response()->json($registro['primero'] ,200);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
}
