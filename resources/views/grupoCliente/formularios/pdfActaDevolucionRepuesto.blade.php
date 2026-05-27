<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta Devolución Repuestos N° {{ $adr->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th, td { border: 1px solid #000; padding: 4px; }
        
        .header-title { font-size: 16px; font-weight: bold; text-align: center; color: #00008B; text-decoration: underline; }
        
        .no-border-table th, .no-border-table td { border: none !important; }
        
        .label-bg { text-align: right; font-style: italic; background-color: #fff2cc; border: none; padding-right: 10px; }
        .val-bg { background-color: #fbe5d6; font-weight: bold; border: none; padding-left: 5px; }
        
        .border-box { border: 1px solid #000; padding: 5px; margin-bottom: 5px; }
    </style>
</head>
<body>

    @include('grupoCliente.formularios.pdfHeader', ['titulo' => 'ACTA DE DEVOLUCION DE<br>REPUESTOS'])

    <table class="no-border-table" style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td class="label-bg" style="width: 18%;">N° de Orden:</td>
            <td class="val-bg" style="width: 32%;">{{ $numOt }}</td>
            <td class="label-bg" style="width: 18%;">Fecha:</td>
            <td class="val-bg" style="width: 32%;">{{ $adr->created_at ? $adr->created_at->format('Y-m-d') : '' }}</td>
        </tr>
        <tr><td colspan="4" style="height: 2px;"></td></tr>
        <tr>
            <td class="label-bg">Empresa/ Cliente:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->nombres ?? '' }}</td>
            <td class="label-bg">NIT/ C.I.:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->nit ?? '' }}</td>
        </tr>
        <tr><td colspan="4" style="height: 2px;"></td></tr>
        <tr>
            <td class="label-bg">Contacto de ref.:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->numero_celular ?? '' }}</td>
            <td class="label-bg">Telefono/ Correo:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->numero_celular ?? '' }}</td>
        </tr>
        <tr><td colspan="4" style="height: 2px;"></td></tr>
        <tr>
            <td class="label-bg">Placa/ Chasis:</td>
            <td class="val-bg">{{ $orden->auto->placa ?? '' }}</td>
            <td class="label-bg">Kilometraje:</td>
            <td class="val-bg">{{ $orden->kilometraje ?? '' }}</td>
        </tr>
        <tr><td colspan="4" style="height: 2px;"></td></tr>
        <tr>
            <td class="label-bg">Clase de vehiculo:</td>
            <td class="val-bg">{{ $orden->auto->modelo ?? '' }}</td>
            <td class="label-bg">Objeto de la contratacion:</td>
            <td class="val-bg">MANTENIMIENTO PREVENTIVO Y CORRECTIVO</td>
        </tr>
        <tr><td colspan="4" style="height: 2px;"></td></tr>
        <tr>
            <td class="label-bg">Marca/ Tipo:</td>
            <td class="val-bg">{{ $orden->auto->marca->nombre ?? '' }}</td>
            <td class="label-bg">Direccion de cliente:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->direccion ?? '' }}</td>
        </tr>
    </table>

    <div class="border-box font-weight-bold" style="font-size: 12px; margin-bottom: 0; border-bottom: none;">
        DETALLE DE ACTIVIDADES DEL VEHICULO
    </div>
    <div class="border-box" style="margin-bottom: 10px;">
        De acuerdo a ingreso y salida de vehiculo motorizado con placa de control detallado en el presente
        documento, a continuacion se detalla cada uno de los REPUESTOS A DEVOLVER:
    </div>

    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 10%; background-color: #fff2cc;" class="text-center">N°</th>
                <th style="width: 70%; background-color: #fff2cc;" class="text-center">DESCRIPCIÓN</th>
                <th style="width: 20%; background-color: #fff2cc;" class="text-center">CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            @if($repuestosVisibles && count($repuestosVisibles) > 0)
                @php $cont = 1; @endphp
                @foreach($repuestosVisibles as $rp)
                    <tr>
                        <td class="text-center" style="height: 20px;">{{ $cont }}</td>
                        <td>{{ $rp['descripcion'] ?? '' }}</td>
                        <td class="text-center">{{ $rp['cantidad'] ?? '' }}</td>
                    </tr>
                    @php $cont++; @endphp
                @endforeach
                <!-- Llenar espacios en blanco si son pocos para igualar altura de diseño -->
                @for($i = $cont; $i <= 10; $i++)
                    <tr>
                        <td class="text-center" style="height: 20px;"></td>
                        <td></td>
                        <td class="text-center"></td>
                    </tr>
                @endfor
            @else
                <tr>
                    <td class="text-center">1</td>
                    <td class="text-center font-style-italic">No hay repuestos registrados para devolver.</td>
                    <td class="text-center">-</td>
                </tr>
                @for($i = 2; $i <= 10; $i++)
                    <tr>
                        <td class="text-center" style="height: 20px;"></td>
                        <td></td>
                        <td class="text-center"></td>
                    </tr>
                @endfor
            @endif
        </tbody>
    </table>

    @if($adr->observaciones)
        <table style="margin-top: 5px; width: 100%; border: none;">
            <tr>
                <td style="width: 100%; vertical-align: top; padding: 0; border: none;">
                    <table style="width: 100%; margin: 0;">
                        <tr>
                            <th class="text-left font-weight-bold" style="border-bottom: none;">OBSERVACIONES</th>
                        </tr>
                        <tr>
                            <td style="height: 50px; vertical-align: top; font-style: italic; border-top: none;">
                                {{ $adr->observaciones }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    @endif

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
