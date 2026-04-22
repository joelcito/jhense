<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicioController extends Controller
{
    public function listado(){
        return view('servicio.listado');
    }

    public function ajaxListado(Request $request){
        if($request->ajax()){
            $servicios = Producto::where('control_stock', false)->get();
            $valores = [
                'listado' => view('servicio.ajaxListado')->with(compact('servicios'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        }else{
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarServicio(Request $request){
        if($request->ajax()){

            $request->validate([
                'nombre' => 'required',
                'precio_venta' => 'required',
            ]);

            $id = $request->input('id');

            $nombre  = $request->input('nombre');
            $costo  = $request->input('precio_venta');
            $usuario = Auth::user();

            if( $id == 0 ){
                $servicio                     = new Producto();
                $servicio->usuario_creador_id = $usuario->id;
            }else{
                $servicio = Producto::find($id);
                $servicio->usuario_modificador_id = $usuario->id;
            }

            $servicio->nombre           = $nombre;
            $servicio->precio_venta     = $costo;
            $servicio->control_stock    = false;
            $servicio->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");

        }else{
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function eliminarServicio(Request $request){
        if($request->ajax()){

            $id = $request->input('id');
            $usuario = Auth::user();

            $servicio = Producto::find($id);
            $servicio->usuario_eliminador_id = $usuario->id;
            $servicio->save();

            Producto::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");

        }else{
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }
}
