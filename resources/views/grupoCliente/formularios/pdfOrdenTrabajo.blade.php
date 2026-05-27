<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden Trabajo N° {{ $cotizacion->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .title { font-size: 16px; margin-bottom: 20px; color: #003366; text-align: center; font-weight: bold; }
        
        .table-header { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-header th { background-color: #f4f4f4; border: 1px solid #ccc; padding: 5px; text-align: left; width: 25%; }
        .table-header td { border: 1px solid #ccc; padding: 5px; width: 25%; }
        
        .table-items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-items th { background-color: #003366; color: white; border: 1px solid #003366; padding: 5px; text-align: center; }
        .table-items td { border: 1px solid #ccc; padding: 5px; text-align: center; }
        
        .bg-light { background-color: #f4f4f4; }
        .total-box { font-size: 14px; color: #d9534f; }
    </style>
</head>
<body>

    @include('grupoCliente.formularios.pdfHeader', ['titulo' => 'ORDEN DE TRABAJOS REALIZADOS'])

    <table class="table-header">
        <tr>
            <th>VEHÍCULOS:</th>
            <td>{{ $orden->auto->marca->nombre ?? '' }} {{ $orden->auto->modelo ?? '' }}</td>
            <th>ORDEN DE SERVICIO N°:</th>
            <td class="text-center font-weight-bold" style="color: red; font-size: 14px;">{{ $orden->id }}</td>
        </tr>
        <tr>
            <th>PLACAS:</th>
            <td>{{ $orden->auto->placa ?? '' }}</td>
            <th>KILOMETRAJE ACTUAL:</th>
            <td>{{ $orden->kilometraje ?? '' }}</td>
        </tr>
        <tr>
            <th>EMPRESA:</th>
            <td>{{ $orden->grupoCliente->cliente->nombres ?? '' }} {{ $orden->grupoCliente->cliente->ap_paterno ?? '' }}</td>
            <th>FECHA DE INGRESO:</th>
            <td>{{ \Carbon\Carbon::parse($orden->created_at)->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <th>SERVICIO/TALLER:</th>
            <td>{{ $cotizacion->servicio_taller }}</td>
            <th>FECHA DE SALIDA:</th>
            <td>
                {{ $cotizacion->fecha_salida }}<br>
                <small>Días hábiles: {{ $cotizacion->dias_habiles }}</small>
            </td>
        </tr>
    </table>

    @php
        $preventivos = is_string($cotizacion->preventivos) ? json_decode($cotizacion->preventivos, true) : $cotizacion->preventivos;
        $correctivos = is_string($cotizacion->correctivos) ? json_decode($cotizacion->correctivos, true) : $cotizacion->correctivos;
        $repuestos = is_string($cotizacion->repuestos) ? json_decode($cotizacion->repuestos, true) : $cotizacion->repuestos;
        
        $sections = [
            ['title' => 'MANTENIMIENTO PREVENTIVO', 'data' => $preventivos, 'subtotal' => $cotizacion->subtotal_preventivos],
            ['title' => 'MANTENIMIENTO CORRECTIVO', 'data' => $correctivos, 'subtotal' => $cotizacion->subtotal_correctivos],
            ['title' => 'REPUESTOS DE VEHÍCULOS', 'data' => $repuestos, 'subtotal' => $cotizacion->subtotal_repuestos]
        ];
    @endphp

    @foreach($sections as $section)
        <table class="table-items">
            <thead>
                <tr>
                    <th width="10%">ITEM</th>
                    <th width="40%">{{ $section['title'] }}</th>
                    <th width="10%">Cantidad</th>
                    <th width="15%">Unidad</th>
                    <th width="12%">Precio Uni.</th>
                    <th width="13%">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($section['data']) && count($section['data']) > 0)
                    @foreach($section['data'] as $item)
                    <tr>
                        <td>{{ $item['item'] ?? '' }}</td>
                        <td style="text-align: left;">{{ $item['nombre'] ?? '' }}</td>
                        <td>{{ $item['cantidad'] ?? '0' }}</td>
                        <td>{{ $item['unidad_medida'] ?? '' }}</td>
                        <td class="text-right">{{ number_format((float)($item['costo'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($item['total'] ?? 0), 2) }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center text-muted">Sin registros</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="5" class="text-right font-weight-bold bg-light">Sub. TOTAL Bs.</td>
                    <td class="text-right font-weight-bold bg-light">{{ number_format((float)$section['subtotal'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <table class="table-items" style="width: 50%; float: right; margin-top: 20px;">
        <tr>
            <th class="text-right" style="background-color: #f4f4f4; color: #333; border-color: #ccc;">SUMA TOTAL Bs.</th>
            <td class="text-right font-weight-bold total-box" style="width: 30%;">{{ number_format((float)$cotizacion->total_general, 2) }}</td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <div style="margin-top: 80px; width: 100%;">
        <table style="width: 100%; text-align: center; border: none;">
            <tr>
                <td style="width: 50%; border: none;">
                    __________________________________<br>
                    <strong>Firma Encargado de Taller</strong>
                </td>
                <td style="width: 50%; border: none;">
                    __________________________________<br>
                    <strong>Firma Aprobación Cliente</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
