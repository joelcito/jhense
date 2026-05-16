<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteServicio;
use App\Models\Consulta;
use App\Models\Grupo;
use App\Models\GrupoCliente;
use App\Models\InformeDiagnostico;
use App\Models\OrdenRecepcion;
use App\Models\Sucursal;
use App\Models\User;
use App\Utils\Respuesta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GrupoClienteController extends Controller
{
    public function listado($sucursal_id, $grupo_id)
    {
        $sucursal = Sucursal::find($sucursal_id);
        $grupo = Grupo::find($grupo_id);
        $clientes = Cliente::where('sucursal_id', $sucursal_id)->get();

        return view('grupoCliente.listado', compact('sucursal', 'grupo', 'clientes'));
    }

    public function ajaxListado(Request $request)
    {
        if ($request->ajax()) {
            $sucursal_id = $request->input('sucursal_id');
            $grupo_id = $request->input('grupo_id');
            $grupos = GrupoCliente::with('cliente')
                ->where('grupo_id', $grupo_id)
                ->whereHas('cliente', function ($query) use ($sucursal_id) {
                    $query->where('sucursal_id', $sucursal_id);
                })
                ->get();
            $valores = [
                'listado' => view('grupoCliente.ajaxListado')->with(compact('grupos'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarGrupo(Request $request)
    {
        if ($request->ajax()) {

            $request->validate([
                'grupo_id' => 'required',
                'cliente_id' => 'required',
            ]);

            $id = $request->input('id');

            $grupo_id  = $request->input('grupo_id');
            $cliente_id  = $request->input('cliente_id');
            $monto_inicio  = $request->input('monto_inicio');
            $usuario = Auth::user();

            if ($id == 0) {
                $grupo                     = new GrupoCliente();
                $grupo->usuario_creador_id = $usuario->id;
            } else {
                $grupo = GrupoCliente::find($id);
                $grupo->usuario_modificador_id = $usuario->id;
            }

            $grupo->grupo_id           = $grupo_id;
            $grupo->cliente_id     = $cliente_id;
            $grupo->monto_inicio = $monto_inicio;
            $grupo->save();

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function eliminarGrupo(Request $request)
    {
        if ($request->ajax()) {

            $id = $request->input('id');
            $usuario = Auth::user();

            $grupo = GrupoCliente::find($id);
            $grupo->usuario_eliminador_id = $usuario->id;
            $grupo->save();

            GrupoCliente::destroy($id);

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    //DETALLE
    public function detalle($grupo_cliente_id)
    {
        $grupoCliente = GrupoCliente::with('cliente.autos.marca')->find($grupo_cliente_id);
        $sucursal = Sucursal::find($grupoCliente->cliente->sucursal_id);
        $grupo = Grupo::find($grupoCliente->grupo_id);

        $ordenesRecepcion = OrdenRecepcion::where('grupo_cliente_id', $grupo_cliente_id)->get();

        // Obtener mecánicos de la sucursal (rol_id = 4)
        $mecanicos = User::where('sucursal_id', $sucursal->id)->where('rol_id', 4)->get();

        // Obtener consultas para el autocompletado de diagnóstico
        $consultas = Consulta::where('estado', 'ACTIVO')->orWhereNull('estado')->get();

        return view('grupoCliente.detalle', compact('sucursal', 'grupo', 'grupoCliente', 'ordenesRecepcion', 'mecanicos', 'consultas'));
    }

    public function ajaxDetalle(Request $request)
    {
        if ($request->ajax()) {
            $grupo_cliente_id = $request->input('grupo_cliente_id');
            $grupoCliente = GrupoCliente::with('servicios')->find($grupo_cliente_id);
            $servicios = $grupoCliente->servicios;
            $valores = [
                'listado' => view('grupoCliente.ajaxDetalle')->with(compact('grupoCliente', 'servicios'))->render()
            ];
            $data = Respuesta::success($valores, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "Error al obtener los datos");
        }
        return $data;
    }

    public function guardarItemRangos(Request $request)
    {
        if ($request->ajax()) {

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

            $grupo = GrupoCliente::with(['servicios', 'servicios' => function ($query) use ($item_1, $item_2) {
                $query->whereBetween('item', [$item_1, $item_2]);
            }])
                ->find($grupo_cliente_id);

            $serviciosEnRango = $grupo->servicios;
            if (count($serviciosEnRango) == 0) {
                return Respuesta::error(null, "No hay servicios en el rango de items seleccionado");
            }

            foreach ($serviciosEnRango as $servicio) {
                $servicio->usuario_modificador_id = $usuario->id;
                $servicio->categoria = $categoria;
                $servicio->save();
            }

            $data = Respuesta::success(null, "Datos obtenidos correctamente");
        } else {
            $data = Respuesta::error(null, "No existe");
        }
        return $data;
    }

    public function descargarFormatoImportarExcel(Request $request)
    {
        if ($request->ajax()) {
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

            $encabezadoStyle = [
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
            for ($i = $contadorCeldas; $i <= 1000; $i++) {

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
            $hoja->getStyle('A3:H' . ($contadorCeldas - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            // Establecer los encabezados para forzar la descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');

            // Guardar el archivo
            $writer = new Xlsx($libro);
            $writer->save('php://output');
            exit;
        } else {
            $data['text']   = 'No existe';
            $data['estado'] = 'error';
        }
        return $data;
    }

    public function importarServiciosExcel(Request $request)
    {
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

                $categoria = $row[0]; // CATEGORIA
                $sub_categoria = $row[1]; // CATEGORIA
                $item = (int)$row[2]; //ITEM
                $servicio = $row[3]; //SERVICIO
                $unidad_medida = $row[4]; //UNIDAD DE MEDIDA
                $cantidad = (float)$row[5]; //CANTIDAD
                $costo   = (float)$row[6]; //COSTO
                $total   = (float)$row[7]; //TOTAL

                if (empty($item) && empty($categoria) && empty($sub_categoria) && empty($servicio) && empty($costo) && empty($unidad_medida) && empty($cantidad) && empty($total)) {
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

                if ($item > 0) {
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
                    'errores' => $errores
                ];
            }

            if ($contador == 0) {
                return ['estado' => 'error', 'text' => 'No se realizo ningun registro, revise su archivo excel.'];
            }

            return ['estado' => 'success', 'text' => 'Productos importados correctamente'];
        }

        return ['estado' => 'error', 'text' => 'Petición inválida'];
    }

    public function guardarOrdenRecepcion(Request $request)
    {
        if ($request->ajax()) {
            $request->validate([
                'grupo_cliente_id' => 'required',
                'auto_id' => 'required',
                'fecha_recepcion' => 'required',
                'tipo_unidad' => 'required',
            ]);

            $usuario = Auth::user();

            $orden_id = $request->input('orden_recepcion_id');

            if ($orden_id) {
                $orden = OrdenRecepcion::find($orden_id);
                $orden->usuario_modificador_id = $usuario->id;
            } else {
                $orden = new OrdenRecepcion();
                $orden->usuario_creador_id = $usuario->id;
            }

            $orden->grupo_cliente_id = $request->input('grupo_cliente_id');
            $orden->auto_id = $request->input('auto_id');
            $orden->fecha_recepcion = $request->input('fecha_recepcion');
            $orden->tipo_unidad = $request->input('tipo_unidad');
            $orden->kilometraje = $request->input('kilometraje');
            $orden->objeto_contratacion = $request->input('objeto_contratacion');
            $orden->porcentaje_combustible = $request->input('porcentaje_combustible');
            $orden->observacion_general = $request->input('observacion_general');

            // Procesar el checklist array
            $orden->checklist = json_encode($request->input('checklist', []));

            $orden->save();

            return response()->json([
                'estado' => true,
                'orden_recepcion_id' => $orden->id
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerOrdenRecepcion(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');
            $orden = OrdenRecepcion::find($orden_id);
            if ($orden) {
                return response()->json(['estado' => true, 'orden' => $orden]);
            }
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfOrdenRecepcion($id)
    {
        $orden = OrdenRecepcion::with(['grupoCliente.cliente', 'auto.marca'])->findOrFail($id);

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfOrdenRecepcion', compact('orden'));

        return $pdf->download('Orden_Recepcion_' . $orden->id . '.pdf');
    }

    public function descargarExcelOrdenRecepcion($id)
    {
        $orden = OrdenRecepcion::with(['grupoCliente.cliente', 'auto.marca'])->findOrFail($id);

        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();

        $hoja->setTitle('Orden de Recepción');

        // Encabezado
        $hoja->setCellValue('A1', 'ORDEN DE RECEPCIÓN DE VEHÍCULOS');
        $hoja->mergeCells('A1:D1');
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $hoja->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $hoja->setCellValue('A3', 'Fecha Solicitud:');
        $hoja->setCellValue('B3', Carbon::parse($orden->fecha_recepcion)->format('d/m/Y'));
        $hoja->setCellValue('C3', 'Placa - Chasis:');
        $hoja->setCellValue('D3', ($orden->auto->placa ?? '') . ' - ' . ($orden->auto->vin ?? ''));

        $hoja->setCellValue('A4', 'Empresa - Cliente:');
        $hoja->setCellValue('B4', $orden->grupoCliente->cliente->nombres . ' ' . $orden->grupoCliente->cliente->ap_paterno . ' ' . ($orden->grupoCliente->cliente->razon_social ?? ''));
        $hoja->setCellValue('C4', 'Clase de vehículo:');
        $hoja->setCellValue('D4', $orden->tipo_unidad);

        $hoja->setCellValue('A5', 'NIT - C.I.:');
        $hoja->setCellValue('B5', $orden->grupoCliente->cliente->nit ?? $orden->grupoCliente->cliente->cedula);
        $hoja->setCellValue('C5', 'Marca - Tipo:');
        $hoja->setCellValue('D5', ($orden->auto->marca->nombre ?? '') . ' - ' . ($orden->auto->modelo ?? ''));

        $hoja->setCellValue('A6', 'Contacto de ref.:');
        $hoja->setCellValue('B6', $orden->grupoCliente->cliente->numero_celular);
        $hoja->setCellValue('C6', 'Kilometraje:');
        $hoja->setCellValue('D6', $orden->kilometraje);

        $hoja->setCellValue('A7', 'Teléfono - Correo:');
        $hoja->setCellValue('B7', $orden->grupoCliente->cliente->correo);
        $hoja->setCellValue('C7', 'Objeto de la contratación:');
        $hoja->setCellValue('D7', $orden->objeto_contratacion);

        $hoja->setCellValue('C8', 'Porcentaje de combustible (%):');
        $hoja->setCellValue('D8', $orden->porcentaje_combustible);

        $hoja->getStyle('A3:A7')->getFont()->setBold(true);
        $hoja->getStyle('C3:C8')->getFont()->setBold(true);

        // Checklist
        $hoja->setCellValue('A10', 'DESCRIPCIÓN DE ELEMENTOS');
        $hoja->mergeCells('A10:D10');
        $hoja->getStyle('A10')->getFont()->setBold(true);
        $hoja->getStyle('A10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('A10')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE0B2');

        $elementos = [
            'cenicero' => 'CENICERO',
            'encendedor' => 'ENCENDEDOR',
            'manual' => 'MANUAL',
            'espejos' => 'ESPEJOS',
            'radio' => 'RADIO',
            'pisos' => 'PISOS',
            'control_alarma' => 'CONTROL ALARMA',
            'limpia_parabrisas' => 'LIMPIA PARABRISAS',
            'herramientas' => 'HERRAMIENTAS',
            'gato' => 'GATO',
            'llave_rueda' => 'LLAVE DE RUEDA',
            'arresta_llama' => 'ARRESTA LLAMA',
            'auxilio' => 'AUXILIO',
            'varilla' => 'VARILLA',
            'tapacubos' => 'TAPACUBOS',
            'halogenos' => 'HALOGENOS',
            'antena' => 'ANTENA',
            'tapa_combustible' => 'TAPA COMBUSTIBLE',
            'extintor' => 'EXTINTOR',
            'triangulo' => 'TRIANGULO',
            'llave_seguridad' => 'LLAVE DE SEGURIDAD',
            'placas' => 'PLACAS',
            'botiquin' => 'BOTIQUIN',
            'usb' => 'USB'
        ];

        $checklist = is_string($orden->checklist) ? json_decode($orden->checklist, true) : $orden->checklist;
        if (!is_array($checklist)) $checklist = [];

        $col1 = array_slice($elementos, 0, 12, true);
        $col2 = array_slice($elementos, 12, 12, true);

        $keys1 = array_keys($col1);
        $keys2 = array_keys($col2);

        $row = 11;
        for ($i = 0; $i < 12; $i++) {
            $k1 = $keys1[$i];
            $k2 = $keys2[$i];

            $hoja->setCellValue('A' . $row, $col1[$k1]);
            $hoja->setCellValue('B' . $row, $checklist[$k1] ?? 'N/A');
            $hoja->setCellValue('C' . $row, $col2[$k2]);
            $hoja->setCellValue('D' . $row, $checklist[$k2] ?? 'N/A');
            $row++;
        }

        // Bordes checklist
        $hoja->getStyle('A11:D22')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $row++;
        $hoja->setCellValue('A' . $row, 'OBSERVACIÓN GENERAL');
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $hoja->setCellValue('A' . $row, $orden->observacion_general);
        $hoja->mergeCells('A' . $row . ':D' . ($row + 2));
        $hoja->getStyle('A' . $row)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach (range('A', 'D') as $col) {
            $hoja->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Orden_Recepcion_' . $orden->id . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    public function guardarInformeDiagnostico(Request $request)
    {
        if ($request->ajax()) {
            $informe_id = $request->input('informe_diagnostico_id');
            $usuario = Auth::user();

            if ($informe_id) {
                $informe = InformeDiagnostico::find($informe_id);
                $informe->usuario_modificador_id = $usuario->id;
            } else {
                $informe = new InformeDiagnostico();
                $informe->usuario_creador_id = $usuario->id;
                $informe->orden_recepcion_id = $request->input('orden_recepcion_id');
            }

            $informe->mecanico_id = $request->input('mecanico_id');
            $informe->inspeccion_exterior_texto = $request->input('inspeccion_exterior_texto');
            $informe->inspeccion_interior_texto = $request->input('inspeccion_interior_texto');

            // Handle main images
            if ($request->hasFile('inspeccion_exterior_imagen')) {
                $file = $request->file('inspeccion_exterior_imagen');
                $filename = time() . '_ext_' . $file->getClientOriginalName();
                $path = $file->storeAs('diagnostico', $filename, 'public');
                $informe->inspeccion_exterior_imagen = 'storage/' . $path;
            }

            if ($request->hasFile('inspeccion_interior_imagen')) {
                $file = $request->file('inspeccion_interior_imagen');
                $filename = time() . '_int_' . $file->getClientOriginalName();
                $path = $file->storeAs('diagnostico', $filename, 'public');
                $informe->inspeccion_interior_imagen = 'storage/' . $path;
            }

            // Process dynamic table for diagnosticos
            $diagnosticos = [];
            $componentes = $request->input('diag_componente', []);
            $sintomas = $request->input('diag_sintoma', []);
            $tipo_fallas = $request->input('diag_tipo_falla', []);
            $causas = $request->input('diag_causa_probable', []);
            $estados = $request->input('diag_estado_actual', []);
            $recomendaciones = $request->input('diag_recomendacion', []);
            $riesgos = $request->input('diag_riesgo_asociado', []);
            $justificaciones = $request->input('diag_justificacion', []);

            $imagenes_nuevas = $request->file('diagnostico_imagenes') ?? [];
            $imagenes_existentes = $request->input('diag_imagen_existente', []);

            foreach ($componentes as $key => $componente) {
                if (empty($componente)) continue; // skip empty rows

                $img_path = $imagenes_existentes[$key] ?? '';

                // If a new image was uploaded for this row
                if (isset($imagenes_nuevas[$key])) {
                    $file = $imagenes_nuevas[$key];
                    $filename = time() . '_diag_' . $key . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('diagnostico', $filename, 'public');
                    $img_path = 'storage/' . $path;
                }

                $diagnosticos[] = [
                    'componente' => $componente,
                    'sintoma' => $sintomas[$key] ?? '',
                    'tipo_falla' => $tipo_fallas[$key] ?? '',
                    'causa_probable' => $causas[$key] ?? '',
                    'estado_actual' => $estados[$key] ?? '',
                    'recomendacion' => $recomendaciones[$key] ?? '',
                    'riesgo_asociado' => $riesgos[$key] ?? '',
                    'justificacion' => $justificaciones[$key] ?? '',
                    'imagen_path' => $img_path,
                    'imagen_url' => $img_path ? asset($img_path) : ''
                ];
            }

            $informe->diagnosticos = json_encode($diagnosticos);
            $informe->save();

            return response()->json([
                'estado' => true,
                'informe_diagnostico_id' => $informe->id
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerInformeDiagnostico(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');
            $informe = InformeDiagnostico::where('orden_recepcion_id', $orden_id)->first();

            if ($informe) {
                $informe->inspeccion_exterior_imagen_url = $informe->inspeccion_exterior_imagen ? asset($informe->inspeccion_exterior_imagen) : null;
                $informe->inspeccion_interior_imagen_url = $informe->inspeccion_interior_imagen ? asset($informe->inspeccion_interior_imagen) : null;
                $informe->diagnosticos = is_string($informe->diagnosticos) ? json_decode($informe->diagnosticos, true) : $informe->diagnosticos;
                return response()->json(['estado' => true, 'informe' => $informe]);
            }
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfInformeDiagnostico($orden_id)
    {
        $informe = InformeDiagnostico::with(['mecanico', 'ordenRecepcion.grupoCliente.cliente', 'ordenRecepcion.auto.marca'])
            ->where('orden_recepcion_id', $orden_id)->firstOrFail();

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfInformeDiagnostico', compact('informe'));

        return $pdf->download('Informe_Diagnostico_' . $informe->id . '.pdf');
    }

    public function descargarExcelInformeDiagnostico($orden_id)
    {
        $informe = InformeDiagnostico::with(['mecanico', 'ordenRecepcion.grupoCliente.cliente', 'ordenRecepcion.auto.marca'])
            ->where('orden_recepcion_id', $orden_id)->firstOrFail();

        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();
        $hoja->setTitle('Informe de Diagnóstico');

        // Estilos
        $boldCenter = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $bold = ['font' => ['bold' => true]];

        // Cabecera
        $hoja->setCellValue('A1', 'JHENSE - INFORME TÉCNICO DE DIAGNOSTICO N°: ' . $informe->id);
        $hoja->mergeCells('A1:H1');
        $hoja->getStyle('A1')->applyFromArray($boldCenter);

        $hoja->setCellValue('A3', 'Ref.: Informe de Inspección y Evaluación Técnica');
        $hoja->mergeCells('A3:D3');
        $hoja->getStyle('A3')->getFont()->setItalic(true);

        $hoja->setCellValue('A5', 'Elaborado por:');
        $hoja->setCellValue('B5', ($informe->mecanico->nombres ?? '') . ' ' . ($informe->mecanico->ap_paterno ?? ''));
        $hoja->setCellValue('A6', 'Destinado a:');
        $hoja->setCellValue('B6', ($informe->ordenRecepcion->grupoCliente->cliente->nombres ?? '') . ' ' . ($informe->ordenRecepcion->grupoCliente->cliente->ap_paterno ?? ''));
        $hoja->setCellValue('A7', 'Fecha:');
        $hoja->setCellValue('B7', Carbon::parse($informe->created_at)->format('d/m/Y'));

        $hoja->getStyle('A5:A7')->applyFromArray($bold);

        // 1. Antecedentes
        $hoja->setCellValue('A9', '1. Antecedentes');
        $hoja->getStyle('A9')->applyFromArray($bold);
        $hoja->setCellValue('A10', 'De acuerdo al ingreso a taller JHENSE del vehículo con placa de circulación: ' . ($informe->ordenRecepcion->auto->placa ?? ''));

        // 3.1 Datos Generales del Vehículo
        $hoja->setCellValue('A12', '3.1 Datos Generales del Vehículo');
        $hoja->getStyle('A12')->applyFromArray($bold);

        $hoja->setCellValue('A13', 'Clase:');
        $hoja->setCellValue('B13', $informe->ordenRecepcion->tipo_unidad ?? '');
        $hoja->setCellValue('A14', 'Marca:');
        $hoja->setCellValue('B14', $informe->ordenRecepcion->auto->marca->nombre ?? '');
        $hoja->setCellValue('A15', 'Tipo:');
        $hoja->setCellValue('B15', $informe->ordenRecepcion->auto->modelo ?? '');
        $hoja->setCellValue('A16', 'Kilometraje:');
        $hoja->setCellValue('B16', $informe->ordenRecepcion->kilometraje ?? '');

        $hoja->getStyle('A13:A16')->applyFromArray($bold);

        // 3.2 y 3.3
        $hoja->setCellValue('A18', '3.2 Inspección Exterior');
        $hoja->getStyle('A18')->applyFromArray($bold);
        $hoja->setCellValue('A19', $informe->inspeccion_exterior_texto);

        $hoja->setCellValue('A21', '3.3 Inspección Interior');
        $hoja->getStyle('A21')->applyFromArray($bold);
        $hoja->setCellValue('A22', $informe->inspeccion_interior_texto);

        // 3.4 Diagnóstico
        $hoja->setCellValue('A24', '3.4 Diagnóstico del Estado Automotriz');
        $hoja->getStyle('A24')->applyFromArray($bold);

        $headers = ['Componente', 'Síntoma', 'Tipo de falla', 'Causa probable', 'Estado Actual', 'Recomendación', 'Riesgos asociados', 'Justificación'];
        $col = 'A';
        foreach ($headers as $h) {
            $hoja->setCellValue($col . '25', $h);
            $col++;
        }
        $hoja->getStyle('A25:H25')->applyFromArray($bold);
        $hoja->getStyle('A25:H25')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $diagnosticos = is_string($informe->diagnosticos) ? json_decode($informe->diagnosticos, true) : $informe->diagnosticos;
        if (!is_array($diagnosticos)) $diagnosticos = [];

        $row = 26;
        foreach ($diagnosticos as $diag) {
            $hoja->setCellValue('A' . $row, $diag['componente'] ?? '');
            $hoja->setCellValue('B' . $row, $diag['sintoma'] ?? '');
            $hoja->setCellValue('C' . $row, $diag['tipo_falla'] ?? '');
            $hoja->setCellValue('D' . $row, $diag['causa_probable'] ?? '');
            $hoja->setCellValue('E' . $row, $diag['estado_actual'] ?? '');
            $hoja->setCellValue('F' . $row, $diag['recomendacion'] ?? '');
            $hoja->setCellValue('G' . $row, $diag['riesgo_asociado'] ?? '');
            $hoja->setCellValue('H' . $row, $diag['justificacion'] ?? '');
            $row++;
        }

        if (count($diagnosticos) > 0) {
            $hoja->getStyle('A26:H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        foreach (range('A', 'H') as $columnID) {
            $hoja->getColumnDimension($columnID)->setAutoSize(true);
        }

        $fileName = 'Informe_Diagnostico_' . $informe->id . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($libro);
        $writer->save('php://output');
        exit;
    }
}
