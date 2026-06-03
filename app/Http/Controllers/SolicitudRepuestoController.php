<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\SolicitudRepuesto;
use App\Models\Sucursal;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolicitudRepuestoController extends Controller
{
    public function listado()
    {
        $sucursal_id = Auth::user()->puntoVenta->sucursal_id ?? 1;
        $sucursal = Sucursal::findOrFail($sucursal_id);
        return view('solicitudRepuesto.listado')->with(compact('sucursal'));
    }

    public function ajaxListado(Request $request)
    {
        if ($request->ajax()) {
            $sucursal_id = $request->input('sucursal_id');
            // Obtener las solicitudes que pertenecen a esta sucursal (ya sea local, central o externos)
            $solicitudes = SolicitudRepuesto::with(['ordenRecepcion.grupoCliente.cliente', 'creador'])
                ->where('sucursal_id', $sucursal_id)
                ->whereIn('tipo_solicitud', ['LOCAL', 'CENTRAL'])
                ->orderBy('created_at', 'desc')
                ->get();

            $valores = [
                'listado' => view('solicitudRepuesto.ajaxListado')->with(compact('solicitudes'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function ajaxObtenerSolicitud(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->input('id');
            $sucursal_id = $request->input('sucursal_id');

            $solicitud = SolicitudRepuesto::with(['ordenRecepcion.grupoCliente.cliente', 'detalles.producto'])->find($id);
            if ($solicitud) {
                // Calcular stock actual para los detalles
                $detalles = [];
                $mov = new Movimiento();
                foreach ($solicitud->detalles as $det) {
                    $stock_actual = 0;
                    if ($det->producto_id) {
                        $stock_actual = $mov->cantidaDisponile($sucursal_id, $det->producto_id);
                    }
                    $detalles[] = [
                        'id' => $det->id,
                        'producto_id' => $det->producto_id,
                        'producto_texto' => $det->producto ? $det->producto->nombre : $det->descripcion,
                        'cantidad' => $det->cantidad,
                        'precio' => $det->precio,
                        'subtotal' => $det->subtotal,
                        'aprobado' => $det->aprobado,
                        'stock_actual' => $stock_actual
                    ];
                }

                $solicitud->detalles_lista = $detalles;

                return Respuesta::success(['solicitud' => $solicitud], "Obtenido");
            }
            return Respuesta::error(null, "No encontrado");
        }
    }

    public function guardarAprobacion(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->input('solicitud_id');
            $estado = $request->input('estado'); // APROBADO o RECHAZADO
            $detalles_aprobar = $request->input('detalles', []);

            $solicitud = SolicitudRepuesto::with(['detalles.producto'])->find($id);
            if ($solicitud) {
                // Verificar stock
                if ($estado === 'APROBADO' && $solicitud->estado !== 'APROBADO') {
                    $mov = new Movimiento();
                    $erroresStock = [];

                    foreach ($solicitud->detalles as $det) {
                        $aprobado = isset($detalles_aprobar[$det->id]) && ($detalles_aprobar[$det->id] == '1' || $detalles_aprobar[$det->id] == 'on' || $detalles_aprobar[$det->id] === true);
                        if ($aprobado && $det->producto_id) {
                            $stock_actual = $mov->cantidaDisponile($solicitud->sucursal_id, $det->producto_id);
                            if ($stock_actual < $det->cantidad) {
                                $nombre = $det->producto ? $det->producto->nombre : 'Desconocido';
                                $erroresStock[] = "Stock insuficiente para '{$nombre}'. Solicitado: {$det->cantidad}, Disponible: {$stock_actual}.";
                            }
                        }
                    }

                    if (count($erroresStock) > 0) {
                        return Respuesta::error(null, implode("<br>", $erroresStock));
                    }
                }

                foreach ($solicitud->detalles as $det) {
                    $det->aprobado = isset($detalles_aprobar[$det->id]) && ($detalles_aprobar[$det->id] == '1' || $detalles_aprobar[$det->id] == 'on' || $detalles_aprobar[$det->id] === true);
                    $det->save();
                }

                $estadoAnterior = $solicitud->estado;
                $solicitud->estado = $estado;
                $solicitud->usuario_modificador_id = Auth::id();
                $solicitud->save();

                if ($estado === 'APROBADO' && $estadoAnterior !== 'APROBADO') {
                    foreach ($solicitud->detalles as $det) {
                        if ($det->aprobado && $det->producto_id) {
                            Movimiento::create([
                                'usuario_creador_id' => Auth::id(),
                                'producto_id' => $det->producto_id,
                                'sucursal_id' => $solicitud->sucursal_id,
                                'precio_compra' => $det->producto->precio_compra ?? 0,
                                'precio_venta' => $det->precio,
                                'ingreso' => 0,
                                'salida' => $det->cantidad,
                                'fecha' => date('Y-m-d'),
                                'descripcion' => 'SALIDA POR SOLICITUD DE REPUESTOS ORDEN #' . $solicitud->orden_recepcion_id
                            ]);
                        }
                    }
                }

                return Respuesta::success(null, "Solicitud actualizada correctamente");
            }
            return Respuesta::error(null, "Error al guardar");
        }
    }
}
