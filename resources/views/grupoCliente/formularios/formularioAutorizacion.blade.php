<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">6. FORMULARIO DE AUTORIZACIÓN</h4>
    </div>
    <div class="card-body">
        <form id="formAutorizacion">
            <input type="hidden" id="formulario_autorizacion_id" name="formulario_autorizacion_id" value="">
            <input type="hidden" id="fa_orden_recepcion_id" name="orden_recepcion_id" value="">

            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">PROPIETARIO DEL VEHICULO:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="fa_propietario" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">MARCA:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="fa_marca" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">CLASE:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="fa_clase" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">FECHA:</th>
                            <td><input type="date" class="form-control form-control-sm" name="fecha" id="fa_fecha"></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">N° DE ORDEN:</th>
                            <td><input type="text" class="form-control form-control-sm border-0 text-danger font-weight-bold" id="fa_numero_orden" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">PLACA:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="fa_placa" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">TIPO:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="fa_tipo" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">CITE:</th>
                            <td><input type="text" class="form-control form-control-sm" name="cite" id="fa_cite"></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Los servicios mostrados a continuación son de solo lectura, agrupados en base a la Cotización / Orden de Trabajo generada.
            </div>

            <!-- TABLAS DE SERVICIOS -->
            <div class="table-responsive mb-4">
                <h5 class="bg-dark text-white p-2 mb-0 text-center">DETALLE</h5>
                
                <!-- 1. Mantenimiento Preventivo -->
                <table class="table table-bordered table-sm mb-0" id="tabla_fa_preventivo">
                    <thead class="bg-light">
                        <tr><th colspan="6" class="text-center">Mantenimiento Preventivo</th></tr>
                        <tr>
                            <th width="10%">ITEM</th>
                            <th width="45%">Mano de obra</th>
                            <th width="10%">CANT.</th>
                            <th width="10%">UNID.</th>
                            <th width="12%">P/UNIT.</th>
                            <th width="13%">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold">TOTAL</td>
                            <td><input type="text" class="form-control form-control-sm text-right border-0 bg-white font-weight-bold fa-subtotal" readonly value="0.00"></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- 2. Mantenimiento Correctivo -->
                <table class="table table-bordered table-sm mb-0" id="tabla_fa_correctivo">
                    <thead class="bg-light">
                        <tr><th colspan="6" class="text-center">Mantenimiento Correctivo</th></tr>
                        <tr>
                            <th width="10%">ITEM</th>
                            <th width="45%">Mano de obra</th>
                            <th width="10%">CANT.</th>
                            <th width="10%">UNID.</th>
                            <th width="12%">P/UNIT.</th>
                            <th width="13%">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold">TOTAL</td>
                            <td><input type="text" class="form-control form-control-sm text-right border-0 bg-white font-weight-bold fa-subtotal" readonly value="0.00"></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- 3. Repuestos - Accesorios - Suministros -->
                <table class="table table-bordered table-sm mb-0" id="tabla_fa_rep_sum">
                    <thead class="bg-light">
                        <tr><th colspan="6" class="text-center">Repuestos - Accesorios - Suministros</th></tr>
                        <tr>
                            <th width="10%">ITEM</th>
                            <th width="45%">DETALLE</th>
                            <th width="10%">CANT.</th>
                            <th width="10%">UNID.</th>
                            <th width="12%">P/UNIT.</th>
                            <th width="13%">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold">TOTAL</td>
                            <td><input type="text" class="form-control form-control-sm text-right border-0 bg-white font-weight-bold fa-subtotal" readonly value="0.00"></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- 4. Otros Servicios Requeridos -->
                <table class="table table-bordered table-sm mb-4" id="tabla_fa_otros">
                    <thead class="bg-light">
                        <tr><th colspan="6" class="text-center">Otros Servicios Requeridos</th></tr>
                        <tr>
                            <th width="10%">ITEM</th>
                            <th width="45%">DETALLE</th>
                            <th width="10%">CANT.</th>
                            <th width="10%">UNID.</th>
                            <th width="12%">P/UNIT.</th>
                            <th width="13%">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold">TOTAL</td>
                            <td><input type="text" class="form-control form-control-sm text-right border-0 bg-white font-weight-bold fa-subtotal" readonly value="0.00"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Div oculto donde guardaremos los JSON para que el Controller los guarde si el FA se guarda -->
            <div id="fa_inputs_hidden"></div>

            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light text-right w-75">TOTAL GENERAL Bs.:</th>
                            <th class="bg-light text-right">
                                <input type="text" class="form-control text-right border-0 bg-light font-weight-bold text-danger" name="total_general" id="total_general_fa" readonly value="0.00">
                            </th>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">OBSERVACIONES</label>
                        <textarea class="form-control" name="observaciones" id="fa_observaciones" rows="4" placeholder="Escriba las observaciones aquí..."></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group text-right mt-5">
                <a id="btnDescargarPdfFa" href="#" target="_blank" class="btn btn-danger" style="display:none;"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a id="btnDescargarExcelFa" href="#" class="btn btn-success" style="display:none;"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                <button type="button" class="btn btn-primary" onclick="guardarFormularioAutorizacion()">Guardar Formulario</button>
            </div>
        </form>
    </div>
