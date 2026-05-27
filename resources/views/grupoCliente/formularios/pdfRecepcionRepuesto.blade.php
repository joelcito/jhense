<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recepción Repuestos N° {{ $rr->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th, td { border: 1px solid #000; padding: 4px; }
        
        .header-title { font-size: 14px; font-weight: bold; text-align: center; }
        
        .no-border-table th, .no-border-table td { border: none !important; }
        .bg-light { background-color: #f2f2f2; }
    </style>
</head>
<body>

    @include('grupoCliente.formularios.pdfHeader', ['titulo' => 'FORMULARIO DE RECEPCIÓN DE REPUESTOS Y ACCESORIOS USADOS', 'rightCode' => 'RG-03-B-PP-1-DAC/UTR-2'])

    <table>
        <tr>
            <th class="text-center" style="width: 15%;">FECHA</th>
            <td class="text-center" style="width: 35%;">{{ $rr->fecha }}</td>
            <th class="text-center" style="width: 20%;">Form.de Mant.</th>
            <td class="text-center" style="width: 30%;">{{ $numOt }}</td>
        </tr>
        <tr>
            <th colspan="4" class="text-center font-weight-bold" style="font-size: 13px;">DATOS DEL VEHÍCULO</th>
        </tr>
        <tr>
            <th class="text-center" style="width: 15%;">MARCA</th>
            <td class="text-center" style="width: 35%;">{{ $orden->auto->marca->nombre ?? '' }}</td>
            <th class="text-center" style="width: 15%;">PLACA</th>
            <td class="text-center" style="width: 35%;">{{ $orden->auto->placa ?? '' }}</td>
        </tr>
        <tr>
            <th class="text-center">CLASE</th>
            <td class="text-center">{{ $orden->auto->modelo ?? '' }}</td>
            <th class="text-center">TIPO</th>
            <td class="text-center"></td>
        </tr>
    </table>

    <table style="margin-top: 5px;">
        <tr>
            <th class="text-center" style="font-size: 15px; padding: 5px;" colspan="5">DETALLE</th>
        </tr>
        <tr>
            <td colspan="5" class="text-left" style="font-size: 10px;">Llenar la siguiente información, en caso de no adjuntar el detalle de repuestos emitido por el taller mecánico</td>
        </tr>
        <tr class="bg-light">
            <th width="8%" class="text-center">N°</th>
            <th width="62%" class="text-center">REPUESTOS</th>
            <th width="15%" class="text-center">CANT.</th>
            <th width="15%" class="text-center">UNIDAD</th>
        </tr>
        
        @if(is_array($repuestosVisible) && count($repuestosVisible) > 0)
            @php $cont = 1; @endphp
            @foreach($repuestosVisible as $item)
            <tr>
                <td class="text-center">{{ $cont }}</td>
                <td>{{ $item['nombre'] ?? '' }}</td>
                <td class="text-center">{{ $item['cantidad'] ?? '0' }}</td>
                <td class="text-center">{{ $item['unidad_medida'] ?? '' }}</td>
            </tr>
            @php $cont++; @endphp
            @endforeach
        @else
            <tr><td colspan="4" class="text-center">Sin registros</td></tr>
        @endif
        
    </table>

    <table style="margin-top: 5px; width: 100%; border: none;">
        <tr>
            <td style="width: 60%; vertical-align: top; padding: 0; padding-right: 5px; border: none;">
                <table style="width: 100%; margin: 0;">
                    <tr>
                        <th class="text-left font-weight-bold" style="border-bottom: none;">OBSERVACIONES</th>
                    </tr>
                    <tr>
                        <td style="height: 80px; vertical-align: top; font-style: italic; border-top: none;">
                            {{ $rr->observaciones }}
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; vertical-align: top; padding: 0; border: none;">
                <table style="width: 100%; margin: 0;">
                    <tr>
                        <th class="text-left font-weight-bold" style="border-bottom: none;">Vo. Bo. ENCARGADO DE TRANSPORTE</th>
                    </tr>
                    <tr>
                        <td style="height: 35px; border-top: none; border-bottom: none;"></td>
                    </tr>
                    <tr>
                        <td class="text-center font-weight-bold" style="height: 30px; vertical-align: bottom; border-top: none;">FIRMA/SELLO AUTORIDAD COMPETENTE</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="margin-top: 5px; width: 100%; border: none;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 0; padding-right: 2px; border: none;">
                <table style="width: 100%; margin: 0;">
                    <tr>
                        <td style="vertical-align: top; height: 90px; border: 1px solid #000; padding: 0;">
                            <div style="font-weight: bold; padding: 3px;">ENTREGADO POR:</div>
                            <div style="height: 50px;"></div>
                            <div class="text-center font-weight-bold" style="font-size: 9px; padding-bottom: 3px;">CONDUCTOR DEL VEHICULO</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding: 0; padding-left: 2px; border: none;">
                <table style="width: 100%; margin: 0;">
                    <tr>
                        <td style="vertical-align: top; height: 90px; border: 1px solid #000; padding: 0;">
                            <div style="font-weight: bold; padding: 3px;">RECIBIDO POR:</div>
                            <div style="height: 50px;"></div>
                            <div class="text-center font-weight-bold" style="font-size: 9px; padding-bottom: 3px;">FISCAL DEL SERVICIO</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
