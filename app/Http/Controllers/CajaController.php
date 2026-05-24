<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Pago;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CajaController extends Controller
{
    public function listado(Request $request)
    {

        $usuario = Auth::user();

        $caja = new Caja();
        $cajaAbierta = $caja->sacaCajaVigente($usuario->id);

        return view('caja.listado')->with(compact('cajaAbierta', 'usuario'));
    }

    public function ajaxListado(Request $request)
    {
        if ($request->ajax()) {
            $cajas = Caja::all();
            $valores = [
                'listado' => view('caja.ajaxListado')->with(compact('cajas'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarAperturaCaja(Request $request)
    {
        if ($request->ajax()) {

            $usuario = Auth::user();
            $usuario_id     = $request->input('usuario_id');
            $monto_apertura = $request->input('monto_apertura');
            $descripcion    = $request->input('descripcion');

            $caja                     = new Caja();
            $caja->usuario_creador_id = $usuario->id;
            $caja->usuario_id         = $usuario_id;
            $caja->punto_venta_id     = $usuario->punto_venta_id;
            $caja->fecha_apertura     = date('Y-m-d H:i:s');
            $caja->monto_apertura     = $monto_apertura;
            $caja->descripcion        = $descripcion;
            $caja->estado             = 'Abierta';
            $caja->save();

            $pago                     = new Pago();
            $pago->usuario_creador_id = $usuario->id;
            $pago->caja_id            = $caja->id;
            $pago->punto_venta_id     = $usuario->punto_venta_id;
            $pago->monto              = $monto_apertura;
            $pago->fecha              = date('Y-m-d H:i:s');
            $pago->descripcion        = $descripcion;
            $pago->apertura_caja      = 'Si';
            $pago->tipo_pago          = 'EFECTIVO';
            $pago->estado             = 'INGRESO';
            $pago->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarCerrarCaja(Request $request)
    {

        if ($request->ajax()) {

            $usuario            = Auth::user();
            $usuario_id_cierre  = $request->input('usuario_id_cierre');
            $caja_id_cierre     = $request->input('caja_id_cierre');
            $monto_cierre       = $request->input('monto_cierre');
            $descripcion_cierre = $request->input('descripcion_cierre');

            $cajaAperturada = Caja::where('id', $caja_id_cierre)
                ->where('estado', 'Abierta')
                ->first();

            if ($cajaAperturada) {

                $totalEfectivo = Pago::where('estado', 'INGRESO')
                    ->where('tipo_pago', 'EFECTIVO')
                    ->where('caja_id', $cajaAperturada->id)
                    ->where('apertura_caja', 'No')
                    ->sum('monto');

                $totalQr = Pago::where('estado', 'INGRESO')
                    ->where('tipo_pago', 'QR')
                    ->where('caja_id', $cajaAperturada->id)
                    ->where('apertura_caja', 'No')
                    ->sum('monto');

                $totalTramsferencia = Pago::where('estado', 'INGRESO')
                    ->where('tipo_pago', 'TRANSFERENCIA')
                    ->where('caja_id', $cajaAperturada->id)
                    ->where('apertura_caja', 'No')
                    ->sum('monto');


                $otrasSalidas = Pago::where('estado', 'SALIDA')
                    ->where('tipo_pago', 'EFECTIVO')
                    ->where('caja_id', $cajaAperturada->id)
                    ->where('apertura_caja', 'No')
                    ->sum('monto');

                $montoTotal     = $totalEfectivo + $totalQr + $totalTramsferencia;
                $montoAlContado = $montoTotal - $totalQr - $totalTramsferencia;
                $saldo          = $monto_cierre - $montoAlContado - $cajaAperturada->monto_apertura - $otrasSalidas;

                $cajaAperturada->usuario_modificador_id = $usuario->id;
                $cajaAperturada->fecha_cierre           = date('Y-m-d H:i:s');
                $cajaAperturada->monto_cierre           = $monto_cierre;
                $cajaAperturada->descripcion_cierre     = $descripcion_cierre;
                $cajaAperturada->total_venta            = $montoTotal;
                $cajaAperturada->venta_contado          = $montoAlContado;
                $cajaAperturada->total_qr               = $totalQr;
                $cajaAperturada->total_salida           = $otrasSalidas;
                $cajaAperturada->total_transferencia    = $totalTramsferencia;
                $cajaAperturada->estado                 = 'Cerrado';
                $cajaAperturada->saldo                  = $saldo;
                $cajaAperturada->save();

                $data = Respuesta::success(null, "Datos obtenidos correctamente");
            } else {
                $data = Respuesta::error(null, "No se encontro la caja o la caja esta en un estado cerrado");
            }
        } else {

            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }
}
