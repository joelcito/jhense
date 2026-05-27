<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteServicio;
use App\Models\Consulta;
use App\Models\Cotizacion;
use App\Models\OrdenTrabajo;
use App\Models\FormularioAutorizacion;
use App\Models\RecepcionRepuesto;
use App\Models\ReporteFotografico;
use App\Models\ActaEntrega;
use App\Models\ActaDevolucionRepuesto;
use App\Models\FormularioDiagnostico;
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

class GrupoClienteController extends Controller {

    
    
    private function _agregarLogosExcel($hoja, $orden, $lastCol, $titleRow = 1)
    {
        // Adjust the row height where the title is located
        $hoja->getRowDimension($titleRow)->setRowHeight(65);
        $hoja->getStyle("A" . $titleRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM);

        $sucursalLogo = null;
        if(isset($orden) && isset($orden->grupoCliente->cliente->sucursal->logo)){
            $sucursalLogo = $orden->grupoCliente->cliente->sucursal->logo;
        }

        // The left logo usually goes in column A
        if ($sucursalLogo && file_exists(storage_path("app/public/" . $sucursalLogo))) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName("Logo Sucursal");
            $drawing->setPath(storage_path("app/public/" . $sucursalLogo));
            $drawing->setHeight(55);
            $drawing->setCoordinates("A" . $titleRow);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($hoja);
        }

