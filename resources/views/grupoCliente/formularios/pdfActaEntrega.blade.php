<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Entrega N° {{ $ae->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th, td { border: 1px solid #000; padding: 4px; }
        
        .header-title { font-size: 16px; font-weight: bold; text-align: center; color: #000; }
        
        .no-border-table th, .no-border-table td { border: none !important; }
        
        .label-bg { text-align: right; font-style: italic; font-weight: bold; padding-right: 10px; border: none; }
        .val-bg { background-color: #fbe5d6; border: 1px dotted #000; padding-left: 5px; }
        
        .border-box { border: 1px solid #000; padding: 5px; margin-bottom: 5px; }
    </style>
</head>
<body>

    <table style="margin-bottom: 15px; width: 100%;" class="no-border-table">
        <tr>
            <td style="width: 25%; text-align: center; border: 1px solid #000 !important;">
                <!-- Espacio logo -->
            </td>
            <td style="width: 50%; border: 1px solid #000 !important;" class="header-title">
                ACTA DE ENTREGA
            </td>
            <td style="width: 25%; text-align: center; border: 1px solid #000 !important;">
                <!-- Espacio -->
            </td>
        </tr>
    </table>
    
    <div class="text-center font-weight-bold" style="text-decoration: underline; margin-bottom: 10px; font-style: italic;">
        {{ $numOt }}
    </div>

    <table class="no-border-table" style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td class="label-bg" style="width: 20%;">Fecha de Ingreso:</td>
            <td class="val-bg" style="width: 30%;">{{ $orden->created_at ? $orden->created_at->format('Y-m-d') : '' }}</td>
            <td class="label-bg" style="width: 20%;">Fecha Entrega:</td>
            <td class="val-bg" style="width: 30%;">{{ $ae->fecha_entrega }}</td>
        </tr>
        <tr><td colspan="4" style="height: 5px;"></td></tr>
        <tr>
            <td class="label-bg">Entregado A:</td>
            <td class="val-bg">{{ $ae->entregado_a }}</td>
            <td class="label-bg">De:</td>
            <td class="val-bg">{{ $ae->de }}</td>
        </tr>
        <tr><td colspan="4" style="height: 5px;"></td></tr>
        <tr>
            <td class="label-bg">Placa:</td>
            <td class="val-bg">{{ $orden->auto->placa ?? '' }}</td>
            <td class="label-bg">Kilometraje:</td>
            <td class="val-bg">{{ $orden->kilometraje ?? '' }}</td>
        </tr>
        <tr><td colspan="4" style="height: 5px;"></td></tr>
        <tr>
            <td class="label-bg">Clase de vehiculo:</td>
            <td class="val-bg">{{ $orden->auto->modelo ?? '' }}</td>
            <td class="label-bg">Objeto de la contratacion:</td>
            <td class="val-bg">MANTENIMIENTO PREVENTIVO Y CORRECTIVO</td>
        </tr>
        <tr><td colspan="4" style="height: 5px;"></td></tr>
        <tr>
            <td class="label-bg">Marca/ Tipo:</td>
            <td class="val-bg">{{ $orden->auto->marca->nombre ?? '' }}</td>
            <td class="label-bg">Direccion del Cliente:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->direccion ?? '' }}</td>
        </tr>
    </table>

    <div class="border-box font-weight-bold" style="font-size: 12px; margin-bottom: 0; border-bottom: none;">
        ASUNTO: {{ $ae->asunto ?? 'MANTENIMIENTO CORRECTIVO' }}
    </div>
    <div class="border-box text-center" style="margin-bottom: 15px;">
        De acuerdo a ingreso y salida de vehiculo motorizado con placa de control detallado en el presente
        documento, a continuacion se detalla cada uno de los servicios de mantenimiento correctivo ejecutados:
    </div>

    <div class="font-weight-bold" style="border: 1px solid #000; border-bottom: none; padding: 5px;">
        DETALLE DEL SERVICIO:
    </div>
    <table style="width: 100%; border-top: none;">
        <tr>
            <td class="text-center" style="width: 50%;">Mantenimiento realizado al vehiculo detallado</td>
            <td class="text-right" style="width: 50%;">Según Orden de Servicio: <b>{{ $numOt }}</b></td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 10px; height: 300px; vertical-align: top; border-bottom: 1px solid #000;">
                <table class="no-border-table" style="width: 100%; margin: 0; padding: 0;">
                    @if($servicios && count($servicios) > 0)
                        @php $cont = 1; @endphp
                        @foreach($servicios as $srv)
                            <tr>
                                <td style="width: 30px; vertical-align: top; padding: 2px;">{{ $cont }})</td>
                                <td style="padding: 2px;">{{ $srv }}</td>
                            </tr>
                            @php $cont++; @endphp
                        @endforeach
                    @else
                        <tr>
                            <td style="width: 30px; vertical-align: top; padding: 2px;">1)</td>
                            <td style="padding: 2px;">Sin servicios registrados</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px; font-size: 11px; color: #0055AA;">
        En conformidad a lo descrito en el presente documento y en honor a la verdad, firmamos al pie de la misma.
    </div>

    <div style="margin-top: 80px; width: 100%; page-break-inside: avoid;">
        <table style="width: 100%; border: none;" class="no-border-table">
            <tr>
                <td style="width: 20%;"></td>
                <td style="width: 25%; border-top: 1px solid #000 !important; text-align: center; font-weight: bold;">
                    Taller
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 25%; border-top: 1px solid #000 !important; text-align: center; font-weight: bold;">
                    Cliente
                </td>
                <td style="width: 20%;"></td>
            </tr>
        </table>
    </div>

</body>
</html>
