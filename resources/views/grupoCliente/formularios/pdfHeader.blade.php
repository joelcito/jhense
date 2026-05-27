@php
    $sucursalLogo = null;
    if(isset($orden) && isset($orden->grupoCliente->cliente->sucursal->logo)){
        $sucursalLogo = $orden->grupoCliente->cliente->sucursal->logo;
    } elseif(isset($grupoCliente) && isset($grupoCliente->cliente->sucursal->logo)){
        $sucursalLogo = $grupoCliente->cliente->sucursal->logo;
    }
    
    // The logo from public
    $logoJhensePath = public_path('assets/imagenes/logo_jhense.png');
    $logoJhenseSrc = '';
    if(file_exists($logoJhensePath)) {
        $logoJhenseBase64 = base64_encode(file_get_contents($logoJhensePath));
        $logoJhenseSrc = 'data:image/png;base64,' . $logoJhenseBase64;
    }

    // The sucursal logo from storage/app/public/
    $sucursalLogoSrc = null;
    if ($sucursalLogo) {
        // the field might store just the file name or a path. Usually 'sucursales/img.jpg' etc
        // Check if it exists in storage/app/public
        $storagePath = storage_path('app/public/' . $sucursalLogo);
        if (file_exists($storagePath)) {
            $ext = pathinfo($storagePath, PATHINFO_EXTENSION);
            $sucursalLogoBase64 = base64_encode(file_get_contents($storagePath));
            $sucursalLogoSrc = 'data:image/' . $ext . ';base64,' . $sucursalLogoBase64;
        }
    }
@endphp

<table style="width: 100%; margin-bottom: 20px; border-collapse: collapse; border: none !important;">
    <tr>
        <td style="width: 33%; text-align: left; vertical-align: middle; border: none !important;">
            @if($sucursalLogoSrc)
                <img src="{{ $sucursalLogoSrc }}" style="max-width: 150px; max-height: 80px;">
            @else
                <h2 style="margin: 0; color: #333; font-size: 24px; font-weight: bold;">JHENSE</h2>
            @endif
        </td>
        <td style="width: 34%; text-align: center; vertical-align: middle; border: none !important;">
            <div class="header" style="margin-bottom: 0; font-size: 16px; font-weight: bold; text-decoration: underline;">{{ $titulo }}</div>
        </td>
        <td style="width: 33%; text-align: right; vertical-align: middle; border: none !important;">
            @if($logoJhenseSrc)
                <img src="{{ $logoJhenseSrc }}" style="max-width: 150px; max-height: 80px;">
            @endif
            @if(isset($rightCode))
                <div style="font-size: 10px; font-weight: bold; margin-top: 5px;">{!! $rightCode !!}</div>
            @endif
        </td>
    </tr>
</table>
