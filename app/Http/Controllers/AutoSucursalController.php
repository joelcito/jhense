<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Cliente;
use App\Models\Marca;
use App\Models\Sucursal;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoSucursalController extends Controller
{
    public function listado($sucursal_id){
        $sucursal = Sucursal::findOrFail($sucursal_id);
        $clientes = Cliente::where('sucursal_id', $sucursal_id)->get();
        $marcas = Marca::all();

        return view('autoSucursal.listado')->with(compact('clientes', 'marcas', 'sucursal'));
    }

    public function ajaxListado(Request $request){
        if ($request->ajax()) {
            $sucursal_id = $request->input('sucursal_id');
            $autos = Auto::with('cliente', 'marca')
                ->whereHas('cliente', function ($q) use ($sucursal_id){
                    $q->where('sucursal_id', $sucursal_id);
                })
                ->get();
            $valores = [
                'listado' => view('autoSucursal.ajaxListado')->with(compact('autos'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarAuto(Request $request){

        if ($request->ajax()) {

            $request->validate([
                'cliente_id' => 'required',
                'marca_id' => 'required',
                'placa' => 'required',
                'modelo' => 'required',
                'motor' => 'required',
                //'anio_fab' => 'required',
                //'vin' => 'required',
            ]);

            $id      = $request->input('id');
            $cliente_id  = $request->input('cliente_id');
            $marca_id  = $request->input('marca_id');
            $placa  = $request->input('placa');
            $modelo  = $request->input('modelo');
            $motor  = $request->input('motor');
            $anio_fab  = $request->input('anio_fab');
            $vin  = $request->input('vin');
            $usuario = Auth::user();

            if ($id == 0) {
                $auto                     = new Auto();
                $auto->usuario_creador_id = $usuario->id;
            } else {
                $auto = Auto::find($id);
                $auto->usuario_modificador_id = $usuario->id;
            }

            $auto->cliente_id = $cliente_id;
            $auto->marca_id = $marca_id;
            $auto->placa = $placa;
            $auto->modelo = $modelo;
            $auto->motor = $motor;
            $auto->anio_fab = $anio_fab;
            $auto->vin = $vin;
            $auto->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function eliminarAuto(Request $request)
    {
        if ($request->ajax()) {

            $id = $request->input('id');
            $usuario = Auth::user();

            $auto = Auto::find($id);
            $auto->usuario_eliminador_id = $usuario->id;
            $auto->save();

            Auto::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }
}
