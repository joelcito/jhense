<div class="table-responsive m-t-40">
    <table id="kt_table_clientes" class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombres</th>
                <th>Ap Paterno</th>
                <th>Ap Materno</th>
                <th>Cedula</th>
                <th>Celular</th>
                <th>Direccion</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ( $clientes as $index => $cli)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $cli->nombres }}</td>
                <td>{{ $cli->ap_paterno }}</td>
                <td>{{ $cli->ap_materno }}</td>
                <td>{{ $cli->cedula }}</td>
                <td>{{ $cli->numero_celular }}</td>
                <td>{{ $cli->direccion }}</td>
                <td>
                    <button class="btn btn-icon btn-sm btn-warning" title="Editar Cliente"
                        onclick="editarCliente({{ json_encode($cli) }})"><i class="fa fa-edit"></i></button>
                    <button class="btn btn-icon btn-sm btn-danger" title="Eliminar Cliente"
                        onclick="eliminarCliente({{ json_encode($cli) }})"><i class="fa fa-trash"></i></button>
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
            $('#kt_table_clientes').DataTable({
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
