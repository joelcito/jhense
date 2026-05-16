<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultaController extends Controller
{
    public function listado()
    {
        return view('consulta.listado');
    }

    public function guardarConsulta(Request $request)
    {

        if ($request->ajax()) {

            $request->validate([
                'componente' => 'required',
                'sintoma' => 'required',
                'tipo_falla' => 'required',
                'causa_probable' => 'required',
                'estado_actual' => 'required',
                'recomendacion' => 'required',
                'riesgo_asociado' => 'required',
                'justificacion' => 'required',
            ]);

            $id          = $request->input('id');
            $componente      = $request->input('componente');
            $sintoma      = $request->input('sintoma');
            $tipo_falla      = $request->input('tipo_falla');
            $causa_probable      = $request->input('causa_probable');
            $estado_actual      = $request->input('estado_actual');
            $recomendacion      = $request->input('recomendacion');
            $riesgo_asociado      = $request->input('riesgo_asociado');
            $justificacion      = $request->input('justificacion');
            $usuario     = Auth::user();

            if ($id == 0) {
                $consulta                     = new Consulta();
                $consulta->usuario_creador_id = $usuario->id;
            } else {
                $consulta = Consulta::find($id);
                $consulta->usuario_modificador_id = $usuario->id;
            }

            $consulta->componente      = $componente;
            $consulta->sintoma = $sintoma;
            $consulta->tipo_falla = $tipo_falla;
            $consulta->causa_probable = $causa_probable;
            $consulta->estado_actual = $estado_actual;
            $consulta->recomendacion = $recomendacion;
            $consulta->riesgo_asociado = $riesgo_asociado;
            $consulta->justificacion = $justificacion;
            $consulta->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function ajaxListado(Request $request)
    {
        if ($request->ajax()) {
            $consultas = Consulta::all();
            $valores = [
                'listado' => view('consulta.ajaxListado')->with(compact('consultas'))->render()
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

            $consulta = Consulta::find($id);
            $consulta->usuario_eliminador_id = $usuario->id;
            $consulta->save();

            Consulta::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }
}
