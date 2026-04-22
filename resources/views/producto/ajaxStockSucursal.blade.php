<div class="table-responsive m-t-40">
    <table id="kt_table_stock" class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Stock</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ( $sucursales as $sucursal )
            <tr>
                <td>{{ $sucursal->nombre }}</td>
                <td>{{ (float)$sucursal->movimientos_sum_ingreso - (float)$sucursal->movimientos_sum_salida }}</td>
                <td>
                    <button class="btn btn-icon btn-sm btn-info" title="Adicionar Stock"
                        onclick="adicionarStockSucursalProducto({{ json_encode($sucursal) }}, {{ $producto }})"><i class="fa fa-calendar-plus"></i></button>
                    <button class="btn btn-icon btn-sm btn-danger" title="Salida de Stock"
                        onclick="adicionarSalidaSucursalProducto({{ json_encode($sucursal) }}, {{ $producto }})"><i class="fa fa-calendar-minus"></i></button>
                </td>
            </tr>
            @empty
            <h4 class="text-danger">No hay datos</h4>
            @endforelse
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
            $('#kt_table_stock').DataTable({
                lengthMenu: [10, 25, 50, 100], // Opciones de longitud de página
                dom: '<"dt-head row"<"col-md-6"l><"col-md-6"f>><"clear">t<"dt-footer row"<"col-md-5"i><"col-md-7"p>>', // Use dom for basic layout
                language: {
                paginate: {
                    first : 'Primero',
                    last : 'Último',
                    next : 'Siguiente',
                    previous: 'Anterior'
                },
                search : 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros por página',
                info : 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                emptyTable: 'No hay datos disponibles'
                },
                order:[],
                //  searching: true,
                responsive: true
            });


        });
</script>
