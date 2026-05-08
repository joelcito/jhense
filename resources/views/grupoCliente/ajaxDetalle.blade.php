<div class="table-responsive m-t-40">
    <table id="kt_table_grupo_cliente_detalles" class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>Item</th>
                <th>Categoria</th>
                <th>Servicio</th>
                <th>Costo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ( $servicios as $index => $ser)
            <tr>
                <td>{{ $ser->item }}</td>
                <td>{{ $ser->categoria ?? '' }}</td>
                <td>{{ $ser->nombre ?? '' }}</td>
                <td>{{ $ser->costo ?? '' }}</td>
            </tr>
            @empty
            <h4 class="text-danger">No hay datos</h4>
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
