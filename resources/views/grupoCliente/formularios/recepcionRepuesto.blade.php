<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">7. RECEPCIÓN DE REPUESTOS</h4>
    </div>
    <div class="card-body">
        <form id="formRecepcionRepuesto">
            <input type="hidden" id="recepcion_repuesto_id" name="recepcion_repuesto_id" value="">
            <input type="hidden" id="rr_orden_recepcion_id" name="orden_recepcion_id" value="">

            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">FECHA:</th>
                            <td><input type="date" class="form-control form-control-sm" name="fecha" id="rr_fecha"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">MARCA:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rr_marca" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">CLASE:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rr_clase" readonly></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">FORM. DE MANT. (OT):</th>
                            <td><input type="text" class="form-control form-control-sm border-0 text-danger font-weight-bold" id="rr_numero_orden" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">PLACA:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rr_placa" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">TIPO:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rr_tipo" readonly></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Los servicios mostrados son <strong>SOLO categoría REPUESTO</strong>. Puede marcar la casilla "Ocultar en reportes" para excluir un ítem de reportes posteriores.
            </div>

            <!-- TABLA DETALLE -->
            <div class="table-responsive mb-4">
                <h5 class="bg-dark text-white p-2 mb-0 text-center">DETALLE</h5>
                <div class="p-1 border border-dark text-center font-italic bg-light">
                    Llenar la siguiente información, en caso de no adjuntar el detalle de repuestos emitido por el taller mecánico
                </div>
                
                <table class="table table-bordered table-sm mb-0" id="tabla_rr_detalle">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%" class="text-center">N°</th>
                            <th width="60%" class="text-center">REPUESTOS</th>
                            <th width="10%" class="text-center">CANT.</th>
                            <th width="10%" class="text-center">UNIDAD</th>
                            <th width="15%" class="text-center">OCULTAR EN REPORTES</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">OBSERVACIONES</label>
                        <textarea class="form-control" name="observaciones" id="rr_observaciones" rows="4" placeholder="Escriba las observaciones aquí..."></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group text-right mt-5">
                <a id="btnDescargarPdfRr" href="#" target="_blank" class="btn btn-danger" style="display:none;"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a id="btnDescargarExcelRr" href="#" class="btn btn-success" style="display:none;"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                <button type="button" class="btn btn-primary" onclick="guardarRecepcionRepuesto()">Guardar Formulario</button>
            </div>
        </form>
    </div>
</div>

<script>
    function renderRrTable(data) {
        let tbody = $('#tabla_rr_detalle tbody');
        tbody.empty();

        if(data && data.length > 0) {
            let cont = 1;
            data.forEach((item, index) => {
                let isChecked = item.ocultar_reporte ? 'checked' : '';

                let tr = `<tr>
                    <td class="text-center align-middle">${cont}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm border-0 bg-transparent" name="repuestos[${index}][nombre]" value="${item.nombre || ''}" readonly>
                        <input type="hidden" name="repuestos[${index}][item]" value="${item.item || ''}">
                    </td>
                    <td class="text-center align-middle">
                        <input type="text" class="form-control form-control-sm text-center border-0 bg-transparent" name="repuestos[${index}][cantidad]" value="${item.cantidad || 0}" readonly>
                    </td>
                    <td class="text-center align-middle">
                        <input type="text" class="form-control form-control-sm text-center border-0 bg-transparent" name="repuestos[${index}][unidad_medida]" value="${item.unidad_medida || ''}" readonly>
                    </td>
                    <td class="text-center align-middle">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ocultar_${index}" name="repuestos[${index}][ocultar_reporte]" value="1" ${isChecked}>
                            <label class="custom-control-label text-danger" for="ocultar_${index}">Ocultar</label>
                        </div>
                    </td>
                </tr>`;
                tbody.append(tr);
                cont++;
            });
        } else {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">No se encontraron servicios de categoría REPUESTO</td></tr>');
        }
    }

    function cargarDatosRecepcionRepuesto(orden) {
        $('#rr_orden_recepcion_id').val(orden.id);
        
        let autoText = $('#auto_id option:selected').text();
        $('#rr_placa').val(orden.auto.placa);
        $('#rr_marca').val(orden.auto.marca.nombre);
        $('#rr_clase').val(orden.auto.modelo);

        let today = new Date().toISOString().split('T')[0];

        $.ajax({
            url: "{{ route('grupoCliente.obtenerRecepcionRepuesto') }}",
            type: 'POST',
            data: { orden_recepcion_id: orden.id },
            success: function(data) {
                $('#rr_numero_orden').val(data.num_ot || '');

                if(data.estado && data.rr) {
                    let rr = data.rr;
                    $('#recepcion_repuesto_id').val(rr.id);
                    $('#rr_fecha').val(rr.fecha || today);
                    $('#rr_observaciones').val(rr.observaciones || '');

                    renderRrTable(rr.repuestos);

                    $('#btnDescargarPdfRr').attr('href', "{{ url('grupo-cliente/descargarPdfRecepcionRepuesto') }}/" + rr.id).show();
                    $('#btnDescargarExcelRr').attr('href', "{{ url('grupo-cliente/descargarExcelRecepcionRepuesto') }}/" + rr.id).show();
                } else if(data.estado && data.cotizacion_reps) {
                    $('#recepcion_repuesto_id').val('');
                    $('#rr_fecha').val(today);
                    $('#rr_observaciones').val('');

                    renderRrTable(data.cotizacion_reps);

                    $('#btnDescargarPdfRr').hide();
                    $('#btnDescargarExcelRr').hide();
                } else {
                    $('#recepcion_repuesto_id').val('');
                    renderRrTable([]);
                    $('#btnDescargarPdfRr').hide();
                    $('#btnDescargarExcelRr').hide();
                }
            }
        });
    }

    function guardarRecepcionRepuesto() {
        let formData = $('#formRecepcionRepuesto').serialize();
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarRecepcionRepuesto') }}",
            type: 'POST',
            data: formData,
            success: function(data) {
                if(data.estado){
                    Swal.fire('Excelente!', 'Recepción de Repuestos guardada exitosamente.', 'success');
                    $('#recepcion_repuesto_id').val(data.rr_id);
                    
                    $('#btnDescargarPdfRr').attr('href', "{{ url('grupo-cliente/descargarPdfRecepcionRepuesto') }}/" + data.rr_id).show();
                    $('#btnDescargarExcelRr').attr('href', "{{ url('grupo-cliente/descargarExcelRecepcionRepuesto') }}/" + data.rr_id).show();
                } else {
                    Swal.fire('Error!', 'Ocurrió un error al guardar.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error en el servidor al guardar.', 'error');
            }
        });
    }
</script>
