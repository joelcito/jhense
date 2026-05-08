<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrupoController extends Controller
{
    public function listado(){
        return view('grupo.listado');
    }

    public function guardarGrupo(Request $request){

        if ($request->ajax()) {

            $request->validate([
                'nombre' => 'required',
                'descripcion' => 'required',
            ]);

            $id          = $request->input('id');
            $nombre      = $request->input('nombre');
            $descripcion = $request->input('descripcion');
            $usuario     = Auth::user();

            if ($id == 0) {
                $grupo                     = new Grupo();
                $grupo->usuario_creador_id = $usuario->id;
            } else {
                $grupo = Grupo::find($id);
                $grupo->usuario_modificador_id = $usuario->id;
            }

            $grupo->nombre      = $nombre;
            $grupo->descripcion = $descripcion;
            $grupo->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function ajaxListado(Request $request)
    {
        if ($request->ajax()) {
            $grupos = Grupo::all();
            $valores = [
                'listado' => view('grupo.ajaxListado')->with(compact('grupos'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function eliminarGrupo(Request $request)
    {
        if ($request->ajax()) {

            $id = $request->input('id');
            $usuario = Auth::user();

            $grupo = Grupo::find($id);
            $grupo->usuario_eliminador_id = $usuario->id;
            $grupo->save();

            Grupo::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }
}
