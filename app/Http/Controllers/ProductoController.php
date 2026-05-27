<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Utils\Respuesta;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ProductoController extends Controller
{
    public function listado()
    {
        $sucursales = Sucursal::all();
        $proveedores = Proveedor::all();

        return view('producto.listado')->with(compact(['sucursales', 'proveedores']));
    }

    public function ajaxListado(Request $request)
    {
        if ($request->ajax()) {

            $sucursal_id = Auth::user()->puntoVenta->sucursal_id; //nuevo
            $sucursal = Sucursal::find($sucursal_id); //nuevo
            $productos = Producto::with('proveedor')->get();
            $valores = [
                'listado' => view('producto.ajaxListado')->with(compact('productos', 'sucursal'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarProducto(Request $request)
    {
        if ($request->ajax()) {

            $request->validate([
                'nombre' => 'required',
                //'codigo' => 'required|unique:productos,codigo',
                'precio_compra'      => 'required',
                'precio_venta'       => 'required',
                'minimo_stock'       => 'required',
                //'proveedor_id'       => 'required',
            ]);

            $id = $request->input('id');

            $nombre        = $request->input('nombre');
            $codigo        = $request->input('codigo');
            $precio_compra = $request->input('precio_compra');
            $precio_venta  = $request->input('precio_venta');
            $minimo_stock  = $request->input('minimo_stock');
            $proveedor_id  = $request->input('proveedor_id');
            $usuario       = Auth::user();

            if ($id == 0) {
                /* $request->validate([
                    'codigo' => 'required|unique:productos,codigo',
                ]); */
                $producto                     = new Producto();
                $producto->usuario_creador_id = $usuario->id;
            } else {
                /* if($existe = Producto::where('codigo', $codigo)->where('id', '!=', $id)->first()){
                    $request->validate([
                        'codigo' => 'required|unique:productos,codigo',
                    ]);
                } */
                $producto = Producto::find($id);
                $producto->usuario_modificador_id = $usuario->id;
            }

            $producto->nombre           = $nombre;
            $producto->codigo           = $codigo;
            $producto->precio_compra    = $precio_compra;
            $producto->precio_venta     = $precio_venta;
            $producto->minimo_stock     = $minimo_stock;
            $producto->proveedor_id     = $proveedor_id;
            $producto->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function eliminarProducto(Request $request)
    {
        if ($request->ajax()) {

            $id = $request->input('id');
            $usuario = Auth::user();

            $producto = Producto::find($id);
            $producto->usuario_eliminador_id = $usuario->id;
            $producto->save();

            Producto::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    //ADICIONES EN MOVIMIENTOS
    public function ajaxStockSucursal(Request $request)
    {
        if ($request->ajax()) {
            $producto_id = $request->input('producto_id');

            $sucursales = Sucursal::withSum(['movimientos' => function ($query) use ($producto_id) {
                $query->where('producto_id', $producto_id);
            }], 'ingreso')
                ->withSum(['movimientos' => function ($query) use ($producto_id) {
                    $query->where('producto_id', $producto_id);
                }], 'salida')
                ->get();
            $producto = Producto::find($producto_id);
            $valores = [
                'listado' => view('producto.ajaxStockSucursal')->with(compact('sucursales', 'producto'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarStockSucursal(Request $request)
    {
        if ($request->ajax()) {

            $request->validate([
                'producto_id' => 'required',
                'sucursal_id' => 'required',
                'f_precio_compra' => 'required',
                'f_precio_venta' => 'required',
                'cantidad_ingreso' => 'required|min:1',
            ]);

            $producto_id = $request->input('producto_id');
            $sucursal_id = $request->input('sucursal_id');
            $precio_compra = $request->input('f_precio_compra');
            $precio_venta = $request->input('f_precio_venta');
            $descripcion     = $request->input('descripcion');
            $usuario         = Auth::user();

            $cantidad_ingreso = $request->input('cantidad_ingreso');

            $producto = Producto::find($producto_id);
            $producto->precio_compra = $precio_compra;
            $producto->precio_venta = $precio_venta;
            $producto->save();

            $nuevo              = new Movimiento();
            $nuevo->producto_id = $producto_id;
            $nuevo->sucursal_id = $sucursal_id;
            $nuevo->ingreso     = $cantidad_ingreso;
            $nuevo->salida             = 0;
            $nuevo->usuario_creador_id = $usuario->id;
            $nuevo->fecha              = date('Y-m-d H:i:s');
            $nuevo->descripcion        = $descripcion;
            $nuevo->precio_compra      = $precio_compra;
            $nuevo->precio_venta       = $precio_venta;
            $nuevo->save();

            $data = Respuesta::success($producto, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function guardarSalidaSucursal(Request $request)
    {
        if ($request->ajax()) {

            $request->validate([
                'salida_producto_id' => 'required',
                'salida_sucursal_id' => 'required',
                'cantidad_salida' => 'required|min:1',
            ]);

            $producto_id = $request->input('salida_producto_id');
            $sucursal_id = $request->input('salida_sucursal_id');
            $descripcion     = $request->input('salida_descripcion');
            $usuario         = Auth::user();

            $cantidad_salida = $request->input('cantidad_salida');

            $nuevo              = new Movimiento();
            $nuevo->producto_id = $producto_id;
            $nuevo->sucursal_id = $sucursal_id;

            $producto = Producto::find($producto_id);

            $nuevo->salida       = $cantidad_salida;
            $nuevo->ingreso             = 0;
            $nuevo->usuario_creador_id = $usuario->id;
            $nuevo->fecha              = date('Y-m-d H:i:s');
            $nuevo->descripcion        = $descripcion;
            $nuevo->save();

            $data = Respuesta::success($producto, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function ajaxFormTransferencia(Request $request)
    {
        if ($request->ajax()) {
            $producto_id = $request->input('producto_id');

            $sucursales = Sucursal::all();
            $sucursalesSalida = Sucursal::with(['movimientos'])
                ->whereHas('movimientos', function ($q) use ($producto_id) {
                    $q->where('producto_id', $producto_id);
                })
                ->get();

            $producto = Producto::find($producto_id);

            $valores = [
                'formulario' => view('producto.ajaxFormTransferencia')->with(compact(['sucursales', 'sucursalesSalida', 'producto_id', 'producto']))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarTransferenciaSucursal(Request $request)
    {
        if ($request->ajax()) {

            $request->validate([
                'sucursal1_id' => 'required',
                'sucursal2_id' => 'required',
                'salida' => 'required|min:1',
            ]);

            $producto_id             = $request->input('producto_transferencia_id');
            $sucursal1_id            = $request->input('sucursal1_id');
            $sucursal2_id            = $request->input('sucursal2_id');
            $salida                  = (int)$request->input('salida');

            $usuario = Auth::user();

            $movimiento = new Movimiento();
            $stockDisponible = $movimiento->cantidaDisponile($sucursal1_id, $producto_id);

            $producto = Producto::find($producto_id);
            $cantidadIngresoStock = $salida;

            // if( $salida <= $stockDisponible){
            if ($cantidadIngresoStock <= $stockDisponible) {

                $nuevo                     = new Movimiento();
                $nuevo->producto_id        = $producto_id;
                $nuevo->sucursal_id        = $sucursal1_id;
                $nuevo->ingreso            = 0;
                // $nuevo->salida             = $salida;
                $nuevo->salida             = $cantidadIngresoStock;
                $nuevo->usuario_creador_id = $usuario->id;
                $nuevo->fecha              = date('Y-m-d H:i:s');
                $nuevo->descripcion        = 'TRANSFERENCIA';
                $nuevo->save();

                $nuevo2                     = new Movimiento();
                $nuevo2->producto_id        = $producto_id;
                $nuevo2->sucursal_id        = $sucursal2_id;
                // $nuevo2->ingreso            = $salida;
                $nuevo2->ingreso            = $cantidadIngresoStock;
                $nuevo2->salida             = 0;
                $nuevo2->usuario_creador_id = $usuario->id;
                $nuevo2->fecha              = date('Y-m-d H:i:s');
                $nuevo2->descripcion        = 'INGRESO POR TRANSFERENCIA';
                $nuevo2->save();

                $data = Respuesta::success(null, "Datos obtenidos correctamente");
            } else {
                $data = Respuesta::error(null, "No exite suficiente stcok para la transferencia");
            }
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    //REPORTES
    public function generarReporteIngreso(Request $request)
    {

        $request->validate([
            'ingreso_producto_id' => 'required',
            'ingreso_tipo' => 'required',
            'ingreso_fecha_ini' => 'required',
            'ingreso_fecha_fin' => 'required',
        ]);

        $producto_id = $request->input('ingreso_producto_id');
        $tipo = $request->input('ingreso_tipo');
        $fecha_ini = $request->input('ingreso_fecha_ini') . ' 00:00:00';
        $fecha_fin = $request->input('ingreso_fecha_fin') . ' 23:59:59';

        $producto = Producto::find($producto_id);
        $operativa = 'INGRESO';

        switch ($tipo) {
            case 'SUCURSAL':
                $sucursales = Sucursal::with(['movimientos' => function ($query) use ($fecha_ini, $fecha_fin, $producto_id) {
                    $query->select([
                        DB::raw('DATE(fecha) as fecha'),
                        'sucursal_id',
                        DB::raw('SUM(ingreso) as total')
                    ])
                        ->whereBetween('fecha', [$fecha_ini, $fecha_fin])
                        ->where('producto_id', $producto_id)
                        ->where('ingreso', '>', 0)
                        ->groupBy(DB::raw('DATE(fecha)'), 'sucursal_id')
                        ->orderBy(DB::raw('DATE(fecha)'), 'ASC');
                }])->get();
                $html = View::make('producto.pdfReporteSucursal', compact(['sucursales', 'producto', 'fecha_ini', 'fecha_fin', 'operativa']))->render();
                break;
            case 'USUARIO':
                $sucursales = Sucursal::with(['movimientos' => function ($query) use ($fecha_ini, $fecha_fin, $producto_id) {
                    $query->whereBetween('fecha', [$fecha_ini, $fecha_fin])
                        ->where('producto_id', $producto_id)
                        ->where('ingreso', '>', 0)
                        ->orderBy(DB::raw('DATE(fecha)'), 'ASC');
                }, 'movimientos.usuarioCreador']) // Carga el usuario sin condiciones
                    ->get();
                $html = View::make('producto.pdfReporteUsuario', compact(['sucursales', 'producto', 'fecha_ini', 'fecha_fin', 'operativa']))->render();
                break;
        }

        $dompdf = new Dompdf();
        $dompdf->setPaper('letter'); //cambio orientacion de la hoja
        $dompdf->loadHtml($html);
        $dompdf->render();
        //return $dompdf->stream('Reporte_Ingresos.pdf');

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename=Reporte_Ingresos.pdf');
    }

    public function generarReporteSalida(Request $request)
    {

        $request->validate([
            'salida_producto_id' => 'required',
            'salida_tipo' => 'required',
            'salida_fecha_ini' => 'required',
            'salida_fecha_fin' => 'required',
        ]);

        $producto_id = $request->input('salida_producto_id');
        $tipo = $request->input('salida_tipo');
        $fecha_ini = $request->input('salida_fecha_ini') . ' 00:00:00';
        $fecha_fin = $request->input('salida_fecha_fin') . ' 23:59:59';

        $producto = Producto::find($producto_id);
        $operativa = 'SALIDA';

        switch ($tipo) {
            case 'SUCURSAL':
                $sucursales = Sucursal::with(['movimientos' => function ($query) use ($fecha_ini, $fecha_fin, $producto_id) {
                    $query->select([
                        DB::raw('DATE(fecha) as fecha'),
                        'sucursal_id',
                        DB::raw('SUM(salida) as total')
                    ])
                        ->whereBetween('fecha', [$fecha_ini, $fecha_fin])
                        ->where('producto_id', $producto_id)
                        ->where('salida', '>', 0)
                        ->groupBy(DB::raw('DATE(fecha)'), 'sucursal_id')
                        ->orderBy(DB::raw('DATE(fecha)'), 'ASC');
                }])->get();
                $html = View::make('producto.pdfReporteSucursal', compact(['sucursales', 'producto', 'fecha_ini', 'fecha_fin', 'operativa']))->render();
                break;
            case 'USUARIO':
                $sucursales = Sucursal::with(['movimientos' => function ($query) use ($fecha_ini, $fecha_fin, $producto_id) {
                    $query->whereBetween('fecha', [$fecha_ini, $fecha_fin])
                        ->where('producto_id', $producto_id)
                        ->where('salida', '>', 0)
                        ->orderBy(DB::raw('DATE(fecha)'), 'ASC');
                }, 'movimientos.usuarioCreador']) // Carga el usuario sin condiciones
                    ->get();
                $html = View::make('producto.pdfReporteUsuario', compact(['sucursales', 'producto', 'fecha_ini', 'fecha_fin', 'operativa']))->render();
                break;
        }

        $html = View::make('producto.pdfReporteUsuario', compact(['sucursales', 'producto', 'fecha_ini', 'fecha_fin', 'operativa']))->render();
        $dompdf = new Dompdf();
        $dompdf->setPaper('letter'); //cambio orientacion de la hoja
        $dompdf->loadHtml($html);
        $dompdf->render();
        //return $dompdf->stream('Reporte_Salidas.pdf');

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename=Reporte_Salidas.pdf');
    }
}
