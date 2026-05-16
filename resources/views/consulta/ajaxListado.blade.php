<div class="table-responsive m-t-40">
    <table id="kt_table_consultas" class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>#</th>
                <th>Componente</th>
                <th>Sintoma</th>
                <th>Tipo Falla</th>
                <th>Causa Probable</th>
                <th>Estado Actual</th>
                <th>Recomendacion</th>
                <th>Riesgo Asociado</th>
                <th>Justificacion</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ( $consultas as $index => $consulta)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $consulta->componente }}</td>
                <td>{{ $consulta->sintoma }}</td>
                <td>{{ $consulta->tipo_falla }}</td>
                <td>{{ $consulta->causa_probable }}</td>
                <td>{{ $consulta->estado_actual }}</td>
                <td>{{ $consulta->recomendacion }}</td>
                <td>{{ $consulta->riesgo_asociado }}</td>
                <td>{{ $consulta->justificacion }}</td>
                <td>
                    <button class="btn btn-icon btn-sm btn-warning" title="Editar Consulta"
                        onclick="editarConsulta({{ json_encode($consulta) }})"><i class="fa fa-edit"></i></button>
                    <button class="btn btn-icon btn-sm btn-danger" title="Eliminar Consulta"
                        onclick="eliminarConsulta({{ json_encode($consulta) }})"><i class="fa fa-trash"></i></button>
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
            $('#kt_table_consultas').DataTable({
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
