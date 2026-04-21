<div class="table-responsive m-t-40">
    <table id="myTable" class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ( $roles as $rol)
            <tr>
                <td>{{ $rol->id }}</td>
                <td>{{ $rol->nombre }}</td>
                <td>{{ $rol->descripcion }}</td>
                <td>
                    <button class="btn btn-icon btn-sm btn-warning" title="Editar rol"
                        onclick="editarRol({{ json_encode($rol) }})"><i class="fa fa-edit"></i></button>
                    <button class="btn btn-icon btn-sm btn-danger" title="Eliminar rol"
                        onclick="eliminarRol({{ json_encode($rol) }})"><i class="fa fa-trash"></i></button>
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
            $('#myTable').DataTable({
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
