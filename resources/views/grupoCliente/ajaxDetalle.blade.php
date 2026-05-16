<div class="table-responsive m-t-40">
    <table id="kt_table_grupo_cliente_detalles" class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Sub Categoria</th>
                <th>Item</th>
                <th>Servicio</th>
                <th>Unidad de Medida</th>
                <th>Cantidad</th>
                <th>Costo</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ( $servicios as $index => $ser)
            <tr>
                <td>{{ $ser->categoria ?? '' }}</td>
                <td>{{ $ser->sub_categoria ?? '' }}</td>
                <td>{{ $ser->item }}</td>
                <td>{{ $ser->nombre ?? '' }}</td>
                <td>{{ $ser->unidad_medida ?? '' }}</td>
                <td>{{ $ser->cantidad ?? '' }}</td>
                <td>{{ $ser->costo ?? '' }}</td>
                <td>{{ $ser->total ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8">No hay datos disponibles</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
            $('#kt_table_grupo_cliente_detalles').DataTable({
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
