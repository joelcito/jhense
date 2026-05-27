<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual {{ $nombreMes }} {{ $anio }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 4px; }
        
        .header-title { font-size: 18px; font-weight: bold; text-align: center; color: #00008B; text-decoration: underline; }
        .page-break { page-break-after: always; }
        
        .bg-header { background-color: #d9e1f2; } /* Light blue per excel */
        .bg-gray { background-color: #f4f4f4; }
        .bg-yellow { background-color: #ffff00; }
        .no-border { border: none !important; }

        /* OT CSS */
        .ot-title { font-size: 16px; margin-bottom: 20px; color: #003366; text-align: center; font-weight: bold; }
        .ot-table-header { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .ot-table-header th { background-color: #f4f4f4; border: 1px solid #ccc; padding: 5px; text-align: left; width: 20%; color: #333; }
        .ot-table-header td { border: 1px solid #ccc; padding: 5px; width: 30%; color: #333; }
        
        .ot-table-items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .ot-table-items th { background-color: #003366; color: white; border: 1px solid #003366; padding: 5px; text-align: center; }
        .ot-table-items td { border: 1px solid #ccc; padding: 5px; text-align: center; color: #333; }
        
        .ot-bg-light { background-color: #f4f4f4; }
        .ot-total-box { font-size: 14px; color: #d9534f; }
    </style>
</head>
<body>

    <!-- PORTADA / RESUMEN -->
    @include('grupoCliente.formularios.pdfHeader', ['titulo' => 'REPORTE MENSUAL DE MANTENIMIENTO<br><span style="font-size: 14px;">' . strtoupper($nombreMes) . ' ' . $anio . '</span>'])

    <table style="width: 100%; margin: 0 auto;">
        <thead>
            <tr>
                <th class="text-center bg-header font-weight-bold">PLACA</th>
                <th class="text-center bg-header font-weight-bold">Kilometraje</th>
                <th class="text-center bg-header font-weight-bold">N° de Orden</th>
                <th class="text-center bg-header font-weight-bold">Mantenimiento<br>Preventivo</th>
                <th class="text-center bg-header font-weight-bold">Mantenimiento<br>Correctivo</th>
                <th class="text-center bg-header font-weight-bold">Repuestos</th>
                <th class="text-center bg-header font-weight-bold">Otros Ajustes</th>
                <th class="text-center bg-header font-weight-bold">Total<br>Reparaciones</th>
                <th class="text-center bg-header font-weight-bold">Sumatoria<br>Total Bs</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ordenesTrabajo as $ot)
                @php $cat = $ot->costos_categorizados; @endphp
                <tr>
                    <td class="text-center">{{ $ot->orden->auto->placa ?? '' }}</td>
                    <td class="text-center">{{ $ot->orden->kilometraje ?? '' }}</td>
                    <td class="text-center">{{ $ot->numero_orden_secuencial }}</td>
                    <td class="text-right">{{ number_format($cat['PREVENTIVO'], 2, '.', '') }}</td>
                    <td class="text-right">{{ number_format($cat['CORRECTIVO'], 2, '.', '') }}</td>
                    <td class="text-right">{{ number_format($cat['REPUESTOS'], 2, '.', '') }}</td>
                    <td class="text-right">{{ number_format($cat['OTROS'], 2, '.', '') }}</td>
                    <td class="text-right">{{ number_format($cat['TOTAL_REPARACIONES'], 2, '.', '') }}</td>
                    <td class="text-right">{{ number_format($cat['SUMATORIA_TOTAL'], 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach($ordenesTrabajo as $ot)
        <div class="page-break"></div>
        
        <!-- ENCABEZADO ORDEN DE TRABAJO (IDENTICO AL OFICIAL) -->
        @include('grupoCliente.formularios.pdfHeader', ['titulo' => 'ORDEN DE TRABAJO', 'orden' => $ot->orden])

        <table class="ot-table-header">
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
                <td>{{ $ot->orden->grupoCliente->cliente->nombres ?? '' }} {{ $ot->orden->grupoCliente->cliente->ap_paterno ?? '' }}</td>
                <th>NIT / C.I.:</th>
                <td>{{ $ot->orden->grupoCliente->cliente->nit ?? '' }}</td>
            </tr>
            <tr>
                <th>Vehículos:</th>
                <td>{{ $ot->orden->auto->marca->nombre ?? '' }} {{ $ot->orden->auto->modelo ?? '' }}</td>
                <th>Placas / Chasis:</th>
                <td>{{ $ot->orden->auto->placa ?? '' }}</td>
            </tr>
            <tr>
                <th>Kilometraje:</th>
                <td>{{ $ot->orden->kilometraje ?? '' }}</td>
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
            <table class="ot-table-items" style="margin-top: 0; border-top: none;">
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
                            <td class="text-right">{{ number_format((float)($item['total'] ?? $item['costo_total'] ?? 0), 2) }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center text-muted">Sin registros</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="5" class="text-right font-weight-bold ot-bg-light">sub TOTAL Bs.</td>
                        <td class="text-right font-weight-bold ot-bg-light">{{ number_format((float)$section['subtotal'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <table class="ot-table-items" style="width: 50%; float: right; margin-top: 10px;">
            <tr>
                <th class="text-right" style="background-color: #f4f4f4; color: #333; border-color: #ccc;">Total (Bs.):</th>
                <td class="text-right font-weight-bold ot-total-box" style="width: 30%;">{{ number_format((float)$ot->total_general, 2) }}</td>
            </tr>
        </table>

        <div style="clear: both;"></div>

        <div style="margin-top: 60px; width: 100%;">
            <table style="width: 100%; text-align: center; border: none;" class="no-border">
                <tr>
                    <td style="width: 100%; border: none;">
                        __________________________________<br>
                        <strong>Firma del Responsable</strong>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

</body>
</html>
