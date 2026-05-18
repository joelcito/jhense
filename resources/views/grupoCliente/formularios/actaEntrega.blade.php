<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">9. ACTA DE ENTREGA</h4>
    </div>
    <div class="card-body">
        <form id="formActaEntrega">
            <input type="hidden" id="ae_acta_id" name="acta_id" value="">
            <input type="hidden" id="ae_orden_recepcion_id" name="orden_recepcion_id" value="">

            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">N° de Orden:</th>
                            <td><input type="text" class="form-control form-control-sm border-0 text-danger font-weight-bold" id="ae_numero_orden" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Fecha de Ingreso:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="ae_fecha_ingreso" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Entregado A:</th>
                            <td><input type="text" class="form-control form-control-sm" name="entregado_a" id="ae_entregado_a"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Placa:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="ae_placa" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Clase de vehículo:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="ae_clase" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Marca / Tipo:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="ae_marca" readonly></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40 text-white border-0"></th>
                            <td class="border-0"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Fecha Entrega:</th>
                            <td><input type="date" class="form-control form-control-sm" name="fecha_entrega" id="ae_fecha_entrega"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">De:</th>
                            <td><input type="text" class="form-control form-control-sm" name="de" id="ae_de"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Kilometraje:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="ae_kilometraje" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Objeto de la contratación:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="ae_objeto" value="MANTENIMIENTO PREVENTIVO Y CORRECTIVO" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Dirección del Cliente:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="ae_direccion" readonly></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="font-weight-bold">ASUNTO:</label>
                <input type="text" class="form-control" name="asunto" id="ae_asunto" value="MANTENIMIENTO CORRECTIVO">
            </div>

            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">DETALLE DEL SERVICIO</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0" id="tabla_ae_servicios">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%" class="text-center">N°</th>
                                <th width="95%" class="text-left">Mantenimiento realizado al vehículo detallado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="form-group text-right mt-4">
                <a id="btnDescargarPdfAe" href="#" target="_blank" class="btn btn-danger" style="display:none;"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a id="btnDescargarExcelAe" href="#" class="btn btn-success" style="display:none;"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                <button type="button" class="btn btn-primary" onclick="guardarActaEntrega()">Guardar Formulario</button>
            </div>
        </form>
    </div>
</div>

<script>
    function renderAeServicios(data) {
        let tbody = $('#tabla_ae_servicios tbody');
        tbody.empty();

        if(data && data.length > 0) {
            let cont = 1;
            data.forEach((item, index) => {
                let nombre = item.nombre || item.servicio || '';
                if(typeof item === 'string') nombre = item;

                let tr = `<tr>
                    <td class="text-center align-middle">${cont})</td>
                    <td>
                        <input type="text" class="form-control form-control-sm border-0 bg-transparent" name="servicios_realizados[]" value="${nombre}" readonly>
                    </td>
                </tr>`;
                tbody.append(tr);
                cont++;
            });
        } else {
            tbody.append('<tr><td colspan="2" class="text-center text-muted">No hay servicios registrados para mostrar.</td></tr>');
        }
    }

    function cargarDatosActaEntrega(orden) {
        $('#ae_orden_recepcion_id').val(orden.id);
        
        let today = new Date().toISOString().split('T')[0];

        // Llenar cabecera (cliente heredado de grupoCliente, auto heredado)
        let cliente = @json($grupoCliente->cliente ?? null);
        if(cliente) {
            $('#ae_direccion').val(cliente.direccion || '');
        }

        $('#ae_placa').val(orden.auto ? orden.auto.placa : '');
        $('#ae_kilometraje').val(orden.kilometraje || '');
        $('#ae_clase').val(orden.auto ? orden.auto.modelo : '');
        $('#ae_marca').val(orden.auto && orden.auto.marca ? orden.auto.marca.nombre : '');
        
        // Fecha ingreso (desde BD)
        let fechaIngreso = orden.created_at ? orden.created_at.split('T')[0] : '';
        $('#ae_fecha_ingreso').val(fechaIngreso);

        $.ajax({
            url: "{{ route('grupoCliente.obtenerActaEntrega') }}",
            type: 'POST',
            data: { orden_recepcion_id: orden.id },
            success: function(data) {
                $('#ae_numero_orden').val(data.num_ot || '');

                if(data.estado && data.ae) {
                    let ae = data.ae;
                    $('#ae_acta_id').val(ae.id);
                    $('#ae_fecha_entrega').val(ae.fecha_entrega || today);
                    $('#ae_entregado_a').val(ae.entregado_a || '');
                    $('#ae_de').val(ae.de || '');
                    $('#ae_asunto').val(ae.asunto || 'MANTENIMIENTO CORRECTIVO');

                    renderAeServicios(ae.servicios_realizados);

                    $('#btnDescargarPdfAe').attr('href', "{{ url('grupo-cliente/descargarPdfActaEntrega') }}/" + ae.id).show();
                    $('#btnDescargarExcelAe').attr('href', "{{ url('grupo-cliente/descargarExcelActaEntrega') }}/" + ae.id).show();
                } else if(data.estado && data.cotizacion_servicios) {
                    $('#ae_acta_id').val('');
                    $('#ae_fecha_entrega').val(today);
                    $('#ae_entregado_a').val(cliente ? cliente.nombres : '');
                    $('#ae_de').val('JHENSE');
                    $('#ae_asunto').val('MANTENIMIENTO CORRECTIVO');
                    
                    renderAeServicios(data.cotizacion_servicios);

                    $('#btnDescargarPdfAe').hide();
                    $('#btnDescargarExcelAe').hide();
                } else {
                    $('#ae_acta_id').val('');
                    renderAeServicios([]);
                    $('#btnDescargarPdfAe').hide();
                    $('#btnDescargarExcelAe').hide();
                }
            }
        });
    }

    function guardarActaEntrega() {
        let formData = $('#formActaEntrega').serialize();
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarActaEntrega') }}",
            type: 'POST',
            data: formData,
            success: function(data) {
                if(data.estado){
                    Swal.fire('Excelente!', 'Acta de Entrega guardada exitosamente.', 'success');
                    $('#ae_acta_id').val(data.ae_id);
                    
                    $('#btnDescargarPdfAe').attr('href', "{{ url('grupo-cliente/descargarPdfActaEntrega') }}/" + data.ae_id).show();
                    $('#btnDescargarExcelAe').attr('href', "{{ url('grupo-cliente/descargarExcelActaEntrega') }}/" + data.ae_id).show();
                } else {
                    Swal.fire('Error!', 'Ocurrió un error al guardar.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error en el servidor al guardar el acta de entrega.', 'error');
            }
        });
    }
</script>
