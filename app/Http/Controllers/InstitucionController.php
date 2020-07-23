<?php

namespace App\Http\Controllers;
use Exception;
use App\Institucion;
use App\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class InstitucionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $datos['institucion']=Institucion::where('estado','act')->get();
            return response()->json($datos['institucion']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            $institucion = new Institucion();
            $institucion->nombre = $request->nombre;
            $institucion->logo=$request->logo;
            $institucion->direccion = $request->direccion;
            $institucion->telefono = $request->telefono;
            $institucion->siglas=$request->siglas;
            $institucion->estado='act';
            $institucion->usuario_creacion=$request->usuario_creacion;
            $institucion->usuario_actualizacion=$request->usuario_actualizacion;
            $institucion->save();
            return response()->json($institucion);
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
            $dato['institucion']=Institucion::find($id);
            return response()->json($dato['institucion']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
            $institucion = Institucion::find($id);
            $institucion->update($request->all());
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
    public function destroy($id)
    {
        try{
            $institucion = Institucion::find($id);
            $institucion->estado='eli';
            $institucion->save();
            return response()->json(['status'=> 'success'],204);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    //listar por nombre
    public function listNombre(Request $request){
        try{
            //$inserts=$request->all();
            $nombre="";
            $datos['institucion']=Institucion::where('nombre','like', '%' . $nombre . '%')->where('estado','act')->get();//arreflar, eso solo funciona para id
            return response()->json($datos['institucion']);
        }catch(Exception $e) {
            echo 'Excepción capturada: ', $e->getMessage(), "\n";
        }
    }
    public function subirLogo(Request $request){
        if($request->get('image'))
        {
            $image = $request->get('image');
            $name = 'logo'.'.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
            //Image::make($request->get('image'))->save(public_path('../storage/app/public/images/').$name);
            Storage::putFileAs('images',$image,$name);
            return response()->json(['success' => 'You have successfully uploaded an image',
            'path' => 'https://assisstanceproyecto20201.vizcochitos.cloudns.cl/images/images/'.$name,
            'name' => $name], 200);
        }
    }
}
