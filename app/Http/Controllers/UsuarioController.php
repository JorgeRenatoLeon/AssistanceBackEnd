<?php
namespace App\Http\Controllers;

use App\Cita;
use App\Mail\CorreoUsuario;
use App\PlanAccion;
use App\TipoTutoria;
use App\UsuarioxPrograma;
use App\Valores;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Exception;
use App\Programa;
use App\Solicitud;
use App\TipoUsuario;
use App\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    //Lista todos los usuarios con sus roles en cada programa
    public function index(Request $request)
    {
        try {
            $usuario = Usuario::
                where(function($query) use ($request){
                    $query->where('apellidos','ILIKE','%'.$request->busqueda.'%');
                    $query->orWhere('nombre','ILIKE','%'.$request->busqueda.'%');
                    $query->orWhere('correo','ILIKE','%'.$request->busqueda.'%');
                    $query->orWhere('codigo','ILIKE','%'.$request->busqueda.'%');
                })
                ->where('estado','!=','eli')
                ->with('usuarioXProgramas')
                ->with('usuarioXProgramas.programa','usuarioXProgramas.tipoUsuario')
                ->with('usuarioXProgramas.tipoUsuario.permisos')
                ->orderBy('nombre', 'ASC')
                ->paginate(10);
            return response()->json($usuario);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Lista por ID
    public function show($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            $condciones=DB::table('valores')->where('tabla','CONDICION_ALUMNO')->get();
            foreach ($condciones as $condcione) {
                if($condcione->abreviatura == $usuario->condicion_alumno){
                    $usuario->cond = $condcione->nombre;
                }
            }
            return response()->json($usuario);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //Lista usuario con todos sus roles y programas
    public function usuarioProgramaRol($id)
    {
        try {
            $usuario = Usuario::with('usuarioXProgramas')->
            with('usuarioXProgramas.programa','usuarioXProgramas.tipoUsuario')->findOrFail($id);
            return response()->json($usuario);

        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Buscar por nombre
    public function usuariosPorNombre(Request $request)
    {
        try {
            $usuario = Usuario::where(DB::raw("concat(nombre,' ',apellidos)"),'ilike', '%' . $request->nombre . '%')
                ->where('estado','act')->with('usuarioXProgramas')
                ->with('usuarioXProgramas.programa','usuarioXProgramas.tipoUsuario')->get();
            return response()->json($usuario);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //Verificar usuario
    public function verificarUsuario(Request $request){
        try {
            $pos = strpos($request->criterio, '@');//1200ms
            if ($pos === false) {
                $usuario_codigo = Usuario::where('codigo', $request->criterio)->where('estado', '!=', 'eli')->get();
                if (count($usuario_codigo) > 0) {
                    $usuario_codigo[0]->usuarioXProgramas;
                    foreach ($usuario_codigo[0]->usuarioXProgramas as $usuarioXPrograma) {
                        $usuarioXPrograma->programa;
                        $usuarioXPrograma->tipoUsuario;
                    }
                    return response()->json(['status' => 'El codigo ingresado ya existe',
                        'usuario' => $usuario_codigo]);
                }
            }
            if($pos>=0) {
                $usuario_correo = Usuario::where('correo', $request->criterio)->where('estado', '!=', 'eli')->get();
                if(count($usuario_correo)>0) {
                    $usuario_correo[0]->usuarioXProgramas;
                    foreach ($usuario_correo[0]->usuarioXProgramas as $usuarioXPrograma) {
                        $usuarioXPrograma->programa;
                        $usuarioXPrograma->tipoUsuario;
                    }
                    return response()->json(['status' => 'El correo ingresado ya existe',
                    'usuario'=>$usuario_correo]);
                }
            }
            return response()->json(['status'=>'Codigo o correo no encontrado']);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Inserta
    public function store(Request $request)
    {
        try {
            //Registrar a un usuario nuevo en en el software
            if (!$request->has('id_usuario')) {
                $usuario = new Usuario();
                $usuario->codigo = $request->codigo;
                $usuario->estado = 'act';
                if ($request->has('password')) {
                    $pass = password_hash($request->password, PASSWORD_DEFAULT);
                    $request->merge(['password' => $pass]);
                }
                $usuario->password = $request->password;
                $usuario->usuario_creacion = $request->usuario_creacion;
                $usuario->usuario_actualizacion = $request->usuario_creacion;
                $usuario->correo = $request->correo;
                $usuario->telefono = $request->telefono;
                $usuario->nombre = $request->nombre;
                $usuario->apellidos = $request->apellidos;
                $usuario->sexo = $request->sexo;
                $usuario->condicion_alumno = $request->condicion_alumno;
                $usuario->save();
                if (!is_null($request->id_tipo_usuario) and !is_null($request->id_programaNuevo)) {
                    $usuario->tipoUsuario()->attach($request->id_tipo_usuario, ['id_programa' => $request->id_programaNuevo]);
                }
                    elseif (!is_null($request->id_tipo_usuario)) {
                    $usuario->tipoUsuario()->attach($request->id_tipo_usuario);
                }
                return response()->json(['status' => 'success',
                    'user' => $usuario],200);
            }
            else{
                //Registrar usuario existente a un programa
                //*request recibido [id_usuario->,id_tipo_usuario->,id_programaNuevo->]
                $usuario= Usuario::findOrFail($request->id_usuario);
                if ($request->has('condicion_alumno')){
                    $usuario->update(['condicion_alumno'=>$request->condicion_alumno]);
                }
                $usuario->tipoUsuario()->attach($request->id_tipo_usuario,['id_programa'=>$request->id_programaNuevo]);
                return response()->json([
                    'status' => 'success'],200);
            }
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Actualiza
    public function update(Request $request, $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            if ($request->has('password')) {
                $pass = password_hash($request->password, PASSWORD_DEFAULT);
                $request->merge(['password' => $pass]);
            }
            if($request->recuperar){
                $usuario->token_recuperacion =  str_replace('.','',str_replace('/','',password_hash(Carbon::now(), PASSWORD_DEFAULT)));
                $usuario->save();
            }
            /*
            $usuario->correo = $request->correo;
            $usuario->codigo = $request->codigo;

            $usuario->estado = $request->estado;
            $usuario->usuario_actualizacion = $request->usuario_actualizacion;
            $usuario->telefono = $request->telefono;
            $usuario->nombre = $request->nombre;
            $usuario->apellidos = $request->apellidos;
            $usuario->sexo = $request->sexo;
            $usuario->condicion_alumno = $request->condicion_alumno;
            $usuario->save();
            */
            $usuario->update($request->all());
            //debe mandar id_tipo_usuario_Nuevo, id_programa y el id_usuario
            if ($request->has('id_tipo_usuario_Nuevo')) {
                $usuario->programa()->
                updateExistingPivot($request->id_programa, ['id_tipo_usuario' => $request->id_tipo_usuario_Nuevo]);
            }
            return response()->json($usuario,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Modificación de tipos de tutorias de un usuario("TUTOR")
    public function updateTipoTutoria(Request $request, $id)
    {
        try {
            // se recibe en el $request tutorias_insertar(es un array),id_programa
            $usuario = Usuario::findOrFail($id);
            $tutorias = $usuario->tipoTutorias()
                ->where('id_programa', $request->id_programa)->get()->toArray();
            $ids_tutorias_actual = array_column($tutorias, 'id_tipo_tutoria');
            $tutorias_final = $request->tutorias_insertar;
            $eliminar = array_diff($ids_tutorias_actual, $tutorias_final);
            $insertar = array_diff($tutorias_final, $ids_tutorias_actual);
            if (count($insertar) > 0) {
                $usuario->tipoTutorias()->attach($insertar);
            }
            if (count($eliminar) > 0) {
                $usuario->tipoTutorias()->detach($eliminar);
            }
            return response()->json(['status' => 'Cambios realizados'], 200);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //Eliminar
    public function destroy(Request$request,$id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            $usuario->usuario_actualizacion=$request->usuario_actualizacion;
            $usuario->estado = 'eli';
            $usuario->save();
            return response()->json(['status'=> 'success']);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }


    //Elimnar a usuario de un programa(o rol de un usuario en un programa)
    public function eliUsuarioPrograma(Request $request)
    {
        try {
            $usuario = Usuario::findOrFail($request->id_usuario);
            $usuario->programa()->wherePivot('id_tipo_usuario',$request->tipo_usuario)->detach($request->id_programa);
            $usuario->save();
            return response()->json(['status'=>'sucess']);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Elimnar a coordinador de un programa/facultad si se asigna nuevo
    public function eliCoordinador(Request $request)
    {
        try {
            $programa = Programa::findOrFail($request->id_programa);
            //actualizar coordinadores
            $exist=UsuarioxPrograma::where('id_programa',$programa->id_programa)->get();
            if(!empty($exist)){
                $programa->usuarios()->wherePivot('id_tipo_usuario',$request->tipo_usuario)->detach();
                if($request->nuevo == 1) {
                    $usuario = Usuario::findOrFail($request->id_usuario);
                    $usuario->tipoUsuario()->attach($request->tipo_usuario, ['id_programa' => $request->id_programa]);
                }
            }
            $existAux =UsuarioxPrograma::where('id_usuario',$request->id_usuario)->where('id_tipo_usuario',$request->tipo_usuario)->get();
            if(!empty($existAux) && $request->nuevo==0){
                $usuario = Usuario::where('id_usuario',$request->id_usuario)->first();
                if ($usuario) $usuario->tipoUsuario()->wherePivot('id_programa',null)->wherePivot('id_tipo_usuario',$request->tipo_usuario)->detach();
            }
            return response()->json(['status'=>'sucess']);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Sacar Tipo de Usuario
    public function tipoUsuario(Request $request = null){
        return response()->json(auth()->user()->tipoUsuarios);
    }

    //Sacar Tipo de Usuario por Programa
    public function programas(Request $request){

        if(auth()->user() == null){
            $findUs = Usuario::where('correo',$request->usuario['correo'])->first();
            $usuariosxprograma = $findUs->usuarioXProgramas;
            $listado = array();
            foreach ($usuariosxprograma as $item) {
                $item->programa->facultad;
                array_push($listado,['tipoUsuario'=>$item->tipoUsuario,'programa'=>$item->programa]);
            }
            return response()->json($listado);
        }
        else{
            $usuariosxprograma = auth()->user()->usuarioXProgramas;
            $listado = array();
            foreach ($usuariosxprograma as $item) {
                array_push($listado,['tipoUsuario'=>$item->tipoUsuario,'programa'=>$item->programa]);
            }
            return response()->json($listado);
        }
    }

    //Sacar Tipo de Usuario
    public function permisos(Request $request){
        $tipoUsu = new TipoUsuario();
        if(auth()->user() == null){
            if($request->usuario){
                if($request->programa){
                    if ($request->programa === 'admin'){
                        $tipoUsu = TipoUsuario::where('nombre','Admin')->first();
                    }
                    else{
                        $findus = Usuario::where('correo',$request->usuario['correo'])->first();
                        $tipoUsus = $findus->usuarioXProgramas;
                        foreach ($tipoUsus as $item) {
                            if($item->programa['nombre'] === $request->programa){
                                $tipoUsu = $item->tipoUsuario;
                            }
                        }
                    }
                }
                else{
                    $finduser = Usuario::where('correo', $request->usuario['correo'])->first();
                    $tipoUsu = $finduser->tipoUsuario->first();
                }
            }
            else{
                return null;
            }
        }
        else{
            if($request->programa){
                $tipoUsus = auth()->user()->usuarioXProgramas;
                foreach ($tipoUsus as $item) {
                    if($item->programa->nombre === $request->programa){
                        $tipoUsu = $item->tipoUsuario;
                    }
                }
            }
            else{
                $tipoUsu = auth()->user()->tipoUsuario->first();
            }
        }
        $pila = array();
        if($tipoUsu===null) return response()->json([]);
        foreach ($tipoUsu->permisos as $element) {
            array_push($pila, $element->nombre);
        }
        return response()->json($pila);
    }

    public function CryptoJSAesDecrypt($passphrase, $jsonString){


        $jsondata = json_decode($jsonString, true);
        $salt = hex2bin($jsondata["s"]);
        $ct = base64_decode($jsondata["ct"]);
        $iv  = hex2bin($jsondata["iv"]);
        $concatedPassphrase = $passphrase.$salt;
        $md5 = array();
        $md5[0] = md5($concatedPassphrase, true);
        $result = $md5[0];
        for ($i = 1; $i < 3; $i++) {
            $md5[$i] = md5($md5[$i - 1].$concatedPassphrase, true);
            $result .= $md5[$i];
        }
        $key = substr($result, 0, 32);
        $data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
        return json_decode($data, true);

    }

    public function vuelogin(Request $request)
    {
        $pass = $this->CryptoJSAesDecrypt("assistancesoporte",$request->password);
        if(Auth::attempt(['correo' => $request->correo, 'password' => $pass])){
            $finduser = Usuario::where('correo', $request->correo)->first();
            if($finduser->estado == 'act'){
                Auth::loginUsingId($finduser->id_usuario);
                $user = Auth::user();
                $condciones=DB::table('valores')->where('tabla','CONDICION_ALUMNO')->get();
                foreach ($condciones as $condcione) {
                    if($condcione->abreviatura == $user->condicion_alumno){
                        $user->cond = $condcione->nombre;
                    }
                }
                return response()->json([
                    'status'   => 'success',
                    'user' => $user->attributesToArray()
                ]);
            }
            else{
                return response()->json([
                    'status' => 'error',
                    'user'   => 'Unauthorized Access 2'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'user'   => 'Unauthorized Access'
            ]);
        }
    }

    public function vuelogout($request = null){

        auth()->guard()->logout();

        session()->invalidate();

        session()->regenerateToken();

        return response()->json([
            'status'   => 'success'
        ]);
    }

    public function vueuser(Request $request){
        if(auth()->user() == null){
            if($request->usuario){
                $finduser = Usuario::where('correo', $request->usuario['correo'])->first();
                return response()->json(['user' => $finduser]);
            }
            return null;
        }
        return response()->json(['user' => auth()->user()]);
    }

    public function googleLogin(Request $request)
    {
        if ($request->institucion === 'pucp.edu.pe' || $request->institucion === 'pucp.pe') {
            $finduser = Usuario::where('correo', $request->correo)->first();
            if ($finduser !== null) {
                if($finduser->estado == 'act'){
                    Auth::loginUsingId($finduser->id_usuario);
                    $user = Auth::user();
                    return response()->json([
                        'status'   => 'success',
                        'user' => $user,
                    ]);
                }
                else{
                    return response()->json([
                        'status' => 'Cuenta Inhabilitada',
                    ]);
                }
            } else {
                return response()->json(['status'=> 'No se encuentra registrado']);
            }
        }
        else{
            return response()->json(['status'=> 'No es un correo de la institución']);
        }
    }

    public function googleregister(Request $request){
        if ($request->institucion === 'pucp.edu.pe' || $request->institucion === 'pucp.pe') {

            $request->nombre = ucwords(strtolower($request->nombre));
            $request->apellidos = ucwords(strtolower($request->apellidos));
            $this->store($request);
            $usuario = Usuario::where('correo',$request->correo)->first();
            $idAux = Programa::where('nombre',$request->programa['nombre'])->first()->id_programa;
            $cordProg = app('App\Http\Controllers\ProgramaController')->idCoordinador($idAux);
            $solicitud = new Solicitud();
            $solicitud->id_solicitante = $usuario->id_usuario;
            $solicitud->id_remitente = $cordProg->id_usuario;
            $solicitud->id_usuario_relacionado = $cordProg->id_usuario;
            $solicitud->descripcion = "Solicitud para pertenecer al programa y acceder al sistema de tutorias";
            $solicitud->tipo_solicitud = "Programa";
            $solicitud->usuario_creacion = $usuario->id_usuario;
            $solicitud->id_programa = $request->programa['id_programa'];
            $solicitud->estado = 'act';
            $solicitud->save();
            return response()->json([
                'status'   => 'success',
                'user' => $usuario,
            ]);
        }
        else{
            return response()->json(['status'=> 'No es un correo de la institucion']);
        }
    }

    public function vueregister(Request $request){
        if((explode("@", $request->correo)[1] === 'pucp.edu.pe') || (explode("@", $request->correo)[1] === 'pucp.pe')){
            $find = Usuario::where('correo', $request->correo)->first();
            if($find){
                return response()->json(['status'=> 'Ya existe una cuenta con el correo especificado']);
            }
            else{
                $this->store($request);
                $usuario = Usuario::where('correo',$request->correo)->first();
                $idAux = Programa::where('nombre',$request->programa['nombre'])->first()->id_programa;
                $cordProg = app('App\Http\Controllers\ProgramaController')->idCoordinador($idAux);
                $solicitud = new Solicitud();
                $solicitud->id_solicitante = $usuario->id_usuario;
                $solicitud->id_remitente = $cordProg->id_usuario;
                $solicitud->id_usuario_relacionado = $cordProg->id_usuario;
                $solicitud->descripcion = "Solicitud para pertenecer al programa y acceder al sistema de tutorias";
                $solicitud->tipo_solicitud = "Programa";
                $solicitud->usuario_creacion = $usuario->id_usuario;
                $solicitud->id_programa = $request->programa['id_programa'];
                $solicitud->estado = 'act';
                $solicitud->save();
                return response()->json([
                    'status'   => 'success',
                    'user' => $usuario,
                ]);
            }
        }
        else{
            return response()->json(['status'=> 'No es un correo de la institucion']);
        }
    }

    public function subirFoto(Request $request){
        try {
            if($request->get('image'))
            {
                $usuario = Usuario::where('id_usuario',$request->id_usuario)->first();
                $image = $request->get('image');
                $name = 'perfil'.'.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
                $path = 'usuarios/' . $usuario->codigo;
                $files_ruta = Storage::files($path);
                foreach ($files_ruta as $file) {
                    if (strpos($file, 'perfil') !== false) {
                        Storage::delete($file);
                    }
                }
                Storage::putFileAs($path, $image, $name);
                $base_datos = $path . '/' . $name;
                $usuario->imagen = $base_datos;
                $usuario->usuario_actualizacion=$request->usuario_actualizacion;
                $usuario->save();
                return response()->json(['success' => 'You have successfully uploaded an image',
                    'path' => 'https://assisstanceproyecto20201.vizcochitos.cloudns.cl/'.$path.'/'.$name,
                    'name' => $name], 200);
            }
        }
        catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function subirNotas(Request $request){
        try {
            $codigos=$request->codigos;
            $files=$request->file('files');
            $errores=array();
            foreach ($codigos as $index => $codigo) {
                $usuario = Usuario::where('codigo', $codigo)->first();

                $notas = $files[$index];
                if ($notas->extension() == 'pdf') {
                    $time = time();
                    $str = Str::random(8);
                    $name = $time . $str . '_notas.pdf';
                    $path = 'usuarios/' . $codigo;
                    $files_ruta = Storage::files($path);
                    foreach ($files_ruta as $file) {
                        if (strpos($file, '_notas') !== false) {
                            Storage::delete($file);
                        }
                    }
                    $put = Storage::putFileAs($path, $notas, $name);
                    $base_datos = url('/') . '/storage/' . $path . '/' . $name;
                    $usuario->notas = $base_datos;
                    $usuario->usuario_actualizacion = $request->usuario_actualizacion;
                    $usuario->save();
                } else {
                    array_push($errores, ['codigo' => $codigo, 'file' => $notas->getClientOriginalName(),
                        'error' => 'Archivo sin extension .pdf']);
                    continue;
                }
            }
            $cant_errores=count($errores);
            if($cant_errores>0){
                return response()->json(['status'=>'Se han encontrado errores',
                    'cantidad'=>$cant_errores,
                    'reporte' =>$errores ]);
            }
            else{
                return response()->json(['status'=>'Subida terminada'],200);
            }
        }catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function masivo(Request $request,$id_programa){
        try {
            $files=$request->file('files');
            $errores=array();
            foreach ($files  as $file) {
                $codigo=substr($file->getClientOriginalName(),0,8);
                if (!($file->extension() == 'pdf')){
                    array_push($errores, ['codigo' => $codigo, 'file' => $file->getClientOriginalName(),
                        'error' => 'Archivo sin formato PDF']);
                    continue;
                }
                $usuario = Usuario::where('codigo',$codigo)->first();
                if(!$usuario){
                    array_push($errores,['codigo'=>$codigo,'file'=>$file->getClientOriginalName(),
                        'error'=>'El codigo no existe en el sistema']);
                    continue;
                }
                $tipoUsuarios = $usuario->tipoUsuario()
                    ->where('usuario_x_programa.id_programa', $id_programa)->get()->toArray();
                /*$tipoUsuarios = $usuario->tipoUsuario()
                    ->where('usuario_x_programa.id_programa', $id_programa)->get();
                #alumno=0;
                foreach($tipoUsuarios as $rol){
                    $permisos=$rol->permisos;
                    foreach($permisos as $p){
                        if($p->id==12){$alumno=1}
                    }
                }
                if(!$alumno){
                    array_push($errores,['codigo'=>$codigo,'file'=>$file->getClientOriginalName(),
                        'error'=>'El usuario no es alumno del programa']);
                    continue;
                }
                 */
                $ids_tipo_usuario = array_column($tipoUsuarios, 'id_tipo_usuario');
                if(!in_array(5,$ids_tipo_usuario)){
                    array_push($errores,['codigo'=>$codigo,'file'=>$file->getClientOriginalName(),
                        'error'=>'El usuario no es alumno del programa']);
                    continue;
                }
                $time = time();
                $str = Str::random(8);
                $name = $time . $str . '_notas.pdf';
                $path = 'usuarios/' . $codigo;
                $files_ruta = Storage::files($path);
                foreach ($files_ruta as $file_guardado) {
                    if (strpos($file_guardado, '_notas') !== false) {
                        Storage::delete($file_guardado);
                    }
                }
                $put = Storage::putFileAs($path, $file, $name);
                $completo = url('/') . '/storage/' . $path . '/' . $name;
                $base_datos = '/'.$codigo . '/' . $name;
                $usuario->notas = $base_datos;
                $usuario->usuario_actualizacion=$request->usuario_actualizacion;
                $usuario->save();
            }
            $cant_errores=count($errores);
            if($cant_errores>0){
                return response()->json(['status'=>'Se han encontrado errores',
                    'cantidad'=>$cant_errores,
                    'reporte' =>$errores]);
            }
            else{
                return response()->json(['status'=>'Subida terminada'],200);
            }
        }catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function nuevoPrograma(Request $request,$id){
        try{
            $usuario = Usuario::findOrFail($id);
            if ($request->has('id_tipo_usuario')) {
                if($request->id_programa!=null){
                    $usuario->tipoUsuario()->attach($request->id_tipo_usuario, ['id_programa' => $request->id_programa]);
                }
                else{
                    $usuario->tipoUsuario()->attach($request->id_tipo_usuario);
                }
            }
            return response()->json($usuario,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //CONDICIONES Alumno
    public function condAlumno(){
        try{
            $condciones=DB::table('valores')->where('tabla','CONDICION_ALUMNO')->get();
            return response()->json($condciones,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //Prueba Recibir Notas
    public function notas(Request $request)
    {
        try {
            $files = $request->get('files');
            $i = 0;
            foreach ($files as $file) {
                Storage::putFileAs('images', $file, '200' . $i . '.pdf');
                $i = $i + 1;
            }
            return response()->json('Subido', 200);
        } catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
            return response()->json($e, 200);
        }
    }

    //Listar tipos de tutoria del TUTOR
    public function tiposTutoriaTutor(Request $request)
    {
        try {
            $tutor = Usuario::findOrFail($request->idTutor);
            $tipos = $tutor->tipoTutorias()->get();
            return response()->json($tipos,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Listar tipos de tutoria del TUTOR
    public function tutoriaTutor(Request $request)
    {
        try {
            $tutor = Usuario::findOrFail($request->idTutor);
            $tipos = $tutor->tipoTutorias()->where('estado','act')->
            where('id_programa',$request->id_programa)->get();
            return response()->json($tipos,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function alumnoMasivo(Request $request,$id_programa){
        try {
            $const_errores=15;
            $file = $request->file('file');
            if(!($file->extension() == 'csv') && !($file->extension()=='txt')){
                return response()->json(['status'=>'El archivo no tiene formato csv' ],200);
            }
            $handle = fopen($file, "r");
            $header = fgetcsv($handle,1000,";");
            $x=0;
            foreach ($header as $col){
                $col=strtolower($col);
                $header[$x]=$col;
                $x++;
            }
            $obligatorios=['nombre','apellidos','telefono','correo','codigo','condicion'];
            foreach ($obligatorios as $item){
                if(!in_array($item,$header)){
                    return response()->json(['status'=>'No se ha encontrado la cabecera '.$item.' en el archivo'],200);
                }
            }
            $errores=array();
            $condiciones_abreviatura=Valores::where('tabla','CONDICION_ALUMNO')->get('abreviatura')->toArray();
            $condiciones=implode(',',array_column($condiciones_abreviatura,'abreviatura'));
            $i=1;
            $error_repetitivo=0;
            while ($csvLine = fgetcsv($handle, 1000, ";")) {
                $i++;
                if(count($header)==count($csvLine)){
                    $data=array_combine($header, $csvLine);
                } else{
                    array_push($errores, ['linea'=>$i,'codigo'=>$csvLine[0],'error'=>'Tiene menos o mas datos que las cabaceras']);
                    continue;
                }
                //Usuario existente en tu programa como alumno
                //DB::enableQueryLog();
                $usuario = Usuario::where(function ($query) use($data) {
                    $query->where('codigo', $data['codigo']);})
                    ->where('estado','!=','eli')
                    ->whereHas('usuarioXProgramas', function($q) use($id_programa) {
                        $q->where('id_programa','=',$id_programa)->where('id_tipo_usuario','=',5);})
                    ->first();

                //print_r(DB::getQueryLog()[0]['query']);//prueba del query generado por laravel
                //Si exite el usuario en tu programa como alumno
                if ($usuario) {
                    //luego validamos campos
                    $resultado = $this->validarCsv($data, $condiciones, 0);
                    if (count($resultado) != 0) {
                        //si no pasa validacion se agrega al arreglo de errores
                        $llave = array_key_first($resultado);
                        array_push($errores, ['linea' => $i, 'codigo' => $data['codigo'], 'error' => $resultado[$llave]]);
                        $error_repetitivo++;
                        if ($error_repetitivo == $const_errores) {
                            $cant_errores = count($errores);
                            return response()->json(['status' => 'Subida detenida, se ha detectado varios errores consecutivos de validaciones de datos.
                                Por favor revisar los datos en las lineas:' . ($i - $const_errores) . ' - ' . $i . '.', 'cantidad' => $cant_errores,
                                'reporte' => $errores]);
                        }
                        continue;
                    }
                    $error_repetitivo = 0;
                    //si pasa la validacion, no se actualiza campos que esten vacios en el csv
                    if ($data['nombre'] != '') {
                        $usuario->nombre = utf8_encode($data['nombre']);
                    }
                    if ($data['apellidos'] != '') {
                        $usuario->apellidos = utf8_encode($data['apellidos']);
                    }
                    if ($data['correo'] != '') {
                        $usuario->correo = $data['correo'];
                    }
                    if ($data['telefono'] != '') {
                        $usuario->telefono = $data['telefono'];
                    }
                    if ($data['condicion'] != '') {
                        $usuario->condicion_alumno = $data['condicion'];
                    }
                    $usuario->usuario_actualizacion = $request->usuario;
                    //se modifica el usuario
                    $usuario->save();
                    continue;

                }
                //SI NO se hace to-do como insercion
                //Sacamos espacios en blanco
                $data['nombre']=trim($data['nombre']);
                $data['apellidos']=trim($data['apellidos']);
                $usuario = Usuario::where(function ($query) use($data) {
                    $query->where('codigo', $data['codigo'])
                        ->orWhere('correo',$data['correo']);})
                    ->where('estado','!=','eli')->first();
                if($usuario) {
                    if (($usuario->codigo != $data['codigo']) || ($usuario->correo != $data['correo'])){
                        array_push($errores, ['linea' => $i, 'codigo' => $data['codigo'],
                            'error' => 'El codigo y el correo no corresponden al mismo registro en el sistema']);
                        continue;
                    }
                    $alumno=0;$rol=0;
                    $tipo_usuarios=$usuario->usuarioXProgramas;
                    foreach ($tipo_usuarios as $item){
                        if($item->id_tipo_usuario==5){$alumno=1;}
                        if($item->id_programa==$id_programa){$rol=1;}
                    }
                    if($rol){
                        array_push($errores, ['linea' => $i, 'codigo' => $data['codigo'],
                            'error' => 'El usuario ya tiene un tipo de usuario en el programa']);
                        continue;
                    }
                    if($alumno){
                        $resultado=$this->validarCsv($data,$condiciones,1);
                        if(count($resultado)!=0){
                            $llave=array_key_first($resultado);
                            array_push($errores, ['linea'=>$i,'codigo' => $data['codigo'],'error' => $resultado[$llave]]);
                            $error_repetitivo++;
                            if($error_repetitivo==$const_errores) {
                                $cant_errores=count($errores);
                                return response()->json(['status'=>'Subida detenida, se ha detectado varios errores consecutivos de validaciones de datos.Por favor revisar los datos en las lineas:'.($i-$const_errores).' - '.$i.'.',
                                    'cantidad'=>$cant_errores,
                                    'reporte' =>$errores]);
                            }
                            continue;
                        }
                        $error_repetitivo=0;
                        $usuario->tipoUsuario()->updateExistingPivot(5, ['id_programa' => $id_programa]);
                        continue;
                    }
                    else{
                        array_push($errores, ['linea' => $i, 'codigo' => $data['codigo'],
                            'error' => 'El usuario existe, pero no es alumno en el sistema']);
                        continue;
                    }
                }
                //validamos con campos requeridos
                $resultado=$this->validarCsv($data,$condiciones,1);
                if(count($resultado)!=0){
                    $llave=array_key_first($resultado);
                    array_push($errores, ['linea'=>$i,'codigo' => $data['codigo'],'error' => $resultado[$llave]]);
                    $error_repetitivo++;
                    if($error_repetitivo==$const_errores) {
                        $cant_errores=count($errores);
                        return response()->json(['status'=>'Subida detenida, se ha detectado varios errores consecutivos de validaciones de datos.Por favor revisar los datos en las lineas:'.($i-$const_errores).' - '.$i.'.',
                            'cantidad'=>$cant_errores,
                            'reporte' =>$errores]);
                    }
                    continue;
                }
                $error_repetitivo=0;
                $str = Str::random(12);
                $pass = password_hash($str, PASSWORD_DEFAULT);
                $usuario = new Usuario();
                $usuario->codigo = $data['codigo'];
                $usuario->correo = $data['correo'];
                $usuario->nombre = utf8_encode($data['nombre']);
                $usuario->apellidos = utf8_encode($data['apellidos']);
                $usuario->password = $pass;
                $usuario->usuario_creacion = $request->usuario;
                $usuario->usuario_actualizacion = $request->usuario;
                if ($data['condicion'] != '') {
                    $usuario->condicion_alumno = $data['condicion'];
                } else {
                    $usuario->condicion_alumno = 'pri';
                }
                if ($data['telefono'] != '') {
                    $usuario->telefono = $data['telefono'];
                }
                $usuario->save();
                $usuario->tipoUsuario()->attach(5, ['id_programa' => $id_programa]);
                //Email
                Mail::to($usuario->correo)->send(new CorreoUsuario($usuario, $str));
            }

            $cant_errores=count($errores);
            if($cant_errores>0){
                return response()->json(['status'=>'Se han encontrado errores',
                    'lineas_totales'=>$i-1,
                    'cantidad_errores'=>$cant_errores,
                    'cantidad_procesados'=>$i-$cant_errores-1,
                    'reporte' =>$errores]);
            }
            else{
                return response()->json(['status'=>'Subida terminada','lineas_totales'=>$i-1,
                    'cantidad_procesados'=>$i-$cant_errores-1,],200);
            }
        }catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function validarCsv($datos,$condiones,$int){
        if($int==1) {//validar insercion
            $v = \Validator::make($datos, [
                'codigo' => ['required', 'max:8', 'regex:/^[A-Z0-9]+$/'],//codigo
                'correo' => ['required', 'email', 'regex:/(.*)(@pucp.edu.pe$|@pucp.pe$)/'],//correo
                //$reg = '/^[a-z"]{1, '. $number .'}$/'; usando variable para el dominio en un futuro
                'nombre' => ['required', 'max:100', 'regex:/^[\pL\s\-\ñ\Ñ]+$/'],//nombres
                'apellidos' => ['required', 'max:100', 'regex:/^[\pL\s\-\ñ\Ñ]+$/'],//apellidos
                'telefono' => 'min:7|max:20|regex:/^[0-9-]*$/',//telefono
                'condicion' => 'in:' . $condiones,
            ]);
        }
        elseif($int==0){//validar modificacion
            $v = \Validator::make($datos, [
                'codigo' => ['required', 'max:8', 'regex:/^[A-Z0-9]+$/'],//codigo
                'correo' => ['unique:usuario','email', 'regex:/(.*)(@pucp.edu.pe$|@pucp.pe$)/'],//correo
                //$reg = '/^[a-z"]{1, '. $number .'}$/'; usando variable para el dominio en un futuro
                'nombre' => ['max:100', 'regex:/^[\pL\s\-\ñ\Ñ]*$/'],//nombres
                'apellidos' => ['max:100', 'regex:/^[\pL\s\-\ñ\Ñ]*$/'],//apellidos
                'telefono' => 'min:7|max:20|regex:/^[0-9-]*$/',//telefono
                'condicion' => 'in:' . $condiones,
            ]);
        }
        if ($v->fails()){return $v->errors()->all();}
        else{return [];}
    }
    //Combinado en la funcion insercion alumno masivo
    public function modCondAlumnoMasivo(Request $request,$id_programa){
        try {
            $file = $request->file('file');
            if(!($file->extension() == 'csv') && !($file->extension()=='txt')){
                return response()->json(['status'=>'El archivo no tiene formato csv' ],200);
            }
            $handle = fopen($file, "r");
            $header = fgetcsv($handle,1000,";");
            $x=0;
            foreach ($header as $col){
                $col=strtolower($col);
                $header[$x]=$col;
                $x++;
            }
            $obligatorios=['codigo','condicion'];
            foreach ($obligatorios as $item){
                if(!in_array($item,$header)){
                    return response()->json(['status'=>'No se ha encontrado la cabecera '.$item.' en el archivo'],200);
                }
            }
            $errores=array();
            $condiciones_abreviatura=Valores::where('tabla','CONDICION_ALUMNO')->
            get('abreviatura')->toArray();
            $condiciones=implode(',',array_column($condiciones_abreviatura,'abreviatura'));
            $i=1;
            $error_repetitivo=0;
            while ($csvLine = fgetcsv($handle, 1000, ";")) {
                $i++;
                $data=array_combine($header, $csvLine);
                if ($data==='False'){
                    array_push($errores, ['status'=>'El registro '.$i.' tiene menos o mas datos que las cabaceras']);
                    continue;
                }
                $resultado=$this->validarCsv($data,$condiciones,0);
                if(count($resultado)!=0){
                    $llave=array_key_first($resultado);
                    array_push($errores, ['linea'=>'Error en la linea '.$i,'codigo' => $data['codigo'],
                        'condicion' => $data['condicion'], 'error' => $resultado[$llave]]);
                    $error_repetitivo++;
                    if($error_repetitivo==50) {
                        $cant_errores=count($errores);
                        return response()->json(['status'=>'Subida detenida, se ha detectado varios errores consecutivos de validaciones de datos.Por favor revisar los datos en las lineas:'.($i-50).'-'.$i.'.',
                            'cantidad'=>$cant_errores,
                            'reporte' =>$errores]);
                    }
                    continue;
                }
                $usuario = Usuario::where('codigo', $data['codigo'])->where('estado','!=','eli')
                    ->whereHas('usuarioxprograma', function($q) use($id_programa) {
                        $q->where('id_programa','=',$id_programa)->where('id_tipo_usuario','=',5);})
                    ->first();
                $error_repetitivo=0;
                if(!$usuario) {
                    array_push($errores, ['linea'=>'Error en la linea '.$i,'codigo' => $data['codigo'],
                        'condicion' => $data['condicion'], 'error' => 'No se encuentra el usuario en el programa
                        como alumno']);
                }else {
                    $usuario->condicion_alummno=$data['condicion'];
                    $usuario->usuario_actualizacion=$request->usuario_actualizacion;
                    $usuario->save();
                }
            }
            $cant_errores=count($errores);
            if($cant_errores>0){
                return response()->json(['status'=>'Se han encontrado errores',
                    'cantidad'=>$cant_errores,
                    'reporte' =>$errores]);
            }
            else{
                return response()->json(['status'=>'Subida terminada'],200);
            }
        }catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    //Reporte de alumnos de bajo rendimiento que sisitieron a citas
    //puede filtrarse por programas y puede filtrarse por tutor
    public function datosBajoRendimiento(Request $request){
        try {
            $id_tutor=$request->id_tutor;
            $id_programa=$request->id_programa;
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin;
            //DB::enableQueryLog();
            /*
            $subquery = DB::table('cita_x_usuario AS A')
                ->select('historico_condicion_alumno.condicion_alumno AS condicion',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('sum(case when "A".asistencia=\'asi\' then 1 else 0 end) AS asistio'),
                    DB::raw('sum(case when "A".asistencia=\'noa\' then 1 else 0 end) AS no_asistio'),
                    DB::raw('CASE WHEN sum(case when "A".asistencia=\'noa\' then 1 else 0 end)>sum(case when "A".asistencia=\'asi\' then 1 else 0 end) THEN \'menos 50%\' ELSE \'mas 50%\' END as grupo'))
                    //DB::raw('CASE WHEN sum(case when "A"."asistencia"=\'noa\' then 1 else 0 end)>0 THEN \'no asistio\' ELSE \'asistio\' END as grupo'))
                ->join('cita', 'cita.id_cita', '=', 'A.id_cita')
                ->join('disponibilidad','disponibilidad.id_disponibilidad','=','cita.id_disponibilidad')
                //->join('usuario','usuario.id_usuario','=','A.id_usuario');
                ->join('historico_condicion_alumno', function ($join) {
                    $join->on('historico_condicion_alumno.id_usuario', '=', 'A.id_usuario');
                    //$join->on(DB::raw('(disponibilidad.fecha between historico_condicion_alumno.fecha_creacion
                    //and(case when h.fecha_actualizacion is null then now()else h.fecha_actualizacion end))'), DB::raw(''), DB::raw(''));
                    $join->on('disponibilidad.fecha','between',DB::raw('historico_condicion_alumno.fecha_creacion
                    and(case when historico_condicion_alumno.fecha_actualizacion is null then now() else historico_condicion_alumno.fecha_actualizacion end)'));
                });
                if(count($id_tutor)>0){
                    $subquery->whereIn('disponibilidad.id_usuario',$id_tutor);
                };
                $subquery->whereIn('disponibilidad.id_programa', $id_programa)
                ->whereBetween('disponibilidad.fecha',[$ini,$fin])
                ->whereIn('A.asistencia',['noa','asi'])
                ->groupBy('A.id_usuario','historico_condicion_alumno.condicion_alumno')
                ->orderBy('A.id_usuario');
                //->toSql();//solo para probar el subquery

            $prueba = DB::table(DB::raw("({$subquery->toSql()}) AS sub"))
                ->select('condicion','grupo',
                    DB::raw('count(*) as total_alumnos,sum(total) as total_citas,
                    sum(asistio)as total_citas_asistidas,sum(no_asistio)as total_citas_no_asisitidas'))
                ->mergeBindings( $subquery )
                ->groupBy('condicion')
                ->groupBy('grupo')
                ->orderBy('condicion')->get();
            //print_r(DB::getQueryLog()[0]['query']);//prueba del query generado por laravel
            */
            $subquery = DB::table('cita_x_usuario AS A')
                ->select('tipo_tutoria.nombre AS nombre',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('sum(case when "A".asistencia=\'asi\' then 1 else 0 end) AS asistio'),
                    DB::raw('sum(case when "A".asistencia=\'noa\' then 1 else 0 end) AS no_asistio'),
                    DB::raw('CASE WHEN sum(case when "A".asistencia=\'noa\' then 1 else 0 end)>sum(case when "A".asistencia=\'asi\' then 1 else 0 end) THEN \'menos 50%\' ELSE \'mas 50%\' END as grupo'))
                ->join('cita', 'cita.id_cita', '=', 'A.id_cita')
                ->join('disponibilidad','disponibilidad.id_disponibilidad','=','cita.id_disponibilidad')
                ->join('tipo_tutoria','tipo_tutoria.id_tipo_tutoria','=','cita.id_tipo_tutoria');
            if(count($id_tutor)>0){
                $subquery->whereIn('disponibilidad.id_usuario',$id_tutor);
            };
            $subquery->whereIn('disponibilidad.id_programa', $id_programa)
                ->whereBetween('disponibilidad.fecha',[$ini,$fin])
                ->whereIn('A.asistencia',['noa','asi'])
                ->where('tipo_tutoria.bajo_rendimiento','=',1)
                ->groupBy('A.id_usuario','nombre')
                ->orderBy('A.id_usuario');

            $query = DB::table(DB::raw("({$subquery->toSql()}) AS sub"))
                ->select('nombre','grupo',
                    DB::raw('count(*) as total_alumnos,sum(total) as total_citas,
                   sum(asistio)as total_citas_asistidas,sum(no_asistio)as total_citas_no_asisitidas'))
                ->mergeBindings( $subquery )
                ->groupBy('nombre')
                ->groupBy('grupo')
                ->orderBy('nombre')->get();

           //print_r(DB::getQueryLog()[0]['query']);//prueba del query generado por laravel
            $tipo_tutoria_bajo_rendimiento=TipoTutoria::where([['id_programa',$id_programa],
                ['estado','act'],['bajo_rendimiento',1]])->orderBy('nombre')->get();

            $resultado=array();

            foreach ($tipo_tutoria_bajo_rendimiento as $bajo){
                array_push($resultado,array("condicion"=>$bajo['nombre'],"grupo"=>'mas 50%',"total_alumnos"=>0,
                    "total_citas"=>"0","total_citas_asistidas"=>"0","total_citas_no_asisitidas"=>"0"));
                array_push($resultado,array("condicion"=>$bajo['nombre'],"grupo"=>'menos 50%',"total_alumnos"=>0,
                    "total_citas"=>"0","total_citas_asistidas"=>"0","total_citas_no_asisitidas"=>"0"));
            }
            foreach ($query as $item) {
                foreach($resultado as &$res) {
                    if (($item->nombre == $res['condicion']) && ($item->grupo==$res['grupo'])) {
                        $res['total_alumnos']=$item->total_alumnos;
                        $res['total_citas'] = $item->total_citas;
                        $res['total_citas_asistidas'] = $item->total_citas_asistidas;
                        $res['total_citas_no_asisitidas'] = $item->total_citas_no_asisitidas;
                        break;
                    }
                }
            }
            return response()->json($resultado,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function datosAlumnosPlan(Request $request){
        try {
            //DB::enableQueryLog();//habilitamos el registro de conusltas para imprimir el query si se requiere
            $id_tutor=$request->id_tutor;
            $id_programa=$request->id_programa;
            $ini=$request->fecha_ini;
            $fin=$request->fecha_fin;
            $subquery_1= DB::table('compromiso')
                ->select('id_alumno','plan_accion.id_plan_accion',
                    DB::raw('CASE WHEN (compromiso.estado!=\'hec\' and
                    compromiso.estado!=\'eli\') THEN \'no terminado\' ELSE \'terminado\' END as estado'))
                ->join('plan_accion','compromiso.id_plan_accion','=','plan_accion.id_plan_accion')
                ->whereBetween('plan_accion.fecha_creacion',[$ini,$fin]);
            if(count($id_tutor)>0){
                $subquery_1->whereIn('plan_accion.id_tutor',$id_tutor);
            };
            $subquery_1->whereIn('plan_accion.id_programa',$id_programa)
                ->groupBy('id_alumno','plan_accion.id_plan_accion','compromiso.estado')
                ->orderBy('id_alumno');

            $subquery = DB::table(DB::raw("({$subquery_1->toSql()}) AS sub"))
                ->select('id_alumno',
                    DB::raw('CASE WHEN sum(case when estado=\'terminado\' then 1 else 0 end)>=sum(case when estado=\'no terminado\'
                    then 1 else 0 end) THEN \'mas 50%\' ELSE \'menos 50%\' END as grupo'))
                ->mergeBindings( $subquery_1)
                ->groupBy('id_alumno');

            $query = DB::table(DB::raw("({$subquery->toSql()}) AS l"))
                ->select('grupo',DB::raw('count(grupo) as total_alumnos'))
                ->mergeBindings( $subquery)
                ->groupBy('grupo')->get();
            /*$planes=PlanAccion::select('usuario.condicion_alumno',DB::raw('count(DISTINCT id_alumno) as total_alumnos'))
                ->join('usuario', 'plan_accion.id_alumno', '=', 'usuario.id_usuario')
                ->join('usuario_x_programa','usuario_x_programa.id_usuario','=','usuario.id_usuario')
                ->whereIn('usuario_x_programa.id_programa',$id_programa)
                ->where('usuario_x_programa.id_tipo_usuario','=',5)
                ->whereDoesntHave('compromisos',function ($query){
                    $query->where('estado','!=','hec')->where('estado','!=','eli');})
                ->groupBy('usuario.condicion_alumno')
                ->whereBetween('plan_accion.fecha_creacion',[$ini,$fin])
                ->get();
             */
            //$query=DB::getQueryLog();//asignacion a una variable para solo sacar el query
            //print_r($query[0]['query']);//impresion de solo el query
            $resultado=array(['grupo'=>'mas 50%','total_alumno'=>0],['grupo'=>'menos 50%','total_alumno'=>0]);
            foreach ($query as $item){
                foreach ($resultado as &$res){
                    if($item->grupo==$res['grupo']){
                        $res['total_alumno']=$item->total_alumnos;
                    }
                }
            }
            return response()->json($resultado,200);
        } catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    public function testData(Request $request){
        $user =
            factory(Usuario::class,50)
            ->create()
            ->each(function ($user) {
                $user->tipoUsuario()->attach(5, ['id_programa' => 5]);
            });

        return response()->json($user,200);
    }

    public function recuperarContrasenaVal(Request $request){
        try {
            $usuario = Usuario::where('token_recuperacion',$request->token)->first();
            if($usuario){
                return response()->json($usuario,200);
            }
            else{
                return response()->json(null,200);
            }
        }
        catch (Exception $e){
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

}

/*
public function alumnoMasivo(Request $request,$id_programa){
    try {
        $file = $request->file('file');
        if(!($file->extension() == 'csv') && !($file->extension()=='txt')){
            return response()->json(['status'=>'El archivo no tiene formato csv' ],200);
        }
        $handle = fopen($file, "r");
        $header = fgetcsv($handle,1000,";");
        $x=0;
        foreach ($header as $col){
            $col=strtolower($col);
            $header[$x]=$col;
            $x++;
        }
        $obligatorios=['nombre','apellidos','telefono','correo','codigo','condicion'];
        foreach ($obligatorios as $item){
            if(!in_array($item,$header)){
                return response()->json(['status'=>'No se ha encontrado la cabecera '.$item.' en el archivo'],200);
            }
        }
        $errores=array();
        $condiciones_abreviatura=Valores::where('tabla','CONDICION_ALUMNO')->
        get('abreviatura')->toArray();
        $condiciones=implode(',',array_column($condiciones_abreviatura,'abreviatura'));
        $i=1;
        $error_repetitivo=0;
        while ($csvLine = fgetcsv($handle, 1000, ";")) {
            $i++;
            $data=array_combine($header, $csvLine);
            if ($data==='False'){
                array_push($errores, ['status'=>'El registro '.$i.' tiene menos o mas datos que las cabaceras']);
                continue;
            }
            $data['nombre']=trim($data['nombre']);
            $data['apellidos']=trim($data['apellidos']);
            $resultado=$this->validarCsv($data,$condiciones,1);
            if(count($resultado)!=0){
                $llave=array_key_first($resultado);
                array_push($errores, ['linea'=>'Error en la linea '.$i,'codigo' => $data['codigo'], 'nombre' => $data['nombre'],
                    'apellido' => $data['apellidos'], 'correo' => $data['correo'],'condicion' => $data['condicion'],
                    'error' => $resultado[$llave]]);
                $error_repetitivo++;
                if($error_repetitivo==50) {
                    $cant_errores=count($errores);
                    return response()->json(['status'=>'Subida detenida, se ha detectado varios errores consecutivos de validaciones de datos.Por favor revisar los datos en las lineas:'.($i-50).'-'.$i.'.',
                        'cantidad'=>$cant_errores,
                        'reporte' =>$errores]);
                }
                continue;
            }
            $usuario = Usuario::where('codigo', $data['codigo'])->orWhere('correo',$data['correo'])
                ->where('estado','!=','eli')->first();//->orwhere('correo', $csvLine[1]);
            $error_repetitivo=0;
            if($usuario) {
                if(($usuario->codigo != $data['codigo'])||($usuario->correo != $data['correo'])){
                    array_push($errores, ['linea'=>'Error en la linea '.$i ,'codigo' => $data['codigo'], 'nombre' => $data['nombre'],
                        'apellido' => $data['apellidos'], 'correo' => $data['correo'],'condicion' => $data['condicion'],
                        'error' =>'El codigo y el correo no corresponden al mismo registro en el sistema']);
                    continue;
                }
                $usuario->tipoUsuario()->updateExistingPivot(5, ['id_programa' => $id_programa]);
            } else {
                $str = Str::random(12);
                $pass = password_hash($str, PASSWORD_DEFAULT);
                $usuario=Usuario::create([
                    'codigo'=> $data['codigo'],
                    'correo'=> $data['correo'],
                    'nombre' => $data['nombre'],
                    'apellidos' =>$data['apellidos'],
                    'password'=>$pass,
                    'usuario_creacion'=>$request->usuario_creacion,
                    'usuario_actualizacion'=>$request->usuario_creacion
                ]);
                if($data['condicion']!='') {
                    $usuario->condicion_alumno=$data['condicion'];
                }
                if($data['telefono']!=''){
                    $usuario->telefono=$data['telefono'];
                }
                $usuario->save();
                $usuario->tipoUsuario()->attach(5, ['id_programa' => $id_programa]);
                //Email
                Mail::to($usuario->correo)->send(new CorreoUsuario($usuario,$str));
            }
        }
        $cant_errores=count($errores);
        if($cant_errores>0){
            return response()->json(['status'=>'Se han encontrado errores',
                'cantidad'=>$cant_errores,
                'reporte' =>$errores]);
        }
        else{
            return response()->json(['status'=>'Subida terminada'],200);
        }
    }catch (Exception $e) {
        echo 'Excepción capturada: ', $e->getMessage(), "\n";
    }
}*/

/*
  public function modCondAlumnoMasivo(Request $request,$id_programa){
        try {
            $file = $request->file('file');
            if(!($file->extension() == 'csv') && !($file->extension()=='txt')){
                return response()->json(['status'=>'El archivo no tiene formato csv' ],200);
            }
            $handle = fopen($file, "r");
            $header = fgetcsv($handle,1000,";");
            $x=0;
            foreach ($header as $col){
                $col=strtolower($col);
                $header[$x]=$col;
                $x++;
            }
            $obligatorios=['codigo','condicion'];
            foreach ($obligatorios as $item){
                if(!in_array($item,$header)){
                    return response()->json(['status'=>'No se ha encontrado la cabecera '.$item.' en el archivo'],200);
                }
            }
            $errores=array();
            $condiciones_abreviatura=Valores::where('tabla','CONDICION_ALUMNO')->
            get('abreviatura')->toArray();
            $condiciones=implode(',',array_column($condiciones_abreviatura,'abreviatura'));
            $i=1;
            $error_repetitivo=0;
            while ($csvLine = fgetcsv($handle, 1000, ";")) {
                $i++;
                $data=array_combine($header, $csvLine);
                if ($data==='False'){
                    array_push($errores, ['status'=>'El registro '.$i.' tiene menos o mas datos que las cabaceras']);
                    continue;
                }
                $resultado=$this->validarCsv($data,$condiciones,0);
                if(count($resultado)!=0){
                    $llave=array_key_first($resultado);
                    array_push($errores, ['linea'=>'Error en la linea '.$i,'codigo' => $data['codigo'],
                        'condicion' => $data['condicion'], 'error' => $resultado[$llave]]);
                    $error_repetitivo++;
                    if($error_repetitivo==50) {
                        $cant_errores=count($errores);
                        return response()->json(['status'=>'Subida detenida, se ha detectado varios errores consecutivos de validaciones de datos.Por favor revisar los datos en las lineas:'.($i-50).'-'.$i.'.',
                            'cantidad'=>$cant_errores,
                            'reporte' =>$errores]);
                    }
                    continue;
                }
                $usuario = Usuario::where('codigo', $data['codigo'])->where('estado','!=','eli')
                    ->whereHas('usuarioxprograma', function($q) use($id_programa) {
                        $q->where('id_programa','=',$id_programa)->where('id_tipo_usuario','=',5);})
                    ->first();
                $error_repetitivo=0;
                if(!$usuario) {
                    array_push($errores, ['linea'=>'Error en la linea '.$i,'codigo' => $data['codigo'],
                        'condicion' => $data['condicion'], 'error' => 'No se encuentra el usuario en el programa
                        como alumno']);
                }else {
                    $usuario->condicion_alummno=$data['condicion'];
                    $usuario->usuario_actualizacion=$request->usuario_actualizacion;
                    $usuario->save();
                }
            }
            $cant_errores=count($errores);
            if($cant_errores>0){
                return response()->json(['status'=>'Se han encontrado errores',
                    'cantidad'=>$cant_errores,
                    'reporte' =>$errores]);
            }
            else{
                return response()->json(['status'=>'Subida terminada'],200);
            }
        }catch (Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }*/
