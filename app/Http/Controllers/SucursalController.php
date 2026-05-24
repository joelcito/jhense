<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SucursalController extends Controller
{

    public function listado()
    {

        return view('sucursal.listado');
    }

    public function ajaxListado(Request $request)
    {
        if ($request->ajax()) {
            $sucursales = Sucursal::all();
            $valores = [
                'listado' => view('sucursal.ajaxListado')->with(compact('sucursales'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarSucursal(Request $request)
    {
        if ($request->ajax()) {

            $request->validate([
                'nombre' => 'required|string',
                'codigo_sucursal' => 'required|integer|min:0',
                'direccion' => 'required|string',
            ]);

            try {
                $nombre          = $request->input('nombre');
                $codigo_sucursal = $request->input('codigo_sucursal');
                $direccion       = $request->input('direccion');
                $sucursal_id     = $request->input('id');
                $usuario         = Auth::user();

                if ($sucursal_id == 0) {
                    $sucursal                     = new Sucursal();
                    $sucursal->usuario_creador_id = $usuario->id;
                } else {
                    $sucursal                         = Sucursal::find($sucursal_id);
                    $sucursal->usuario_modificador_id = $usuario->id;
                }

                $sucursal->nombre          = $nombre;
                $sucursal->codigo_sucursal = $codigo_sucursal;
                $sucursal->direccion       = $direccion;
                if ($request->hasFile('logo')) {
                    $logo = $request->file('logo');
                    $nombreArchivo = time() . '_' . Str::uuid() . '.' . $logo->getClientOriginalExtension();
                    $ruta = $logo->storeAs('sucursales', $nombreArchivo, 'public');
                    $sucursal->logo = $ruta;
                }
                $sucursal->save();

                $punto = PuntoVenta::where('sucursal_id', $sucursal->id)->first();
                if (!$punto) {
                    $nuevo = new PuntoVenta();
                    $nuevo->usuario_creador_id = $usuario->id;
                    $nuevo->sucursal_id = $sucursal->id;
                    $nuevo->codigo = '123';
                    $nuevo->nombre = "Primer punto de venta";
                    $nuevo->tipo = "Primero";
                    $nuevo->codigo_ambiente = "Ambiente";
                    $nuevo->save();
                }

                $data = Respuesta::success(null, "Se proceso con exito");
            } catch (\Exception $e) {
                $data = Respuesta::error(null, "Error un error :" . $e->getMessage());
            }
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function eliminarSucursal(Request $request)
    {
        if ($request->ajax()) {

            $id = $request->input('id');
            $usuario = Auth::user();

            $sucursal = Sucursal::find($id);
            $sucursal->usuario_eliminador_id = $usuario->id;
            $sucursal->save();

            Sucursal::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }
}
