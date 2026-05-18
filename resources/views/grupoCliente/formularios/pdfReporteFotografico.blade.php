<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Fotográfico N° {{ $rf->id }}</title>
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
        .bg-light { background-color: #fce4d6; } /* similar a la imagen (naranja claro pastel) */
        .bg-yellow { background-color: #fff2cc; } /* cabecera interior amarillo pastel */

        .label-bg { background-color: #fff2cc; font-style: italic; text-align: right; border: none; padding-right: 10px; }
        .val-bg { background-color: #fbe5d6; font-weight: bold; border: none; padding-left: 5px; }
    </style>
</head>
<body>

    <table style="margin-bottom: 10px;">
        <tr>
            <td style="width: 25%; text-align: center; vertical-align: middle;">
                <div style="font-size: 18px; font-weight: bold; color: #B8860B;">JHENSE</div>
                <div style="font-size: 8px;">SERVICIOS Y PRODUCTOS</div>
            </td>
            <td style="width: 50%; border-left: 1px solid #000; border-right: 1px solid #000;" class="header-title">
                REPORTE<br>FOTOGRÁFICO
            </td>
            <td style="width: 25%; text-align: center; vertical-align: middle;">
                <!-- Espacio para foto de camiones si existe, por ahora texto -->
                <span style="font-size: 10px; color: #666;">[Imagen Transporte]</span>
            </td>
        </tr>
    </table>

    <table class="no-border-table" style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td class="label-bg" style="width: 18%;">N° de Orden:</td>
            <td class="val-bg" style="width: 32%;">{{ $numOt }}</td>
            <td class="label-bg" style="width: 18%;">Fecha:</td>
            <td class="val-bg" style="width: 32%;">{{ $rf->fecha }}</td>
        </tr>
        <tr><td colspan="4" style="height: 2px;"></td></tr>
        <tr>
            <td class="label-bg">Empresa/ Cliente:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->nombre_razon_social ?? '' }}</td>
            <td class="label-bg">NIT/ C.I.:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->nit_ci ?? '' }}</td>
        </tr>
        <tr><td colspan="4" style="height: 2px;"></td></tr>
        <tr>
            <td class="label-bg">Contacto de ref.:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->contacto_referencia ?? '' }}</td>
            <td class="label-bg">Telefono/ Correo:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->telefono_celular ?? '' }}</td>
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
            <td class="val-bg">{{ $rf->objeto_contratacion }}</td>
        </tr>
        <tr><td colspan="4" style="height: 2px;"></td></tr>
        <tr>
            <td class="label-bg">Marca/ Tipo:</td>
            <td class="val-bg">{{ $orden->auto->marca->nombre ?? '' }}</td>
            <td class="label-bg">Direccion de cliente:</td>
            <td class="val-bg">{{ $orden->grupoCliente->cliente->direccion ?? '' }}</td>
        </tr>
    </table>

    <div style="border: 1px solid #000; padding: 5px; margin-bottom: 5px;">
        <div class="font-weight-bold" style="font-size: 12px;">REPORTE FOTOGRAFICO DEL VEHICULO:</div>
        <div>
            De acuerdo a ingreso y salida de vehiculo motorizado con placa de control detallado en el presente
            documento, a continuacion se detalla <b>visualmente</b> las diferentes etapas de un servicio, repuestos y
            accesorios cambiados:
        </div>
    </div>

    @if($filas && count($filas) > 0)
        @foreach($filas as $f)
            <table style="width: 100%; margin-bottom: 10px; page-break-inside: avoid;">
                <tr>
                    <td class="bg-yellow text-center font-weight-bold" colspan="3" style="font-size: 12px;">
                        REPORTE FOTOGRAFICO
                    </td>
                </tr>
                <tr>
                    <td style="width: 33.33%; text-align: center; vertical-align: middle;">
                        
                    </td>
                    <td style="width: 33.33%; text-align: center; vertical-align: middle;">
                        REPUESTOS CAMBIADOS
                    </td>
                    <td style="width: 33.33%; text-align: center; vertical-align: middle;">
                        REPUESTOS NUEVOS
                    </td>
                </tr>
                <tr>
                    <td style="width: 33.33%; text-align: center; height: 150px; vertical-align: middle;">
                        @if(!empty($f['foto1']) && file_exists(storage_path('app/public/' . $f['foto1'])))
                            <img src="{{ storage_path('app/public/' . $f['foto1']) }}" style="max-height: 140px; max-width: 100%;">
                        @else
                            <span style="color: #ccc;">[SIN FOTO]</span>
                        @endif
                    </td>
                    <td style="width: 33.33%; text-align: center; height: 150px; vertical-align: middle;">
                        @if(!empty($f['foto2']) && file_exists(storage_path('app/public/' . $f['foto2'])))
                            <img src="{{ storage_path('app/public/' . $f['foto2']) }}" style="max-height: 140px; max-width: 100%;">
                        @else
                            <span style="color: #ccc;">[SIN FOTO]</span>
                        @endif
                    </td>
                    <td style="width: 33.33%; text-align: center; height: 150px; vertical-align: middle;">
                        @if(!empty($f['foto3']) && file_exists(storage_path('app/public/' . $f['foto3'])))
                            <img src="{{ storage_path('app/public/' . $f['foto3']) }}" style="max-height: 140px; max-width: 100%;">
                        @else
                            <span style="color: #ccc;">[SIN FOTO]</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="bg-yellow text-center font-weight-bold" colspan="3" style="font-size: 12px;">
                        DETALLE DEL SERVICIO
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding: 10px; font-style: italic;">
                        {{ $f['descripcion'] ?? 'Sin descripción' }}
                    </td>
                </tr>
            </table>
        @endforeach
    @else
        <div class="text-center" style="padding: 20px; border: 1px solid #000;">No hay fotos registradas.</div>
    @endif

    <div style="margin-top: 30px; font-size: 11px; color: #0055AA;">
        En conformidad a lo descrito en el presente documento y en honor a la verdad, firmamos al pie de la misma.
    </div>

    <div style="margin-top: 60px; text-align: center; width: 100%; page-break-inside: avoid;">
        <table style="width: 40%; margin: 0 auto; border: none;" class="no-border-table">
            <tr>
                <td style="border-bottom: 1px solid #000 !important; height: 40px;"></td>
            </tr>
            <tr>
                <td class="text-center font-weight-bold" style="padding-top: 5px;">JHENSE</td>
            </tr>
            <tr>
                <td class="text-center font-weight-bold">Agente de Servicio</td>
            </tr>
        </table>
    </div>

</body>
</html>
