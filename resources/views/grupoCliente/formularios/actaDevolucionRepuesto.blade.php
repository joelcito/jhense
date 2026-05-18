<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">10. ACTA DE DEVOLUCIÓN DE REPUESTOS</h4>
    </div>
    <div class="card-body">
        <form id="formActaDevolucionRepuesto">
            <input type="hidden" id="adr_acta_id" name="acta_id" value="">
            <input type="hidden" id="adr_orden_recepcion_id" name="orden_recepcion_id" value="">

            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">N° de Orden:</th>
                            <td><input type="text" class="form-control form-control-sm border-0 text-danger font-weight-bold" id="adr_numero_orden" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Empresa / Cliente:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_cliente" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Contacto de ref.:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_contacto" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Placa / Chasis:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_placa" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Clase de vehículo:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_clase" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Marca / Tipo:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_marca" readonly></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">Fecha:</th>
                            <td><input type="date" class="form-control form-control-sm" name="fecha" id="adr_fecha"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">NIT / C.I.:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_nit" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Teléfono / Correo:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_telefono" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Kilometraje:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_kilometraje" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Objeto de la contratación:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_objeto" value="MANTENIMIENTO PREVENTIVO Y CORRECTIVO" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Dirección de cliente:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="adr_direccion" readonly></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">DETALLE DE ACTIVIDADES DEL VEHICULO</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0" id="tabla_adr_repuestos">
                        <thead class="bg-light">
                            <tr>
                                <th width="10%">CANTIDAD</th>
                                <th width="75%">DESCRIPCIÓN</th>
                                <th width="15%" class="text-center">OCULTAR EN REPORTE</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">OBSERVACIONES</label>
                <textarea class="form-control" name="observaciones" id="adr_observaciones" rows="3"></textarea>
            </div>

            <div class="form-group text-right mt-4">
                <a id="btnDescargarPdfAdr" href="#" target="_blank" class="btn btn-danger" style="display:none;"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a id="btnDescargarExcelAdr" href="#" class="btn btn-success" style="display:none;"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                <button type="button" class="btn btn-primary" onclick="guardarActaDevolucionRepuesto()">Guardar Formulario</button>
            </div>
        </form>
    </div>
</div>

<script>
    function renderAdrRepuestos(data) {
        let tbody = $('#tabla_adr_repuestos tbody');
        tbody.empty();

        if(data && data.length > 0) {
            data.forEach((item, index) => {
                let cantidad = item.cantidad || '';
                let nombre = item.nombre || item.descripcion || '';
                let ocultar = item.ocultar_reporte ? 'checked' : '';

                let tr = `<tr>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="repuestos[${index}][cantidad]" value="${cantidad}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="repuestos[${index}][descripcion]" value="${nombre}">
                    </td>
                    <td class="text-center align-middle">
                        <input type="hidden" name="repuestos[${index}][ocultar_reporte]" value="0">
                        <input type="checkbox" name="repuestos[${index}][ocultar_reporte]" value="1" ${ocultar}>
                    </td>
                </tr>`;
                tbody.append(tr);
            });
        } else {
            tbody.append('<tr><td colspan="3" class="text-center text-muted">No hay repuestos registrados en la orden para devolver.</td></tr>');
        }
    }

    function cargarDatosActaDevolucionRepuesto(orden) {
        $('#adr_orden_recepcion_id').val(orden.id);
        
        let today = new Date().toISOString().split('T')[0];

        // Llenar cabecera
        let cliente = @json($grupoCliente->cliente ?? null);
        if(cliente) {
            $('#adr_cliente').val(cliente.nombres);
            $('#adr_nit').val(cliente.nit);
            $('#adr_contacto').val(cliente.numero_celular || '');
            $('#adr_telefono').val(cliente.numero_celular || '');
            $('#adr_direccion').val(cliente.direccion || '');
        }

        $('#adr_placa').val(orden.auto ? orden.auto.placa : '');
        $('#adr_kilometraje').val(orden.kilometraje || '');
        $('#adr_clase').val(orden.auto ? orden.auto.modelo : '');
        $('#adr_marca').val(orden.auto && orden.auto.marca ? orden.auto.marca.nombre : '');

        $.ajax({
            url: "{{ route('grupoCliente.obtenerActaDevolucionRepuesto') }}",
            type: 'POST',
            data: { orden_recepcion_id: orden.id },
            success: function(data) {
                $('#adr_numero_orden').val(data.num_ot || '');

                if(data.estado && data.adr) {
                    let adr = data.adr;
                    $('#adr_acta_id').val(adr.id);
                    $('#adr_fecha').val(adr.created_at ? adr.created_at.split('T')[0] : today);
                    $('#adr_observaciones').val(adr.observaciones || '');

                    renderAdrRepuestos(adr.repuestos);

                    $('#btnDescargarPdfAdr').attr('href', "{{ url('grupo-cliente/descargarPdfActaDevolucionRepuesto') }}/" + adr.id).show();
                    $('#btnDescargarExcelAdr').attr('href', "{{ url('grupo-cliente/descargarExcelActaDevolucionRepuesto') }}/" + adr.id).show();
                } else if(data.estado && data.cotizacion_repuestos) {
                    $('#adr_acta_id').val('');
                    $('#adr_fecha').val(today);
                    $('#adr_observaciones').val('');
                    
                    renderAdrRepuestos(data.cotizacion_repuestos);

                    $('#btnDescargarPdfAdr').hide();
                    $('#btnDescargarExcelAdr').hide();
                } else {
                    $('#adr_acta_id').val('');
                    renderAdrRepuestos([]);
                    $('#btnDescargarPdfAdr').hide();
                    $('#btnDescargarExcelAdr').hide();
                }
            }
        });
    }

    function guardarActaDevolucionRepuesto() {
        let formData = $('#formActaDevolucionRepuesto').serialize();
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarActaDevolucionRepuesto') }}",
            type: 'POST',
            data: formData,
            success: function(data) {
                if(data.estado){
                    Swal.fire('Excelente!', 'Acta de Devolución guardada exitosamente.', 'success');
                    $('#adr_acta_id').val(data.adr_id);
                    
                    $('#btnDescargarPdfAdr').attr('href', "{{ url('grupo-cliente/descargarPdfActaDevolucionRepuesto') }}/" + data.adr_id).show();
                    $('#btnDescargarExcelAdr').attr('href', "{{ url('grupo-cliente/descargarExcelActaDevolucionRepuesto') }}/" + data.adr_id).show();
                } else {
                    Swal.fire('Error!', 'Ocurrió un error al guardar.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error en el servidor al guardar el formulario de devolución.', 'error');
            }
        });
    }
</script>
