<?php

namespace App\Http\Controllers;

use App\Utils\Respuesta;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends Controller
{
    public function listado()
    {
        return view('proveedor.listado');
    }

    public function ajaxListado(Request $request)
    {
        if ($request->ajax()) {
            $proveedores = Proveedor::all();
            $valores = [
                'listado' => view('proveedor.ajaxListado')->with(compact('proveedores'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarProveedor(Request $request)
    {
        if ($request->ajax()) {

            $request->validate([
                'nombre' => 'required',
                /* 'direccion' => 'required',
                'celular' => 'required',
                'razon_social' => 'required',
                'nit' => 'required', */
            ]);

            $id = $request->input('id');

            $nombre       = $request->input('nombre');
            $direccion    = $request->input('direccion');
            $celular      = $request->input('celular');
            $razon_social = $request->input('razon_social');
            $nit          = $request->input('nit');
            $usuario     = Auth::user();

            if ($id == 0) {
                $proveedor                     = new Proveedor();
                $proveedor->usuario_creador_id = $usuario->id;
            } else {
                $proveedor = Proveedor::find($id);
                $proveedor->usuario_modificador_id = $usuario->id;
            }

            $proveedor->nombre    = $nombre;
            $proveedor->direccion = $direccion;
            $proveedor->celular   = $celular;
            $proveedor->nit       = $nit;
            $proveedor->razon_social = $razon_social;
            $proveedor->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function eliminarProveedor(Request $request)
    {
        if ($request->ajax()) {

            $id = $request->input('id');
            $usuario = Auth::user();

            $proveedor = Proveedor::find($id);
            $proveedor->usuario_eliminador_id = $usuario->id;
            $proveedor->save();

            Proveedor::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }
}
