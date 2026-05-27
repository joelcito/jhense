<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Técnico de Diagnóstico</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
        .header-table { width: 100%; margin-bottom: 20px; }
        .header-table td { vertical-align: top; }
        .title { text-align: center; font-size: 18px; font-weight: bold; text-decoration: underline; }
        .n-orden { text-align: right; font-weight: bold; }
        .n-orden span { border: 1px dashed #000; padding: 2px 10px; background-color: #fcf1d2; }
        
        .info-box { background-color: #fcf1d2; border: 1px dashed #000; padding: 2px 10px; display: inline-block; min-width: 150px; }
        
        h4 { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        
        .image-box { border: 1px dashed #000; width: 100%; height: 300px; text-align: center; margin-bottom: 20px; }
        .image-box img { max-height: 200px; max-width: 98%; padding-top: 5px; }
        
        .diag-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px; }
        .diag-table th, .diag-table td { border: 1px dashed #000; padding: 5px; text-align: left; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    @include('grupoCliente.formularios.pdfHeader', [
        'titulo' => 'INFORME TÉCNICO DE DIAGNOSTICO'
    ])

    <p><i>Ref.: Informe de Inspección y Evaluación Técnica</i></p>

    <p>
        <b>Elaborado por:</b> {{ $informe->mecanico->name ?? 'JHENSE' }} {{ $informe->mecanico->ap_paterno ?? '' }}<br>
        <b>Destinado a:</b> {{ $informe->ordenRecepcion->grupoCliente->cliente->nombres ?? '' }} {{ $informe->ordenRecepcion->grupoCliente->cliente->ap_paterno ?? '' }}<br>
        <b>Fecha:</b> <span class="info-box">{{ \Carbon\Carbon::parse($informe->created_at)->format('d/m/Y') }}</span>
    </p>
    <hr style="border: 1px dashed #ccc;">

    <h4>1. Antecedentes</h4>
    <p>De acuerdo al ingreso a taller JHENSE del vehículo con placa de circulación: <span class="info-box">{{ $informe->ordenRecepcion->auto->placa ?? '' }}</span><br>
    se procedio a realizar la revision y diagnostico de las anomalias para su conocimiento y atencion.</p>

    <h4>2. Objetivo</h4>
    <p>El objetivo de este informe es proporcionar un análisis técnico detallado del estado actual del vehículo referido, considerando aspectos como:</p>
    <ul style="border: 1px solid green; padding: 10px 10px 10px 30px;">
        <li>Informe de diagnóstico de mantenimiento de vehículos</li>
        <li>Estado exterior e interior.</li>
        <li>Condiciones mecánicas y eléctricas.</li>
        <li>Desempeño general del vehículo según prueba de conducción.</li>
    </ul>
    <p>Este informe está destinado a poner en conocimiento el estado actual del vehículo motorizado y guiar las decisiones de mantenimiento.</p>

    <h4>3. Desarrollo</h4>
    <b>3.1 Datos Generales del Vehículo</b>
    <ul style="list-style: none; padding-left: 20px;">
        <li><b>Clase:</b> <span class="info-box">{{ $informe->ordenRecepcion->tipo_unidad ?? '' }}</span></li>
        <li><b>Marca:</b> <span class="info-box">{{ $informe->ordenRecepcion->auto->marca->nombre ?? '' }}</span></li>
        <li><b>Tipo:</b> <span class="info-box">{{ $informe->ordenRecepcion->auto->modelo ?? '' }}</span></li>
        <li><b>Kilometraje:</b> <span class="info-box">{{ $informe->ordenRecepcion->kilometraje ?? '' }}</span></li>
        <li><b>Color:</b> <span class="info-box">{{ $informe->ordenRecepcion->auto->color ?? 'N/A' }}</span></li>
    </ul>

    <div class="page-break"></div>

    <h4>3.2 Inspección Exterior</h4>
    <p>{{ $informe->inspeccion_exterior_texto }}</p>
    <p>REPORTE FOTOGRAFICO DEL EXTERIOR DEL VEHICULO</p>
    <div class="image-box">
        @if($informe->inspeccion_exterior_imagen)
            <img src="{{ public_path($informe->inspeccion_exterior_imagen) }}" alt="Exterior">
        @endif
    </div>

    <h4>3.3 Inspección Interior</h4>
    <p>{{ $informe->inspeccion_interior_texto }}</p>
    <p>REPORTE FOTOGRAFICO DEL INTERIOR DEL VEHICULO</p>
    <div class="image-box">
        @if($informe->inspeccion_interior_imagen)
            <img src="{{ public_path($informe->inspeccion_interior_imagen) }}" alt="Interior">
        @endif
    </div>

    <div class="page-break"></div>

    <h4>3.4 Diagnóstico del Estado Automotriz</h4>
    
    @php
        $diagnosticos = is_string($informe->diagnosticos) ? json_decode($informe->diagnosticos, true) : $informe->diagnosticos;
        if(!is_array($diagnosticos)) $diagnosticos = [];
    @endphp

    @foreach($diagnosticos as $diag)
        <div class="image-box" style="height: 200px;">
            @if(!empty($diag['imagen_path']))
                <img src="{{ public_path($diag['imagen_path']) }}" alt="Componente">
            @else
                <br><br><br><br><i>Sin imagen de componente</i>
            @endif
        </div>

        <table class="diag-table">
            <tr>
                <th>Componente</th>
                <th>Síntoma</th>
                <th>Tipo de falla</th>
                <th>Causa probable</th>
                <th>Estado Actual</th>
                <th>Recomendación</th>
                <th>Riesgos asociados a la falla</th>
                <th>Justificación</th>
            </tr>
            <tr>
                <td>{{ $diag['componente'] ?? '' }}</td>
                <td>{{ $diag['sintoma'] ?? '' }}</td>
                <td>{{ $diag['tipo_falla'] ?? '' }}</td>
                <td>{{ $diag['causa_probable'] ?? '' }}</td>
                <td>{{ $diag['estado_actual'] ?? '' }}</td>
                <td>{{ $diag['recomendacion'] ?? '' }}</td>
                <td>{{ $diag['riesgo_asociado'] ?? '' }}</td>
                <td>{{ $diag['justificacion'] ?? '' }}</td>
            </tr>
        </table>
        <br>
    @endforeach

</body>
</html>
