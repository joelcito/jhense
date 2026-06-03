@extends('layouts.app')

@section('css')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- modal para revisar solicitud -->
<div id="modalSolicitud" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">REVISIÓN DE SOLICITUD DE REPUESTOS</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioAprobacion">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="solicitud_id" id="solicitud_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5 class="text-primary border-bottom pb-1" id="titulo_detalles">Detalles de la Solicitud</h5>
                            <button type="button" id="btn_marcar_todos" class="btn btn-sm btn-info mb-2" onclick="marcarTodos()">Marcar Todos</button>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center" id="tabla_rev_detalles">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Aprobar</th>
                                            <th>Producto</th>
                                            <th>Cant. Solicitada</th>
                                            <th>Precio Unit.</th>
                                            <th>Subtotal</th>
                                            {{-- <th>Stock Actual</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12" id="div_accion_revision">
                            <div class="form-group">
                                <label>Acción de Revisión:</label>
                                <select name="estado" id="estado_solicitud" class="form-control">
                                    <option value="APROBADO">APROBAR SOLICITUD (Con los ítems marcados)</option>
                                    <option value="RECHAZADO">RECHAZAR SOLICITUD COMPLETA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12" id="div_estado_actual" style="display: none;">
                            <div class="alert alert-info font-weight-bold text-center" style="font-size: 16px;">
                                ESTADO DE LA SOLICITUD: <span id="texto_estado_actual"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success mt-0" id="btn_guardar_decision" onclick="guardarAprobacion()">GUARDAR DECISIÓN</button>
                    <button type="button" class="btn waves-effect waves-light btn-block btn-secondary mt-0" id="btn_cerrar_decision" data-dismiss="modal" style="display: none;">CERRAR</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- fin modal revisar solicitud -->

<div class="card border-info">
    <div class="card-header bg-info">
        <h4 class="mb-0 text-white">
            SOLICITUDES - {{ $sucursal->nombre }}
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_solicitudes"></div>
    </div>
</div>
@stop

@section('js')
<script src="{{ asset('assets/libs/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('dist/js/pages/datatable/custom-datatable.js') }}"></script>
<script>
    const sucursal = @json($sucursal);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        ajaxListado();
    });

    function ajaxListado(){
        $.ajax({
            url: "{{ route('solicitudRepuesto.ajaxListado') }}",
            method: "POST",
            data: { sucursal_id: sucursal.id },
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_solicitudes').html(resultado.data.listado);
                }
            }
        });
    }

    function revisarSolicitud(id) {
        $.ajax({
            url: "{{ route('solicitudRepuesto.ajaxObtenerSolicitud') }}",
            method: "POST",
            data: { id: id, sucursal_id: sucursal.id },
            success: function(resp) {
                if(resp.estado) {
                    let sol = resp.data.solicitud;
                    $('#solicitud_id').val(sol.id);
                    let esPendiente = sol.estado === 'PENDIENTE';

                    if (esPendiente) {
                        $('#estado_solicitud').val('APROBADO');
                        $('#div_accion_revision').show();
                        $('#div_estado_actual').hide();
                        $('#btn_guardar_decision').show();
                        $('#btn_cerrar_decision').hide();
                        $('#btn_marcar_todos').show();
                    } else {
                        $('#div_accion_revision').hide();
                        $('#texto_estado_actual').text(sol.estado);
                        $('#div_estado_actual').show();
                        $('#btn_guardar_decision').hide();
                        $('#btn_cerrar_decision').show();
                        $('#btn_marcar_todos').hide();
                    }

                    $('#titulo_detalles').text('Productos Solicitados (' + sol.tipo_solicitud + ')');
                    
                    let tbody = $('#tabla_rev_detalles tbody');
                    tbody.empty();
                    if(sol.detalles_lista) {
                        sol.detalles_lista.forEach((p) => {
                            let checked = p.aprobado ? 'checked' : '';
                            let disabled = esPendiente ? '' : 'disabled';
                            let badgeClass = p.stock_actual >= p.cantidad ? 'badge-success' : 'badge-danger';
                            tbody.append(`
                                <tr>
                                    <td><input type="checkbox" name="detalles[${p.id}]" class="chk-detalle" ${checked} ${disabled}></td>
                                    <td>${p.producto_texto}</td>
                                    <td>${p.cantidad}</td>
                                    <td>${p.precio}</td>
                                    <td>${p.subtotal}</td>
                                    {{-- <td><span class="badge ${badgeClass}">${p.stock_actual}</span></td> --}}
                                </tr>
                            `);
                        });
                    }

                    $('#modalSolicitud').modal('show');
                } else {
                    Swal.fire('Error', 'No se pudo obtener la solicitud', 'error');
                }
            }
        });
    }

    function marcarTodos() {
        $('.chk-detalle').prop('checked', true);
    }

    function guardarAprobacion() {
        let datos = $('#formularioAprobacion').serialize();
        
        $.ajax({
            url: "{{ route('solicitudRepuesto.guardarAprobacion') }}",
            type: 'POST',
            data: datos,
            success: function(resp) {
                if(resp.estado) {
                    $('#modalSolicitud').modal('hide');
                    ajaxListado();
                    Swal.fire('Excelente!', 'La solicitud ha sido procesada.', 'success');
                } else {
                    Swal.fire('Error', resp.mensaje, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Ocurrió un problema en el servidor', 'error');
            }
        });
    }
</script>
@endsection
