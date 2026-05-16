<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Recepción</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 20px; text-decoration: underline; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; }
        .info-label { font-weight: bold; text-align: right; width: 25%; }
        .info-value { border-bottom: 1px solid #000; width: 25%; }
        
        .checklist-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .checklist-table th, .checklist-table td { border: 1px solid #000; padding: 4px; text-align: left; }
        .checklist-table th { background-color: #f2e3b6; text-align: center; font-weight: bold; }
        
        .general-obs { width: 100%; border: 1px solid #000; height: 80px; padding: 5px; margin-bottom: 30px; }
        
        .signatures { width: 100%; margin-top: 50px; text-align: center; }
        .signatures td { width: 50%; padding-top: 40px; }
        .sign-line { border-top: 1px solid #000; width: 60%; margin: 0 auto; padding-top: 5px; }
        
        .ref-box { width: 40%; font-size: 10px; border-collapse: collapse; margin-bottom: 10px; }
        .ref-box td { border: 1px solid #000; padding: 2px; }
    </style>
</head>
<body>

    <div class="header">ORDEN DE RECEPCIÓN DE VEHÍCULOS</div>
    
    <div style="text-align: right; margin-bottom: 10px;">
        <b>N° de Orden:</b> <span style="border: 1px solid #000; padding: 2px 20px; display: inline-block;">{{ $orden->id }}</span>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Fecha Solicitud:</td>
            <td class="info-value">{{ \Carbon\Carbon::parse($orden->fecha_recepcion)->format('d/m/Y') }}</td>
            <td class="info-label">Placa - Chasis:</td>
            <td class="info-value">{{ $orden->auto->placa ?? '' }} - {{ $orden->auto->vin ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Empresa - Cliente:</td>
            <td class="info-value">{{ $orden->grupoCliente->cliente->nombres }} {{ $orden->grupoCliente->cliente->ap_paterno }} {{ $orden->grupoCliente->cliente->razon_social ?? '' }}</td>
            <td class="info-label">Clase de vehículo:</td>
            <td class="info-value">{{ $orden->tipo_unidad }}</td>
        </tr>
        <tr>
            <td class="info-label">NIT - C.I.:</td>
            <td class="info-value">{{ $orden->grupoCliente->cliente->nit ?? $orden->grupoCliente->cliente->cedula }}</td>
            <td class="info-label">Marca - Tipo:</td>
            <td class="info-value">{{ $orden->auto->marca->nombre ?? '' }} - {{ $orden->auto->modelo ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Contacto de ref.:</td>
            <td class="info-value">{{ $orden->grupoCliente->cliente->numero_celular }}</td>
            <td class="info-label">Kilometraje:</td>
            <td class="info-value">{{ $orden->kilometraje }}</td>
        </tr>
        <tr>
            <td class="info-label">Teléfono - Correo:</td>
            <td class="info-value">{{ $orden->grupoCliente->cliente->correo }}</td>
            <td class="info-label">Objeto de la contratación:</td>
            <td class="info-value">{{ $orden->objeto_contratacion }}</td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="info-label">Porcentaje de combustible (%):</td>
            <td class="info-value">{{ $orden->porcentaje_combustible }}</td>
        </tr>
    </table>

    <table class="ref-box">
        <tr><td>Buen estado / Tiene / Si</td><td style="text-align: center;">v</td></tr>
        <tr><td>Mal estado / No tiene / No</td><td style="text-align: center;">X</td></tr>
        <tr><td>No Aplica</td><td style="text-align: center;">N/A</td></tr>
    </table>

    @php
        $elementos = [
            'cenicero' => 'CENICERO', 'encendedor' => 'ENCENDEDOR', 'manual' => 'MANUAL', 'espejos' => 'ESPEJOS',
            'radio' => 'RADIO', 'pisos' => 'PISOS', 'control_alarma' => 'CONTROL ALARMA', 'limpia_parabrisas' => 'LIMPIA PARABRISAS',
            'herramientas' => 'HERRAMIENTAS', 'gato' => 'GATO', 'llave_rueda' => 'LLAVE DE RUEDA', 'arresta_llama' => 'ARRESTA LLAMA',
            'auxilio' => 'AUXILIO', 'varilla' => 'VARILLA', 'tapacubos' => 'TAPACUBOS', 'halogenos' => 'HALOGENOS',
            'antena' => 'ANTENA', 'tapa_combustible' => 'TAPA COMBUSTIBLE', 'extintor' => 'EXTINTOR', 'triangulo' => 'TRIANGULO',
            'llave_seguridad' => 'LLAVE DE SEGURIDAD', 'placas' => 'PLACAS', 'botiquin' => 'BOTIQUIN', 'usb' => 'USB'
        ];
        
        $checklist = is_string($orden->checklist) ? json_decode($orden->checklist, true) : $orden->checklist;
        if(!is_array($checklist)) $checklist = [];
        
        $col1 = array_slice($elementos, 0, 12, true);
        $col2 = array_slice($elementos, 12, 12, true);
        
        $keys1 = array_keys($col1);
        $keys2 = array_keys($col2);
    @endphp

    <table class="checklist-table">
        <tr>
            <th colspan="4">DESCRIPCIÓN DE ELEMENTOS</th>
        </tr>
        @for($i = 0; $i < 12; $i++)
            @php 
                $k1 = $keys1[$i];
                $k2 = $keys2[$i];
            @endphp
            <tr>
                <td style="width: 20%;">{{ $col1[$k1] }}</td>
                <td style="width: 10%; text-align: center; font-weight: bold;">{{ $checklist[$k1] ?? 'N/A' }}</td>
                <td style="width: 20%;">{{ $col2[$k2] }}</td>
                <td style="width: 10%; text-align: center; font-weight: bold;">{{ $checklist[$k2] ?? 'N/A' }}</td>
            </tr>
        @endfor
    </table>

    <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">OBSERVACIÓN GENERAL</div>
    <div class="general-obs">
        {{ $orden->observacion_general }}
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sign-line">FIRMA DEL CLIENTE</div>
            </td>
            <td>
                <div class="sign-line">FIRMA TÉCNICO MECÁNICO</div>
            </td>
        </tr>
    </table>

</body>
</html>
