<?php

namespace App\Http\Controllers;
use App\Disponibilidad;
use App\Facultad;
use App\RegistroAlumno;
use Exception;
use App\Programa;
use App\TipoUsuario;
use App\Valores;
use App\Usuario;
use App\UsuarioxPrograma;
use App\TipoTutoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgramaController extends Controller
{

    public function index()
    {
        try{
            $resp = array();
            $programas =Programa::where('estado','act')->orderBy('programa.nombre', 'ASC')->get();
            foreach ($programas as $programa) {
                if ($programa->nombre != $programa->facultad->nombre){
                    array_push($resp,$programa);
                }
                else{
                    $listado = $programa->facultad->programas;
                    if(count ($listado)==1){
                        array_push($resp,$programa);
                    }
                }
            }
            return response()->json($resp);
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
            $programas['datos']=$request->all();
            foreach ($programas['datos'] as $prog){
                $programa = new Programa();
                $programa->id_facultad=$prog['id_facultad'];
                $programa->nombre=$prog['nombre'];
                $programa->correo=$prog['correo'];
                $programa->codigo=$prog['codigo'];
                $programa->usuario_creacion=$prog['usuario_creacion'];
                $programa->usuario_actualizacion=$prog['usuario_creacion'];
                $programa->estado='act';
                $programa->save();
                //insertar coordinadores
                if($prog['coordinador']){
                    $exist=UsuarioxPrograma::where('id_programa',$programa->id_programa)->get();
                    if(!empty($exist)){
                        $programa=Programa::find($programa->id_programa);
                        $programa->usuarios()->wherePivot('id_tipo_usuario',3)->detach();
                    }
                    $id_coordi=$prog['coordinador']['id_usuario'];
                    $usuario=Usuario::find($id_coordi);
                    $usuario->tipoUsuario()->attach(3,['id_programa'=>$programa->id_programa]);
                }
            }
            return response()->json([],202);
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
            $programa=Programa::find($id);
            $programa->facultad;
            return response()->json($programa);
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
    public function update(Request $request)
    {
        try{
            $programas['datos']=$request->all();
            foreach ($programas['datos'] as $prog){
                $programa = Programa::findOrFail($prog['id_programa']);
                $programa->id_facultad=$prog['id_facultad'];
                $programa->nombre=$prog['nombre'];
                $programa->correo=$prog['correo'];
                $programa->codigo=$prog['codigo'];
                //$programa->descripcion=$prog['descripcion'];
                //$programa->hora_bloque=$prog['hora_bloque'];
                $programa->usuario_actualizacion=$prog['usuario_actualizacion'];
                $programa->estado='act';
                $programa->update();
                //actualizar coordinadores
                if($prog['coordinador']){
                    $exist=UsuarioxPrograma::where('id_programa',$programa->id_programa)->get();
                    if(!empty($exist)){
                        $programa=Programa::find($programa->id_programa);
                        $programa->usuarios()->wherePivot('id_tipo_usuario',3)->detach();
                    }
                    $id_coordi=$prog['coordinador']['id_usuario'];
                    $usuario=Usuario::find($id_coordi);
                    //verificar si tiene programa null
                    $datos=array();
                    array_push($datos,2);
                    array_push($datos,3);
                    $verifi['dato']=UsuarioxPrograma::where('id_programa',null)->whereIn('id_tipo_usuario',$datos)->get();
                    if(empty($verifi['dato'][0]['id_usuario'])){//se inserta un nuevo
                        $usuario->tipoUsuario()->attach(3,['id_programa'=>$programa->id_programa]);
                    }else{
                        $query=UsuarioxPrograma::where('id_programa',null)->whereIn('id_tipo_usuario',$datos)->update(['id_programa'=>$programa->id_programa]);
                    }
                    //$usuario->tipoUsuario()->attach(3,['id_programa'=>$programa->id_programa]);
                }
            }
            //return response()->json($programa,200);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    /*public function hola(){
        try{
            $datos=array();
            array_push($datos,2);
            array_push($datos,3);
            $verifi['dato']=UsuarioxPrograma::where('id_programa',null)->whereIn('id_tipo_usuario',$datos)->get();
            if(empty($verifi['dato'][0]['id_usuario'])){//se inserta un nuevo
                $usuario->tipoUsuario()->attach(3,['id_programa'=>$programa->id_programa]);
            }else{
                $query=UsuarioxPrograma::where('id_programa',null)->whereIn('id_tipo_usuario',$datos)->update(['id_programa'=>$programa->id_programa]);
            }
            return response()->json($verifi['dato']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }*/
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try{
            $datos['programas']=$request->all();
            foreach($datos['programas'] as $programas){
                $id=$programas['id_programa'];
                $usu=$programas['usuario_actualizacion'];
                $programa = Programa::where('id_programa',$id)->first();
                $programa->usuario_actualizacion=$usu;
                $programa->estado='eli';
                if($programas['coordinador']){
                    $id_coordi=$programas['coordinador']['id_usuario'];
                    $usuario = Usuario::where('id_usuario',$id_coordi)->first();
                    $usuario->programa()->wherePivot('id_tipo_usuario',3)->detach($id);
                }
                $programa->save();
            }
            return response()->json(['status'=> 'success'],204);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //listar por nombre
    public function listNombre(){
        try{
            //$inserts=$request->all();
            $nombre="";
            $datos['programa']=Programa::where('nombre','like', '%' . $nombre . '%')->where('estado','act')->get();
            return response()->json($datos['programa']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Usuarios con TipoUsario por Programa
    public function usuarioPrograma(Request $request,$id_programa){
        try{
            $tipo_usuario=$request->tipo_usuario;
            //$programa=Programa::findOrFail($id_programa);

            $subquery = DB::table('usuario')->distinct('usuario.codigo')
                ->join('usuario_x_programa','usuario.id_usuario','=',
                    'usuario_x_programa.id_usuario')
                ->where("usuario_x_programa.id_programa",'=', $id_programa)
                ->where(function ($query) use ($request) {
                    $query->Where('usuario.nombre','ILIKE','%'.$request->criterio.'%')->
                    orWhere('usuario.apellidos','ILIKE','%'.$request->criterio.'%')->
                    orWhere('usuario.codigo','ILIKE','%'.$request->criterio.'%');})->
                where('estado','!=','eli');
            if($tipo_usuario!=0){
                $subquery->where('usuario_x_programa.id_tipo_usuario','=',$tipo_usuario);
            };
            $usuarios=Usuario::fromSub($subquery,'subquery')->orderBy('nombre')
                ->orderBy('apellidos')->with(array('tipoUsuario' => function($query) use ($id_programa)
                {$query->where('usuario_x_programa.id_programa',$id_programa);}))->
                with(array('tipoTutorias' => function($query)
                use ($id_programa){$query->where('id_programa',$id_programa);}))->paginate(10);
            ;
            /*
            $usuarios=$programa->usuarios()->distinct('usuario.codigo')->
            where(function ($query) use ($request) {
                $query->Where('usuario.nombre','ILIKE','%'.$request->criterio.'%')->
            orWhere('usuario.apellidos','ILIKE','%'.$request->criterio.'%')->
            orWhere('usuario.codigo','ILIKE','%'.$request->criterio.'%');})->
            where('estado','!=','eli');
            if($tipo_usuario!=0){
                $usuarios->where('usuario_x_programa.id_tipo_usuario','=',$tipo_usuario);
            };
            $usuarios=$usuarios->with(array('tipoUsuario' => function($query) use ($id_programa)
            {$query->where('usuario_x_programa.id_programa',$id_programa);}))->
            with(array('tipoTutorias' => function($query)
            use ($id_programa){$query->where('id_programa',$id_programa);}))->paginate(10);
            */


            if (is_null($usuarios[0])){
                return response()->json([],204);
            }else{
                //se retorna un array
                $datosFinal=['paginate' => [
                    'total'         => $usuarios->total(),
                    'current_page'  => $usuarios->currentPage(),
                    'per_page'      => $usuarios->perPage(),
                    'last_page'     => $usuarios->lastPage(),
                    'from'          => $usuarios->firstItem(),
                    'to'            => $usuarios->lastPage(),
                ],
                    'tasks'=> $usuarios
                ];
                //compact('datos')
                return response()->json($datosFinal);
            }
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function asignarCoordinadoresProg(Request $request){
        try{
            $inserts['datos']=$request->all();
            $exist=UsuarioxPrograma::where('id_programa',$inserts['datos']['id_programa'])->get();
            if(!empty($exist)){
                $programa=Programa::find($inserts['datos']['id_programa']);
                $programa->usuarios()->wherePivot('id_tipo_usuario',3)->detach();
            }
            foreach ($inserts['datos'] as $datos ){
                $usuario=Usuario::find($datos['id_usuario']);
                $usuario->tipoUsuario()->attach(3,['id_programa'=>$datos['id_programa']]);
            }
            return response()->json([],202);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //devolver id del coordinadores
    public function idCoordinador($id){
        try{
            $idTCoordProg = TipoUsuario::where('nombre','Coordinador Programa')->first()->id_tipo_usuario;
            $id_usuario=UsuarioxPrograma::where('id_programa',$id)->where('id_tipo_usuario',$idTCoordProg)->first();
            if($id_usuario){
                $usuario = Usuario::where('id_usuario',$id_usuario->id_usuario)->first();
                return $usuario;
            }
            else{
                return null;
            }
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //Listar Tipos de tutoria de programa
    public function listTipoTutoria($id_programa)
    {
        try {
            $programa = Programa::findOrFail($id_programa);
            $datos['tipo_tutorias']=$programa->tipoTutorias()->where('estado','act')->get();
            return response()->json($datos['tipo_tutorias'], 202);

        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function listConCoord(){
        $programas = Programa::where('estado','act')->get();
        //al sacarlo se debe verificar si el nombre del programas es el mismo en la tabla facultad
        $resp = array();
        foreach ($programas as $programa) {
            if ($programa->nombre !== $programa->facultad->nombre){
                $coord = $this->idCoordinador($programa->id_programa);
                $facu = $programa->facultad;
                array_push($resp,['programa'=>$programa,'coordinador'=>$coord,'facultad'=>$facu]);
            }
        }
        return $resp;
    }

    public function listConCoord2($id){
        $programas = Programa::where('id_facultad',$id)->where('estado','act')->get();
        //al sacarlo se debe verificar si el nombre del programas es el mismo en la tabla facultad
        $resp = array();
        foreach ($programas as $programa) {
            if ($programa->nombre !== $programa->facultad->nombre){
                $coord = $this->idCoordinador($programa->id_programa);
                $facu = $programa->facultad;
                array_push($resp,['programa'=>$programa,'coordinador'=>$coord,'facultad'=>$facu]);
            }
        }
        return $resp;
    }
    //verificacion de codigo
    public function verificarCodigo(Request $request, $id=''){
        try{
            $codigo=$request->getContent();
            if(empty($id)){
                $dato=Programa::where('codigo','ilike', $codigo)->where('estado','act')->get();
            }else{
                $dato=Programa::where('id_programa','!=',$id)->where('codigo','ilike', $codigo )->where('estado','act')->get();
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
                $dato=Programa::where('nombre','ilike', $nombre )->where('estado','act')->get();
            }else{
                $dato=Programa::where('id_programa','!=',$id)->where('nombre','ilike', $nombre )->where('estado','act')->get();
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
    //listar todos los tutores de este programa
    public function tutores(Request $request){
        try{
            $tiposProg = TipoUsuario::where('id_programa',$request->id_programa)->get();
            $idFacu = Programa::where('nombre',$request->nomFacu)->first()->id_programa;
            $tiposFacu = TipoUsuario::where('id_programa',$idFacu)->get();
            $tiposGen = TipoUsuario::where('id_programa',1)->get();

            $tiposTotal = array();
            foreach ($tiposProg as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposFacu as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposGen as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }

            $tutores = UsuarioxPrograma::
                selectRaw("*")
                ->join('usuario','usuario.id_usuario','=','usuario_x_programa.id_usuario')
                ->where('id_programa',$request->id_programa)
                ->where('usuario.nombre','ILIKE','%'.$request->nombre.'%');

            $tutores = $tutores->where(function($query) use ($tiposTotal)  {
                for ($i = 0; $i <= count($tiposTotal)-1; $i++) {
                    if($i==0) $query->where('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                    else $query->orWhere('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                }
            });

            $tutores = $tutores->paginate(10);

            foreach ($tutores as $tutor) {
                $tutor->usuario;
                $tutor['usuario']->tipoTutorias;
            }

            $datosFinal=[
                'paginate' => [
                    'total'         => $tutores->total(),
                    'current_page'  => $tutores->currentPage(),
                    'per_page'      => $tutores->perPage(),
                    'last_page'     => $tutores->lastPage(),
                    'from'          => $tutores->firstItem(),
                    'to'            => $tutores->lastPage(),
                ],
                'tasks'=> $tutores
            ];
            return response()->json($datosFinal);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }



    public function tutoresAsignar(Request $request){
        try{
            $tiposProg = TipoUsuario::where('id_programa',$request->id_programa)->get();
            $idFacu = Programa::where('nombre',$request->nomFacu)->first()->id_programa;
            $tiposFacu = TipoUsuario::where('id_programa',$idFacu)->get();
            $tiposGen = TipoUsuario::where('id_programa',1)->get();

            $tiposTotal = array();
            foreach ($tiposProg as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposFacu as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposGen as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }

            $tutores = UsuarioxPrograma::
            selectRaw("*")
                ->join('usuario','usuario.id_usuario','=','usuario_x_programa.id_usuario')
                ->where('id_programa',$request->id_programa)
                ->where('usuario.nombre','ILIKE','%'.$request->nombre.'%');

            $tutores = $tutores->where(function($query) use ($tiposTotal)  {
                for ($i = 0; $i <= count($tiposTotal)-1; $i++) {
                    if($i==0) $query->where('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                    else $query->orWhere('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                }
            });

            $tutores = $tutores->get();

            $tutoresFinal = array();
            $tiposTutoria = TipoTutoria::where('id_programa',$request->id_programa)->where('tutor_fijo',1)->where('tutor_asignado',1)->where('estado','act')->get();
            foreach ($tutores as $tutor) {
                $tutor->usuario;
                $si = false;
                foreach ($tutor['usuario']->tipoTutorias as $item) {
                    foreach ($tiposTutoria as $tipo) {
                        if($item['id_tipo_tutoria'] == $tipo['id_tipo_tutoria']){
                            $si = true;
                            array_push($tutoresFinal,$tutor['usuario']);
                            break;
                        }
                    }
                    if($si) break;
                }
            }
            foreach ($tutoresFinal as $item) {
                $tts = array();
                foreach ($item->tipoTutorias as $tipoTutoria) {
                    if($tipoTutoria['id_programa'] == $request->id_programa && $tipoTutoria['tutor_fijo']==1 && $tipoTutoria['tutor_asignado']==1 ){
                        array_push($tts,$tipoTutoria);
                    }
                }
                $item->tiposTutoriaAsignar = $tts;
            }

            return response()->json($tutoresFinal);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function tiposTutoriaAlumno(Request $request){
        try {
            $resp = array();
            $tiposTutoria = TipoTutoria::where('id_programa',$request->id_programa)->where('tutor_fijo',0)->where('estado','act')->get();
            $tiposTutoriaAsignado = RegistroAlumno::where('id_alumno',$request->id_alumno)->where('id_programa',$request->id_programa)->where('estado','act')->get();
            if(count($tiposTutoriaAsignado)>0) {
                foreach ($tiposTutoriaAsignado as $item) {
                    if ($item && $item->tipoTutoria && $item->tipoTutoria['tutor_asignado'] == 0) array_push($resp,$item->tipoTutoria);
                }
            }
            foreach ($tiposTutoria as $item) {
                array_push($resp,$item);
            }
            return response()->json($resp,200);
        }
        catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function tutoresAlumno(Request $request){
        try{
            $tiposProg = TipoUsuario::where('id_programa',$request->id_programa)->get();
            $idFacu = Programa::where('nombre',$request->nomFacu)->first()->id_programa;
            $tiposFacu = TipoUsuario::where('id_programa',$idFacu)->get();
            $tiposGen = TipoUsuario::where('id_programa',1)->get();

            $tiposTotal = array();
            foreach ($tiposProg as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposFacu as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposGen as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }

            $tutores = UsuarioxPrograma::
            selectRaw("*")
                ->join('usuario','usuario.id_usuario','=','usuario_x_programa.id_usuario')
                ->where('id_programa',$request->id_programa)
                ->where('usuario.nombre','ILIKE','%'.$request->nombre.'%');

            $tutores = $tutores->where(function($query) use ($tiposTotal)  {
                for ($i = 0; $i <= count($tiposTotal)-1; $i++) {
                    if($i==0) $query->where('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                    else $query->orWhere('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                }
            });

            $tutores = $tutores->get();

            $tutoresFinal = array();

            $tutoresAsignados = array();

            $tutorAsignado = RegistroAlumno::where('id_alumno',$request->id_alumno)->where('id_programa',$request->id_programa)->where('estado','act')->get();
            if($tutorAsignado) {
                foreach ($tutorAsignado as $ta) {
                    if($ta){
                        $tutorAsignadoM = Usuario::findOrFail($ta->id_tutor);
                        $tutorAsignadoM->ttAsignado = $ta->tipoTutoria;
                        $ttsPrograma= array();
                        foreach ($tutorAsignadoM->tipoTutorias as $item) {
                            if($item['id_programa']==$request->id_programa){
                                array_push($ttsPrograma,$item);
                            }
                        }
                        $tutorAsignadoM['tutoriasPrograma'] = $ttsPrograma;
                        array_push($tutoresFinal, $tutorAsignadoM);
                        array_push($tutoresAsignados, $tutorAsignadoM->nombre);
                    }
                }
            }
            $i = 0;
            $tiposTutoria = TipoTutoria::where('id_programa',$request->id_programa)->where('tutor_fijo',0)->where('estado','act')->get();
            foreach ($tutores as $tutor) {
                $tutor->usuario;
                $si = false;
                $asignado = false;
                foreach ($tutoresAsignados as $tutoresAsignado) {
                    if($tutor['usuario']['nombre'] == $tutoresAsignado){
                        $asignado = true;
                    }
                }
                if(!$asignado){
                    $ttsPrograma= array();
                    foreach ($tutor['usuario']->tipoTutorias as $item) {
                        foreach ($tiposTutoria as $tipo) {
                            if($item['id_tipo_tutoria'] == $tipo['id_tipo_tutoria']){
                                $si = true;
                                break;
                            }
                        }
                        if($item['id_programa']==$request->id_programa){
                            array_push($ttsPrograma,$item);
                        }
                    }
                    if($si) {
                        $tutor['usuario']['tutoriasPrograma'] = $ttsPrograma;
                        array_push($tutoresFinal,$tutor['usuario']);
                    }
                }
                $i = $i + 1;
            }

            return response()->json($tutoresFinal);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }


    public function tutoresAlumnoPaginado(Request $request){
        try{
            $tiposProg = TipoUsuario::where('id_programa',$request->id_programa)->get();
            $idFacu = Programa::where('nombre',$request->nomFacu)->first()->id_programa;
            $tiposFacu = TipoUsuario::where('id_programa',$idFacu)->get();
            $tiposGen = TipoUsuario::where('id_programa',1)->get();

            $tiposTotal = array();
            foreach ($tiposProg as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposFacu as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposGen as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }

            $tutores = UsuarioxPrograma::
            selectRaw("*")
                ->join('usuario','usuario.id_usuario','=','usuario_x_programa.id_usuario')
                ->where('id_programa',$request->id_programa)
                ->where(function($query) use ($request)  {
                    $query->where('usuario.nombre','ILIKE','%'.$request->nombre.'%');
                    $query->orWhere('usuario.apellidos','ILIKE','%'.$request->nombre.'%');
                });

            $tutores = $tutores->where(function($query) use ($tiposTotal)  {
                for ($i = 0; $i <= count($tiposTotal)-1; $i++) {
                    if($i==0) $query->where('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                    else $query->orWhere('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                }
            });

            $tutores = $tutores->get();

            $tutoresFinalIds = array();

            $tiposTutoria = array();

            if($request->id_tipo_tutoria==null) {
                $tiposTutoriasLista = TipoTutoria::where('id_programa', $request->id_programa)->where('tutor_fijo', "0")->where('estado','act')->get();
                foreach ($tiposTutoriasLista as $tt) {
                    array_push($tiposTutoria,$tt);
                }
            }
            else array_push($tiposTutoria,TipoTutoria::findOrFail($request->id_tipo_tutoria));

            $tutoriasAsignado = RegistroAlumno::where('id_alumno',$request->id_alumno)->where('id_programa',$request->id_programa)->where('estado','act')->get();
            if(count($tutoriasAsignado)>0 && $request->id_tipo_tutoria==null) {
                foreach ($tutoriasAsignado as $item) {
                    $item->tipoTutoria;
                    $item['tipoTutoria']['asignado'] = 1;
                    if($item['tipoTutoria']['tutor_asignado']==0) array_push($tiposTutoria,$item['tipoTutoria']);
                }
            }

            foreach ($tutores as $tutor) {
                $tutor->usuario;
                $si = false;
                foreach ($tutor['usuario']->tipoTutorias as $item) {
                    foreach ($tiposTutoria as $tipo) {
                        if($item['id_tipo_tutoria'] == $tipo['id_tipo_tutoria']){
                            $si = true;
                            array_push($tutoresFinalIds,$tutor['usuario']['id_usuario']);
                            break;
                        }
                    }
                    if($si) break;
                }
            }

            $tutoresPaginado = Usuario::whereIn('id_usuario',$tutoresFinalIds)->orderBy('nombre','ASC')->paginate(10);

            $tutorAsignado = RegistroAlumno::where('id_alumno',$request->id_alumno)->where('id_programa',$request->id_programa)->where('estado','act')->get();
            $ttAsignado = array();

            if($tutorAsignado) {
                foreach ($tutorAsignado as $item) {
                    if($item->tipoTutoria['tutor_asignado']==0) array_push($ttAsignado,$item->tipoTutoria);
                }
            }

            foreach ($tutoresPaginado as $tutor) {
                $tutor->tipoTutorias;
                $stop = false;
                $i=0;
                $ttPorBorrar = array();
                foreach ($tutor->tipoTutorias as $tipoTutoria) {
                    if($tipoTutoria['id_programa'] == $request->id_programa){
                        $verdadero = false;
                        foreach ($ttAsignado as $item) {
                            if ($tipoTutoria['id_tipo_tutoria']== $item['id_tipo_tutoria']){
                                $verdadero = true;
                                break;
                            }
                        }
                        if($tipoTutoria['tutor_fijo'] == "1" && $verdadero){
                            if($tipoTutoria['tutor_asignado'] == "0") {
                                $tutor['solicitado'] = 1;
                                $stop = true;
                            }
                            else{
                                if($stop!=true) $tutor['solicitado'] = 0;
                            }
                        }
                        else{
                            if($stop!=true) $tutor['solicitado'] = 0;
                            $tutor['fijo'] = 0;
                        }
                        $i++;
                    }
                    else{
                        array_push($ttPorBorrar,$i);
                    }
                }
                $ttsPrograma = $tutor->tipoTutorias->toArray();
                foreach ($ttPorBorrar as $item) {
                    array_splice($ttsPrograma,$item,1);
                }
                $tutor->tipoTutoriasPrograma = $ttsPrograma;
                if ($tutor['fijo']!=0) $tutor['fijo'] = 1;
            }

            return response()->json([
                'paginado'=>$tutoresPaginado,
                'tipoAsignado'=>$ttAsignado]);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function tutoresAsignados(Request $request){
        try {
            $tutoresFinal = array();
            $tutorAsignado = RegistroAlumno::where('id_alumno',$request->id_alumno)->where('id_programa',$request->id_programa)->where('estado','act')->get();
            if($tutorAsignado) {
                foreach ($tutorAsignado as $ta) {
                    if($ta->id_tutor){
                        $tutorAsignadoM = Usuario::findOrFail($ta->id_tutor);
                        $tutorAsignadoM->tipoTutorias;
                        if($ta->tipoTutoria) $tutorAsignadoM->tipoAsignadoAlumno = $ta->tipoTutoria;
                        array_push($tutoresFinal, $tutorAsignadoM);
                    }
                }
            }

            return response()->json($tutoresFinal);
        }
        catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //listar todos los tutores de este programa
    public function tutoresTodo(Request $request){
        try{
            $tiposProg = TipoUsuario::where('id_programa',$request->id_programa)->get();
            $idFacu = Programa::where('nombre',$request->nomFacu)->first()->id_programa;
            $tiposFacu = TipoUsuario::where('id_programa',$idFacu)->get();
            $tiposGen = TipoUsuario::where('id_programa',1)->get();

            $tiposTotal = array();
            foreach ($tiposProg as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposFacu as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }
            foreach ($tiposGen as $item) {
                $perm = $item->permisos;
                foreach ($perm as $permiso) {
                    if($permiso['id_permiso']==21){
                        array_push($tiposTotal,$item);
                        break;
                    }
                }
            }

            $tutores = UsuarioxPrograma::where('id_programa',$request->id_programa);

            $tutores = $tutores->where(function($query) use ($tiposTotal)  {
                for ($i = 0; $i <= count($tiposTotal)-1; $i++) {
                    if($i==0) $query->where('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                    else $query->orWhere('id_tipo_usuario',$tiposTotal[$i]['id_tipo_usuario']);
                }
            });

            $tutores = $tutores
                ->get();

            $final = array();
            foreach ($tutores as $tutor) {
                $tutor->usuario;
                if($tutor['usuario']['estado']== 'act'){
                    $tutor['usuario']->tipoTutorias;
                    array_push($final,$tutor);
                }

            }

            return response()->json($final);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function alumnosProg(Request $request){
        //alumnos por programa
        try{
            $programa=Programa::find($request->idProg);
            $datos['alumnos']=$programa->belongsToMany('App\Usuario','usuario_x_programa','id_programa','id_usuario')
                ->selectRaw("usuario.*")
                //->selectRaw("valores.nombre as condicion, usuario.*")
                //->join('valores','valores.abreviatura','=','usuario.condicion_alumno')
                ->where('usuario_x_programa.id_tipo_usuario',$request->idTipoU)
                ->where('usuario.nombre','ilike', '%' . $request->nombre . '%')
                ->where('usuario.estado','act')
                ->orderBy('usuario.codigo','ASC')->get();
            return response()->json($datos['alumnos']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function asistenciaXTutores(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $consulta['datos']=Disponibilidad::selectRaw("count(cita_x_usuario.id_usuario) as total ,usuario.id_usuario, concat(usuario.nombre,' ', usuario.apellidos)")
                ->join('cita','cita.id_disponibilidad','=','disponibilidad.id_disponibilidad')
                ->join('cita_x_usuario','cita.id_cita','=','cita_x_usuario.id_cita')
                ->join('usuario','usuario.id_usuario','=','disponibilidad.id_usuario')
                ->where('disponibilidad.id_programa','=',$request->id_programa)
                ->where('disponibilidad.estado','=','act')
                ->where('usuario.estado','=','act')
                ->where('cita.estado','=','act')
                ->where('cita_x_usuario.asistencia','=','asi')
                ->whereIn('disponibilidad.id_usuario',$request->id_tutores)
                ->whereBetween('disponibilidad.fecha',[$ini,$fin])
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
            if(count($consulta['datos'])!=count($consulta2['datos'])){
                //verificar que tutores tienen cantidad de alumnos y cuales no
                $i=0;
                foreach ($consulta2['datos'] as $tutores){
                    $cant=0;
                    $j=0;
                    foreach ($consulta['datos'] as $datos){
                        if(($tutores->id_usuario==$datos->id_usuario)) {
                            $consulta2['datos'][$i]['total'] = $consulta['datos'][$j]['total'];
                            $cant=1;
                            break;
                        }
                        $j++;
                    }
                    if($cant!=1){//significa que no tiene ninguno alumno
                        $consulta2['datos'][$i]['total']=0;
                        $cant=0;
                    }
                    $i++;
                }
                return response()->json($consulta2['datos']);
            }
            return response()->json($consulta['datos']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function asistenciaXPrograma(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $consulta=Disponibilidad::selectRaw('count(cita_x_usuario.id_usuario) as total ,programa.id_programa, programa.nombre as data')
                ->join('cita','cita.id_disponibilidad','=','disponibilidad.id_disponibilidad')
                ->join('cita_x_usuario','cita.id_cita','=','cita_x_usuario.id_cita')
                ->join('programa','programa.id_programa','=','disponibilidad.id_programa')
                ->where('programa.id_facultad','=',$request->id_facultad)
                ->whereIn('disponibilidad.id_programa',$request->id_programa)
                ->where('disponibilidad.estado','=','act')
                ->where('programa.estado','=','act')
                ->where('cita.estado','=','act')
                ->where('cita_x_usuario.asistencia','=','asi')
                ->whereBetween('disponibilidad.fecha',[$ini,$fin])
                ->groupBy('programa.id_programa')
                ->orderBy('programa.nombre', 'ASC')
                ->get();
            return response()->json($consulta);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function cantAtendidos(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $consulta=Disponibilidad::selectRaw('count(cita_x_usuario.id_usuario) as total, cita_x_usuario.asistencia as data')
                ->join('cita','cita.id_disponibilidad','=','disponibilidad.id_disponibilidad')
                ->join('cita_x_usuario','cita.id_cita','=','cita_x_usuario.id_cita')
                ->where('disponibilidad.id_programa','=',$request->id_programa)
                ->whereIn('disponibilidad.id_usuario',$request->id_tutores)
                ->where('disponibilidad.estado','=','act')
                ->whereBetween('disponibilidad.fecha',[$ini,$fin])
                ->groupBy('cita_x_usuario.asistencia')
                ->get();
            return response()->json($consulta);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function citasxdia(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $consulta=Disponibilidad::selectRaw('count(disponibilidad.fecha) as total,disponibilidad.fecha as data')
                ->join('cita','cita.id_disponibilidad','=','disponibilidad.id_disponibilidad')
                ->join('cita_x_usuario','cita.id_cita','=','cita_x_usuario.id_cita')
                ->join('usuario','usuario.id_usuario','=','disponibilidad.id_usuario')
                ->where('disponibilidad.id_programa','=',$request->id_programa)
                ->where('disponibilidad.estado','=','act')
                ->where('cita_x_usuario.asistencia','=','asi')
                ->whereIn('usuario.id_usuario',$request->id_tutores)
                ->whereBetween('disponibilidad.fecha',[$ini,$fin])
                ->groupBy('disponibilidad.fecha')
                ->orderBy('disponibilidad.fecha','ASC')
                ->get();
            return response()->json($consulta);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function citasXDiaTodos(Request $request){
        try{
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin.' 23:59:59';
            $consulta=Disponibilidad::selectRaw('count(disponibilidad.fecha) as total ,disponibilidad.fecha as data')
                ->join('cita','cita.id_disponibilidad','=','disponibilidad.id_disponibilidad')
                ->join('cita_x_usuario','cita.id_cita','=','cita_x_usuario.id_cita')
                ->join('usuario','usuario.id_usuario','=','disponibilidad.id_usuario')
                ->where('disponibilidad.id_programa','=',$request->id_programa)
                ->where('disponibilidad.estado','=','act')
                ->where('cita_x_usuario.asistencia','=','asi')
                ->whereBetween('disponibilidad.fecha',[$ini,$fin])
                ->groupBy('disponibilidad.fecha')
                ->orderBy('disponibilidad.fecha','ASC')
                ->get();
            return response()->json($consulta);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }


    public function listProgFacu()
    {
        try{
            $resp = array();
            $programas =Programa::where('estado','act')->get();
            foreach ($programas as $programa) {
                if ($programa->nombre == $programa->facultad->nombre){
                    $listado = $programa->facultad->programas;
                    if(count ($listado)>1) array_push($resp,$programa);
                }
            }
            return response()->json($resp);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
}
