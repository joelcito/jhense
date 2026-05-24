<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\Sucursal;
use App\Models\User;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagoController extends Controller
{
    public function listado(Request $request)
    {

        $usuario = Auth::user();

        // PARA VERIFICAR LA CAJA
        $caja            = new Caja();
        $cajaAbierta     = $caja->sacaCajaVigente($usuario->id);
        $sucursales      = Sucursal::all();
        $fechaIni        = date('Y-m-d');
        $fechaFin        = date('Y-m-d');
        $cajasRangoFecha = $caja->sacarCajasRangoFechas($fechaIni, $fechaFin);
        $usuarios        = User::all();

        return view('pago.listado')->with(compact('cajaAbierta', 'sucursales', 'fechaIni', 'fechaFin', 'cajasRangoFecha', 'usuarios'));
    }

    public function ajaxListado(Request $request)
    {
        if ($request->ajax()) {

            // dd($request->all());
            $sucursal_id = $request->input('sucursal_id');
            $fecha_ini   = $request->input('fecha_ini');
            $fecha_fin   = $request->input('fecha_fin');
            // $caja_id     = $request->input('caja_id');
            $usuario_id  = $request->input('usuario_id');

            $query = Pago::select();

            if ($sucursal_id != null) {
                $sucursal      = Sucursal::find($sucursal_id);
                $puntoVentasId = $sucursal->puntoVentas->pluck('id')->toArray();
                $query->whereIn('punto_venta_id', $puntoVentasId);
            }

            if ($fecha_ini != null && $fecha_fin != null) {
                $query->where('fecha', '>=', $fecha_ini . ' 00:00:00')
                    ->where('fecha', '<=', $fecha_fin . ' 23:59:59');
            }

            // if($caja_id != null){
            //     $query->where('caja_id',$caja_id);
            // }

            if ($usuario_id != null) {
                $query->where('usuario_creador_id', $usuario_id);
            }

            // dd(
            //     $query->toSql(),
            //     $sucursal_id,
            //     $fecha_ini,
            //     $fecha_fin,
            //     $caja_id,
            //     $usuario_id
            // );

            $pagos = $query->orderBy('id', 'desc')->get();
            $valores = [
                'listado' => view('pago.ajaxListado')->with(compact('pagos'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarTipoIngresoSalida(Request $request)
    {

        if ($request->ajax()) {

            $usuario     = Auth::user();
            $caja_id     = $request->input('caja_abierto_ingre_cerra');
            $monto       = $request->input('monto');
            $descripcion = $request->input('descripcion');
            $tipo = $request->input('tipo');


            $pago                     = new Pago();
            $pago->usuario_creador_id = $usuario->id;
            $pago->caja_id            = $caja_id;
            $pago->punto_venta_id     = $usuario->punto_venta_id;
            $pago->monto              = $monto;
            $pago->fecha              = date('Y-m-d H:i:s');
            $pago->descripcion        = $descripcion;
            $pago->apertura_caja      = "No";
            $pago->tipo_pago          = 'EFECTIVO';
            $pago->estado             = $tipo;

            $pago->save();
            $data = Respuesta::success(null, "Datos registrados correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    /*Cuentas por cobrar */
    public function listadoDeuda()
    {

        $usuario = Auth::user();

        // PARA VERIFICAR LA CAJA
        $caja = new Caja();
        $cajaAbierta = $caja->sacaCajaVigente($usuario->id);

        return view('pago.listadoDeuda')->with(compact('cajaAbierta', 'usuario'));
    }

    public function ajaxListadoDeuda(Request $request)
    {
        if ($request->ajax()) {
            $facturas = Factura::with(['cliente', 'sucursal'])->where('estado_pago', 'DEUDA')->get();

            $valores = [
                'listado' => view('pago.ajaxListadoDeuda')->with(compact('facturas'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function ajaxFormPagoDeuda(Request $request)
    {
        if ($request->ajax()) {
            $factura_id = $request->input('factura_id');

            $factura = Factura::with(['cliente', 'sucursal'])->where('id', $factura_id)->first();
            $pagos = Pago::where('factura_id', $factura_id)
                ->where('estado', 'INGRESO')
                ->get();
            $pagado = Pago::where('factura_id', $factura_id)
                ->where('estado', 'INGRESO')
                ->sum('monto');

            $valores = [
                'formulario' => view('pago.ajaxFormPagoDeuda')->with(compact('factura', 'pagos', 'pagado'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarPagoDeuda(Request $request)
    {
        if ($request->ajax()) {

            $request->validate([
                'factura_id' => 'required',
                'tipo_pago' => 'required',
                'importe_pago' => 'required',
                'saldo' => 'required',
            ]);

            $factura_id = $request->input('factura_id');
            $tipo_pago = $request->input('tipo_pago');
            $importe_pago = $request->input('importe_pago');
            $saldo = $request->input('saldo');

            $usuario = Auth::user();
            $caja = new Caja();
            $cajaAbierta = $caja->sacaCajaVigente($usuario->id);

            if ($importe_pago > 0 && $importe_pago <= $saldo) {
                $nuevo                     = new Pago();
                $nuevo->usuario_creador_id = $usuario->id;
                $nuevo->factura_id         = $factura_id;
                $nuevo->caja_id            = $cajaAbierta->id;
                $nuevo->punto_venta_id     = $usuario->punto_venta_id;
                $nuevo->monto              = $importe_pago;
                $nuevo->cambio             = 0;
                $nuevo->fecha              = date('Y-m-d H:i:s');
                $nuevo->descripcion        = 'VENTA';
                $nuevo->apertura_caja      = 'No';
                $nuevo->tipo_pago          = $tipo_pago;
                $nuevo->estado             = 'INGRESO';
                $nuevo->save();

                if (($saldo - $importe_pago) == 0) {
                    $factura = Factura::find($factura_id);
                    $factura->estado_pago = 'PAGADO';
                    $factura->save();
                }

                $data = Respuesta::success(null, "Datos obtenidos correctamente");
            } else {
                $data = Respuesta::error(null, "Error en registro de datos.");
            }
        } else {
            $data = Respuesta::error(null, "Error en registro de datos.");
        }
        return $data;
    }
}
