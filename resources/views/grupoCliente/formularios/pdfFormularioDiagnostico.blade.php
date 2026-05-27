<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario para Diagnóstico de Mantenimiento de Vehículos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.2; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 4px; }
        .text-center { text-align: center; }
        .font-weight-bold { font-weight: bold; }
        .bg-light { background-color: #f0f0f0; }
        
        .header-table td { border: 1px solid #000; text-align: center; font-weight: bold; }
        .header-table .title { font-size: 14px; }
        
        .main-title { background-color: #d9d9d9; font-weight: bold; text-align: center; font-size: 12px; }
        
        .section-title { background-color: #e6e6e6; font-weight: bold; text-transform: uppercase; }
        
        .column-left { width: 60%; float: left; padding-right: 2%; box-sizing: border-box; }
        .column-right { width: 38%; float: left; box-sizing: border-box; }
        .clearfix::after { content: ""; clear: both; display: table; }
        
        .servicios-table th { background-color: #f2f2f2; text-align: left; }
        
        .firma-box { border: 1px solid #000; height: 100px; text-align: center; vertical-align: top; padding-top: 5px; }
    </style>
</head>
<body>

    @include('grupoCliente.formularios.pdfHeader', [
        'titulo' => 'FORMULARIO PARA DIAGNOSTICO DE MANTENIMIENTO DE VEHICULOS',
        'rightCode' => 'RG-01-B-PP-1-DAC/UTR-2, ' . \Carbon\Carbon::parse($form->created_at ?? now())->format('d de F de Y')
    ])

    <table>
        <tr>
            <td class="main-title">DIRECCION DE ADMINISTRACION CORPORATIVA – DAC</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="4" class="section-title">DATOS GENERALES</td>
        </tr>
        <tr>
            <td width="25%">RESPONSABLE DE VEHICULO</td>
            <td colspan="3">{{ $form->responsable_vehiculo ?? '' }}</td>
        </tr>
        <tr>
            <td>VEHICULO ASIGNADO A</td>
            <td colspan="3">{{ $form->vehiculo_asignado_a ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="4" class="section-title">CARACTERISTICAS DEL VEHICULO</td>
        </tr>
        <tr>
            <td>AÑO</td>
            <td width="25%">{{ $orden->auto->anio ?? '' }}</td>
            <td width="25%">MARCA</td>
            <td width="25%">{{ $orden->auto->marca->nombre ?? '' }}</td>
        </tr>
        <tr>
            <td>CLASE</td>
            <td>{{ $orden->tipo_unidad ?? '' }}</td>
            <td>PLACA</td>
            <td>{{ $orden->auto->placa ?? '' }}</td>
        </tr>
        <tr>
            <td>TIPO</td>
            <td>{{ $orden->auto->tipo ?? '' }}</td>
            <td>MODELO</td>
            <td>{{ $orden->auto->modelo ?? '' }}</td>
        </tr>
        <tr>
            <td>Km/Mlls</td>
            <td>{{ $orden->kilometraje ?? '' }}</td>
            <td>MOTOR</td>
            <td>{{ $orden->auto->nro_motor ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td>CHASIS</td>
            <td>{{ $orden->auto->nro_chasis ?? '' }}</td>
        </tr>
    </table>

    <div class="clearfix">
        <!-- COLUMNA IZQUIERDA -->
        <div class="column-left">
            <table>
                <tr>
                    <td class="main-title">REQUERIMIENTO DE SERVICIO</td>
                </tr>
            </table>

            @php
                $preventivos = is_string($form->servicios_preventivos) ? json_decode($form->servicios_preventivos, true) : $form->servicios_preventivos;
                if(!is_array($preventivos) || count($preventivos) == 0) $preventivos = array_fill(0, 5, '');
                
                $correctivos = is_string($form->servicios_correctivos) ? json_decode($form->servicios_correctivos, true) : $form->servicios_correctivos;
                if(!is_array($correctivos) || count($correctivos) == 0) $correctivos = array_fill(0, 5, '');
                
                $otros = is_string($form->servicios_otros) ? json_decode($form->servicios_otros, true) : $form->servicios_otros;
                if(!is_array($otros) || count($otros) == 0) $otros = array_fill(0, 4, '');
            @endphp

            <table class="servicios-table">
                <tr><th colspan="2">Mantenimiento Preventivo</th></tr>
                @foreach($preventivos as $index => $item)
                <tr>
                    <td width="5%" class="text-center font-weight-bold">{{ $index + 1 }}</td>
                    <td>{{ $item }}</td>
                </tr>
                @endforeach
            </table>

            <table class="servicios-table">
                <tr><th colspan="2">Mantenimiento Correctivo</th></tr>
                @foreach($correctivos as $index => $item)
                <tr>
                    <td width="5%" class="text-center font-weight-bold">{{ $index + 1 }}</td>
                    <td>{{ $item }}</td>
                </tr>
                @endforeach
            </table>

            <table class="servicios-table">
                <tr><th colspan="2">Otros servicios requeridos</th></tr>
                @foreach($otros as $index => $item)
                <tr>
                    <td width="5%" class="text-center font-weight-bold">{{ $index + 1 }}</td>
                    <td>{{ $item }}</td>
                </tr>
                @endforeach
            </table>

            <table style="margin-top: 10px;">
                <tr>
                    <td width="50%" class="firma-box">
                        <b>Solicita y Valida</b><br>
                        Responsable del Vehículo:
                        <br><br><br><br><br>
                    </td>
                    <td width="50%" class="firma-box">
                        <b>Verifica y Aprueba</b><br>
                        Fiscal de Servicio:
                        <br><br><br><br><br>
                    </td>
                </tr>
            </table>
        </div>

        <!-- COLUMNA DERECHA -->
        <div class="column-right">
            <table>
                <tr>
                    <td colspan="2" class="main-title">INVENTARIO</td>
                </tr>
                @php
                    $checklist = is_string($orden->checklist) ? json_decode($orden->checklist, true) : $orden->checklist;
                    if(!is_array($checklist)) $checklist = [];
                @endphp
                
                @foreach($checklist as $key => $val)
                    @if($key !== 'firma_entrega')
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                        <td width="15%" class="text-center">{{ $val }}</td>
                    </tr>
                    @endif
                @endforeach
            </table>

            <table>
                <tr>
                    <td class="main-title">Recepción del Taller</td>
                </tr>
                <tr>
                    <td style="height: 100px; vertical-align: top;">
                        {{ $form->recepcion_taller ?? '' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