</div>

<script>

    function renderFaReadOnlyTable(tablaId, data, categoryName) {
        let tbody = $('#' + tablaId + ' tbody');
        tbody.empty();
        let subtotal = 0;
        
        let containerHidden = $('#fa_inputs_hidden');

        if(data && data.length > 0) {
            data.forEach((item, index) => {
                let cost = parseFloat(item.costo || 0);
                let tot = parseFloat(item.total || 0);
                subtotal += tot;

                let tr = `<tr>
                    <td>${item.item || ''}</td>
                    <td>${item.nombre || ''}</td>
                    <td class="text-center">${item.cantidad || 0}</td>
                    <td class="text-center">${item.unidad_medida || ''}</td>
                    <td class="text-right">${cost.toFixed(2)}</td>
                    <td class="text-right">${tot.toFixed(2)}</td>
                </tr>`;
                tbody.append(tr);

                // Add hidden inputs for saving snapshot
                containerHidden.append(`<input type="hidden" name="${categoryName}[${index}][item]" value="${item.item || ''}">`);
                containerHidden.append(`<input type="hidden" name="${categoryName}[${index}][nombre]" value="${item.nombre || ''}">`);
                containerHidden.append(`<input type="hidden" name="${categoryName}[${index}][cantidad]" value="${item.cantidad || 0}">`);
                containerHidden.append(`<input type="hidden" name="${categoryName}[${index}][unidad_medida]" value="${item.unidad_medida || ''}">`);
                containerHidden.append(`<input type="hidden" name="${categoryName}[${index}][costo]" value="${cost}">`);
                containerHidden.append(`<input type="hidden" name="${categoryName}[${index}][total]" value="${tot}">`);
            });
        } else {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>');
        }

        $('#' + tablaId + ' tfoot .fa-subtotal').val(subtotal.toFixed(2));
        return subtotal;
    }

    function cargarDatosFormularioAutorizacion(orden) {
        $('#fa_orden_recepcion_id').val(orden.id);
        
        $('#fa_propietario').val("{{ $grupoCliente->cliente->nombres ?? '' }} {{ $grupoCliente->cliente->ap_paterno ?? '' }}");
        let autoText = $('#auto_id option:selected').text();
        // Extract Marca and Clase from autoText or we can just use variables if we had them. 
        // We do have $orden->auto->marca but JS object is limited here unless we fetch.
        // As a fallback, use the raw data if available. Actually, orden might have auto.
        $('#fa_placa').val(orden.placa);

        let today = new Date().toISOString().split('T')[0];

        $.ajax({
            url: "{{ route('grupoCliente.obtenerFormularioAutorizacion') }}",
            type: 'POST',
            data: { orden_recepcion_id: orden.id },
            success: function(data) {
                $('#fa_inputs_hidden').empty();
                $('#fa_numero_orden').val(data.num_ot || '');

                if(data.estado && data.fa) {
                    let fa = data.fa;
                    $('#formulario_autorizacion_id').val(fa.id);
                    $('#fa_fecha').val(fa.fecha || today);
                    $('#fa_cite').val(fa.cite || '');
                    $('#fa_observaciones').val(fa.observaciones || '');

                    let subPrev = renderFaReadOnlyTable('tabla_fa_preventivo', fa.preventivo, 'preventivo');
                    let subCorr = renderFaReadOnlyTable('tabla_fa_correctivo', fa.correctivo, 'correctivo');
                    let subRepSum = renderFaReadOnlyTable('tabla_fa_rep_sum', fa.repuestos_suministros, 'repuestos_suministros');
                    let subOtros = renderFaReadOnlyTable('tabla_fa_otros', fa.otros, 'otros');

                    let totalG = subPrev + subCorr + subRepSum + subOtros;
                    $('#total_general_fa').val(totalG.toFixed(2));

                    $('#btnDescargarPdfFa').attr('href', "{{ url('grupo-cliente/descargarPdfFormularioAutorizacion') }}/" + fa.id).show();
                    $('#btnDescargarExcelFa').attr('href', "{{ url('grupo-cliente/descargarExcelFormularioAutorizacion') }}/" + fa.id).show();
                } else if(data.estado && data.cotizacion) {
                    $('#formulario_autorizacion_id').val('');
                    $('#fa_fecha').val(today);
                    $('#fa_cite').val('');
                    $('#fa_observaciones').val('');

                    let cot = data.cotizacion;
                    let subPrev = renderFaReadOnlyTable('tabla_fa_preventivo', cot.preventivo, 'preventivo');
                    let subCorr = renderFaReadOnlyTable('tabla_fa_correctivo', cot.correctivo, 'correctivo');
                    let subRepSum = renderFaReadOnlyTable('tabla_fa_rep_sum', cot.repuestos_suministros, 'repuestos_suministros');
                    let subOtros = renderFaReadOnlyTable('tabla_fa_otros', cot.otros, 'otros');

                    let totalG = subPrev + subCorr + subRepSum + subOtros;
                    $('#total_general_fa').val(totalG.toFixed(2));

                    $('#btnDescargarPdfFa').hide();
                    $('#btnDescargarExcelFa').hide();
                } else {
                    $('#formulario_autorizacion_id').val('');
                    renderFaReadOnlyTable('tabla_fa_preventivo', [], 'preventivo');
                    renderFaReadOnlyTable('tabla_fa_correctivo', [], 'correctivo');
                    renderFaReadOnlyTable('tabla_fa_rep_sum', [], 'repuestos_suministros');
                    renderFaReadOnlyTable('tabla_fa_otros', [], 'otros');
                    $('#total_general_fa').val('0.00');

                    $('#btnDescargarPdfFa').hide();
                    $('#btnDescargarExcelFa').hide();
                }
            }
        });
    }

    function guardarFormularioAutorizacion() {
        let formData = $('#formAutorizacion').serialize();
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarFormularioAutorizacion') }}",
            type: 'POST',
            data: formData,
            success: function(data) {
                if(data.estado){
                    Swal.fire('Excelente!', 'Formulario de Autorización guardado exitosamente.', 'success');
                    $('#formulario_autorizacion_id').val(data.fa_id);
                    
                    $('#btnDescargarPdfFa').attr('href', "{{ url('grupo-cliente/descargarPdfFormularioAutorizacion') }}/" + data.fa_id).show();
                    $('#btnDescargarExcelFa').attr('href', "{{ url('grupo-cliente/descargarExcelFormularioAutorizacion') }}/" + data.fa_id).show();
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
