<?php

namespace App\Http\Controllers;
use Exception;
use App\UsuarioxPrograma;
use App\Facultad;
use App\Programa;
use App\Usuario;
use App\TipoTutoria;
use App\TipoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class FacultadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{
            $datos['facultades']=Facultad::where('estado','act')->orderBy('nombre','ASC')->paginate(4);
            if (is_null($datos['facultades'][0])){
                return response()->json([],204);
            }else{
                //se retorna un array
                $datosFinal=['paginate' => [
                    'total'         => $datos['facultades']->total(),
                    'current_page'  => $datos['facultades']->currentPage(),
                    'per_page'      => $datos['facultades']->perPage(),
                    'last_page'     => $datos['facultades']->lastPage(),
                    'from'          => $datos['facultades']->firstItem(),
                    'to'            => $datos['facultades']->lastPage(),
                ],
                    'tasks'=> $datos['facultades']
                ];
                //compact('datos')
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $facultad = new Facultad();
            $facultad->id_institucion=$request->id_institucion;
            $facultad->nombre=$request->nombre;
            $facultad->codigo=$request->codigo;
            $facultad->descripcion=$request->descripcion;
            $facultad->correo=$request->correo;
            $facultad->estado='act';
            $facultad->usuario_creacion=$request->usuario_creacion;
            $facultad->usuario_actualizacion=$request->usuario_creacion;
            $facultad->save();
            //insertar su programa default
            $findId = Facultad::where('nombre',$request->nombre)->first();
            $programa = new Programa();
            $programa->id_facultad=$findId->id_facultad;
            $programa->nombre=$request->nombre;
            $programa->codigo=$request->codigo;
            $programa->descripcion=$request->descripcion;
            $programa->correo=$request->correo;
            $programa->estado='act';
            $programa->hora_bloque=0;
            $programa->usuario_creacion=$request->usuario_creacion;
            $programa->usuario_actualizacion=$request->usuario_creacion;
            $programa->save();
            return response()->json($programa);
        }catch(Exception $e) {
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
            $dato= new Facultad();
            $dato['facultades']=Facultad::join('programa','programa.nombre','=','facultad.nombre')
                ->select('programa.id_programa', 'facultad.*')
                ->where('facultad.estado','act')->where('facultad.id_facultad',$id)->get();
            return response()->json($dato['facultades']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            #dd($e->getMessage());
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
            $facultad = Facultad::findOrFail($id);
            $programa=Programa::where('nombre',$facultad->nombre)->first();
            //dd($request);
            $programa->nombre=$request->nombre;
            $programa->codigo=$request->codigo;
            $programa->correo=$request->correo;
            $programa->descripcion=$request->descripcion;
            $programa->usuario_actualizacion=$request->usuario_actualizacion;
            $programa->update();
            //facultad
            $facultad->nombre=$request->nombre;
            $facultad->codigo=$request->codigo;
            $facultad->correo=$request->correo;
            $facultad->descripcion=$request->descripcion;
            $facultad->usuario_actualizacion=$request->usuario_actualizacion;
            $facultad->save();
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
            $facultad = Facultad::findOrFail($request->id);
            $facultad->usuario_actualizacion=$request->usuario_actualizacion;
            $facultad->estado='eli';
            $facultad->save();
            //elimina los programas de esa facultad
            $dato['programas']=Programa::where('id_facultad',$request->id)->get();
            foreach($dato['programas'] as $programa){
                $programa->usuario_actualizacion=$request->usuario_actualizacion;
                $programa->estado='eli';
                $programa->usuarios()->detach();
                $programa->save();
            }
            return response()->json(['status'=> 'success'],204);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function listarProgramas(Request $request){
        try {
            $inserts=$request->all();
            $id_facultad=$inserts['id_facultad'];
            $nombre=$inserts['nombre'];
            $dato['programas']=Programa::where('id_facultad',$id_facultad)->get();
            $i=0;
            foreach($dato['programas'] as $programa){
                if($nombre==$programa->nombre){
                    $dato['programas'][$i]="";
                }
                $i+=1;
            }
            return response()->json($dato['programas'],202);
        }catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    /**
     *Devuelve la cantidad de programas que hay en una facultad
     *
     * @param $id
     * @return mixed
     */
    public function cantProgramas($id){
        try{
            $datos['cant']=Programa::selectRaw('count(programa)')->where('id_facultad',$id)->where('estado','act')->get();
            return response()->json($datos['cant']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    /**
     * Devuelve todas las facultades con la cantidad de programas y los coordinadores de la facultad
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listFacuConCantid(){
        try{
            $dato= new Facultad();
            $dato['facultades']=Facultad::selectRaw("count(programa.*) as cantidad, facultad.*")
                ->join('programa','programa.id_facultad','=','facultad.id_facultad')
                ->where('facultad.estado','act')->where('programa.estado','act')->orderBy('facultad.nombre', 'ASC')
                ->groupBy('facultad.id_facultad')->get();
            return response()->json($dato['facultades']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //devuelve coordinadores de facutlad
    public function listFacuConCoordi(Request $request){
        try{
            if(empty($request['nombre'])){
                $nombre='';
            }else{
                $nombre=$request['nombre'];
            }
            $dato['facultades']= new Facultad();
            $dato['facultades']=Facultad::selectRaw("count(programa.*) as cantidad, facultad.*")
                ->join('programa','programa.id_facultad','=','facultad.id_facultad')
                ->where('facultad.estado','act')->where('programa.estado','act')->where('facultad.nombre','ilike', '%' . $nombre. '%')
                ->orderBy('facultad.nombre', 'ASC')->groupBy('facultad.id_facultad')->paginate(10);
            $i=0;
            $coord['coordinadores']=array();
            foreach($dato['facultades'] as $facultad){
                $programa=Programa::where('nombre',$facultad->nombre)->where('estado','act')->get()->toArray();
                if(!empty($programa[0]['id_programa'])){
                    $id=$programa[0]['id_programa'];
                    $usu=UsuarioxPrograma::where('id_programa',$id)->where('id_tipo_usuario',2)->get();
                    if(empty($usu[0]['id_usuario'])){
                        $coordinador= new Usuario();
                        $coordinador->nombre='Sin coordinador';
                        array_push($coord['coordinadores'],$coordinador);
                    }else{
                        array_push($coord['coordinadores'],Usuario::where('id_usuario',$usu[0]['id_usuario'])->get());
                    }
                    $i+=1;
                }
            }
            $total=array_merge_recursive($dato['facultades']->toArray(),$coord['coordinadores']);
            //se retorna un array
            $datosFinal=['paginate' => [
                'total'         => $dato['facultades']->total(),
                'current_page'  => $dato['facultades']->currentPage(),
                'per_page'      => $dato['facultades']->perPage(),
                'last_page'     => $dato['facultades']->lastPage(),
                'from'          => $dato['facultades']->firstItem(),
                'to'            => $dato['facultades']->lastPage(),
            ],
                'tasks'=> $total
            ];
            return response()->json($datosFinal);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //Devuelve el coordinador de facultad, me manda el id del programa default de esa facultad
    public function coordinadorFacultad($id){
        try{
            $programa = Programa::find($id);
            $datos['coordinador']=$programa->belongsToMany('App\Usuario','usuario_x_programa','id_programa','id_usuario')->withPivot('id_tipo_usuario')->where('usuario_x_programa.id_tipo_usuario',2)->get();
            return response()->json($datos['coordinador']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //listar por nombre
    public function listNombre(){
        try{
            //$inserts=$request->all();
            $nombre="";
            $datos['facultad']=Facultad::where('nombre','ilike', '%' . $nombre . '%')->where('estado','act')->get();
            return response()->json($datos['facultad']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //Devuelve los posibles coordinadores para la facultad, tanto de programa como de facultad
    public function coordinadores(){
        try {
            //$inserts=$request->all();
            $nombre="";
            $tipoUsuario2 = TipoUsuario::find(2);
            $tipoUsuario3 = TipoUsuario::find(3);
            $datos['coordinadores2']=$tipoUsuario2->usuarios()->where(Usuario::raw("concat(nombre,' ',apellidos)"),'ilike', '%' . $nombre . '%')->get()->toArray();
            $datos['coordinadores3']=$tipoUsuario3->usuarios()->where(Usuario::raw("concat(nombre,' ',apellidos)"),'ilike', '%' . $nombre . '%')->get()->toArray();
            $datos['coordinadores']=array_merge_recursive($datos['coordinadores2'],$datos['coordinadores3']);
            //dd($datos['coordinadores']);
            $id_usuarios=array();
            $resultado=array();
            foreach ($datos['coordinadores'] as $usuario) {
                if (!in_array($usuario['id_usuario'], $id_usuarios)) {
                    array_push($id_usuarios,$usuario['id_usuario']);
                    $usuario['lugares'] = array();
                    $prg = Programa::where('id_programa',$usuario['pivot']['id_programa'])->first();
                    if($prg) array_push($usuario['lugares'],$prg->nombre);
                    array_push($resultado, $usuario);
                }
                else{
                    $ind = array_search($usuario['id_usuario'],$id_usuarios);
                    $prg = Programa::where('id_programa',$usuario['pivot']['id_programa'])->first();
                    if($prg){
                        if($prg->nombre){
                            array_push($resultado[$ind]['lugares'],Programa::where('id_programa',$usuario['pivot']['id_programa'])->first()->nombre);
                        }
                    }
                }
            }
            return response()->json($resultado);//falta arreglar para que no devuelva repetidos
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    ///coordinadores facultad
    public function coordinadoresFacu(){
        try {
            $nombre="";
            $tipoUsuario2 = TipoUsuario::find(2);
            $datos['coordinadores']=$tipoUsuario2->usuarios()->where(Usuario::raw("concat(nombre,' ',apellidos)"),'ilike', '%' . $nombre . '%')->get()->toArray();
            $id_usuarios=array();
            $resultado=array();
            foreach ($datos['coordinadores'] as $usuario) {
                if (!in_array($usuario['id_usuario'], $id_usuarios)) {
                    array_push($id_usuarios,$usuario['id_usuario']);
                    $usuario['lugares'] = array();
                    $prg = Programa::where('id_programa',$usuario['pivot']['id_programa'])->first();
                    if($prg) array_push($usuario['lugares'],$prg->nombre);
                    array_push($resultado, $usuario);
                }
                else{
                    $ind = array_search($usuario['id_usuario'],$id_usuarios);
                    $prg = Programa::where('id_programa',$usuario['pivot']['id_programa'])->first();
                    if($prg){
                        if($prg->nombre){
                            array_push($resultado[$ind]['lugares'],Programa::where('id_programa',$usuario['pivot']['id_programa'])->first()->nombre);
                        }
                    }
                }
            }
            return response()->json($resultado);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    /// coordinadores programa
    public function coordinadoresProg(){
        try {
            $nombre="";
            $tipoUsuario3 = TipoUsuario::find(3);
            $datos['coordinadores']=$tipoUsuario3->usuarios()->where(Usuario::raw("concat(nombre,' ',apellidos)"),'ilike', '%' . $nombre . '%')->get()->toArray();
            $id_usuarios=array();
            $resultado=array();
            foreach ($datos['coordinadores'] as $usuario) {
                if (!in_array($usuario['id_usuario'], $id_usuarios)) {
                    array_push($id_usuarios,$usuario['id_usuario']);
                    $usuario['lugares'] = array();
                    $prg = Programa::where('id_programa',$usuario['pivot']['id_programa'])->first();
                    if($prg) array_push($usuario['lugares'],$prg->nombre);
                    array_push($resultado, $usuario);
                }
                else{
                    $ind = array_search($usuario['id_usuario'],$id_usuarios);
                    $prg = Programa::where('id_programa',$usuario['pivot']['id_programa'])->first();
                    if($prg){
                        if($prg->nombre){
                            array_push($resultado[$ind]['lugares'],Programa::where('id_programa',$usuario['pivot']['id_programa'])->first()->nombre);
                        }
                    }
                }
            }
            return response()->json($resultado);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function asignarCoordinadoresFacu(Request $request){
        try{
            $inserts['datos']=$request->all();
            $exist=UsuarioxPrograma::where('id_programa',$inserts['datos']['id_programa'])->get();
            if(!empty($exist)){
                $programa=Programa::find($inserts['datos']['id_programa']);
                $programa->usuarios()->wherePivot('id_tipo_usuario',2)->detach();
                //signfica que existe, entonces hay que eliminarlo e insertarlo
            }
            $usuario=Usuario::find($inserts['datos']['id_usuario']);
            $usuario->tipoUsuario()->attach(2,['id_programa'=>$inserts['datos']['id_programa']]);
            return response()->json([],202);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //verificacion de codigo
    public function verificarCodigo(Request $request, $id=''){
        try{
            $codigo=$request->getContent();
            if(empty($id)){
                $dato=Facultad::where('codigo','ilike', $codigo )->where('estado','act')->get();
            }else{
                $dato=Facultad::where('id_facultad','!=',$id)->where('codigo','ilike',  $codigo )->where('estado','act')->get();
            }
            if(empty($dato[0])){//significa que no existe ese codigo
                return response()->json(["success" => false],202);
            }else{
                return response()->json(["success" => true],202);
            }
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //verificacion de nombre
    public function verificarNombre(Request $request, $id=''){
        try{
            $nombre=$request->getContent();
            if(empty($id)){
                $dato=Facultad::where('nombre','ilike',  $nombre )->where('estado','act')->get();
            }else{
                $dato=Facultad::where('id_facultad','!=',$id)->where('nombre','ilike', $nombre )->where('estado','act')->get();
            }
            if(empty($dato[0])){//significa que no existe ese nombre
                return response()->json(["success" => false],202);
            }else{
                return response()->json(["success" => true],202);
            }
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function listarFacultades(Request $request)
    {
        try{
            $datos['facultades']=Facultad::where('estado','act')->where('id_institucion',$request->id_institucion)
                ->orderBy('nombre','ASC')->get();
            if (is_null($datos['facultades'])){
                return response()->json([],204);
            }else{
                return response()->json($datos['facultades']);
            }
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function listarProgramasDefault(Request $request){
        try {
            $dato['programas']=Programa::where('estado','act')->where('id_facultad',$request->id_facultad)
                ->orderBy('nombre','ASC')->get();
            /*if (is_null($datos['programas'][0])){
                return response()->json([],204);
            }else{
                return response()->json($datos['facultades']);
            }*/
            return response()->json($dato['programas'],202);
        }catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
}
