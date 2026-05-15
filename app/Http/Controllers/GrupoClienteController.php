<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteServicio;
use App\Models\Grupo;
use App\Models\GrupoCliente;
use App\Models\Sucursal;
use App\Utils\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class GrupoClienteController extends Controller
{
    public function listado($sucursal_id, $grupo_id){
        $sucursal = Sucursal::find($sucursal_id);
        $grupo = Grupo::find($grupo_id);
        $clientes = Cliente::where('sucursal_id', $sucursal_id)->get();

        return view('grupoCliente.listado', compact('sucursal', 'grupo', 'clientes'));
    }

    public function ajaxListado(Request $request){
        if($request->ajax()){
            $sucursal_id = $request->input('sucursal_id');
            $grupo_id = $request->input('grupo_id');
            $grupos = GrupoCliente::with('cliente')
                ->where('grupo_id', $grupo_id)
                ->whereHas('cliente', function($query) use ($sucursal_id){
                    $query->where('sucursal_id', $sucursal_id);
                })
                ->get();
            $valores = [
                'listado' => view('grupoCliente.ajaxListado')->with(compact('grupos'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        }else{
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarGrupo(Request $request){
        if($request->ajax()){

            $request->validate([
                'grupo_id' => 'required',
                'cliente_id' => 'required',
            ]);

            $id = $request->input('id');

            $grupo_id  = $request->input('grupo_id');
            $cliente_id  = $request->input('cliente_id');
            $monto_inicio  = $request->input('monto_inicio');
            $usuario = Auth::user();

            if( $id == 0 ){
                $grupo                     = new GrupoCliente();
                $grupo->usuario_creador_id = $usuario->id;
            }else{
                $grupo = GrupoCliente::find($id);
                $grupo->usuario_modificador_id = $usuario->id;
            }

            $grupo->grupo_id           = $grupo_id;
            $grupo->cliente_id     = $cliente_id;
            $grupo->monto_inicio = $monto_inicio;
            $grupo->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");

        }else{
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function eliminarGrupo(Request $request){
        if($request->ajax()){

            $id = $request->input('id');
            $usuario = Auth::user();

            $grupo = GrupoCliente::find($id);
            $grupo->usuario_eliminador_id = $usuario->id;
            $grupo->save();

            GrupoCliente::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");

        }else{
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    //DETALLE
    public function detalle($grupo_cliente_id){
        $grupoCliente = GrupoCliente::find($grupo_cliente_id);
        $sucursal = Sucursal::find($grupoCliente->cliente->sucursal_id);
        $grupo = Grupo::find($grupoCliente->grupo_id);

        return view('grupoCliente.detalle', compact('sucursal', 'grupo', 'grupoCliente'));
    }

    public function ajaxDetalle(Request $request){
        if($request->ajax()){
            $grupo_cliente_id = $request->input('grupo_cliente_id');
            $grupoCliente = GrupoCliente::with('servicios')->find($grupo_cliente_id);
            $servicios = $grupoCliente->servicios;
            $valores = [
                'listado' => view('grupoCliente.ajaxDetalle')->with(compact('grupoCliente', 'servicios'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        }else{
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarItemRangos(Request $request){
        if($request->ajax()){

            $request->validate([
                'grupo_cliente_id' => 'required',
                'item_1' => 'required|numeric',
                'item_2' => 'required|numeric',
                'categoria' => 'required',
            ]);

            $grupo_cliente_id = $request->input('grupo_cliente_id');
            $item_1 = $request->input('item_1');
            $item_2 = $request->input('item_2');
            $categoria = $request->input('categoria');
            $usuario = Auth::user();

            $grupo = GrupoCliente::with(['servicios', 'servicios' => function($query) use ($item_1, $item_2){
                    $query->whereBetween('item', [$item_1, $item_2]);
                }])
                ->find($grupo_cliente_id);

            $serviciosEnRango = $grupo->servicios;
            if(count($serviciosEnRango) == 0){
                return Respuesta::error(null, "No hay servicios en el rango de items seleccionado");
            }

            foreach($serviciosEnRango as $servicio){
                $servicio->usuario_modificador_id = $usuario->id;
                $servicio->categoria = $categoria;
                $servicio->save();
            }

            $data = Respuesta::success(null, "Datos obtenidos correctamente");

        }else{
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function descargarFormatoImportarExcel(Request $request){
        if($request->ajax()){
            // generacion del excel
            $fileName    = 'ImportarServicios.xlsx';
            $libro       = new Spreadsheet();
            $hoja        = $libro->getActiveSheet();

            $hoja->getColumnDimension('A')->setWidth(20);
            $hoja->getColumnDimension('B')->setWidth(20);
            $hoja->getColumnDimension('C')->setWidth(20);
            $hoja->getColumnDimension('D')->setWidth(20);
            $hoja->getColumnDimension('E')->setWidth(20);
            $hoja->getColumnDimension('F')->setWidth(20);
            $hoja->getColumnDimension('G')->setWidth(20);
            $hoja->getColumnDimension('H')->setWidth(20);

            // CREAMOS LA HOJA
            $hojaListaCategorias = $libro->createSheet();
            $hojaListaCategorias->setTitle('ListaCategorias');                
            $hojaListaCategorias->setCellValue('A' . 1, 'PREVENTIVO');
            $hojaListaCategorias->setCellValue('A' . 2, 'CORRECTIVO');
            $hojaListaCategorias->setCellValue('A' . 3, 'SUMINISTRO');
            $hojaListaCategorias->setCellValue('A' . 4, 'REPUESTOS');
            $hojaListaCategorias->setCellValue('A' . 5, 'OTROS');
            $listaRangoCategorias = 'ListaCategorias!$A$1:$A$' . 5;

            //Hoja principal continuacion
            $hoja->setCellValue('A1', "SERVICIOS PARA IMPORTAR AL SISTEMA");

            $hoja->setCellValue('A2', "CATEGORIA");
            $hoja->setCellValue('B2', "SUB CATEGORIA");
            $hoja->setCellValue('C2', "ITEM");
            $hoja->setCellValue('D2', "SERVICIO");
            $hoja->setCellValue('E2', "UNIDAD DE MEDIDA");
            $hoja->setCellValue('F2', "CANTIDAD");
            $hoja->setCellValue('G2', "COSTO");
            $hoja->setCellValue('H2', "TOTAL");

            $encabezadoStyle =[
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ];

            $hoja->mergeCells('A1:H1');
            $hoja->getStyle('A1')->applyFromArray($encabezadoStyle);

            // Aplicar márgenes y formato a los encabezados
            $encabezadoStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFFFE0B2', // Color de fondo
                    ],
                ],
            ];
            $hoja->getStyle('A2:H2')->applyFromArray($encabezadoStyle);

            $contadorCeldas = 3;
            for($i = $contadorCeldas ; $i <= 1000 ; $i++){

                // de aqui el seleccionable
                $validacion = $hoja->getCell('A' . $i)->getDataValidation();
                $validacion->setType(DataValidation::TYPE_LIST);
                $validacion->setErrorStyle(DataValidation::STYLE_STOP);
                $validacion->setAllowBlank(true);
                $validacion->setShowInputMessage(true);
                $validacion->setShowErrorMessage(true);
                $validacion->setShowDropDown(true);
                $validacion->setErrorTitle('Valor inválido');
                $validacion->setError('El valor ingresado no es válido.');
                $validacion->setPromptTitle('Seleccione de la lista');
                $validacion->setPrompt('Seleccione un valor de la lista.');
                $validacion->setFormula1($listaRangoCategorias);

                $contadorCeldas++;

            }

            // Proteger la hoja con la lista para evitar modificaciones
            $hojaListaCategorias->getProtection()->setSheet(true);
            $hojaListaCategorias->getProtection()->setSort(true);
            $hojaListaCategorias->getProtection()->setInsertRows(true);
            $hojaListaCategorias->getProtection()->setFormatCells(true);

            // Aplicar bordes a las celdas de datos
            $hoja->getStyle('A3:H'.($contadorCeldas-1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            // Establecer los encabezados para forzar la descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $fileName .'"');
            header('Cache-Control: max-age=0');

            // Guardar el archivo
            $writer = new Xlsx($libro);
            $writer->save('php://output');
            exit;

        }else{
            $data['text']   = 'No existe';
            $data['estado'] = 'error';
        }
        return $data;
    }

    public function importarServiciosExcel(Request $request){
        if ($request->ajax()) {
            $request->validate([
                'grupo_cliente_id' => 'required',
                'excel_servicio_masivo' => 'required|file|mimes:xlsx,xls',
            ]);

            if (! $request->hasFile('excel_servicio_masivo') || ! $request->file('excel_servicio_masivo')->isValid()) {
                return back()->withError('Error al subir el archivo.');
            }

            $grupo_cliente_id = $request->input('grupo_cliente_id');
            $archivo = $request->file('excel_servicio_masivo');
            $spreadsheet = IOFactory::load($archivo);
            $hoja = $spreadsheet->getActiveSheet();
            $rows = $hoja->toArray();

            $usuario = Auth::user();
            $errores = [];

            $contador = 0;

            foreach ($rows as $key => $row) {
                if ($key < 2) continue; // Saltar encabezados

                $categoria = $row[0];// CATEGORIA
                $sub_categoria = $row[1];// CATEGORIA
                $item = (int)$row[2];//ITEM
                $servicio = $row[3];//SERVICIO
                $unidad_medida = $row[4];//UNIDAD DE MEDIDA
                $cantidad = (float)$row[5];//CANTIDAD
                $costo   = (float)$row[6];//COSTO
                $total   = (float)$row[7];//TOTAL

                if(empty($item) && empty($categoria) && empty($sub_categoria) && empty($servicio) && empty($costo) && empty($unidad_medida) && empty($cantidad) && empty($total)){
                    break;
                }

                /* if (empty($item)) {
                    $errores[] = [
                        'fila' => $key + 1,
                        'texto' => 'El registro no tiene número de item, complete el campo',
                        'datos' => $row
                    ];
                    continue;
                } */

                if($item > 0){
                    if (empty($categoria)) {
                        $errores[] = [
                            'fila' => $key + 1,
                            'texto' => 'El registro no tiene categoria, complete el campo',
                            'datos' => $row
                        ];
                        continue;
                    }

                    if (empty($sub_categoria)) {
                        $errores[] = [
                            'fila' => $key + 1,
                            'texto' => 'El registro no tiene subcategoria, complete el campo',
                            'datos' => $row
                        ];
                        continue;
                    }
                }

                if (empty($servicio)) {
                    $errores[] = [
                        'fila' => $key + 1,
                        'texto' => 'El registro no tiene servicio, complete el campo',
                        'datos' => $row
                    ];
                    continue;
                }

                if (empty($unidad_medida)) {
                    $errores[] = [
                        'fila' => $key + 1,
                        'texto' => 'El registro no tiene unidad de medida, complete el campo',
                        'datos' => $row
                    ];
                    continue;
                }

                if (empty($cantidad)) {
                    $errores[] = [
                        'fila' => $key + 1,
                        'texto' => 'El registro no tiene cantidad, complete el campo',
                        'datos' => $row
                    ];
                    continue;
                }

                if (empty($costo)) {
                    $errores[] = [
                        'fila' => $key + 1,
                        'texto' => 'El registro no tiene costo, complete el campo',
                        'datos' => $row
                    ];
                    continue;
                }

                $nuevo = new ClienteServicio();
                $nuevo->usuario_creador_id = $usuario->id;
                $nuevo->grupo_cliente_id   = $grupo_cliente_id;
                $nuevo->item = $item;
                $nuevo->categoria = $categoria;
                $nuevo->sub_categoria = $sub_categoria;
                $nuevo->nombre = $servicio;
                $nuevo->costo = $costo;
                $nuevo->unidad_medida = $unidad_medida;
                $nuevo->cantidad = $cantidad;
                $nuevo->total = $total;
                $nuevo->save();

                $contador++;

            }

            if (count($errores) > 0) {
                return [
                    'estado' => 'warning',
                    'titulo' => $contador == 0 ? 'ERROR!' : 'SE REGISTRO, PERO HAY OBSERVACIONES',
                    'text'   => 'Algunos registros no pudieron ser importados',
                    'errores'=> $errores
                ];
            }

            if($contador == 0){
                return ['estado' => 'error', 'text' => 'No se realizo ningun registro, revise su archivo excel.'];
            }

            return ['estado' => 'success', 'text' => 'Productos importados correctamente'];
        }

        return ['estado' => 'error', 'text' => 'Petición inválida'];
    }
}