        // The right logo goes in $lastCol
        $logoJhensePath = public_path("assets/imagenes/logo_jhense.png");
        if(file_exists($logoJhensePath)){
            $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing2->setName("Logo General");
            $drawing2->setPath($logoJhensePath);
            $drawing2->setHeight(55);
            $drawing2->setCoordinates($lastCol . $titleRow);
            $drawing2->setOffsetX(5);
            $drawing2->setOffsetY(5);
            $drawing2->setWorksheet($hoja);
        }
    }



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
        $grupoCliente = GrupoCliente::with(['cliente.autos.marca', 'servicios'])->find($grupo_cliente_id);
        $sucursal = Sucursal::find($grupoCliente->cliente->sucursal_id);
        $grupo = Grupo::find($grupoCliente->grupo_id);

        // Catálogo improvisado de servicios (todos los únicos registrados en el sistema)
        $catalogoServicios = ClienteServicio::select('nombre', 'categoria', 'unidad_medida', 'costo')
            ->whereNotNull('nombre')
            ->groupBy('nombre', 'categoria', 'unidad_medida', 'costo')
            ->get();

        // Obtener mecánicos de la sucursal (rol_id = 4)
        $mecanicos = User::where('sucursal_id', $sucursal->id)->where('rol_id', 4)->get();

        // Obtener consultas para el autocompletado de diagnóstico
        $ordenesRecepcion = OrdenRecepcion::with(['usuarioCreador', 'usuarioModificador', 'auto.marca'])
            ->where('grupo_cliente_id', $grupo_cliente_id)
            ->get();

        $consultas = Consulta::all();

        return view('grupoCliente.detalle', compact('grupoCliente', 'sucursal', 'grupo', 'ordenesRecepcion', 'mecanicos', 'consultas', 'catalogoServicios'));
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
            $orden = OrdenRecepcion::with(['auto.marca'])->find($orden_id);
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

        $fileName = $this->generarNombreArchivo('Orden de Recepcion', $orden);
        return $pdf->download($fileName . '.pdf');
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

        $fileName = $this->generarNombreArchivo('Orden de Recepcion', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $this->_agregarLogosExcel($hoja, $orden, 'D');
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

        $orden = $informe->ordenRecepcion;
        $pdf = Pdf::loadView('grupoCliente.formularios.pdfInformeDiagnostico', compact('informe'));

        $fileName = $this->generarNombreArchivo('Informe de Diagnostico', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelInformeDiagnostico($orden_id)
    {
        $informe = InformeDiagnostico::with(['mecanico', 'ordenRecepcion.grupoCliente.cliente', 'ordenRecepcion.auto.marca'])
            ->where('orden_recepcion_id', $orden_id)->firstOrFail();
        $orden = $informe->ordenRecepcion;

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

        $fileName = $this->generarNombreArchivo('Informe de Diagnostico', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $this->_agregarLogosExcel($hoja, ($informe->ordenRecepcion ?? null), 'H');
        $writer = new Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    public function guardarFormularioDiagnostico(Request $request)
    {
        if ($request->ajax()) {
            $form_id = $request->input('formulario_diagnostico_id');
            $usuario = Auth::user();

            if ($form_id) {
                $form = FormularioDiagnostico::find($form_id);
                $form->usuario_modificador_id = $usuario->id;
            } else {
                $form = new FormularioDiagnostico();
                $form->usuario_creador_id = $usuario->id;
                $form->orden_recepcion_id = $request->input('orden_recepcion_id');
            }

            $form->responsable_vehiculo = $request->input('responsable_vehiculo');
            $form->vehiculo_asignado_a = $request->input('vehiculo_asignado_a');
            $form->recepcion_taller = $request->input('recepcion_taller');

            // Sanitize arrays (remove empty values)
            $prev = array_values(array_filter($request->input('preventivos', []), 'strlen'));
            $corr = array_values(array_filter($request->input('correctivos', []), 'strlen'));
            $otro = array_values(array_filter($request->input('otros', []), 'strlen'));

            $form->servicios_preventivos = json_encode($prev);
            $form->servicios_correctivos = json_encode($corr);
            $form->servicios_otros = json_encode($otro);

            $form->save();

            return response()->json([
                'estado' => true,
                'formulario_diagnostico_id' => $form->id
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerFormularioDiagnostico(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');
            $form = FormularioDiagnostico::where('orden_recepcion_id', $orden_id)->first();

            $orden = OrdenRecepcion::find($orden_id);
            $checklist = $orden ? (is_string($orden->checklist) ? json_decode($orden->checklist, true) : $orden->checklist) : null;

            if ($form) {
                $form->servicios_preventivos = is_string($form->servicios_preventivos) ? json_decode($form->servicios_preventivos, true) : $form->servicios_preventivos;
                $form->servicios_correctivos = is_string($form->servicios_correctivos) ? json_decode($form->servicios_correctivos, true) : $form->servicios_correctivos;
                $form->servicios_otros = is_string($form->servicios_otros) ? json_decode($form->servicios_otros, true) : $form->servicios_otros;

                return response()->json(['estado' => true, 'formulario' => $form, 'checklist' => $checklist]);
            }

            // Si no hay form, devolvemos checklist de todos modos para que el panel derecho se arme
            return response()->json(['estado' => true, 'formulario' => null, 'checklist' => $checklist]);
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfFormularioDiagnostico($orden_id)
    {
        $form = FormularioDiagnostico::where('orden_recepcion_id', $orden_id)->firstOrFail();
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($orden_id);

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfFormularioDiagnostico', compact('form', 'orden'));

        $fileName = $this->generarNombreArchivo('Formulario de Diagnostico', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelFormularioDiagnostico($orden_id)
    {
        $form = FormularioDiagnostico::where('orden_recepcion_id', $orden_id)->firstOrFail();
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($orden_id);

        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();
        $hoja->setTitle('Formulario Diagnóstico');

        // Estilos
        $bold = ['font' => ['bold' => true]];
        $center = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $borderThin = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        // Dimensiones
        $hoja->getColumnDimension('A')->setWidth(20);
        $hoja->getColumnDimension('B')->setWidth(30);
        $hoja->getColumnDimension('C')->setWidth(20);
        $hoja->getColumnDimension('D')->setWidth(30);
        $hoja->getColumnDimension('E')->setWidth(5);
        $hoja->getColumnDimension('F')->setWidth(30);
        $hoja->getColumnDimension('G')->setWidth(10);

        // Header
        $hoja->setCellValue('A1', 'JHENSE');
        $hoja->mergeCells('A1:B2');
        $hoja->getStyle('A1:B2')->applyFromArray($center)->applyFromArray($bold)->applyFromArray($borderThin);

        $hoja->setCellValue('C1', "FORMULARIO PARA DIAGNOSTICO DE\nMANTENIMIENTO DE VEHICULOS");
        $hoja->mergeCells('C1:D2');
        $hoja->getStyle('C1:D2')->applyFromArray($center)->applyFromArray($bold)->applyFromArray($borderThin);
        $hoja->getStyle('C1')->getAlignment()->setWrapText(true);

        $hoja->setCellValue('E1', 'RG-01-B-PP-1-DAC/UTR-2 CITE: 001');
        $hoja->mergeCells('E1:G1');
        $hoja->getStyle('E1:G1')->applyFromArray($center)->applyFromArray($borderThin);

        $hoja->setCellValue('E2', 'SCZ, ' . Carbon::parse($form->created_at)->format('d \d\e F \d\e Y'));
        $hoja->mergeCells('E2:G2');
        $hoja->getStyle('E2:G2')->applyFromArray($center)->applyFromArray($borderThin);

        $hoja->setCellValue('A3', 'DIRECCION DE ADMINISTRACION CORPORATIVA – DAC');
        $hoja->mergeCells('A3:G3');
        $hoja->getStyle('A3')->applyFromArray($center)->applyFromArray($bold)->applyFromArray($borderThin)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');

        // General Data
        $hoja->setCellValue('A4', 'DATOS GENERALES');
        $hoja->mergeCells('A4:G4');
        $hoja->getStyle('A4:G4')->applyFromArray($bold)->applyFromArray($borderThin);

        $hoja->setCellValue('A5', 'RESPONSABLE DE VEHICULO');
        $hoja->setCellValue('B5', $form->responsable_vehiculo);
        $hoja->mergeCells('B5:G5');

        $hoja->setCellValue('A6', 'VEHICULO ASIGNADO A');
        $hoja->setCellValue('B6', $form->vehiculo_asignado_a);
        $hoja->mergeCells('B6:G6');

        $hoja->getStyle('A5:G6')->applyFromArray($borderThin);

        // Vehicle Data
        $hoja->setCellValue('A7', 'CARACTERISTICAS DEL VEHICULO');
        $hoja->mergeCells('A7:G7');
        $hoja->getStyle('A7:G7')->applyFromArray($bold)->applyFromArray($borderThin);

        $hoja->setCellValue('A8', 'AÑO');
        $hoja->setCellValue('B8', $orden->auto->anio ?? '');
        $hoja->setCellValue('C8', 'MARCA');
        $hoja->setCellValue('D8', $orden->auto->marca->nombre ?? '');

        $hoja->setCellValue('A9', 'CLASE');
        $hoja->setCellValue('B9', $orden->tipo_unidad ?? '');
        $hoja->setCellValue('C9', 'PLACA');
        $hoja->setCellValue('D9', $orden->auto->placa ?? '');

        $hoja->setCellValue('A10', 'TIPO');
        $hoja->setCellValue('B10', $orden->auto->tipo ?? '');
        $hoja->setCellValue('C10', 'MODELO');
        $hoja->setCellValue('D10', $orden->auto->modelo ?? '');

        $hoja->setCellValue('A11', 'Km/Mlls');
        $hoja->setCellValue('B11', $orden->kilometraje ?? '');
        $hoja->setCellValue('C11', 'MOTOR');
        $hoja->setCellValue('D11', $orden->auto->nro_motor ?? '');

        $hoja->setCellValue('C12', 'CHASIS');
        $hoja->setCellValue('D12', $orden->auto->nro_chasis ?? '');

        $hoja->getStyle('A8:D12')->applyFromArray($borderThin);

        // Requerimiento Servicio & Inventario Header
        $hoja->setCellValue('A14', 'REQUERIMIENTO DE SERVICIO');
        $hoja->mergeCells('A14:D14');
        $hoja->getStyle('A14:D14')->applyFromArray($center)->applyFromArray($bold)->applyFromArray($borderThin)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');

        $hoja->setCellValue('F14', 'INVENTARIO');
        $hoja->mergeCells('F14:G14');
        $hoja->getStyle('F14:G14')->applyFromArray($center)->applyFromArray($bold)->applyFromArray($borderThin)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');

        // Preventivos
        $row = 15;
        $hoja->setCellValue('A' . $row, 'Mantenimiento Preventivo');
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($bold)->applyFromArray($borderThin);
        $row++;

        $preventivos = is_string($form->servicios_preventivos) ? json_decode($form->servicios_preventivos, true) : $form->servicios_preventivos;
        if (!is_array($preventivos) || count($preventivos) == 0) $preventivos = array_fill(0, 5, '');

        foreach ($preventivos as $index => $item) {
            $hoja->setCellValue('A' . $row, $index + 1);
            $hoja->setCellValue('B' . $row, $item);
            $hoja->mergeCells('B' . $row . ':D' . $row);
            $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
            $hoja->getStyle('A' . $row)->applyFromArray($center)->applyFromArray($bold);
            $row++;
        }

        // Correctivos
        $hoja->setCellValue('A' . $row, 'Mantenimiento Correctivo');
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($bold)->applyFromArray($borderThin);
        $row++;

        $correctivos = is_string($form->servicios_correctivos) ? json_decode($form->servicios_correctivos, true) : $form->servicios_correctivos;
        if (!is_array($correctivos) || count($correctivos) == 0) $correctivos = array_fill(0, 5, '');

        foreach ($correctivos as $index => $item) {
            $hoja->setCellValue('A' . $row, $index + 1);
            $hoja->setCellValue('B' . $row, $item);
            $hoja->mergeCells('B' . $row . ':D' . $row);
            $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
            $hoja->getStyle('A' . $row)->applyFromArray($center)->applyFromArray($bold);
            $row++;
        }

        // Otros
        $hoja->setCellValue('A' . $row, 'Otros servicios requeridos');
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($bold)->applyFromArray($borderThin);
        $row++;

        $otros = is_string($form->servicios_otros) ? json_decode($form->servicios_otros, true) : $form->servicios_otros;
        if (!is_array($otros) || count($otros) == 0) $otros = array_fill(0, 4, '');

        foreach ($otros as $index => $item) {
            $hoja->setCellValue('A' . $row, $index + 1);
            $hoja->setCellValue('B' . $row, $item);
            $hoja->mergeCells('B' . $row . ':D' . $row);
            $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
            $hoja->getStyle('A' . $row)->applyFromArray($center)->applyFromArray($bold);
            $row++;
        }

        // Firmas (left side)
        $hoja->setCellValue('A' . $row, "Solicita y Valida\nResponsable del Vehículo:\n\n\n\n");
        $hoja->mergeCells('A' . $row . ':B' . ($row + 3));
        $hoja->setCellValue('C' . $row, "Verifica y Aprueba\nFiscal de Servicio:\n\n\n\n");
        $hoja->mergeCells('C' . $row . ':D' . ($row + 3));

        $hoja->getStyle('A' . $row . ':D' . ($row + 3))->applyFromArray($borderThin)->applyFromArray($center);
        $hoja->getStyle('A' . $row . ':D' . ($row + 3))->getAlignment()->setWrapText(true);

        // Inventario (Right side, starting from row 15)
        $invRow = 15;
        $checklist = is_string($orden->checklist) ? json_decode($orden->checklist, true) : $orden->checklist;
        if (!is_array($checklist)) $checklist = [];

        foreach ($checklist as $key => $val) {
            if ($key !== 'firma_entrega') {
                $hoja->setCellValue('F' . $invRow, ucfirst(str_replace('_', ' ', $key)));
                $hoja->setCellValue('G' . $invRow, $val);
                $hoja->getStyle('F' . $invRow . ':G' . $invRow)->applyFromArray($borderThin);
                $hoja->getStyle('G' . $invRow)->applyFromArray($center)->applyFromArray($bold);
                $invRow++;
            }
        }

        // Recepcion Taller
        $invRow++;
        $hoja->setCellValue('F' . $invRow, 'Recepción del Taller');
        $hoja->mergeCells('F' . $invRow . ':G' . $invRow);
        $hoja->getStyle('F' . $invRow . ':G' . $invRow)->applyFromArray($center)->applyFromArray($bold)->applyFromArray($borderThin)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
        $invRow++;

        $hoja->setCellValue('F' . $invRow, $form->recepcion_taller ?? '');
        $hoja->mergeCells('F' . $invRow . ':G' . ($invRow + 4));
        $hoja->getStyle('F' . $invRow . ':G' . ($invRow + 4))->applyFromArray($borderThin);
        $hoja->getStyle('F' . $invRow)->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);


        $fileName = $this->generarNombreArchivo('Formulario de Diagnostico', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $this->_agregarLogosExcel($hoja, $orden, 'D');
        $writer = new Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    public function guardarCotizacion(Request $request)
    {
        if ($request->ajax()) {
            $cotizacion_id = $request->input('cotizacion_id');
            $usuario = Auth::user();

            if ($cotizacion_id) {
                $cotizacion = Cotizacion::find($cotizacion_id);
                $cotizacion->usuario_modificador_id = $usuario->id;
            } else {
                $cotizacion = new Cotizacion();
                $cotizacion->usuario_creador_id = $usuario->id;
                $cotizacion->orden_recepcion_id = $request->input('orden_recepcion_id');
            }

            $cotizacion->servicio_taller = $request->input('servicio_taller');
            $cotizacion->fecha_salida = $request->input('fecha_salida');
            $cotizacion->dias_habiles = $request->input('dias_habiles');

            // Arrays
            $prevs = array_values($request->input('preventivos', []));
            $corrs = array_values($request->input('correctivos', []));
            $reps = array_values($request->input('repuestos', []));

            $cotizacion->preventivos = json_encode($prevs);
            $cotizacion->correctivos = json_encode($corrs);
            $cotizacion->repuestos = json_encode($reps);

            $cotizacion->subtotal_preventivos = $request->input('subtotal_preventivos', 0);
            $cotizacion->subtotal_correctivos = $request->input('subtotal_correctivos', 0);
            $cotizacion->subtotal_repuestos = $request->input('subtotal_repuestos', 0);
            $cotizacion->total_general = $request->input('total_general', 0);

            $cotizacion->save();

            $this->sincronizarCotizacionYOT('COTIZACION', $cotizacion->orden_recepcion_id, $usuario->id);

            return response()->json([
                'estado' => true,
                'cotizacion_id' => $cotizacion->id
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerCotizacion(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');
            $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->first();
            $orden = OrdenRecepcion::find($orden_id);

            if ($cotizacion && $orden) {
                $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);
                return response()->json(['estado' => true, 'cotizacion' => $cotizacion]);
            }
            return response()->json(['estado' => true, 'cotizacion' => null]);
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfCotizacion($orden_id)
    {
        $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->firstOrFail();
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($orden_id);

        $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfCotizacion', compact('cotizacion', 'orden'));

        $fileName = $this->generarNombreArchivo('Cotizacion', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelCotizacion($orden_id)
    {
        $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->firstOrFail();
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($orden_id);

        $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);

        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();
        $hoja->setTitle('Cotización');

        // Styles
        $borderThin = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $center = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];
        $right = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]];

        // Column widths
        $hoja->getColumnDimension('A')->setWidth(10);
        $hoja->getColumnDimension('B')->setWidth(40);
        $hoja->getColumnDimension('C')->setWidth(15);
        $hoja->getColumnDimension('D')->setWidth(15);
        $hoja->getColumnDimension('E')->setWidth(15);
        $hoja->getColumnDimension('F')->setWidth(15);

        // Title
        $hoja->setCellValue('A1', 'COTIZACIÓN');
        $hoja->mergeCells('A1:F1');
        $hoja->getStyle('A1')->applyFromArray($titleStyle);

        // Header Information
        $hoja->setCellValue('A3', 'VEHÍCULOS:');
        $hoja->setCellValue('B3', ($orden->auto->marca->nombre ?? '') . ' ' . ($orden->auto->modelo ?? ''));
        $hoja->setCellValue('E3', 'ORDEN DE SERVICIO N°');
        $hoja->setCellValue('F3', $orden->id);

        $hoja->setCellValue('A4', 'PLACAS:');
        $hoja->setCellValue('B4', $orden->auto->placa ?? '');
        $hoja->setCellValue('E4', 'KILOMETRAJE ACTUAL:');
        $hoja->setCellValue('F4', $orden->kilometraje ?? '');

        $hoja->setCellValue('A5', 'EMPRESA:');
        $hoja->setCellValue('B5', ($orden->grupoCliente->cliente->nombres ?? '') . ' ' . ($orden->grupoCliente->cliente->ap_paterno ?? ''));
        $hoja->setCellValue('E5', 'FECHA DE INGRESO:');
        $hoja->setCellValue('F5', Carbon::parse($orden->created_at)->format('Y-m-d'));

        $hoja->setCellValue('A6', 'SERVICIO/TALLER:');
        $hoja->setCellValue('B6', $cotizacion->servicio_taller);
        $hoja->setCellValue('E6', 'FECHA DE SALIDA:');
        $hoja->setCellValue('F6', $cotizacion->fecha_salida);

        $hoja->setCellValue('E7', 'Días Hábiles:');
        $hoja->setCellValue('F7', $cotizacion->dias_habiles);

        $row = 9;

        $preventivos = is_string($cotizacion->preventivos) ? json_decode($cotizacion->preventivos, true) : $cotizacion->preventivos;
        $correctivos = is_string($cotizacion->correctivos) ? json_decode($cotizacion->correctivos, true) : $cotizacion->correctivos;
        $repuestos = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : $cotizacion->repuestos;

        $sections = [
            ['title' => 'MANTENIMIENTO PREVENTIVO', 'data' => $preventivos, 'subtotal' => $cotizacion->subtotal_preventivos],
            ['title' => 'MANTENIMIENTO CORRECTIVO', 'data' => $correctivos, 'subtotal' => $cotizacion->subtotal_correctivos],
            ['title' => 'REPUESTOS DE VEHÍCULOS', 'data' => $repuestos, 'subtotal' => $cotizacion->subtotal_repuestos]
        ];

        foreach ($sections as $section) {
            $hoja->setCellValue('A' . $row, 'ITEM');
            $hoja->setCellValue('B' . $row, $section['title']);
            $hoja->setCellValue('C' . $row, 'Cantidad');
            $hoja->setCellValue('D' . $row, 'Unidad');
            $hoja->setCellValue('E' . $row, 'Precio Uni.');
            $hoja->setCellValue('F' . $row, 'TOTAL');

            $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
            $startRow = $row;
            $row++;

            if (is_array($section['data']) && count($section['data']) > 0) {
                foreach ($section['data'] as $item) {
                    $hoja->setCellValue('A' . $row, $item['item'] ?? '');
                    $hoja->setCellValue('B' . $row, $item['nombre'] ?? '');
                    $hoja->setCellValue('C' . $row, $item['cantidad'] ?? 0);
                    $hoja->setCellValue('D' . $row, $item['unidad_medida'] ?? '');
                    $hoja->setCellValue('E' . $row, number_format((float)($item['costo'] ?? 0), 2));
                    $hoja->setCellValue('F' . $row, number_format((float)($item['total'] ?? 0), 2));

                    $hoja->getStyle('C' . $row . ':F' . $row)->applyFromArray($center);
                    $hoja->getStyle('E' . $row . ':F' . $row)->applyFromArray($right);
                    $row++;
                }
            } else {
                $hoja->setCellValue('A' . $row, '');
                $hoja->setCellValue('B' . $row, 'Sin registros');
                $hoja->mergeCells('B' . $row . ':F' . $row);
                $row++;
            }

            $hoja->setCellValue('A' . $row, 'Sub. TOTAL');
            $hoja->mergeCells('A' . $row . ':E' . $row);
            $hoja->setCellValue('F' . $row, number_format((float)$section['subtotal'], 2));
            $hoja->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $hoja->getStyle('A' . $row . ':E' . $row)->applyFromArray($right);

            $hoja->getStyle('A' . $startRow . ':F' . $row)->applyFromArray($borderThin);
            $row += 2;
        }

        $row++;
        $hoja->setCellValue('D' . $row, 'SUMA TOTAL Bs.');
        $hoja->mergeCells('D' . $row . ':E' . $row);
        $hoja->setCellValue('F' . $row, number_format((float)$cotizacion->total_general, 2));
        $hoja->getStyle('D' . $row . ':F' . $row)->applyFromArray($borderThin);
        $hoja->getStyle('D' . $row . ':F' . $row)->getFont()->setBold(true);
        $hoja->getStyle('D' . $row . ':E' . $row)->applyFromArray($right);

        $fileName = $this->generarNombreArchivo('Cotizacion', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $this->_agregarLogosExcel($hoja, $orden, 'F');
        $writer = new Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    private function reagruparCotizacion($cotizacion, $grupo_cliente_id)
    {
        $preventivos = is_string($cotizacion->preventivos) ? json_decode($cotizacion->preventivos, true) : $cotizacion->preventivos;
        $correctivos = is_string($cotizacion->correctivos) ? json_decode($cotizacion->correctivos, true) : $cotizacion->correctivos;
        $repuestos = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : $cotizacion->repuestos;

        $serviciosGrup = ClienteServicio::where('grupo_cliente_id', $grupo_cliente_id)->get();

        $todosLosGuardados = array_merge((array)$preventivos, (array)$correctivos, (array)$repuestos);

        $nuevosPrev = [];
        $nuevosCorr = [];
        $nuevosRep = [];

        $subPrev = 0;
        $subCorr = 0;
        $subRep = 0;

        foreach ($todosLosGuardados as $srv) {
            if (!is_array($srv)) continue;

            $catActual = 'OTROS';
            $srvBusqueda = null;

            if (!empty($srv['item'])) {
                $srvBusqueda = $serviciosGrup->where('item', $srv['item'])->first();
            }
            if (!$srvBusqueda && !empty($srv['nombre'])) {
                $srvBusqueda = $serviciosGrup->where('nombre', $srv['nombre'])->first();
            }

            if ($srvBusqueda) {
                $catActual = strtoupper($srvBusqueda->categoria);
            }

            if ($catActual == 'PREVENTIVO') {
                $nuevosPrev[] = $srv;
                $subPrev += floatval($srv['total'] ?? 0);
            } elseif ($catActual == 'CORRECTIVO') {
                $nuevosCorr[] = $srv;
                $subCorr += floatval($srv['total'] ?? 0);
            } else {
                $nuevosRep[] = $srv;
                $subRep += floatval($srv['total'] ?? 0);
            }
        }

        $cotizacion->preventivos = $nuevosPrev;
        $cotizacion->correctivos = $nuevosCorr;
        $cotizacion->repuestos = $nuevosRep;
        $cotizacion->subtotal_preventivos = $subPrev;
        $cotizacion->subtotal_correctivos = $subCorr;
        $cotizacion->subtotal_repuestos = $subRep;
        $cotizacion->total_general = $subPrev + $subCorr + $subRep;

        // Auto-guardar la actualización si lo deseas, o solo devolverla
        $cotizacion->preventivos = json_encode($nuevosPrev);
        $cotizacion->correctivos = json_encode($nuevosCorr);
        $cotizacion->repuestos = json_encode($nuevosRep);
        $cotizacion->save();


        // Para el uso en json/vista devolvemos como arrays
        $cotizacion->preventivos = $nuevosPrev;
        $cotizacion->correctivos = $nuevosCorr;
        $cotizacion->repuestos = $nuevosRep;

        return $cotizacion;
    }

    public function descargarPdfOrdenTrabajo($orden_id)
    {
        $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->firstOrFail();
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($orden_id);

        $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfOrdenTrabajo', compact('cotizacion', 'orden'));

        $fileName = $this->generarNombreArchivo('Orden de Trabajo', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelOrdenTrabajo($orden_id)
    {
        $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->firstOrFail();
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($orden_id);

        $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);

        $libro = new Spreadsheet();
        $hoja  = $libro->getActiveSheet();
        $hoja->setTitle('Orden de Trabajo');

        $borderThin  = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $right = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]];
        $center = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];

        $hoja->getColumnDimension('A')->setWidth(10);
        $hoja->getColumnDimension('B')->setWidth(40);
        $hoja->getColumnDimension('C')->setWidth(12);
        $hoja->getColumnDimension('D')->setWidth(14);
        $hoja->getColumnDimension('E')->setWidth(14);
        $hoja->getColumnDimension('F')->setWidth(14);

        $hoja->setCellValue('A1', 'ORDEN DE TRABAJOS REALIZADOS');
        $hoja->mergeCells('A1:F1');
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $hoja->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $hoja->setCellValue('A3', 'VEHÍCULOS:');
        $hoja->setCellValue('B3', ($orden->auto->marca->nombre ?? '') . ' ' . ($orden->auto->modelo ?? ''));
        $hoja->setCellValue('D3', 'ORDEN DE SERVICIO N°:');
        $hoja->setCellValue('E3', $orden->id);
        $hoja->setCellValue('A4', 'PLACAS:');
        $hoja->setCellValue('B4', $orden->auto->placa ?? '');
        $hoja->setCellValue('D4', 'KILOMETRAJE ACTUAL:');
        $hoja->setCellValue('E4', $orden->kilometraje ?? '');
        $hoja->setCellValue('A5', 'EMPRESA :');
        $hoja->setCellValue('B5', ($orden->grupoCliente->cliente->nombres ?? '') . ' ' . ($orden->grupoCliente->cliente->ap_paterno ?? ''));
        $hoja->setCellValue('D5', 'FECHA DE INGRESO:');
        $hoja->setCellValue('E5', Carbon::parse($orden->created_at)->format('Y-m-d'));
        $hoja->setCellValue('A6', 'SERVICIO/TALLER:');
        $hoja->setCellValue('B6', $cotizacion->servicio_taller ?? '');
        $hoja->setCellValue('D6', 'FECHA DE SALIDA:');
        $hoja->setCellValue('E6', $cotizacion->fecha_salida ?? '');

        $hoja->getStyle('A3:F6')->applyFromArray($borderThin);

        $row = 8;

        $preventivos = is_string($cotizacion->preventivos) ? json_decode($cotizacion->preventivos, true) : $cotizacion->preventivos;
        $correctivos = is_string($cotizacion->correctivos) ? json_decode($cotizacion->correctivos, true) : $cotizacion->correctivos;
        $repuestos = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : $cotizacion->repuestos;

        $sections = [
            ['title' => 'MANTENIMIENTO PREVENTIVO', 'data' => $preventivos, 'subtotal' => $cotizacion->subtotal_preventivos],
            ['title' => 'MANTENIMIENTO CORRECTIVO', 'data' => $correctivos, 'subtotal' => $cotizacion->subtotal_correctivos],
            ['title' => 'REPUESTOS DE VEHÍCULOS', 'data' => $repuestos, 'subtotal' => $cotizacion->subtotal_repuestos],
        ];

        foreach ($sections as $section) {
            $hoja->setCellValue('A' . $row, 'ITEM');
            $hoja->setCellValue('B' . $row, $section['title']);
            $hoja->setCellValue('C' . $row, 'Cantidad');
            $hoja->setCellValue('D' . $row, 'Unidad');
            $hoja->setCellValue('E' . $row, 'Precio Uni.');
            $hoja->setCellValue('F' . $row, 'TOTAL');

            $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
            $startRow = $row;
            $row++;

            if (count($section['data']) > 0) {
                foreach ($section['data'] as $item) {
                    $hoja->setCellValue('A' . $row, $item['item'] ?? '');
                    $hoja->setCellValue('B' . $row, $item['nombre'] ?? '');
                    $hoja->setCellValue('C' . $row, $item['cantidad'] ?? 0);
                    $hoja->setCellValue('D' . $row, $item['unidad_medida'] ?? '');
                    $hoja->setCellValue('E' . $row, number_format((float)($item['costo'] ?? 0), 2));
                    $hoja->setCellValue('F' . $row, number_format((float)($item['total'] ?? 0), 2));

                    $hoja->getStyle('C' . $row . ':F' . $row)->applyFromArray($center);
                    $hoja->getStyle('E' . $row . ':F' . $row)->applyFromArray($right);
                    $row++;
                }
            } else {
                $hoja->setCellValue('A' . $row, '');
                $hoja->setCellValue('B' . $row, 'Sin registros');
                $hoja->mergeCells('B' . $row . ':F' . $row);
                $row++;
            }

            $hoja->setCellValue('A' . $row, 'Sub. TOTAL');
            $hoja->mergeCells('A' . $row . ':E' . $row);
            $hoja->setCellValue('F' . $row, number_format((float)$section['subtotal'], 2));
            $hoja->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $hoja->getStyle('A' . $row . ':E' . $row)->applyFromArray($right);

            $hoja->getStyle('A' . $startRow . ':F' . $row)->applyFromArray($borderThin);
            $row += 2;
        }

        $row++;
        $hoja->setCellValue('D' . $row, 'SUMA TOTAL Bs.');
        $hoja->mergeCells('D' . $row . ':E' . $row);
        $hoja->setCellValue('F' . $row, number_format((float)$cotizacion->total_general, 2));
        $hoja->getStyle('D' . $row . ':F' . $row)->applyFromArray($borderThin);
        $hoja->getStyle('D' . $row . ':F' . $row)->getFont()->setBold(true);
        $hoja->getStyle('D' . $row . ':E' . $row)->applyFromArray($right);

        $fileName = $this->generarNombreArchivo('Orden de Trabajo', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $this->_agregarLogosExcel($hoja, $orden, 'F');
        $writer = new Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    /* ============================================================
     * FORMULARIO 6 — ORDEN DE TRABAJO OFICIAL
     * ============================================================ */

    public function guardarOrdenTrabajoOficial(Request $request)
    {
        if ($request->ajax()) {
            $orden_trabajo_oficial_id = $request->input('orden_trabajo_oficial_id');
            $usuario = Auth::user();
            $orden_recepcion_id = $request->input('orden_recepcion_id');

            if ($orden_trabajo_oficial_id) {
                $ot = OrdenTrabajo::find($orden_trabajo_oficial_id);
                $ot->usuario_modificador_id = $usuario->id;
            } else {
                $ot = new OrdenTrabajo();
                $ot->usuario_creador_id = $usuario->id;
                $ot->orden_recepcion_id = $orden_recepcion_id;

                // Generar número de orden secuencial
                $orden = OrdenRecepcion::find($orden_recepcion_id);
                $grupo_cliente_id = $orden->grupo_cliente_id;
                $anioActual = date('y');

                $maxSecuencial = OrdenTrabajo::whereHas('ordenRecepcion', function ($q) use ($grupo_cliente_id) {
                    $q->where('grupo_cliente_id', $grupo_cliente_id);
                })->where('anio', $anioActual)->max('numero_orden_secuencial');

                $ot->numero_orden_secuencial = ($maxSecuencial ? $maxSecuencial : 0) + 1;
                $ot->anio = $anioActual;
            }

            $ot->fecha_emision = $request->input('fecha_emision');

            $mO = array_values($request->input('mano_obra', []));
            $rE = array_values($request->input('repuestos', []));
            $iN = array_values($request->input('insumos', []));
            $tT = array_values($request->input('trabajos_tercero', []));

            $ot->mano_obra        = json_encode($mO);
            $ot->repuestos        = json_encode($rE);
            $ot->insumos          = json_encode($iN);
            $ot->trabajos_tercero = json_encode($tT);

            $ot->subtotal_mano_obra        = $request->input('subtotal_mano_obra', 0);
            $ot->subtotal_repuestos        = $request->input('subtotal_repuestos', 0);
            $ot->subtotal_insumos          = $request->input('subtotal_insumos', 0);
            $ot->subtotal_trabajos_tercero = $request->input('subtotal_trabajos_tercero', 0);
            $ot->total_general             = $request->input('total_general', 0);

            $ot->save();

            $this->sincronizarCotizacionYOT('OT', $ot->orden_recepcion_id, $usuario->id);

            $numStr = $ot->numero_orden_secuencial . '/' . $ot->anio;

            return response()->json([
                'estado' => true,
                'orden_id' => $ot->id,
                'numero_orden' => $numStr
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerOrdenTrabajoOficial(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');

            $ot = OrdenTrabajo::where('orden_recepcion_id', $orden_id)->first();
            if ($ot) {
                $ot->mano_obra        = is_string($ot->mano_obra)        ? json_decode($ot->mano_obra, true)        : $ot->mano_obra;
                $ot->repuestos        = is_string($ot->repuestos)        ? json_decode($ot->repuestos, true)        : $ot->repuestos;
                $ot->insumos          = is_string($ot->insumos)          ? json_decode($ot->insumos, true)          : $ot->insumos;
                $ot->trabajos_tercero = is_string($ot->trabajos_tercero) ? json_decode($ot->trabajos_tercero, true) : $ot->trabajos_tercero;
                return response()->json(['estado' => true, 'orden' => $ot, 'cotizacion' => null]);
            }

            // No existe, intentar cargar de cotización (si hay)
            $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->first();
            if ($cotizacion) {
                $orden = OrdenRecepcion::find($orden_id);
                $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);

                // We need to map Cotizacion -> OrdenTrabajo format to help frontend
                $prevs = is_string($cotizacion->preventivos) ? json_decode($cotizacion->preventivos, true) : $cotizacion->preventivos;
                $corrs = is_string($cotizacion->correctivos) ? json_decode($cotizacion->correctivos, true) : $cotizacion->correctivos;
                $reps  = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : $cotizacion->repuestos;

                // Let's divide reps into repuestos, insumos, otros based on category
                $serviciosGrup = ClienteServicio::where('grupo_cliente_id', $orden->grupo_cliente_id)->get();

                $n_reps = [];
                $n_insumos = [];
                $n_otros = [];

                if (is_array($reps)) {
                    foreach ($reps as $srv) {
                        if (!is_array($srv)) continue;
                        $catActual = 'OTROS';
                        $srvBusqueda = null;

                        if (!empty($srv['item'])) {
                            $srvBusqueda = $serviciosGrup->where('item', $srv['item'])->first();
                        }
                        if (!$srvBusqueda && !empty($srv['nombre'])) {
                            $srvBusqueda = $serviciosGrup->where('nombre', $srv['nombre'])->first();
                        }

                        if ($srvBusqueda) {
                            $catActual = strtoupper($srvBusqueda->categoria);
                        }

                        if ($catActual == 'REPUESTOS') {
                            $n_reps[] = $srv;
                        } elseif ($catActual == 'SUMINISTRO') {
                            $n_insumos[] = $srv;
                        } else {
                            $n_otros[] = $srv;
                        }
                    }
                }

                $retCotiz = [
                    'preventivos' => $prevs,
                    'correctivos' => $corrs,
                    'repuestos' => $n_reps,
                    'insumos' => $n_insumos,
                    'otros' => $n_otros
                ];

                return response()->json(['estado' => true, 'orden' => null, 'cotizacion' => $retCotiz]);
            }

            return response()->json(['estado' => true, 'orden' => null, 'cotizacion' => null]);
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfOrdenTrabajoOficial($id)
    {
        $ot = OrdenTrabajo::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($ot->orden_recepcion_id);

        $ot->mano_obra        = is_string($ot->mano_obra)        ? json_decode($ot->mano_obra, true)        : $ot->mano_obra;
        $ot->repuestos        = is_string($ot->repuestos)        ? json_decode($ot->repuestos, true)        : $ot->repuestos;
        $ot->insumos          = is_string($ot->insumos)          ? json_decode($ot->insumos, true)          : $ot->insumos;
        $ot->trabajos_tercero = is_string($ot->trabajos_tercero) ? json_decode($ot->trabajos_tercero, true) : $ot->trabajos_tercero;

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfOrdenTrabajoOficial', compact('ot', 'orden'));
        $fileName = $this->generarNombreArchivo('Orden de Trabajo Oficial', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelOrdenTrabajoOficial($id)
    {
        $ot = OrdenTrabajo::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($ot->orden_recepcion_id);

        $ot->mano_obra        = is_string($ot->mano_obra)        ? json_decode($ot->mano_obra, true)        : $ot->mano_obra;
        $ot->repuestos        = is_string($ot->repuestos)        ? json_decode($ot->repuestos, true)        : $ot->repuestos;
        $ot->insumos          = is_string($ot->insumos)          ? json_decode($ot->insumos, true)          : $ot->insumos;
        $ot->trabajos_tercero = is_string($ot->trabajos_tercero) ? json_decode($ot->trabajos_tercero, true) : $ot->trabajos_tercero;

        $libro = new Spreadsheet();
        $hoja  = $libro->getActiveSheet();
        $hoja->setTitle('Orden de Trabajo');

        $borderThin  = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $right = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]];
        $center = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];

        $hoja->getColumnDimension('A')->setWidth(10);
        $hoja->getColumnDimension('B')->setWidth(40);
        $hoja->getColumnDimension('C')->setWidth(12);
        $hoja->getColumnDimension('D')->setWidth(14);
        $hoja->getColumnDimension('E')->setWidth(14);
        $hoja->getColumnDimension('F')->setWidth(14);

        $hoja->setCellValue('A1', 'ORDEN DE TRABAJO');
        $hoja->mergeCells('A1:F1');
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $hoja->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $numStr = $ot->numero_orden_secuencial . '/' . $ot->anio;

        $hoja->setCellValue('A3', 'VEHÍCULOS:');
        $hoja->setCellValue('B3', ($orden->auto->marca->nombre ?? '') . ' ' . ($orden->auto->modelo ?? ''));
        $hoja->setCellValue('D3', 'N° DE ORDEN:');
        $hoja->setCellValue('E3', $numStr);
        $hoja->setCellValue('A4', 'PLACAS / CHASIS:');
        $hoja->setCellValue('B4', $orden->auto->placa ?? '');
        $hoja->setCellValue('D4', 'KILOMETRAJE:');
        $hoja->setCellValue('E4', $orden->kilometraje ?? '');
        $hoja->setCellValue('A5', 'EMPRESA / CLIENTE:');
        $hoja->setCellValue('B5', ($orden->grupoCliente->cliente->nombres ?? '') . ' ' . ($orden->grupoCliente->cliente->ap_paterno ?? ''));
        $hoja->setCellValue('D5', 'NIT / C.I.:');
        $hoja->setCellValue('E5', $orden->grupoCliente->cliente->nit ?? '');
        $hoja->setCellValue('A6', 'FECHA DE EMISIÓN:');
        $hoja->setCellValue('B6', $ot->fecha_emision ?? '');

        $hoja->getStyle('A3:F6')->applyFromArray($borderThin);

        $row = 8;

        $sections = [
            ['title' => 'MANO DE OBRA',       'data' => $ot->mano_obra,        'subtotal' => $ot->subtotal_mano_obra],
            ['title' => 'REPUESTOS',          'data' => $ot->repuestos,        'subtotal' => $ot->subtotal_repuestos],
            ['title' => 'INSUMOS',            'data' => $ot->insumos,          'subtotal' => $ot->subtotal_insumos],
            ['title' => 'TRABAJOS A TERCERO', 'data' => $ot->trabajos_tercero, 'subtotal' => $ot->subtotal_trabajos_tercero],
        ];

        foreach ($sections as $section) {
            $hoja->setCellValue('A' . $row, 'N° Item Contrato');
            $hoja->setCellValue('B' . $row, $section['title']);
            $hoja->setCellValue('C' . $row, 'Unidad Medida');
            $hoja->setCellValue('D' . $row, 'Cantidad Solicitada');
            $hoja->setCellValue('E' . $row, 'Precio Unitario');
            $hoja->setCellValue('F' . $row, 'Precio Total (Bs.)');

            $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
            $startRow = $row;
            $row++;

            if (is_array($section['data']) && count($section['data']) > 0) {
                foreach ($section['data'] as $item) {
                    $hoja->setCellValue('A' . $row, $item['item'] ?? '');
                    $hoja->setCellValue('B' . $row, $item['nombre'] ?? '');
                    $hoja->setCellValue('C' . $row, $item['unidad_medida'] ?? '');
                    $hoja->setCellValue('D' . $row, $item['cantidad'] ?? 0);
                    $hoja->setCellValue('E' . $row, number_format((float)($item['costo'] ?? 0), 2));
                    $hoja->setCellValue('F' . $row, number_format((float)($item['total'] ?? 0), 2));

                    $hoja->getStyle('C' . $row . ':F' . $row)->applyFromArray($center);
                    $hoja->getStyle('E' . $row . ':F' . $row)->applyFromArray($right);
                    $row++;
                }
            } else {
                $hoja->setCellValue('A' . $row, '');
                $hoja->setCellValue('B' . $row, 'Sin registros');
                $hoja->mergeCells('B' . $row . ':F' . $row);
                $row++;
            }

            $hoja->setCellValue('A' . $row, 'sub TOTAL Bs.');
            $hoja->mergeCells('A' . $row . ':E' . $row);
            $hoja->setCellValue('F' . $row, number_format((float)$section['subtotal'], 2));
            $hoja->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $hoja->getStyle('A' . $row . ':E' . $row)->applyFromArray($right);

            $hoja->getStyle('A' . $startRow . ':F' . $row)->applyFromArray($borderThin);
            $row += 2;
        }

        $row++;
        $hoja->setCellValue('D' . $row, 'Total (Bs.):');
        $hoja->mergeCells('D' . $row . ':E' . $row);
        $hoja->setCellValue('F' . $row, number_format((float)$ot->total_general, 2));
        $hoja->getStyle('D' . $row . ':F' . $row)->applyFromArray($borderThin);
        $hoja->getStyle('D' . $row . ':F' . $row)->getFont()->setBold(true);
        $hoja->getStyle('D' . $row . ':E' . $row)->applyFromArray($right);

        $fileName = $this->generarNombreArchivo('Orden de Trabajo Oficial', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $this->_agregarLogosExcel($hoja, $orden, 'F');
        $writer = new Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    /* ============================================================
     * FORMULARIO 7 — FORMULARIO DE AUTORIZACIÓN
     * ============================================================ */

    public function guardarFormularioAutorizacion(Request $request)
    {
        if ($request->ajax()) {
            $fa_id = $request->input('formulario_autorizacion_id');
            $usuario = Auth::user();
            $orden_recepcion_id = $request->input('orden_recepcion_id');

            if ($fa_id) {
                $fa = FormularioAutorizacion::find($fa_id);
                $fa->usuario_modificador_id = $usuario->id;
            } else {
                $fa = new FormularioAutorizacion();
                $fa->usuario_creador_id = $usuario->id;
                $fa->orden_recepcion_id = $orden_recepcion_id;
            }

            $fa->fecha = $request->input('fecha');
            $fa->cite = $request->input('cite');
            $fa->observaciones = $request->input('observaciones');

            // Save JSON snapshot if passed (read-only from frontend but we send them to save)
            if ($request->has('preventivo')) {
                $fa->preventivo = json_encode(array_values($request->input('preventivo')));
            }
            if ($request->has('correctivo')) {
                $fa->correctivo = json_encode(array_values($request->input('correctivo')));
            }
            if ($request->has('repuestos_suministros')) {
                $fa->repuestos_suministros = json_encode(array_values($request->input('repuestos_suministros')));
            }
            if ($request->has('otros')) {
                $fa->otros = json_encode(array_values($request->input('otros')));
            }

            $fa->total_general = $request->input('total_general', 0);

            $fa->save();

            return response()->json([
                'estado' => true,
                'fa_id' => $fa->id
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerFormularioAutorizacion(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');

            $fa = FormularioAutorizacion::where('orden_recepcion_id', $orden_id)->first();
            if ($fa) {
                $fa->preventivo            = is_string($fa->preventivo)            ? json_decode($fa->preventivo, true)            : $fa->preventivo;
                $fa->correctivo            = is_string($fa->correctivo)            ? json_decode($fa->correctivo, true)            : $fa->correctivo;
                $fa->repuestos_suministros = is_string($fa->repuestos_suministros) ? json_decode($fa->repuestos_suministros, true) : $fa->repuestos_suministros;
                $fa->otros                 = is_string($fa->otros)                 ? json_decode($fa->otros, true)                 : $fa->otros;

                // Get Order for sequential number display
                $ot = OrdenTrabajo::where('orden_recepcion_id', $orden_id)->first();
                $numOt = '';
                if ($ot) {
                    $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
                }

                return response()->json(['estado' => true, 'fa' => $fa, 'cotizacion' => null, 'num_ot' => $numOt]);
            }

            // No existe, leemos los items de Cotizacion para generar la vista previa
            $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->first();
            $numOt = '';
            $ot = OrdenTrabajo::where('orden_recepcion_id', $orden_id)->first();
            if ($ot) {
                $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
            }

            if ($cotizacion) {
                $orden = OrdenRecepcion::find($orden_id);
                $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);

                $prevs = is_string($cotizacion->preventivos) ? json_decode($cotizacion->preventivos, true) : $cotizacion->preventivos;
                $corrs = is_string($cotizacion->correctivos) ? json_decode($cotizacion->correctivos, true) : $cotizacion->correctivos;
                $reps  = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : $cotizacion->repuestos;

                $serviciosGrup = ClienteServicio::where('grupo_cliente_id', $orden->grupo_cliente_id)->get();

                $n_reps_sum = [];
                $n_otros = [];

                if (is_array($reps)) {
                    foreach ($reps as $srv) {
                        if (!is_array($srv)) continue;
                        $catActual = 'OTROS';
                        $srvBusqueda = null;

                        if (!empty($srv['item'])) {
                            $srvBusqueda = $serviciosGrup->where('item', $srv['item'])->first();
                        }
                        if (!$srvBusqueda && !empty($srv['nombre'])) {
                            $srvBusqueda = $serviciosGrup->where('nombre', $srv['nombre'])->first();
                        }
                        if ($srvBusqueda) {
                            $catActual = strtoupper($srvBusqueda->categoria);
                        }

                        if ($catActual == 'REPUESTOS' || $catActual == 'SUMINISTRO') {
                            $n_reps_sum[] = $srv;
                        } else {
                            $n_otros[] = $srv;
                        }
                    }
                }

                $retData = [
                    'preventivo' => $prevs,
                    'correctivo' => $corrs,
                    'repuestos_suministros' => $n_reps_sum,
                    'otros' => $n_otros
                ];

                return response()->json(['estado' => true, 'fa' => null, 'cotizacion' => $retData, 'num_ot' => $numOt]);
            }

            return response()->json(['estado' => true, 'fa' => null, 'cotizacion' => null, 'num_ot' => $numOt]);
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfFormularioAutorizacion($id)
    {
        $fa = FormularioAutorizacion::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($fa->orden_recepcion_id);

        $fa->preventivo            = is_string($fa->preventivo)            ? json_decode($fa->preventivo, true)            : $fa->preventivo;
        $fa->correctivo            = is_string($fa->correctivo)            ? json_decode($fa->correctivo, true)            : $fa->correctivo;
        $fa->repuestos_suministros = is_string($fa->repuestos_suministros) ? json_decode($fa->repuestos_suministros, true) : $fa->repuestos_suministros;
        $fa->otros                 = is_string($fa->otros)                 ? json_decode($fa->otros, true)                 : $fa->otros;

        $ot = OrdenTrabajo::where('orden_recepcion_id', $fa->orden_recepcion_id)->first();

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfFormularioAutorizacion', compact('fa', 'orden', 'ot'));
        $fileName = $this->generarNombreArchivo('Formulario de Autorizacion', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelFormularioAutorizacion($id)
    {
        $fa = FormularioAutorizacion::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($fa->orden_recepcion_id);

        $fa->preventivo            = is_string($fa->preventivo)            ? json_decode($fa->preventivo, true)            : $fa->preventivo;
        $fa->correctivo            = is_string($fa->correctivo)            ? json_decode($fa->correctivo, true)            : $fa->correctivo;
        $fa->repuestos_suministros = is_string($fa->repuestos_suministros) ? json_decode($fa->repuestos_suministros, true) : $fa->repuestos_suministros;
        $fa->otros                 = is_string($fa->otros)                 ? json_decode($fa->otros, true)                 : $fa->otros;

        $ot = OrdenTrabajo::where('orden_recepcion_id', $fa->orden_recepcion_id)->first();
        $numOt = '';
        if ($ot) {
            $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
        }

        $libro = new Spreadsheet();
        $hoja  = $libro->getActiveSheet();
        $hoja->setTitle('Form. Autorizacion');

        $borderThin  = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $right = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]];
        $center = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];

        $hoja->getColumnDimension('A')->setWidth(8);
        $hoja->getColumnDimension('B')->setWidth(50);
        $hoja->getColumnDimension('C')->setWidth(10);
        $hoja->getColumnDimension('D')->setWidth(10);
        $hoja->getColumnDimension('E')->setWidth(12);
        $hoja->getColumnDimension('F')->setWidth(14);

        $hoja->setCellValue('A2', 'FORMULARIO DE AUTORIZACION DE CAMBIO DE REPUESTOS, PARTES, ACCESORIOS, SERVICIOS Y SUMINISTROS PARA VEHÍCULOS');
        $hoja->mergeCells('A2:E4');
        $hoja->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $hoja->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('A2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $hoja->getStyle('A2')->getAlignment()->setWrapText(true);
        $hoja->getStyle('A2:E4')->applyFromArray($borderThin);

        $hoja->setCellValue('F2', 'RG-02-B-PP-1-DAC/UTR-2');
        $hoja->mergeCells('F2:F4');
        $hoja->getStyle('F2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('F2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $hoja->getStyle('F2:F4')->applyFromArray($borderThin);

        $hoja->setCellValue('A6', 'FECHA');
        $hoja->setCellValue('B6', $fa->fecha ?? '');
        $hoja->setCellValue('D6', 'CITE');
        $hoja->setCellValue('E6', $fa->cite ?? '');
        $hoja->mergeCells('E6:F6');

        $hoja->setCellValue('A7', 'DATOS DEL VEHICULO');
        $hoja->mergeCells('A7:C7');
        $hoja->getStyle('A7')->getFont()->setBold(true);

        $hoja->setCellValue('A8', 'PROPIETARIO DEL VEHICULO');
        $hoja->mergeCells('A8:B8');
        $hoja->setCellValue('C8', ($orden->grupoCliente->cliente->nombres ?? '') . ' ' . ($orden->grupoCliente->cliente->ap_paterno ?? ''));

        $hoja->setCellValue('A9', 'MARCA');
        $hoja->setCellValue('B9', $orden->auto->marca->nombre ?? '');
        $hoja->setCellValue('C9', 'PLACA');
        $hoja->setCellValue('D9', $orden->auto->placa ?? '');
        $hoja->mergeCells('D9:F9');

        $hoja->setCellValue('A10', 'CLASE');
        $hoja->setCellValue('B10', $orden->auto->modelo ?? '');
        $hoja->setCellValue('C10', 'TIPO');
        $hoja->setCellValue('D10', '');
        $hoja->mergeCells('D10:F10');

        $hoja->getStyle('A6:F10')->applyFromArray($borderThin);
        $hoja->getStyle('A6:A10')->getFont()->setBold(true);
        $hoja->getStyle('C9:C10')->getFont()->setBold(true);

        $row = 12;
        $hoja->setCellValue('A' . $row, 'DETALLE');
        $hoja->mergeCells('A' . $row . ':F' . $row);
        $hoja->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $row++;

        $sections = [
            ['title' => 'Mantenimiento Preventivo', 'data' => $fa->preventivo, 'header' => 'Mano de obra'],
            ['title' => 'Mantenimiento Correctivo', 'data' => $fa->correctivo, 'header' => 'Mano de obra'],
            ['title' => 'Repuestos - Accesorios - Suministros', 'data' => $fa->repuestos_suministros, 'header' => 'DETALLE'],
            ['title' => 'Otros Servicios Requeridos', 'data' => $fa->otros, 'header' => 'DETALLE'],
        ];

        $totalGral = 0;

        foreach ($sections as $section) {
            $hoja->setCellValue('A' . $row, $section['title']);
            $hoja->mergeCells('A' . $row . ':F' . $row);
            $hoja->getStyle('A' . $row)->applyFromArray($headerStyle);
            $startRow = $row;
            $row++;

            $hoja->setCellValue('A' . $row, 'ITEM');
            $hoja->setCellValue('B' . $row, $section['header']);
            $hoja->setCellValue('C' . $row, 'CANT.');
            $hoja->setCellValue('D' . $row, 'UNID.');
            $hoja->setCellValue('E' . $row, 'P/UNIT.');
            $hoja->setCellValue('F' . $row, 'TOTAL');

            $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
            $row++;

            $subtotal = 0;

            if (is_array($section['data']) && count($section['data']) > 0) {
                foreach ($section['data'] as $item) {
                    $hoja->setCellValue('A' . $row, $item['item'] ?? '');
                    $hoja->setCellValue('B' . $row, $item['nombre'] ?? '');
                    $hoja->setCellValue('C' . $row, $item['cantidad'] ?? 0);
                    $hoja->setCellValue('D' . $row, $item['unidad_medida'] ?? '');
                    $hoja->setCellValue('E' . $row, number_format((float)($item['costo'] ?? 0), 2));
                    $hoja->setCellValue('F' . $row, number_format((float)($item['total'] ?? 0), 2));

                    $subtotal += (float)($item['total'] ?? 0);

                    $hoja->getStyle('C' . $row . ':D' . $row)->applyFromArray($center);
                    $hoja->getStyle('E' . $row . ':F' . $row)->applyFromArray($right);
                    $row++;
                }
            } else {
                $hoja->setCellValue('A' . $row, '');
                $hoja->setCellValue('B' . $row, 'Sin registros');
                $hoja->mergeCells('B' . $row . ':F' . $row);
                $row++;
            }

            $totalGral += $subtotal;

            $hoja->setCellValue('E' . $row, 'TOTAL');
            $hoja->setCellValue('F' . $row, number_format($subtotal, 2));
            $hoja->getStyle('E' . $row . ':F' . $row)->getFont()->setBold(true);
            $hoja->getStyle('E' . $row . ':F' . $row)->applyFromArray($right);

            $hoja->getStyle('A' . $startRow . ':F' . $row)->applyFromArray($borderThin);
            $row += 1; // Un pequeño espacio no, mejor junto
        }

        $row++;
        $hoja->setCellValue('E' . $row, 'TOTAL GENERAL Bs.');
        $hoja->setCellValue('F' . $row, number_format($totalGral, 2));
        $hoja->getStyle('E' . $row . ':F' . $row)->applyFromArray($borderThin);
        $hoja->getStyle('E' . $row . ':F' . $row)->getFont()->setBold(true);
        $hoja->getStyle('E' . $row . ':F' . $row)->applyFromArray($right);

        $row += 2;
        $hoja->setCellValue('A' . $row, 'OBSERVACIONES');
        $hoja->mergeCells('A' . $row . ':F' . $row);
        $hoja->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $hoja->setCellValue('A' . $row, $fa->observaciones ?? '');
        $hoja->mergeCells('A' . $row . ':F' . ($row + 3));
        $hoja->getStyle('A' . ($row - 1) . ':F' . ($row + 3))->applyFromArray($borderThin);
        $hoja->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $hoja->getStyle('A' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        $row += 4;

        $fileName = $this->generarNombreArchivo('Formulario de Autorizacion', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $hoja->removeRow(1, 1);
        $this->_agregarLogosExcel($hoja, $orden, 'F', 1);
        $writer = new Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    /* ============================================================
     * FORMULARIO 8 — RECEPCIÓN DE REPUESTOS
     * ============================================================ */

    public function guardarRecepcionRepuesto(Request $request)
    {
        if ($request->ajax()) {
            $rr_id = $request->input('recepcion_repuesto_id');
            $usuario = Auth::user();
            $orden_recepcion_id = $request->input('orden_recepcion_id');

            if ($rr_id) {
                $rr = RecepcionRepuesto::find($rr_id);
                $rr->usuario_modificador_id = $usuario->id;
            } else {
                $rr = new RecepcionRepuesto();
                $rr->usuario_creador_id = $usuario->id;
                $rr->orden_recepcion_id = $orden_recepcion_id;
            }

            $rr->fecha = $request->input('fecha');
            $rr->observaciones = $request->input('observaciones');

            $repuestosRaw = $request->input('repuestos', []);
            $repuestosProc = [];
            foreach ($repuestosRaw as $r) {
                $r['ocultar_reporte'] = isset($r['ocultar_reporte']) && $r['ocultar_reporte'] == '1' ? true : false;
                $repuestosProc[] = $r;
            }
            $rr->repuestos = json_encode($repuestosProc);

            $rr->save();

            return response()->json([
                'estado' => true,
                'rr_id' => $rr->id
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerRecepcionRepuesto(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');

            $rr = RecepcionRepuesto::where('orden_recepcion_id', $orden_id)->first();

            // Get Order for sequential number display
            $ot = OrdenTrabajo::where('orden_recepcion_id', $orden_id)->first();
            $numOt = '';
            if ($ot) {
                $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
            }

            if ($rr) {
                $rr->repuestos = is_string($rr->repuestos) ? json_decode($rr->repuestos, true) : $rr->repuestos;
                return response()->json(['estado' => true, 'rr' => $rr, 'cotizacion' => null, 'num_ot' => $numOt]);
            }

            // No existe, leemos los items de Cotizacion (SOLO REPUESTOS)
            $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->first();

            if ($cotizacion) {
                $orden = OrdenRecepcion::find($orden_id);
                $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);

                $reps  = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : $cotizacion->repuestos;
                $serviciosGrup = ClienteServicio::where('grupo_cliente_id', $orden->grupo_cliente_id)->get();

                $solo_repuestos = [];

                if (is_array($reps)) {
                    foreach ($reps as $srv) {
                        if (!is_array($srv)) continue;

                        $srv['ocultar_reporte'] = false;
                        $solo_repuestos[] = $srv;
                    }
                }

                return response()->json(['estado' => true, 'rr' => null, 'cotizacion_reps' => $solo_repuestos, 'num_ot' => $numOt]);
            }

            return response()->json(['estado' => true, 'rr' => null, 'cotizacion_reps' => [], 'num_ot' => $numOt]);
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfRecepcionRepuesto($id)
    {
        $rr = RecepcionRepuesto::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($rr->orden_recepcion_id);

        $repuestos = is_string($rr->repuestos) ? json_decode($rr->repuestos, true) : $rr->repuestos;
        $repuestosVisible = array_filter($repuestos, function ($r) {
            return !isset($r['ocultar_reporte']) || $r['ocultar_reporte'] == false;
        });

        $ot = OrdenTrabajo::where('orden_recepcion_id', $rr->orden_recepcion_id)->first();
        $numOt = '';
        if ($ot) {
            $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
        }

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfRecepcionRepuesto', compact('rr', 'orden', 'repuestosVisible', 'numOt'));
        $fileName = $this->generarNombreArchivo('Recepcion de Repuestos', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelRecepcionRepuesto($id)
    {
        $rr = RecepcionRepuesto::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($rr->orden_recepcion_id);

        $repuestos = is_string($rr->repuestos) ? json_decode($rr->repuestos, true) : $rr->repuestos;
        $repuestosVisible = array_filter($repuestos, function ($r) {
            return !isset($r['ocultar_reporte']) || $r['ocultar_reporte'] == false;
        });

        $ot = OrdenTrabajo::where('orden_recepcion_id', $rr->orden_recepcion_id)->first();
        $numOt = '';
        if ($ot) {
            $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
        }

        $libro = new Spreadsheet();
        $hoja  = $libro->getActiveSheet();
        $hoja->setTitle('Recepción Repuestos');

        $borderThin  = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $center = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];

        $hoja->getColumnDimension('A')->setWidth(8);
        $hoja->getColumnDimension('B')->setWidth(50);
        $hoja->getColumnDimension('C')->setWidth(15);
        $hoja->getColumnDimension('D')->setWidth(15);

        $hoja->setCellValue('A2', 'FORMULARIO DE RECEPCIÓN DE REPUESTOS Y ACCESORIOS USADOS');
        $hoja->mergeCells('A2:C4');
        $hoja->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $hoja->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('A2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $hoja->getStyle('A2')->getAlignment()->setWrapText(true);
        $hoja->getStyle('A2:C4')->applyFromArray($borderThin);

        $hoja->setCellValue('D2', 'RG-03-B-PP-1-DAC/UTR-2');
        $hoja->mergeCells('D2:D4');
        $hoja->getStyle('D2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('D2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $hoja->getStyle('D2:D4')->applyFromArray($borderThin);

        $hoja->setCellValue('A6', 'FECHA');
        $hoja->setCellValue('B6', $rr->fecha ?? '');
        $hoja->setCellValue('C6', 'Form.de Mant.');
        $hoja->setCellValue('D6', $numOt);

        $hoja->setCellValue('A7', 'DATOS DEL VEHÍCULO');
        $hoja->mergeCells('A7:D7');
        $hoja->getStyle('A7')->getFont()->setBold(true);
        $hoja->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $hoja->setCellValue('A8', 'MARCA');
        $hoja->setCellValue('B8', $orden->auto->marca->nombre ?? '');
        $hoja->setCellValue('C8', 'PLACA');
        $hoja->setCellValue('D8', $orden->auto->placa ?? '');

        $hoja->setCellValue('A9', 'CLASE');
        $hoja->setCellValue('B9', $orden->auto->modelo ?? '');
        $hoja->setCellValue('C9', 'TIPO');
        $hoja->setCellValue('D9', '');

        $hoja->getStyle('A6:D9')->applyFromArray($borderThin);
        $hoja->getStyle('A6')->getFont()->setBold(true);
        $hoja->getStyle('C6')->getFont()->setBold(true);
        $hoja->getStyle('A8:A9')->getFont()->setBold(true);
        $hoja->getStyle('C8:C9')->getFont()->setBold(true);

        $row = 11;
        $hoja->setCellValue('A' . $row, 'DETALLE');
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $hoja->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
        $row++;

        $hoja->setCellValue('A' . $row, 'Llenar la siguiente información, en caso de no adjuntar el detalle de repuestos emitido por el taller mecánico');
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row)->getFont()->setSize(9);
        $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
        $row++;

        $hoja->setCellValue('A' . $row, 'N°');
        $hoja->setCellValue('B' . $row, 'REPUESTOS');
        $hoja->setCellValue('C' . $row, 'CANT.');
        $hoja->setCellValue('D' . $row, 'UNIDAD');

        $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($headerStyle);
        $row++;

        if (count($repuestosVisible) > 0) {
            $cont = 1;
            foreach ($repuestosVisible as $item) {
                $hoja->setCellValue('A' . $row, $cont);
                $hoja->setCellValue('B' . $row, $item['nombre'] ?? '');
                $hoja->setCellValue('C' . $row, $item['cantidad'] ?? 0);
                $hoja->setCellValue('D' . $row, $item['unidad_medida'] ?? '');

                $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
                $hoja->getStyle('A' . $row)->applyFromArray($center);
                $hoja->getStyle('C' . $row . ':D' . $row)->applyFromArray($center);
                $row++;
                $cont++;
            }
        } else {
            $hoja->setCellValue('A' . $row, '');
            $hoja->setCellValue('B' . $row, 'Sin registros visibles');
            $hoja->mergeCells('B' . $row . ':D' . $row);
            $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
            $row++;
        }

        $row += 2;
        $hoja->setCellValue('A' . $row, 'OBSERVACIONES');
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $hoja->setCellValue('A' . $row, $rr->observaciones ?? '');
        $hoja->mergeCells('A' . $row . ':D' . ($row + 3));
        $hoja->getStyle('A' . ($row - 1) . ':D' . ($row + 3))->applyFromArray($borderThin);
        $hoja->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $hoja->getStyle('A' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        $row += 4;

        $fileName = $this->generarNombreArchivo('Recepcion de Repuestos', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $hoja->removeRow(1, 1);
        $this->_agregarLogosExcel($hoja, $orden, 'E', 1);
        $writer = new Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    /* ============================================================
     * FORMULARIO 9 — REPORTE FOTOGRÁFICO
     * ============================================================ */

    public function guardarReporteFotografico(Request $request)
    {
        if ($request->ajax()) {
            $rf_id = $request->input('reporte_id');
            $usuario = Auth::user();
            $orden_recepcion_id = $request->input('orden_recepcion_id');

            if ($rf_id) {
                $rf = ReporteFotografico::find($rf_id);
                $rf->usuario_modificador_id = $usuario->id;
            } else {
                $rf = new ReporteFotografico();
                $rf->usuario_creador_id = $usuario->id;
                $rf->orden_recepcion_id = $orden_recepcion_id;
            }

            $rf->fecha = $request->input('fecha');
            $rf->objeto_contratacion = $request->input('objeto_contratacion');

            $filasData = $request->input('filas', []);
            $archivos = $request->file('filas_archivos', []);

            $filasProcessed = [];

            foreach ($filasData as $index => $filaRow) {
                $foto1Path = $filaRow['path_foto1'] ?? '';
                $foto2Path = $filaRow['path_foto2'] ?? '';
                $foto3Path = $filaRow['path_foto3'] ?? '';

                if (isset($archivos[$index])) {
                    if (isset($archivos[$index]['foto1'])) {
                        $foto1Path = $archivos[$index]['foto1']->store('fotos_reporte', 'public');
                    }
                    if (isset($archivos[$index]['foto2'])) {
                        $foto2Path = $archivos[$index]['foto2']->store('fotos_reporte', 'public');
                    }
                    if (isset($archivos[$index]['foto3'])) {
                        $foto3Path = $archivos[$index]['foto3']->store('fotos_reporte', 'public');
                    }
                }

                $filasProcessed[] = [
                    'foto1' => $foto1Path,
                    'foto2' => $foto2Path,
                    'foto3' => $foto3Path,
                    'descripcion' => $filaRow['descripcion'] ?? ''
                ];
            }

            $rf->filas = json_encode($filasProcessed);
            $rf->save();

            return response()->json([
                'estado' => true,
                'rf_id' => $rf->id
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerReporteFotografico(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');

            $rf = ReporteFotografico::where('orden_recepcion_id', $orden_id)->first();
            $ot = OrdenTrabajo::where('orden_recepcion_id', $orden_id)->first();
            $numOt = '';
            if ($ot) {
                $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
            }

            if ($rf) {
                $rf->filas = is_string($rf->filas) ? json_decode($rf->filas, true) : $rf->filas;
                return response()->json(['estado' => true, 'rf' => $rf, 'num_ot' => $numOt]);
            }

            return response()->json(['estado' => true, 'rf' => null, 'num_ot' => $numOt]);
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfReporteFotografico($id)
    {
        $rf = ReporteFotografico::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($rf->orden_recepcion_id);

        $filas = is_string($rf->filas) ? json_decode($rf->filas, true) : $rf->filas;

        $ot = OrdenTrabajo::where('orden_recepcion_id', $rf->orden_recepcion_id)->first();
        $numOt = '';
        if ($ot) {
            $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
        }

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfReporteFotografico', compact('rf', 'orden', 'filas', 'numOt'));
        // Necesitamos que reconozca los src de public/storage
        $pdf->getDomPDF()->setHttpContext(
            stream_context_create([
                'ssl' => [
                    'verify_peer' => FALSE,
                    'verify_peer_name' => FALSE,
                    'allow_self_signed' => TRUE
                ]
            ])
        );

        $fileName = $this->generarNombreArchivo('Reporte Fotografico', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelReporteFotografico($id)
    {
        // En reportes con imágenes, el Excel es complejo. Generamos el básico con datos.
        // O lo informamos si prefiere que no sea exportable a Excel si es muy complejo.
        // Pero el requerimiento general pide Excel.
        // Vamos a incluir las fotos como Drawing si existen.

        $rf = ReporteFotografico::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($rf->orden_recepcion_id);

        $filas = is_string($rf->filas) ? json_decode($rf->filas, true) : $rf->filas;
        $ot = OrdenTrabajo::where('orden_recepcion_id', $rf->orden_recepcion_id)->first();
        $numOt = '';
        if ($ot) {
            $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
        }

        $libro = new Spreadsheet();
        $hoja  = $libro->getActiveSheet();
        $hoja->setTitle('Reporte Fotográfico');

        $borderThin  = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];

        $hoja->getColumnDimension('A')->setWidth(15);
        $hoja->getColumnDimension('B')->setWidth(20);
        $hoja->getColumnDimension('C')->setWidth(20);
        $hoja->getColumnDimension('D')->setWidth(15);
        $hoja->getColumnDimension('E')->setWidth(20);
        $hoja->getColumnDimension('F')->setWidth(20);

        // Header
        $hoja->setCellValue('C2', 'REPORTE FOTOGRÁFICO');
        $hoja->mergeCells('C2:D4');
        $hoja->getStyle('C2')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('00003366');
        $hoja->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('C2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $hoja->getStyle('C2:D4')->applyFromArray($borderThin);

        $hoja->setCellValue('A6', 'N° de Orden:');
        $hoja->setCellValue('B6', $numOt);
        $hoja->setCellValue('D6', 'Fecha:');
        $hoja->setCellValue('E6', $rf->fecha ?? '');

        $cliente = $orden->grupoCliente->cliente ?? null;
        $hoja->setCellValue('A7', 'Empresa/ Cliente:');
        $hoja->setCellValue('B7', $cliente->nombre_razon_social ?? '');
        $hoja->setCellValue('D7', 'NIT/ C.I.:');
        $hoja->setCellValue('E7', $cliente->nit_ci ?? '');

        $hoja->setCellValue('A8', 'Contacto de ref.:');
        $hoja->setCellValue('B8', $cliente->contacto_referencia ?? '');
        $hoja->setCellValue('D8', 'Telefono/ Correo:');
        $hoja->setCellValue('E8', $cliente->telefono_celular ?? '');

        $hoja->setCellValue('A9', 'Placa/ Chasis:');
        $hoja->setCellValue('B9', $orden->auto->placa ?? '');
        $hoja->setCellValue('D9', 'Kilometraje:');
        $hoja->setCellValue('E9', $orden->kilometraje ?? '');

        $hoja->setCellValue('A10', 'Clase de vehiculo:');
        $hoja->setCellValue('B10', $orden->auto->modelo ?? '');
        $hoja->setCellValue('D10', 'Objeto de la contratacion:');
        $hoja->setCellValue('E10', $rf->objeto_contratacion ?? '');

        $hoja->setCellValue('A11', 'Marca/ Tipo:');
        $hoja->setCellValue('B11', $orden->auto->marca->nombre ?? '');
        $hoja->setCellValue('D11', 'Direccion de cliente:');
        $hoja->setCellValue('E11', $cliente->direccion ?? '');

        $row = 13;
        $hoja->setCellValue('A' . $row, 'REPORTE FOTOGRAFICO DEL VEHICULO:');
        $hoja->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $hoja->setCellValue('A' . $row, 'De acuerdo a ingreso y salida de vehiculo motorizado con placa de control detallado en el presente documento, a continuacion se detalla visualmente las diferentes etapas de un servicio, repuestos y accesorios cambiados:');
        $hoja->mergeCells('A' . $row . ':F' . ($row + 1));
        $hoja->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $row += 2;

        if ($filas && count($filas) > 0) {
            $hoja->setCellValue('A' . $row, 'Reporte fotográfico');
            $hoja->mergeCells('A' . $row . ':F' . $row);
            $hoja->getStyle('A' . $row)->getFont()->setBold(true);
            $hoja->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE699');
            $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderThin);
            $row++;
            
            $hoja->setCellValue('A' . $row, 'DESCRIPCION');
            $hoja->mergeCells('A' . $row . ':B' . $row);
            $hoja->setCellValue('C' . $row, 'RESPALDO DE SERVICIO o REPUESTO INICIAL');
            $hoja->mergeCells('C' . $row . ':D' . $row);
            $hoja->setCellValue('E' . $row, 'RESPALDO DE SERVICIO o REPUESTO ACTUAL');
            $hoja->mergeCells('E' . $row . ':F' . $row);
            $hoja->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $hoja->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle('A' . $row . ':F' . $row)->getAlignment()->setWrapText(true);
            $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderThin);
            $row++;

            foreach ($filas as $f) {
                $hoja->setCellValue('A' . $row, $f['descripcion'] ?? '');
                $hoja->mergeCells('A' . $row . ':B' . $row);
                $hoja->getStyle('A' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $hoja->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $hoja->getStyle('A' . $row)->getAlignment()->setWrapText(true);
                $hoja->getStyle('A' . $row)->getFont()->setBold(true);
                
                $hoja->mergeCells('C' . $row . ':D' . $row);
                $hoja->mergeCells('E' . $row . ':F' . $row);
                $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderThin);

                $rowHeights = 110;
                $hoja->getRowDimension($row)->setRowHeight($rowHeights);

                if (!empty($f['foto1']) && file_exists(storage_path('app/public/' . $f['foto1']))) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Foto1');
                    $drawing->setDescription('Foto 1');
                    $drawing->setPath(storage_path('app/public/' . $f['foto1']));
                    $drawing->setHeight(130);
                    $drawing->setCoordinates('C' . $row);
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($hoja);
                }

                if (!empty($f['foto2']) && file_exists(storage_path('app/public/' . $f['foto2']))) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Foto2');
                    $drawing->setDescription('Foto 2');
                    $drawing->setPath(storage_path('app/public/' . $f['foto2']));
                    $drawing->setHeight(130);
                    $drawing->setCoordinates('E' . $row);
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($hoja);
                }

                $row++;
            }
        }

        $fileName = $this->generarNombreArchivo('Reporte Fotografico', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $hoja->removeRow(1, 1);
        $this->_agregarLogosExcel($hoja, $orden, 'F', 1);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    /* ============================================================
     * FORMULARIO 10 — ACTA DE ENTREGA
     * ============================================================ */

    public function guardarActaEntrega(Request $request)
    {
        if ($request->ajax()) {
            $ae_id = $request->input('acta_id');
            $usuario = Auth::user();
            $orden_recepcion_id = $request->input('orden_recepcion_id');

            if ($ae_id) {
                $ae = ActaEntrega::find($ae_id);
                $ae->usuario_modificador_id = $usuario->id;
            } else {
                $ae = new ActaEntrega();
                $ae->usuario_creador_id = $usuario->id;
                $ae->orden_recepcion_id = $orden_recepcion_id;
            }

            $ae->fecha_entrega = $request->input('fecha_entrega');
            $ae->entregado_a = $request->input('entregado_a');
            $ae->de = $request->input('de');
            $ae->asunto = $request->input('asunto');

            $servicios = $request->input('servicios_realizados', []);
            $ae->servicios_realizados = json_encode($servicios);

            $ae->save();

            return response()->json([
                'estado' => true,
                'ae_id' => $ae->id
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerActaEntrega(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');

            $ae = ActaEntrega::where('orden_recepcion_id', $orden_id)->first();
            $ot = OrdenTrabajo::where('orden_recepcion_id', $orden_id)->first();
            $numOt = '';
            if ($ot) {
                $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
            }

            if ($ae) {
                $ae->servicios_realizados = is_string($ae->servicios_realizados) ? json_decode($ae->servicios_realizados, true) : $ae->servicios_realizados;
                return response()->json(['estado' => true, 'ae' => $ae, 'num_ot' => $numOt]);
            }

            // No existe, traemos la lista plana de servicios desde la Cotizacion o FormularioAutorizacion
            $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->first();
            $serviciosPlanos = [];

            if ($cotizacion) {
                $orden = OrdenRecepcion::find($orden_id);
                $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);

                $prev = is_string($cotizacion->preventivos) ? json_decode($cotizacion->preventivos, true) : $cotizacion->preventivos;
                $rp = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : $cotizacion->repuestos;
                $corr = is_string($cotizacion->correctivos) ? json_decode($cotizacion->correctivos, true) : $cotizacion->correctivos;

                if (is_array($prev)) {
                    foreach ($prev as $item) {
                        $serviciosPlanos[] = $item['nombre'] ?? '';
                    }
                }
                if (is_array($rp)) {
                    foreach ($rp as $item) {
                        $serviciosPlanos[] = $item['nombre'] ?? '';
                    }
                }
                if (is_array($corr)) {
                    foreach ($corr as $item) {
                        $serviciosPlanos[] = $item['nombre'] ?? '';
                    }
                }
            }

            return response()->json(['estado' => true, 'ae' => null, 'cotizacion_servicios' => $serviciosPlanos, 'num_ot' => $numOt]);
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfActaEntrega($id)
    {
        $ae = ActaEntrega::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($ae->orden_recepcion_id);

        $servicios = is_string($ae->servicios_realizados) ? json_decode($ae->servicios_realizados, true) : $ae->servicios_realizados;

        $ot = OrdenTrabajo::where('orden_recepcion_id', $ae->orden_recepcion_id)->first();
        $numOt = '';
        if ($ot) {
            $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
        }

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfActaEntrega', compact('ae', 'orden', 'servicios', 'numOt'));
        $fileName = $this->generarNombreArchivo('Acta de Entrega', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelActaEntrega($id)
    {
        $ae = ActaEntrega::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($ae->orden_recepcion_id);

        $servicios = is_string($ae->servicios_realizados) ? json_decode($ae->servicios_realizados, true) : $ae->servicios_realizados;

        $ot = OrdenTrabajo::where('orden_recepcion_id', $ae->orden_recepcion_id)->first();
        $numOt = '';
        if ($ot) {
            $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
        }

        $libro = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $hoja  = $libro->getActiveSheet();
        $hoja->setTitle('Acta de Entrega');

        $borderThin  = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];

        $hoja->getColumnDimension('A')->setWidth(20);
        $hoja->getColumnDimension('B')->setWidth(30);
        $hoja->getColumnDimension('C')->setWidth(20);
        $hoja->getColumnDimension('D')->setWidth(30);

        // Header
        $hoja->setCellValue('A2', 'ACTA DE ENTREGA');
        $hoja->mergeCells('A2:D4');
        $hoja->getStyle('A2')->getFont()->setBold(true)->setSize(16);
        $hoja->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('A2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $hoja->getStyle('A2:D4')->applyFromArray($borderThin);

        $hoja->setCellValue('A6', 'SCZ-TGM-AC-125/25'); // Static per image, maybe dynamic but let's use numOt context
        $hoja->mergeCells('A6:D6');
        $hoja->getStyle('A6')->getFont()->setBold(true)->setUnderline(true);
        $hoja->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $hoja->setCellValue('A8', 'Fecha de Ingreso:');
        $hoja->setCellValue('B8', $orden->created_at ? $orden->created_at->format('Y-m-d') : '');
        $hoja->setCellValue('C8', 'Fecha Entrega:');
        $hoja->setCellValue('D8', $ae->fecha_entrega ?? '');

        $hoja->setCellValue('A9', 'Entregado A:');
        $hoja->setCellValue('B9', $ae->entregado_a ?? '');
        $hoja->setCellValue('C9', 'De:');
        $hoja->setCellValue('D9', $ae->de ?? '');

        $hoja->setCellValue('A10', 'Placa:');
        $hoja->setCellValue('B10', $orden->auto->placa ?? '');
        $hoja->setCellValue('C10', 'Kilometraje:');
        $hoja->setCellValue('D10', $orden->kilometraje ?? '');

        $hoja->setCellValue('A11', 'Clase de vehiculo:');
        $hoja->setCellValue('B11', $orden->auto->modelo ?? '');
        $hoja->setCellValue('C11', 'Objeto de la contratacion:');
        $hoja->setCellValue('D11', 'MANTENIMIENTO PREVENTIVO Y CORRECTIVO');

        $hoja->setCellValue('A12', 'Marca/ Tipo:');
        $hoja->setCellValue('B12', $orden->auto->marca->nombre ?? '');
        $hoja->setCellValue('C12', 'Direccion del Cliente:');
        $hoja->setCellValue('D12', $orden->grupoCliente->cliente->direccion ?? '');

        $hoja->getStyle('A8:A12')->getFont()->setBold(true);
        $hoja->getStyle('C8:C12')->getFont()->setBold(true);

        $row = 14;
        $hoja->setCellValue('A' . $row, 'ASUNTO: ' . ($ae->asunto ?? 'MANTENIMIENTO CORRECTIVO'));
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row)->getFont()->setBold(true);
        $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
        $row++;

        $hoja->setCellValue('A' . $row, 'De acuerdo a ingreso y salida de vehiculo motorizado con placa de control detallado en el presente documento, a continuacion se detalla cada uno de los servicios de mantenimiento correctivo ejecutados:');
        $hoja->mergeCells('A' . $row . ':D' . ($row + 1));
        $hoja->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $hoja->getStyle('A' . $row . ':D' . ($row + 1))->applyFromArray($borderThin);
        $row += 2;

        $hoja->setCellValue('A' . $row, 'DETALLE DEL SERVICIO:');
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row)->getFont()->setBold(true);
        $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
        $row++;

        $hoja->setCellValue('A' . $row, 'Mantenimiento realizado al vehiculo detallado');
        $hoja->mergeCells('A' . $row . ':B' . $row);
        $hoja->setCellValue('C' . $row, 'Según Orden de Servicio: ' . $numOt);
        $hoja->mergeCells('C' . $row . ':D' . $row);
        $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
        $hoja->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;

        if ($servicios && count($servicios) > 0) {
            $cont = 1;
            foreach ($servicios as $srv) {
                $hoja->setCellValue('A' . $row, $cont . ')');
                $hoja->setCellValue('B' . $row, $srv);
                $hoja->mergeCells('B' . $row . ':D' . $row);
                $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
                $row++;
                $cont++;
            }
        } else {
            $hoja->setCellValue('A' . $row, '1)');
            $hoja->setCellValue('B' . $row, 'Sin servicios registrados');
            $hoja->mergeCells('B' . $row . ':D' . $row);
            $hoja->getStyle('A' . $row . ':D' . $row)->applyFromArray($borderThin);
            $row++;
        }

        $row += 2;
        $hoja->setCellValue('A' . $row, 'En conformidad a lo descrito en el presente documento y en honor a la verdad, firmamos al pie de la misma.');
        $hoja->mergeCells('A' . $row . ':D' . $row);
        $row += 5;

        $hoja->setCellValue('A' . $row, 'Taller');
        $hoja->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('A' . ($row - 1))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $hoja->setCellValue('D' . $row, 'Cliente');
        $hoja->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('D' . ($row - 1))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $fileName = $this->generarNombreArchivo('Acta de Entrega', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $hoja->removeRow(1, 1);
        $this->_agregarLogosExcel($hoja, $orden, 'D', 1);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    /* ============================================================
     * FORMULARIO 11 — ACTA DE DEVOLUCIÓN DE REPUESTOS
     * ============================================================ */

    public function guardarActaDevolucionRepuesto(Request $request)
    {
        if ($request->ajax()) {
            $adr_id = $request->input('acta_id');
            $usuario = Auth::user();
            $orden_recepcion_id = $request->input('orden_recepcion_id');

            if ($adr_id) {
                $adr = ActaDevolucionRepuesto::find($adr_id);
                $adr->usuario_modificador_id = $usuario->id;
            } else {
                $adr = new ActaDevolucionRepuesto();
                $adr->usuario_creador_id = $usuario->id;
                $adr->orden_recepcion_id = $orden_recepcion_id;
            }

            $adr->observaciones = $request->input('observaciones');

            $repuestos = $request->input('repuestos', []);
            $repuestosProcesados = [];
            foreach ($repuestos as $item) {
                $repuestosProcesados[] = [
                    'cantidad' => $item['cantidad'] ?? '',
                    'descripcion' => $item['descripcion'] ?? '',
                    'ocultar_reporte' => isset($item['ocultar_reporte']) && $item['ocultar_reporte'] == '1'
                ];
            }
            $adr->repuestos = json_encode($repuestosProcesados);

            $adr->save();

            return response()->json([
                'estado' => true,
                'adr_id' => $adr->id
            ]);
        }
        return response()->json(['estado' => false]);
    }

    public function obtenerActaDevolucionRepuesto(Request $request)
    {
        if ($request->ajax()) {
            $orden_id = $request->input('orden_recepcion_id');

            $adr = ActaDevolucionRepuesto::where('orden_recepcion_id', $orden_id)->first();
            $ot = OrdenTrabajo::where('orden_recepcion_id', $orden_id)->first();
            $numOt = '';
            if ($ot) {
                $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
            }

            if ($adr) {
                $adr->repuestos = is_string($adr->repuestos) ? json_decode($adr->repuestos, true) : $adr->repuestos;
                return response()->json(['estado' => true, 'adr' => $adr, 'num_ot' => $numOt]);
            }

            $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_id)->first();
            $repuestosPlanos = [];

            if ($cotizacion) {
                $orden = OrdenRecepcion::find($orden_id);
                $cotizacion = $this->reagruparCotizacion($cotizacion, $orden->grupo_cliente_id);
                $rp = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : $cotizacion->repuestos;

                if (is_array($rp)) {
                    foreach ($rp as $item) {
                        $repuestosPlanos[] = [
                            'cantidad' => $item['cantidad'] ?? '',
                            'descripcion' => $item['nombre'] ?? '',
                            'ocultar_reporte' => false
                        ];
                    }
                }
            }

            return response()->json(['estado' => true, 'adr' => null, 'cotizacion_repuestos' => $repuestosPlanos, 'num_ot' => $numOt]);
        }
        return response()->json(['estado' => false]);
    }

    public function descargarPdfActaDevolucionRepuesto($id)
    {
        $adr = ActaDevolucionRepuesto::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($adr->orden_recepcion_id);

        $repuestos = is_string($adr->repuestos) ? json_decode($adr->repuestos, true) : $adr->repuestos;

        $ot = OrdenTrabajo::where('orden_recepcion_id', $adr->orden_recepcion_id)->first();
        $numOt = '';
        if ($ot) {
            $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
        }

        // Filtrar repuestos ocultos para el PDF
        $repuestosVisibles = array_filter($repuestos, function ($r) {
            return !isset($r['ocultar_reporte']) || $r['ocultar_reporte'] == false;
        });

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfActaDevolucionRepuesto', compact('adr', 'orden', 'repuestosVisibles', 'numOt'));
        $fileName = $this->generarNombreArchivo('Acta Devolucion de Repuestos', $orden);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarExcelActaDevolucionRepuesto($id)
    {
        $adr = ActaDevolucionRepuesto::findOrFail($id);
        $orden = OrdenRecepcion::with(['auto.marca', 'grupoCliente.cliente'])->findOrFail($adr->orden_recepcion_id);

        $repuestos = is_string($adr->repuestos) ? json_decode($adr->repuestos, true) : $adr->repuestos;
        $repuestosVisibles = array_filter($repuestos, function ($r) {
            return !isset($r['ocultar_reporte']) || $r['ocultar_reporte'] == false;
        });

        $ot = OrdenTrabajo::where('orden_recepcion_id', $adr->orden_recepcion_id)->first();
        $numOt = '';
        if ($ot) {
            $numOt = $ot->numero_orden_secuencial . '/' . $ot->anio;
        }

        $libro = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $hoja  = $libro->getActiveSheet();
        $hoja->setTitle('Acta Devolución Repuestos');

        $borderThin  = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];

        $hoja->getColumnDimension('A')->setWidth(15);
        $hoja->getColumnDimension('B')->setWidth(20);
        $hoja->getColumnDimension('C')->setWidth(20);
        $hoja->getColumnDimension('D')->setWidth(15);
        $hoja->getColumnDimension('E')->setWidth(20);
        $hoja->getColumnDimension('F')->setWidth(20);

        // Header
        $hoja->setCellValue('C2', 'ACTA DE DEVOLUCION DE REPUESTOS');
        $hoja->mergeCells('C2:E4');
        $hoja->getStyle('C2')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('00003366');
        $hoja->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('C2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $hoja->getStyle('C2:E4')->applyFromArray($borderThin);

        $hoja->setCellValue('A6', 'N° de Orden:');
        $hoja->setCellValue('B6', $numOt);
        $hoja->setCellValue('D6', 'Fecha:');
        $hoja->setCellValue('E6', $adr->created_at ? $adr->created_at->format('Y-m-d') : '');

        $cliente = $orden->grupoCliente->cliente ?? null;
        $hoja->setCellValue('A7', 'Empresa/ Cliente:');
        $hoja->setCellValue('B7', $cliente->nombres ?? '');
        $hoja->setCellValue('D7', 'NIT/ C.I.:');
        $hoja->setCellValue('E7', $cliente->nit ?? '');

        $hoja->setCellValue('A8', 'Contacto de ref.:');
        $hoja->setCellValue('B8', $cliente->numero_celular ?? '');
        $hoja->setCellValue('D8', 'Telefono/ Correo:');
        $hoja->setCellValue('E8', $cliente->numero_celular ?? '');

        $hoja->setCellValue('A9', 'Placa/ Chasis:');
        $hoja->setCellValue('B9', $orden->auto->placa ?? '');
        $hoja->setCellValue('D9', 'Kilometraje:');
        $hoja->setCellValue('E9', $orden->kilometraje ?? '');

        $hoja->setCellValue('A10', 'Clase de vehiculo:');
        $hoja->setCellValue('B10', $orden->auto->modelo ?? '');
        $hoja->setCellValue('D10', 'Objeto de la contratacion:');
        $hoja->setCellValue('E10', 'MANTENIMIENTO PREVENTIVO Y CORRECTIVO');

        $hoja->setCellValue('A11', 'Marca/ Tipo:');
        $hoja->setCellValue('B11', $orden->auto->marca->nombre ?? '');
        $hoja->setCellValue('D11', 'Direccion de cliente:');
        $hoja->setCellValue('E11', $cliente->direccion ?? '');

        $row = 13;
        $hoja->setCellValue('A' . $row, 'DETALLE DE ACTIVIDADES DEL VEHICULO');
        $hoja->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $hoja->setCellValue('A' . $row, 'De acuerdo a ingreso y salida de vehiculo motorizado con placa de control detallado en el presente documento, a continuacion se detalla cada uno de los REPUESTOS A DEVOLVER:');
        $hoja->mergeCells('A' . $row . ':F' . ($row + 1));
        $hoja->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $row += 2;

        $hoja->setCellValue('A' . $row, 'N°');
        $hoja->setCellValue('B' . $row, 'DESCRIPCIÓN');
        $hoja->mergeCells('B' . $row . ':E' . $row);
        $hoja->setCellValue('F' . $row, 'CANTIDAD');
        $hoja->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderThin);
        $hoja->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;

        if ($repuestosVisibles && count($repuestosVisibles) > 0) {
            $cont = 1;
            foreach ($repuestosVisibles as $rp) {
                $hoja->setCellValue('A' . $row, $cont);
                $hoja->setCellValue('B' . $row, $rp['descripcion'] ?? '');
                $hoja->mergeCells('B' . $row . ':E' . $row);
                $hoja->setCellValue('F' . $row, $rp['cantidad'] ?? '');
                $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderThin);
                $hoja->getStyle('A' . $row . ':A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $hoja->getStyle('F' . $row . ':F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $row++;
                $cont++;
            }
        } else {
            $hoja->setCellValue('A' . $row, '1');
            $hoja->setCellValue('B' . $row, 'No hay repuestos registrados para devolver.');
            $hoja->mergeCells('B' . $row . ':E' . $row);
            $hoja->setCellValue('F' . $row, '-');
            $hoja->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderThin);
            $row++;
        }

        $row += 2;
        $hoja->setCellValue('A' . $row, 'En conformidad a lo descrito en el presente documento y en honor a la verdad, firmamos al pie de la misma.');
        $hoja->mergeCells('A' . $row . ':F' . $row);
        $row += 5;

        $hoja->setCellValue('B' . $row, 'Taller');
        $hoja->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('B' . ($row - 1))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $hoja->setCellValue('E' . $row, 'Cliente');
        $hoja->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle('E' . ($row - 1))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $fileName = $this->generarNombreArchivo('Acta Devolucion de Repuestos', $orden) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $hoja->removeRow(1, 1);
        $this->_agregarLogosExcel($hoja, $orden, 'E', 1);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro);
        $writer->save('php://output');
        exit;
    }


    /* ============================================================
     * REPORTE MENSUAL DE MANTENIMIENTO
     * ============================================================ */

    private function agruparItemsPorCategoria($ot, $serviciosCliente)
    {
        $categorias = [
            'PREVENTIVO' => 0,
            'CORRECTIVO' => 0,
            'REPUESTOS' => 0,
            'OTROS' => 0
        ];

        // Función auxiliar para sumar costos
        $procesarArreglo = function ($items) use (&$categorias, $serviciosCliente) {
            if (!$items || !is_array($items)) return;
            foreach ($items as $item) {
                $nombre = $item['nombre'] ?? '';
                $costo = floatval($item['total'] ?? $item['costo_total'] ?? 0);

                // Buscar en el catálogo
                $cat = $serviciosCliente[$nombre] ?? 'OTROS'; // default a OTROS

                if (array_key_exists($cat, $categorias)) {
                    $categorias[$cat] += $costo;
                } else if ($cat == 'SUMINISTRO') {
                    // Si es suministro, se va a repuestos según tu UI o OTROS? 
                    // El usuario pidió "Repuestos (categoria REPUESTOS)", lo meteremos a REPUESTOS
                    $categorias['REPUESTOS'] += $costo;
                } else {
                    $categorias['OTROS'] += $costo;
                }
            }
        };

        $procesarArreglo($ot->mano_obra);
        $procesarArreglo($ot->repuestos);
        $procesarArreglo($ot->insumos);
        $procesarArreglo($ot->trabajos_tercero);

        $totalReparaciones = $categorias['CORRECTIVO'] + $categorias['REPUESTOS'];
        $sumatoriaTotal = $categorias['PREVENTIVO'] + $totalReparaciones + $categorias['OTROS']; // o el ot->total_general

        return [
            'PREVENTIVO' => $categorias['PREVENTIVO'],
            'CORRECTIVO' => $categorias['CORRECTIVO'],
            'REPUESTOS' => $categorias['REPUESTOS'],
            'OTROS' => $categorias['OTROS'],
            'TOTAL_REPARACIONES' => $totalReparaciones,
            'SUMATORIA_TOTAL' => $ot->total_general // usamos el general de la BD para exactitud
        ];
    }

    public function descargarReporteMensualPdf(Request $request)
    {
        $grupo_cliente_id = $request->query('grupo_cliente_id');
        $mes = $request->query('mes');
        $anio = $request->query('anio');

        $grupoCliente = GrupoCliente::with(['cliente'])->findOrFail($grupo_cliente_id);
        $grupo = Grupo::find($grupoCliente->grupo_id);

        $catalogo = ClienteServicio::where('grupo_cliente_id', $grupo_cliente_id)->get();
        $serviciosCliente = [];
        foreach ($catalogo as $cat) {
            $serviciosCliente[$cat->nombre] = $cat->categoria;
        }

        // Fetch ordenes de recepcion de este mes y año
        $ordenes = OrdenRecepcion::with('auto.marca')
            ->where('grupo_cliente_id', $grupo_cliente_id)
            ->whereYear('fecha_recepcion', $anio)
            ->whereMonth('fecha_recepcion', $mes)
            ->get();

        $ordenesTrabajo = collect();
        $resumen = [
            'PREVENTIVO' => 0,
            'CORRECTIVO' => 0,
            'REPUESTOS' => 0,
            'OTROS' => 0,
            'TOTAL' => 0
        ];

        foreach ($ordenes as $orden) {
            $ot = OrdenTrabajo::where('orden_recepcion_id', $orden->id)->first();
            if ($ot) {
                // Decode JSON arrays
                $ot->mano_obra = is_string($ot->mano_obra) ? json_decode($ot->mano_obra, true) : $ot->mano_obra;
                $ot->repuestos = is_string($ot->repuestos) ? json_decode($ot->repuestos, true) : $ot->repuestos;
                $ot->insumos = is_string($ot->insumos) ? json_decode($ot->insumos, true) : $ot->insumos;
                $ot->trabajos_tercero = is_string($ot->trabajos_tercero) ? json_decode($ot->trabajos_tercero, true) : $ot->trabajos_tercero;

                $ot->orden = $orden;

                $catCostos = $this->agruparItemsPorCategoria($ot, $serviciosCliente);
                $ot->costos_categorizados = $catCostos;

                $ordenesTrabajo->push($ot);

                $resumen['PREVENTIVO'] += $catCostos['PREVENTIVO'];
                $resumen['CORRECTIVO'] += $catCostos['CORRECTIVO'];
                $resumen['REPUESTOS'] += $catCostos['REPUESTOS'];
                $resumen['OTROS'] += $catCostos['OTROS'];
                $resumen['TOTAL'] += $ot->total_general;
            }
        }

        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $nombreMes = $meses[(int)$mes] ?? '';

        $pdf = Pdf::loadView('grupoCliente.formularios.pdfReporteMensual', compact('grupoCliente', 'grupo', 'ordenesTrabajo', 'resumen', 'mes', 'nombreMes', 'anio'))
            ->setPaper('a4', 'landscape'); // Lo ponemos apaisado para que entren las columnas

        $fileName = $this->generarNombreArchivo('Reporte Mensual ' . $nombreMes . ' ' . $anio, null, $grupoCliente);
        return $pdf->download($fileName . '.pdf');
    }

    public function descargarReporteMensualExcel(Request $request)
    {
        $grupo_cliente_id = $request->query('grupo_cliente_id');
        $mes = $request->query('mes');
        $anio = $request->query('anio');

        $grupoCliente = GrupoCliente::with(['cliente'])->findOrFail($grupo_cliente_id);
        $grupo = Grupo::find($grupoCliente->grupo_id);

        $catalogo = ClienteServicio::where('grupo_cliente_id', $grupo_cliente_id)->get();
        $serviciosCliente = [];
        foreach ($catalogo as $cat) {
            $serviciosCliente[$cat->nombre] = $cat->categoria;
        }

        $ordenes = OrdenRecepcion::with('auto.marca')
            ->where('grupo_cliente_id', $grupo_cliente_id)
            ->whereYear('fecha_recepcion', $anio)
            ->whereMonth('fecha_recepcion', $mes)
            ->get();

        $ordenesTrabajo = collect();
        $resumen = [
            'PREVENTIVO' => 0,
            'CORRECTIVO' => 0,
            'REPUESTOS' => 0,
            'OTROS' => 0,
            'TOTAL' => 0
        ];

        foreach ($ordenes as $orden) {
            $ot = OrdenTrabajo::where('orden_recepcion_id', $orden->id)->first();
            if ($ot) {
                $ot->mano_obra = is_string($ot->mano_obra) ? json_decode($ot->mano_obra, true) : $ot->mano_obra;
                $ot->repuestos = is_string($ot->repuestos) ? json_decode($ot->repuestos, true) : $ot->repuestos;
                $ot->insumos = is_string($ot->insumos) ? json_decode($ot->insumos, true) : $ot->insumos;
                $ot->trabajos_tercero = is_string($ot->trabajos_tercero) ? json_decode($ot->trabajos_tercero, true) : $ot->trabajos_tercero;

                $ot->orden = $orden;

                $catCostos = $this->agruparItemsPorCategoria($ot, $serviciosCliente);
                $ot->costos_categorizados = $catCostos;

                $ordenesTrabajo->push($ot);

                $resumen['PREVENTIVO'] += $catCostos['PREVENTIVO'];
                $resumen['CORRECTIVO'] += $catCostos['CORRECTIVO'];
                $resumen['REPUESTOS'] += $catCostos['REPUESTOS'];
                $resumen['OTROS'] += $catCostos['OTROS'];
                $resumen['TOTAL'] += $ot->total_general;
            }
        }

        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $nombreMes = $meses[(int)$mes] ?? '';

        $libro = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $borderThin  = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];

        // --- HOJA 1: RESUMEN MENSUAL ---
        $hojaResumen = $libro->getActiveSheet();
        $hojaResumen->setTitle('Resumen');

        $hojaResumen->getColumnDimension('A')->setWidth(15);
        $hojaResumen->getColumnDimension('B')->setWidth(15);
        $hojaResumen->getColumnDimension('C')->setWidth(15);
        $hojaResumen->getColumnDimension('D')->setWidth(20);
        $hojaResumen->getColumnDimension('E')->setWidth(20);
        $hojaResumen->getColumnDimension('F')->setWidth(20);
        $hojaResumen->getColumnDimension('G')->setWidth(15);
        $hojaResumen->getColumnDimension('H')->setWidth(20);
        $hojaResumen->getColumnDimension('I')->setWidth(20);

        $hojaResumen->setCellValue('A1', 'REPORTE MENSUAL DE MANTENIMIENTO');
        $hojaResumen->mergeCells('A1:I1');
        $hojaResumen->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('00003366');
        $hojaResumen->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $hojaResumen->setCellValue('A2', strtoupper($nombreMes) . ' ' . $anio);
        $hojaResumen->mergeCells('A2:I2');
        $hojaResumen->getStyle('A2')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('00003366');
        $hojaResumen->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 3;
        $headers = [
            'PLACA',
            'Kilometraje',
            'N° de Orden',
            'Mantenimiento Preventivo',
            'Mantenimiento Correctivo',
            'Repuestos',
            'Otros Ajustes',
            'Total Reparaciones',
            'Sumatoria Total Bs'
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $hojaResumen->setCellValue($col . $row, $h);
            $hojaResumen->getStyle($col . $row)->getFont()->setBold(true);
            $hojaResumen->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDEBF7');
            $hojaResumen->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hojaResumen->getStyle($col . $row)->getAlignment()->setWrapText(true);
            $col++;
        }
        $hojaResumen->getStyle('A' . $row . ':I' . $row)->applyFromArray($borderThin);
        $row++;

        foreach ($ordenesTrabajo as $ot) {
            $cat = $ot->costos_categorizados;

            $hojaResumen->setCellValue('A' . $row, $ot->orden->auto->placa ?? '');
            $hojaResumen->setCellValue('B' . $row, $ot->orden->kilometraje ?? '');
            $hojaResumen->setCellValue('C' . $row, $ot->numero_orden_secuencial);

            $hojaResumen->setCellValue('D' . $row, number_format($cat['PREVENTIVO'], 2, '.', ''));
            $hojaResumen->setCellValue('E' . $row, number_format($cat['CORRECTIVO'], 2, '.', ''));
            $hojaResumen->setCellValue('F' . $row, number_format($cat['REPUESTOS'], 2, '.', ''));
            $hojaResumen->setCellValue('G' . $row, number_format($cat['OTROS'], 2, '.', ''));

            $hojaResumen->setCellValue('H' . $row, number_format($cat['TOTAL_REPARACIONES'], 2, '.', ''));
            $hojaResumen->setCellValue('I' . $row, number_format($cat['SUMATORIA_TOTAL'], 2, '.', ''));

            $hojaResumen->getStyle('A' . $row . ':I' . $row)->applyFromArray($borderThin);
            $hojaResumen->getStyle('D' . $row . ':I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $row++;
        }

        // --- HOJAS ADICIONALES: ORDENES DE TRABAJO (Usando formato pdfOrdenTrabajoOficial / Excel de Ot) ---
        $sheetIndex = 1;
        foreach ($ordenesTrabajo as $ot) {
            $numOt = $ot->numero_orden_secuencial . '-' . $ot->anio;
            $hoja = $libro->createSheet($sheetIndex);
            $hoja->setTitle('OT ' . $numOt);

            $hoja->getColumnDimension('A')->setWidth(15);
            $hoja->getColumnDimension('B')->setWidth(50);
            $hoja->getColumnDimension('C')->setWidth(20);

            $hoja->setCellValue('C2', 'ORDEN DE TRABAJO OFICIAL');
            $hoja->mergeCells('C2:E4');
            $hoja->getStyle('C2')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('00003366');
            $hoja->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle('C2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $hoja->getStyle('C2:E4')->applyFromArray($borderThin);

            $hoja->setCellValue('A6', 'N° de Orden:');
            $hoja->setCellValue('B6', $numOt);
            $hoja->setCellValue('D6', 'Fecha:');
            $hoja->setCellValue('E6', $ot->fecha_emision ?? '');

            $cliente = $ot->orden->grupoCliente->cliente ?? null;
            $hoja->setCellValue('A7', 'Empresa/ Cliente:');
            $hoja->setCellValue('B7', $cliente->nombres ?? '');
            $hoja->setCellValue('D7', 'NIT/ C.I.:');
            $hoja->setCellValue('E7', $cliente->nit ?? '');

            $hoja->setCellValue('A8', 'Contacto de ref.:');
            $hoja->setCellValue('B8', $cliente->numero_celular ?? '');
            $hoja->setCellValue('D8', 'Telefono/ Correo:');
            $hoja->setCellValue('E8', $cliente->numero_celular ?? '');

            $hoja->setCellValue('A9', 'Placa/ Chasis:');
            $hoja->setCellValue('B9', $ot->orden->auto->placa ?? '');
            $hoja->setCellValue('D9', 'Kilometraje:');
            $hoja->setCellValue('E9', $ot->orden->kilometraje ?? '');

            $hoja->setCellValue('A10', 'Clase de vehiculo:');
            $hoja->setCellValue('B10', $ot->orden->auto->modelo ?? '');
            $hoja->setCellValue('D10', 'Objeto de la contratacion:');
            $hoja->setCellValue('E10', 'MANTENIMIENTO PREVENTIVO Y CORRECTIVO');

            $hoja->setCellValue('A11', 'Marca/ Tipo:');
            $hoja->setCellValue('B11', $ot->orden->auto->marca->nombre ?? '');
            $hoja->setCellValue('D11', 'Direccion de cliente:');
            $hoja->setCellValue('E11', $cliente->direccion ?? '');

            $row = 13;
            $hoja->setCellValue('A' . $row, 'DETALLE DE ACTIVIDADES DEL VEHICULO');
            $hoja->getStyle('A' . $row)->getFont()->setBold(true);
            $row += 2;

            $renderSection = function ($title, $items, $subtotal) use (&$hoja, &$row, $borderThin) {
                if ($items && count($items) > 0) {
                    $hoja->setCellValue('A' . $row, $title);
                    $hoja->mergeCells('A' . $row . ':E' . $row);
                    $hoja->getStyle('A' . $row)->getFont()->setBold(true);
                    $hoja->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderThin);
                    $hoja->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
                    $row++;

                    $hoja->setCellValue('A' . $row, 'N°');
                    $hoja->setCellValue('B' . $row, 'DESCRIPCIÓN');
                    $hoja->mergeCells('B' . $row . ':D' . $row);
                    $hoja->setCellValue('E' . $row, 'COSTO');
                    $hoja->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
                    $hoja->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderThin);
                    $hoja->getStyle('A' . $row . ':E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $row++;

                    $cont = 1;
                    foreach ($items as $item) {
                        $hoja->setCellValue('A' . $row, $cont);
                        $hoja->setCellValue('B' . $row, $item['nombre'] ?? '');
                        $hoja->mergeCells('B' . $row . ':D' . $row);
                        $hoja->setCellValue('E' . $row, number_format($item['total'] ?? $item['costo_total'] ?? 0, 2));
                        $hoja->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderThin);
                        $row++;
                        $cont++;
                    }

                    $hoja->setCellValue('D' . $row, 'SUBTOTAL');
                    $hoja->setCellValue('E' . $row, number_format($subtotal, 2));
                    $hoja->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true);
                    $hoja->getStyle('D' . $row . ':E' . $row)->applyFromArray($borderThin);
                    $row += 2;
                }
            };

            $renderSection('1 MANO DE OBRA', $ot->mano_obra, $ot->subtotal_mano_obra);
            $renderSection('2 REPUESTOS - ACCESORIOS - SUMINISTROS', $ot->repuestos, $ot->subtotal_repuestos);
            $renderSection('3 INSUMOS', $ot->insumos, $ot->subtotal_insumos);
            $renderSection('4 OTROS SERVICIOS REQUERIDOS', $ot->trabajos_tercero, $ot->subtotal_trabajos_tercero);

            $hoja->setCellValue('D' . $row, 'TOTAL GENERAL');
            $hoja->setCellValue('E' . $row, number_format($ot->total_general, 2));
            $hoja->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true);
            $hoja->getStyle('D' . $row . ':E' . $row)->applyFromArray($borderThin);
            $hoja->getStyle('D' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');

            $sheetIndex++;
        }

        $libro->setActiveSheetIndex(0);

        $fileName = $this->generarNombreArchivo('Reporte Mensual ' . $nombreMes . ' ' . $anio, null, $grupoCliente) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro);
        $writer->save('php://output');
        exit;
    }

    /* ============================================================
     * SINCRONIZACIÓN DE FORMULARIOS
     * ============================================================ */

    private function sincronizarCotizacionYOT($origen, $orden_recepcion_id, $usuario_id)
    {
        $orden = OrdenRecepcion::find($orden_recepcion_id);
        if (!$orden) return;

        $serviciosGrup = ClienteServicio::where('grupo_cliente_id', $orden->grupo_cliente_id)->get();
        $mapaCategorias = [];
        foreach ($serviciosGrup as $srv) {
            $mapaCategorias[$srv->nombre] = strtoupper($srv->categoria);
        }

        $cotizacion = Cotizacion::where('orden_recepcion_id', $orden_recepcion_id)->first();
        $ot = OrdenTrabajo::where('orden_recepcion_id', $orden_recepcion_id)->first();

        // 1. Obtener la fuente de verdad y generar las listas agrupadas
        $todosServicios = [];

        if ($origen === 'COTIZACION' && $cotizacion) {
            $prevs = is_string($cotizacion->preventivos) ? json_decode($cotizacion->preventivos, true) : ($cotizacion->preventivos ?? []);
            $corrs = is_string($cotizacion->correctivos) ? json_decode($cotizacion->correctivos, true) : ($cotizacion->correctivos ?? []);
            $reps  = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : ($cotizacion->repuestos ?? []);
            $todosServicios = array_merge($prevs, $corrs, $reps);
        } elseif ($origen === 'OT' && $ot) {
            $mano = is_string($ot->mano_obra) ? json_decode($ot->mano_obra, true) : ($ot->mano_obra ?? []);
            $reps = is_string($ot->repuestos) ? json_decode($ot->repuestos, true) : ($ot->repuestos ?? []);
            $ins  = is_string($ot->insumos) ? json_decode($ot->insumos, true) : ($ot->insumos ?? []);
            $terc = is_string($ot->trabajos_tercero) ? json_decode($ot->trabajos_tercero, true) : ($ot->trabajos_tercero ?? []);
            $todosServicios = array_merge($mano, $reps, $ins, $terc);
        } else {
            return; // No hay data origen
        }

        // 2. Re-agrupar todos los servicios para ambos destinos
        $n_preventivos = [];
        $n_correctivos = [];
        $n_reps_cotiz = [];

        $n_mano_obra = [];
        $n_repuestos_ot = [];
        $n_insumos_ot = [];
        $n_terceros_ot = [];

        $sub_preventivos = 0;
        $sub_correctivos = 0;
        $sub_reps_cotiz = 0;
        $sub_mano_obra = 0;
        $sub_repuestos_ot = 0;
        $sub_insumos_ot = 0;
        $sub_terceros_ot = 0;
        $total_general = 0;

        foreach ($todosServicios as $srv) {
            if (!is_array($srv)) continue;
            $nombre = $srv['nombre'] ?? '';
            $cat = $mapaCategorias[$nombre] ?? 'OTROS';
            $costoTotal = floatval($srv['total'] ?? $srv['costo_total'] ?? 0);
            $total_general += $costoTotal;

            // Para Cotización
            if ($cat === 'PREVENTIVO') {
                $n_preventivos[] = $srv;
                $sub_preventivos += $costoTotal;
            } elseif ($cat === 'CORRECTIVO') {
                $n_correctivos[] = $srv;
                $sub_correctivos += $costoTotal;
            } else {
                $n_reps_cotiz[] = $srv;
                $sub_reps_cotiz += $costoTotal;
            }

            // Para OT Oficial
            if (in_array($cat, ['PREVENTIVO', 'CORRECTIVO'])) {
                $n_mano_obra[] = $srv;
                $sub_mano_obra += $costoTotal;
            } elseif ($cat === 'REPUESTOS') {
                $n_repuestos_ot[] = $srv;
                $sub_repuestos_ot += $costoTotal;
            } elseif ($cat === 'SUMINISTRO') {
                $n_insumos_ot[] = $srv;
                $sub_insumos_ot += $costoTotal;
            } else {
                $n_terceros_ot[] = $srv;
                $sub_terceros_ot += $costoTotal;
            }
        }

        // 3. Actualizar o crear OT (si origen fue Cotizacion)
        if ($origen === 'COTIZACION' && $cotizacion) {
            if (!$ot) {
                $ot = new OrdenTrabajo();
                $ot->usuario_creador_id = $usuario_id;
                $ot->orden_recepcion_id = $orden_recepcion_id;

                $year = date('Y');
                $lastDoc = OrdenTrabajo::where('anio', $year)->orderBy('numero_orden_secuencial', 'desc')->first();
                $ot->numero_orden_secuencial = $lastDoc ? $lastDoc->numero_orden_secuencial + 1 : 1;
                $ot->anio = $year;
                $ot->fecha_emision = date('Y-m-d');
            } else {
                $ot->usuario_modificador_id = $usuario_id;
            }

            $ot->mano_obra = json_encode($n_mano_obra);
            $ot->repuestos = json_encode($n_repuestos_ot);
            $ot->insumos = json_encode($n_insumos_ot);
            $ot->trabajos_tercero = json_encode($n_terceros_ot);

            $ot->subtotal_mano_obra = $sub_mano_obra;
            $ot->subtotal_repuestos = $sub_repuestos_ot;
            $ot->subtotal_insumos = $sub_insumos_ot;
            $ot->subtotal_trabajos_tercero = $sub_terceros_ot;
            $ot->total_general = $total_general;
            $ot->save();
        }

        // 4. Actualizar Cotizacion (si origen fue OT)
        if ($origen === 'OT' && $ot) {
            if ($cotizacion) {
                $cotizacion->usuario_modificador_id = $usuario_id;
                $cotizacion->preventivos = json_encode($n_preventivos);
                $cotizacion->correctivos = json_encode($n_correctivos);
                $cotizacion->repuestos = json_encode($n_reps_cotiz);

                $cotizacion->subtotal_preventivos = $sub_preventivos;
                $cotizacion->subtotal_correctivos = $sub_correctivos;
                $cotizacion->subtotal_repuestos = $sub_reps_cotiz;
                $cotizacion->total_general = $total_general;
                $cotizacion->save();

                $this->sincronizarCotizacionYOT('COTIZACION', $cotizacion->orden_recepcion_id, $usuario_id);
            }
        }

        // 5. Sincronizar actas de repuestos (Formulario 8 y 11)
        $this->sincronizarActasRepuestos($orden_recepcion_id, $n_repuestos_ot, $usuario_id);
    }

    private function sincronizarActasRepuestos($orden_recepcion_id, $n_repuestos_ot, $usuario_id)
    {
        // Actualizamos RecepcionRepuesto (Formulario 8)
        $rr = RecepcionRepuesto::where('orden_recepcion_id', $orden_recepcion_id)->first();
        if ($rr) {
            $repuestosRR = is_string($rr->repuestos) ? json_decode($rr->repuestos, true) : ($rr->repuestos ?? []);
            $mapaOcultos = [];
            foreach ($repuestosRR as $r) {
                if (isset($r['nombre'])) {
                    $mapaOcultos[$r['nombre']] = $r['ocultar_reporte'] ?? false;
                }
            }

            $nuevosRR = [];
            foreach ($n_repuestos_ot as $item) {
                $item['ocultar_reporte'] = $mapaOcultos[$item['nombre'] ?? ''] ?? false;
                $nuevosRR[] = $item;
            }

            $rr->repuestos = json_encode($nuevosRR);
            $rr->usuario_modificador_id = $usuario_id;
            $rr->save();
        }

        // Actualizamos ActaDevolucionRepuesto (Formulario 11)
        $adr = ActaDevolucionRepuesto::where('orden_recepcion_id', $orden_recepcion_id)->first();
        if ($adr) {
            $repuestosADR = is_string($adr->repuestos) ? json_decode($adr->repuestos, true) : ($adr->repuestos ?? []);
            $mapaOcultosADR = [];
            foreach ($repuestosADR as $r) {
                if (isset($r['nombre'])) {
                    $mapaOcultosADR[$r['nombre']] = $r['ocultar_reporte'] ?? false;
                }
            }

            $nuevosADR = [];
            foreach ($n_repuestos_ot as $item) {
                $item['ocultar_reporte'] = $mapaOcultosADR[$item['nombre'] ?? ''] ?? false;
                $nuevosADR[] = $item;
            }

            $adr->repuestos = json_encode($nuevosADR);
            $adr->usuario_modificador_id = $usuario_id;
            $adr->save();
        }
    }

    private function generarNombreArchivo($titulo, $orden = null, $grupoCliente = null)
    {
        if ($orden) {
            $placa = $orden->auto->placa ?? 'SIN_PLACA';
            $nombres = trim(($orden->grupoCliente->cliente->nombres ?? '') . ' ' . ($orden->grupoCliente->cliente->ap_paterno ?? ''));
        } else if ($grupoCliente) {
            $placa = 'SIN_PLACA';
            $nombres = trim(($grupoCliente->cliente->nombres ?? '') . ' ' . ($grupoCliente->cliente->ap_paterno ?? ''));
        } else {
            $placa = 'SIN_PLACA';
            $nombres = 'SIN_NOMBRE';
        }

        $fileName = $placa . ' - ' . $nombres . ' - ' . $titulo;
        return preg_replace('/[\/\\\:\*\?\"\<\>\|]/', '-', $fileName);
    }
}
