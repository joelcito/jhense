<div style="overflow-x: auto;">
    <!--begin::Table-->
    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th>Sucursal</th>
                <th>Fecha</th>
                <th>Descripcion</th>
                <th>Tipo Pago</th>
                <th>Monto Efectivo</th>
                <th>Monto Deposito</th>
                <th>Estado</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
            @php
                $totalEfectivo = 0;
                $totalTramsferencia = 0;
                $totalVenta = 0;
            @endphp
            @forelse ($pagos as $pago)
                {{-- <tr {{ $pago->apertura_caja == 'Si'? 'class="bg-light-danger"' : ''}} > --}}
                {{-- <tr > --}}
                <tr class="{{ $pago->apertura_caja == 'Si' ? 'bg-light-warning' : '' }}">
                    <td>{{ $pago->puntoVenta->sucursal->nombre }}</td>
                    <td>{{ $pago->fecha }}</td>
                    <td>{{ $pago->descripcion }}</td>
                    <td>{{ $pago->tipo_pago }}</td>
                    <td>
                        @if ($pago->estado === 'INGRESO')
                            @if ($pago->tipo_pago === 'EFECTIVO')
                                {{ $pago->monto }}
                            @else
                                0.00
                            @endif
                        @elseif($pago->estado === 'SALIDA')
                            @if ($pago->tipo_pago === 'EFECTIVO')
                                {{ $pago->monto }}
                            @else
                                0.00
                            @endif
                        @endif
                    </td>
                    <td>
                        @if ($pago->estado === 'INGRESO')
                            @if ($pago->tipo_pago === 'TRANSFERENCIA' || $pago->tipo_pago === 'QR')
                                {{ $pago->monto }}
                            @else
                                0.00
                            @endif
                        @elseif($pago->estado === 'SALIDA')
                            @if ($pago->tipo_pago === 'TRANSFERENCIA' || $pago->tipo_pago === 'QR')
                                {{ $pago->monto }}
                            @else
                                0.00
                            @endif
                        @endif
                    </td>
                    <td>
                        @if ($pago->estado === 'INGRESO')
                            <span class="badge badge-success">{{ $pago->estado }}</span>
                        @else
                            <span class="badge badge-danger">{{ $pago->estado }}</span>
                        @endif
                    </td>
                    <td>{{ $pago->usuario->name }}</td>
                </tr>
                @php

                    if ($pago->estado === 'INGRESO') {
                        if ($pago->tipo_pago === 'EFECTIVO') {
                            $totalEfectivo = $totalEfectivo + $pago->monto;
                        }
                    } elseif ($pago->estado === 'SALIDA') {
                        if ($pago->tipo_pago === 'EFECTIVO') {
                            $totalEfectivo = $totalEfectivo - $pago->monto;
                        }
                    }

                    if ($pago->estado === 'INGRESO') {
                        if ($pago->tipo_pago === 'TRANSFERENCIA' || $pago->tipo_pago === 'QR') {
                            $totalTramsferencia = $totalTramsferencia + $pago->monto;
                        }
                    } elseif ($pago->estado === 'SALIDA') {
                        if ($pago->tipo_pago === 'TRANSFERENCIA' || $pago->tipo_pago === 'QR') {
                            $totalTramsferencia = $totalTramsferencia - $pago->monto;
                        }
                    }

                    $totalVenta = $pago->estado === 'INGRESO' ? $totalVenta + $pago->monto : $totalVenta - $pago->monto;

                @endphp
            @empty
                <h4 class="text-danger">No hay datos</h4>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-light-dark">
                <th class="text-center" colspan="4"><b>TOTAL VENTA</b></th>
                <th class="text-center" colspan="4">{{ number_format($totalVenta, 2) }}</th>
            </tr>
            <tr>
                <th class="bg-light-info" colspan="4"><b>TOTALES</b></th>
                <th class="bg-light-success"><b>TOTAL EFECTIVO</b></th>
                <th class="bg-light-success">{{ number_format($totalEfectivo, 2) }}</th>
                <th class="bg-light-primary"><b>TOTAL TRANSFERENCIA</b></th>
                <th class="bg-light-primary">{{ number_format($totalTramsferencia, 2) }}</th>
            </tr>
        </tfoot>
    </table>
    <!--end::Table-->
</div>

<script>
    $(document).ready(function() {
        $('#kt_table_users').DataTable({
            lengthMenu: [10, 25, 50, 100], // Opciones de longitud de página
            // dom: '<"dt-head row"<"col-md-6"l><"col-md-6"f>><"clear">t<"dt-footer row"<"col-md-5"i><"col-md-7"p>>', // Use dom for basic layout
            dom: '<"dt-head row"><"clear">t<"dt-footer row"<"col-md-5"i><"col-md-7"p>>',
            lengthChange: false,
            searching: false,
            language: {
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                },
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                emptyTable: 'No hay datos disponibles'
            },
            order: [],
            responsive: true,
        });
    });
</script>
