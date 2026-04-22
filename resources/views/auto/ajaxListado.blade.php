<div class="table-responsive m-t-40">
    <table id="kt_table_autos" class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Placa</th>
                <th>Motor</th>
                <th>Año Fab.</th>
                <th>Vin</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ( $autos as $index => $au)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $au->cliente->nombres ?? '' }}</td>
                <td>{{ $au->marca->nombre ?? '' }}</td>
                <td>{{ $au->modelo }}</td>
                <td>{{ $au->placa }}</td>
                <td>{{ $au->motor }}</td>
                <td>{{ $au->anio_fab ?? 'S/R' }}</td>
                <td>{{ $au->vin ?? 'S/R' }}</td>
                <td>
                    <button class="btn btn-icon btn-sm btn-warning" title="Editar Auto"
                        onclick="editarAuto({{ json_encode($au) }})"><i class="fa fa-edit"></i></button>
                    <button class="btn btn-icon btn-sm btn-danger" title="Eliminar Auto"
                        onclick="eliminarAuto({{ json_encode($au) }})"><i class="fa fa-trash"></i></button>
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
            $('#kt_table_autos').DataTable({
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
