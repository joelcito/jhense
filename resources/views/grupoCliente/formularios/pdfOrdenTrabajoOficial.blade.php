<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Trabajo N° {{ $ot->numero_orden_secuencial ?? $ot->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .title { font-size: 16px; margin-bottom: 20px; color: #003366; text-align: center; font-weight: bold; }
        
        .table-header { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-header th { background-color: #f4f4f4; border: 1px solid #ccc; padding: 5px; text-align: left; width: 20%; }
        .table-header td { border: 1px solid #ccc; padding: 5px; width: 30%; }
        
        .table-items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-items th { background-color: #003366; color: white; border: 1px solid #003366; padding: 5px; text-align: center; }
        .table-items td { border: 1px solid #ccc; padding: 5px; text-align: center; }
        
        .bg-light { background-color: #f4f4f4; }
        .total-box { font-size: 14px; color: #d9534f; }
    </style>
</head>
<body>

    @include('grupoCliente.formularios.pdfHeader', ['titulo' => 'ORDEN DE TRABAJO'])

    <table class="table-header">
        <tr>
            <th>N° de Orden:</th>
            <td class="text-center font-weight-bold" style="color: red; font-size: 14px;">
                {{ $ot->numero_orden_secuencial ?? $ot->id }}/{{ $ot->anio ?? date('y') }}
            </td>
            <th>Fecha de Emisión:</th>
            <td>{{ $ot->fecha_emision ?? '' }}</td>
        </tr>
        <tr>
            <th>Empresa / Cliente:</th>
            <td>{{ $orden->grupoCliente->cliente->nombres ?? '' }} {{ $orden->grupoCliente->cliente->ap_paterno ?? '' }}</td>
            <th>NIT / C.I.:</th>
            <td>{{ $orden->grupoCliente->cliente->nit ?? '' }}</td>
        </tr>
        <tr>
            <th>Vehículos:</th>
            <td>{{ $orden->auto->marca->nombre ?? '' }} {{ $orden->auto->modelo ?? '' }}</td>
            <th>Placas / Chasis:</th>
            <td>{{ $orden->auto->placa ?? '' }}</td>
        </tr>
        <tr>
            <th>Kilometraje:</th>
            <td>{{ $orden->kilometraje ?? '' }}</td>
            <th>Contacto Ref.:</th>
            <td></td>
        </tr>
    </table>

    <h4 style="margin: 0; padding: 5px; background-color: #003366; color: white; text-align: center; border: 1px solid #003366;">
        TIPO DE SERVICIO: MANTENIMIENTO
    </h4>

    @php
        $mano_obra = is_string($ot->mano_obra) ? json_decode($ot->mano_obra, true) : $ot->mano_obra;
        $repuestos = is_string($ot->repuestos) ? json_decode($ot->repuestos, true) : $ot->repuestos;
        $insumos = is_string($ot->insumos) ? json_decode($ot->insumos, true) : $ot->insumos;
        $trabajos_tercero = is_string($ot->trabajos_tercero) ? json_decode($ot->trabajos_tercero, true) : $ot->trabajos_tercero;
        
        $sections = [
            ['title' => 'MANO DE OBRA', 'data' => $mano_obra, 'subtotal' => $ot->subtotal_mano_obra],
            ['title' => 'REPUESTOS', 'data' => $repuestos, 'subtotal' => $ot->subtotal_repuestos],
            ['title' => 'INSUMOS', 'data' => $insumos, 'subtotal' => $ot->subtotal_insumos],
            ['title' => 'TRABAJOS A TERCERO', 'data' => $trabajos_tercero, 'subtotal' => $ot->subtotal_trabajos_tercero]
        ];
    @endphp

    @foreach($sections as $section)
        <table class="table-items" style="margin-top: 0; border-top: none;">
            <thead>
                <tr>
                    <th colspan="6" style="background-color: #e6f7ff; color: #003366; border-top: none;">{{ $section['title'] }}</th>
                </tr>
                <tr>
                    <th width="12%">N° Item Contrato</th>
                    <th width="35%">Descripción</th>
                    <th width="12%">Unidad Medida</th>
                    <th width="12%">Cantidad</th>
                    <th width="12%">Precio Unit.</th>
                    <th width="17%">Precio Total (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($section['data']) && count($section['data']) > 0)
                    @foreach($section['data'] as $item)
                    <tr>
                        <td>{{ $item['item'] ?? '' }}</td>
                        <td style="text-align: left;">{{ $item['nombre'] ?? '' }}</td>
                        <td>{{ $item['unidad_medida'] ?? '' }}</td>
                        <td>{{ $item['cantidad'] ?? '0' }}</td>
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
                    <td colspan="5" class="text-right font-weight-bold bg-light">sub TOTAL Bs.</td>
                    <td class="text-right font-weight-bold bg-light">{{ number_format((float)$section['subtotal'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <table class="table-items" style="width: 50%; float: right; margin-top: 10px;">
        <tr>
            <th class="text-right" style="background-color: #f4f4f4; color: #333; border-color: #ccc;">Total (Bs.):</th>
            <td class="text-right font-weight-bold total-box" style="width: 30%;">{{ number_format((float)$ot->total_general, 2) }}</td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <div style="margin-top: 60px; width: 100%;">
        <table style="width: 100%; text-align: center; border: none;">
            <tr>
                <td style="width: 100%; border: none;">
                    __________________________________<br>
                    <strong>Firma del Responsable</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
