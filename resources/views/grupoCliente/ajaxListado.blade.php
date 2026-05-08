<div class="table-responsive m-t-40">
    <table id="kt_table_grupo_clientes" class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ( $grupos as $index => $g)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $g->cliente->nombres ?? '' }} {{ $g->cliente->ap_paterno ?? '' }} {{ $g->cliente->ap_materno ?? '' }}</td>
                <td>
                    <a href="{{ route('grupoCliente.detalle', $g->id) }}" class="btn btn-icon btn-sm btn-info" title="Detalles"><i class="fa fa-arrow-right"></i></a>
                    <button class="btn btn-icon btn-sm btn-warning" title="Editar Grupo"
                        onclick="editarGrupo({{ json_encode($g) }})"><i class="fa fa-edit"></i></button>
                    <button class="btn btn-icon btn-sm btn-danger" title="Eliminar Grupo"
                        onclick="eliminarGrupo({{ json_encode($g) }})"><i class="fa fa-trash"></i></button>
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
            $('#kt_table_grupo_clientes').DataTable({
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
