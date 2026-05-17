<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Orden N° {{ $ot->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .title { font-size: 16px; margin-bottom: 20px; text-align: center; font-weight: bold; text-decoration: underline; color: #003366; }
        .table-header { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-header th { background-color: #f4f4f4; border: 1px solid #ccc; padding: 5px; text-align: left; width: 25%; font-weight: bold; }
        .table-header td { border: 1px solid #ccc; padding: 5px; width: 25%; }
        .table-items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .table-items th { background-color: #003366; color: white; border: 1px solid #003366; padding: 5px; text-align: center; }
        .table-items td { border: 1px solid #ccc; padding: 4px 5px; text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bg-light { background-color: #f4f4f4; }
        .bold { font-weight: bold; }
        .total-cell { font-size: 13px; font-weight: bold; color: #c00; }
    </style>
</head>
<body>

    <div class="title">ORDEN  DE TRABAJOS REALIZADOS</div>

    <table class="table-header">
        <tr>
            <th>VEHICULOS:</th>
            <td>{{ $orden->auto->marca->nombre ?? '' }} {{ $orden->auto->modelo ?? '' }}</td>
            <th>ORDEN DE SERVICIO:&nbsp;&nbsp;N°</th>
            <td class="text-right bold" style="font-size:13px; color:red;">{{ $orden->id }}</td>
        </tr>
        <tr>
            <th>PLACAS:</th>
            <td>{{ $orden->auto->placa ?? '' }}</td>
            <th>KILOMETRAJE ACTUAL:</th>
            <td>{{ $orden->kilometraje ?? '' }}</td>
        </tr>
        <tr>
            <th>EMPRESA :</th>
            <td>{{ $orden->grupoCliente->cliente->nombres ?? '' }} {{ $orden->grupoCliente->cliente->ap_paterno ?? '' }}</td>
            <th>FECHA DE INGRESO:</th>
            <td>{{ \Carbon\Carbon::parse($orden->created_at)->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <th>SERVICIO/TALLER:</th>
            <td>{{ $ot->servicio_taller ?? '' }}</td>
            <th>FECHA DE SALIDA:</th>
            <td>{{ $ot->fecha_salida ?? '' }}</td>
        </tr>
    </table>

    @php
        $sections = [
            ['title' => 'MANTENIMIENTO  PREVENTIVO', 'data' => $ot->preventivos],
            ['title' => 'MANTENIMIENTO  CORRECTIVO', 'data' => $ot->correctivos],
            ['title' => 'REPUESTOS  DE  VEHICULOS',  'data' => $ot->repuestos],
        ];
        $props = ['subtotal_preventivos', 'subtotal_correctivos', 'subtotal_repuestos'];
    @endphp

    @foreach($sections as $k => $section)
        <table class="table-items">
            <thead>
                <tr>
                    <th width="8%">ITEM</th>
                    <th width="44%">{{ $section['title'] }}</th>
                    <th width="10%">Cantidad</th>
                    <th width="14%">Unidad</th>
                    <th width="12%">Precio Unita</th>
                    <th width="12%">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($section['data']) && count($section['data']) > 0)
                    @foreach($section['data'] as $item)
                    <tr>
                        <td>{{ $item['item'] ?? '' }}</td>
                        <td class="text-left">{{ $item['nombre'] ?? '' }}</td>
                        <td>{{ $item['cantidad'] ?? '0' }}</td>
                        <td>{{ $item['unidad_medida'] ?? '' }}</td>
                        <td class="text-right">{{ number_format((float)($item['costo'] ?? 0), 2) }}</td>
                        <td class="text-right">-</td>
                    </tr>
                    @endforeach
                @else
                    @for($i = 0; $i < 4; $i++)
                    <tr><td></td><td></td><td></td><td></td><td></td><td class="text-right">-</td></tr>
                    @endfor
                @endif
                <tr>
                    <td colspan="5" class="text-right bold bg-light">Sub. TOTAL</td>
                    <td class="text-right bold bg-light">{{ number_format((float)$ot->{$props[$k]}, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <table style="width: 50%; float: right; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <td class="text-right bold bg-light" style="border: 1px solid #ccc; padding: 5px; width: 65%;">SUMA TOTAL Bs.</td>
            <td class="text-right total-cell" style="border: 1px solid #ccc; padding: 5px;">{{ number_format((float)$ot->total_general, 2) }}</td>
        </tr>
    </table>

    <div style="clear:both; margin-top: 80px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: none; text-align: center;">
                    __________________________________<br>
                    <strong>Responsable del Taller</strong>
                </td>
                <td style="width: 50%; border: none; text-align: center;">
                    __________________________________<br>
                    <strong>Aprobado por el Cliente</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
