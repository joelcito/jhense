<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario Autorizacion N° {{ $fa->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th, td { border: 1px solid #000; padding: 3px; }
        
        .bg-grey { background-color: #d9d9d9; }
        .bg-light-grey { background-color: #f2f2f2; }
        
        .header-title { font-size: 13px; font-weight: bold; text-align: center; }
        .section-title { font-size: 12px; font-weight: bold; background-color: #d9d9d9; }
        
        .no-border-table th, .no-border-table td { border: none !important; }
    </style>
</head>
<body>

    @include('grupoCliente.formularios.pdfHeader', ['titulo' => 'FORMULARIO DE AUTORIZACION DE CAMBIO DE REPUESTOS, PARTES, ACCESORIOS, SERVICIOS Y SUMINISTROS PARA VEHÍCULOS', 'rightCode' => 'RG-02-B-PP-1-DAC/UTR-2'])

    <table>
        <tr>
            <th class="text-center" style="width: 15%;">FECHA</th>
            <td class="text-center" style="width: 35%;">{{ $fa->fecha }}</td>
            <th class="text-center" style="width: 15%;">CITE</th>
            <td class="text-center" style="width: 35%;" colspan="2">{{ $fa->cite }}</td>
        </tr>
        <tr>
            <th colspan="2" class="text-center font-weight-bold">DATOS DEL VEHICULO</th>
            <td colspan="3" rowspan="3" style="text-align: center; vertical-align: bottom; height: 40px;">FIRMA/SELLO TALLER</td>
        </tr>
        <tr>
            <th class="text-center">PROPIETARIO DEL VEHICULO</th>
            <td class="text-center">{{ $orden->grupoCliente->cliente->nombres ?? '' }} {{ $orden->grupoCliente->cliente->ap_paterno ?? '' }}</td>
        </tr>
        <tr>
            <th class="text-center">MARCA</th>
            <td class="text-center">{{ $orden->auto->marca->nombre ?? '' }}</td>
        </tr>
        <tr>
            <th class="text-center">CLASE</th>
            <td class="text-center">{{ $orden->auto->modelo ?? '' }}</td>
            <th class="text-center">PLACA</th>
            <td class="text-center" colspan="2">{{ $orden->auto->placa ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="border: none;"></td>
            <th class="text-center">TIPO</th>
            <td class="text-center" colspan="2"></td>
        </tr>
    </table>

    <table style="margin-top: 5px;">
        <tr>
            <th class="text-left" style="font-size: 14px; padding: 5px;" colspan="6">DETALLE</th>
        </tr>
        
        @php
            $sections = [
                ['title' => 'Mantenimiento Preventivo', 'data' => $fa->preventivo, 'header' => 'Mano de obra'],
                ['title' => 'Mantenimiento Correctivo', 'data' => $fa->correctivo, 'header' => 'Mano de obra'],
                ['title' => 'Repuestos - Accesorios - Suministros', 'data' => $fa->repuestos_suministros, 'header' => 'DETALLE'],
                ['title' => 'Otros Servicios Requeridos', 'data' => $fa->otros, 'header' => 'DETALLE']
            ];
        @endphp

        @foreach($sections as $section)
            <tr>
                <th colspan="6" class="text-center section-title">{{ $section['title'] }}</th>
            </tr>
            <tr>
                <th width="8%" class="text-center">ITEM</th>
                <th width="48%" class="text-center">{{ $section['header'] }}</th>
                <th width="8%" class="text-center">CANT.</th>
                <th width="8%" class="text-center">UNID.</th>
                <th width="14%" class="text-center">P/UNIT.</th>
                <th width="14%" class="text-center">TOTAL</th>
            </tr>
            @php $subtotal = 0; @endphp
            @if(is_array($section['data']) && count($section['data']) > 0)
                @foreach($section['data'] as $item)
                <tr>
                    <td class="text-center">{{ $item['item'] ?? '' }}</td>
                    <td>{{ $item['nombre'] ?? '' }}</td>
                    <td class="text-center">{{ $item['cantidad'] ?? '0' }}</td>
                    <td class="text-center">{{ $item['unidad_medida'] ?? '' }}</td>
                    <td class="text-right">{{ number_format((float)($item['costo'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float)($item['total'] ?? 0), 2) }}</td>
                </tr>
                @php $subtotal += (float)($item['total'] ?? 0); @endphp
                @endforeach
            @else
                <tr><td colspan="6" class="text-center">Sin registros</td></tr>
            @endif
            <tr>
                <td colspan="4" style="border: none;"></td>
                <th class="text-right font-weight-bold">TOTAL</th>
                <td class="text-right font-weight-bold">{{ number_format($subtotal, 2) }}</td>
            </tr>
        @endforeach
        
        <tr>
            <td colspan="4" style="border: none;"></td>
            <th class="text-right font-weight-bold" style="font-size: 12px;">TOTAL GENERAL Bs.</th>
            <td class="text-right font-weight-bold" style="font-size: 12px;">{{ number_format($fa->total_general, 2) }}</td>
        </tr>
    </table>

    <table style="margin-top: 10px;">
        <tr>
            <th class="text-left font-weight-bold">OBSERVACIONES</th>
        </tr>
        <tr>
            <td style="height: 60px; vertical-align: top; font-style: italic;">
                {{ $fa->observaciones }}
            </td>
        </tr>
    </table>

    <table style="margin-top: 5px; text-align: center;">
        <tr>
            <td style="width: 25%; font-weight: bold; border-bottom: none;">SOLICITA Y VALIDA</td>
            <td style="width: 25%; font-weight: bold; border-bottom: none;">ELABORA</td>
            <td style="width: 25%; font-weight: bold; border-bottom: none;">AUTORIZA</td>
            <td style="width: 25%; font-weight: bold; border-bottom: none;">Vo.Bo. APRUEBA</td>
        </tr>
        <tr>
            <td style="height: 60px; border-top: none; border-bottom: none;"></td>
            <td style="height: 60px; border-top: none; border-bottom: none;"></td>
            <td style="height: 60px; border-top: none; border-bottom: none;"></td>
            <td style="height: 60px; border-top: none; border-bottom: none;"></td>
        </tr>
        <tr>
            <td style="font-size: 9px; border-top: none;">RESPONSABLE DEL VEHICULO</td>
            <td style="font-size: 9px; border-top: none;">RESPONSABLE DE TRANSPORTE</td>
            <td style="font-size: 9px; border-top: none;">FISCAL(ES) DEL SERVICIO</td>
            <td style="font-size: 9px; border-top: none;">NOMBRES Y APELLIDOS</td>
        </tr>
    </table>

    <div style="font-size: 9px; font-weight: bold; margin-top: 5px;">
        Nota: Para el Centro Corporativo de YPFB en La Paz, unicamente el inmediato superior en donde presta servicios el vehículo es quién dá el Vo.Bo. al presente Formulario.
    </div>

</body>
</html>
