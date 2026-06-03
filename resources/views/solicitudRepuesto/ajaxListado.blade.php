<div class="table-responsive m-t-40">
    <table id="kt_table_solicitudes" class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha Solicitud</th>
                <th>Orden Recepción N°</th>
                <th>Cliente</th>
                <th>Solicitante</th>
                <th>Total Solicitado</th>
                <th>Estado</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ( $solicitudes as $index => $sol )
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $sol->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $sol->orden_recepcion_id }}</td>
                <td>{{ $sol->ordenRecepcion->grupoCliente->cliente->nombres ?? '' }} {{ $sol->ordenRecepcion->grupoCliente->cliente->ap_paterno ?? '' }}</td>
                <td>{{ $sol->creador->name ?? '' }}</td>
                <td>{{ number_format($sol->total_general, 2) }}</td>
                <td>
                    @if($sol->estado == 'PENDIENTE')
                        <span class="badge badge-warning">PENDIENTE</span>
                    @elseif($sol->estado == 'APROBADO')
                        <span class="badge badge-success">APROBADO</span>
                    @elseif($sol->estado == 'RECHAZADO')
                        <span class="badge badge-danger">RECHAZADO</span>
                    @else
                        <span class="badge badge-secondary">{{ $sol->estado }}</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-icon btn-sm btn-info" title="Revisar Solicitud" onclick="revisarSolicitud({{ $sol->id }})">
                        <i class="fa fa-eye"></i> Revisar
                    </button>
                </td>
            </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $('#kt_table_solicitudes').DataTable({
            lengthMenu: [10, 25, 50, 100],
            dom: '<"dt-head row"<"col-md-6"l><"col-md-6"f>><"clear">t<"dt-footer row"<"col-md-5"i><"col-md-7"p>>',
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
                emptyTable: 'No hay solicitudes pendientes'
            },
            order:[[ 0, "desc" ]],
            responsive: true
        });
    });
</script>
