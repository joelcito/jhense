<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarcaController extends Controller
{
    public function listado(){
        return view('marca.listado');
    }

    public function ajaxListado(Request $request){
        if ($request->ajax()) {
            $marcas = Marca::all();
            $valores = [
                'listado' => view('marca.ajaxListado')->with(compact('marcas'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarMarca(Request $request){

        if ($request->ajax()) {

            $request->validate([
                'nombre' => 'required',
            ]);

            $id      = $request->input('id');
            $nombre  = $request->input('nombre');
            $usuario = Auth::user();

            if ($id == 0) {
                $marca                     = new Marca();
                $marca->usuario_creador_id = $usuario->id;
            } else {
                $marca = Marca::find($id);
                $marca->usuario_modificador_id = $usuario->id;
            }

            $marca->nombre      = $nombre;
            $marca->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function eliminarMarca(Request $request)
    {
        if ($request->ajax()) {

            $id = $request->input('id');
            $usuario = Auth::user();

            $marca = Marca::find($id);
            $marca->usuario_eliminador_id = $usuario->id;
            $marca->save();

            Marca::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }
}
