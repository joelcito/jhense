<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Repuestos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 5px; text-align: left; }
        .table th { background-color: #f2f2f2; text-align: center; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .bg-local { background-color: #D9E1F2; }
        .bg-central { background-color: #E2EFDA; }
        .bg-externo { background-color: #FFF2CC; }
    </style>
</head>
<body>
    @include('grupoCliente.formularios.pdfHeader')

    <table class="table">
        <tbody>
            <tr>
                <td width="20%" class="font-weight-bold">CLIENTE:</td>
                <td width="30%">{{ $grupoCliente->cliente->nombres ?? '' }} {{ $grupoCliente->cliente->ap_paterno ?? '' }}</td>
                <td width="20%" class="font-weight-bold">PLACA:</td>
                <td width="30%">{{ $orden->auto->placa ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- SUCURSAL LOCAL -->
    <table class="table">
        <thead>
            <tr>
                <th colspan="4" class="text-left font-weight-bold bg-local">1. PRODUCTOS SOLICITADOS A SUCURSAL LOCAL</th>
            </tr>
            <tr>
                <th width="50%">PRODUCTO</th>
                <th width="15%">CANTIDAD</th>
                <th width="15%">PRECIO UNIT.</th>
                <th width="20%">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['local_productos'] as $item)
            <tr>
                <td>{{ $item->producto->nombre ?? '' }}</td>
                <td class="text-center">{{ $item->cantidad ?? 0 }}</td>
                <td class="text-right">{{ number_format((float)($item->precio ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format((float)($item->subtotal ?? 0), 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="text-right font-weight-bold">SUBTOTAL LOCAL:</td>
                <td class="text-right font-weight-bold">{{ number_format($data['total_local'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- SUCURSAL CENTRAL -->
    <table class="table">
        <thead>
            <tr>
                <th colspan="4" class="text-left font-weight-bold bg-central">2. PRODUCTOS SOLICITADOS A SUCURSAL CENTRAL</th>
            </tr>
            <tr>
                <th width="50%">PRODUCTO</th>
                <th width="15%">CANTIDAD</th>
                <th width="15%">PRECIO UNIT.</th>
                <th width="20%">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['central_productos'] as $item)
            <tr>
                <td>{{ $item->producto->nombre ?? '' }}</td>
                <td class="text-center">{{ $item->cantidad ?? 0 }}</td>
                <td class="text-right">{{ number_format((float)($item->precio ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format((float)($item->subtotal ?? 0), 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="text-right font-weight-bold">SUBTOTAL CENTRAL:</td>
                <td class="text-right font-weight-bold">{{ number_format($data['total_central'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- EXTERNOS -->
    <table class="table">
        <thead>
            <tr>
                <th colspan="4" class="text-left font-weight-bold bg-externo">3. COMPRAS EXTERNAS</th>
            </tr>
            <tr>
                <th width="50%">DESCRIPCIÓN / NOMBRE</th>
                <th width="15%">CANTIDAD</th>
                <th width="15%">PRECIO UNIT.</th>
                <th width="20%">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['externos_productos'] as $item)
            <tr>
                <td>{{ $item->descripcion ?? '' }}</td>
                <td class="text-center">{{ $item->cantidad ?? 0 }}</td>
                <td class="text-right">{{ number_format((float)($item->precio ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format((float)($item->subtotal ?? 0), 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="text-right font-weight-bold">SUBTOTAL EXTERNO:</td>
                <td class="text-right font-weight-bold">{{ number_format($data['total_externos'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="table" style="margin-top: 15px;">
        <tr>
            <td width="80%" class="text-right font-weight-bold" style="font-size: 14px; border:none;">TOTAL GENERAL:</td>
            <td width="20%" class="text-right font-weight-bold" style="font-size: 14px; color: red;">{{ number_format($data['total_general'], 2) }}</td>
        </tr>
    </table>
</body>
</html>
