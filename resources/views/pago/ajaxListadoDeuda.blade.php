<div style="overflow-x: auto;">
    <!--begin::Table-->
    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_facturas">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th>Sucursal</th>
                <th>Cliente</th>
                <th>Cedula</th>
                <th>Razon Social</th>
                <th>Nit</th>
                <th>N° Factura/Recibo</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
            @forelse ($facturas as $factura)
                <tr>
                    <td>{{ optional($factura->sucursal)->nombre }}</td>
                    <td>{{ optional($factura->cliente)->nombres.' '.optional($factura->cliente)->ap_paterno.' '. optional($factura->cliente)->ap_materno }}</td>
                    <td>{{ optional($factura->cliente)->cedula }}</td>
                    <td>{{ optional($factura->cliente)->razon_social }}</td>
                    <td>{{ optional($factura->cliente)->nit }}</td>
                    <td>
                        @if ($factura->facturado == "Si")
                            <span class="text-success">FAC: </span>{{ $factura->numero_factura }}
                        @else
                            <span class="text-primary">REC: </span>{{ $factura->numero_recibo }}
                        @endif
                        {{-- {{ $factura->numero_factura ?? $factura->numero_recibo }} --}}
                    </td>
                    <td>{{ $factura->fecha }}</td>
                    <td>
                        <button class="btn btn-icon btn-sm btn-info btn-circle" title="Registrar Pago" onclick="registrarPago({{ json_encode($factura) }})"><i class="fa fa-dollar"></i></button>
                    </td>
                </tr>
            @empty
                <h4 class="text-danger">No hay datos</h4>
            @endforelse
        </tbody>
    </table>
    <!--end::Table-->
</div>

<script>
    $(document).ready(function() {
        $('#kt_table_facturas').DataTable({
            lengthMenu: [10, 25, 50, 100], // Opciones de longitud de página
            dom: '<"dt-head row"<"col-md-6"l><"col-md-6"f>><"clear">t<"dt-footer row"<"col-md-5"i><"col-md-7"p>>', // Use dom for basic layout
            language: {
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                },
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros por página',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                emptyTable: 'No hay datos disponibles'
            },
            order: [],
            //  searching: true,
            responsive: true
        });


    });
</script>
