<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">8. REPORTE FOTOGRÁFICO</h4>
    </div>
    <div class="card-body">
        <form id="formReporteFotografico" enctype="multipart/form-data">
            <input type="hidden" id="rf_reporte_id" name="reporte_id" value="">
            <input type="hidden" id="rf_orden_recepcion_id" name="orden_recepcion_id" value="">

            <!-- CABECERA -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">N° de Orden:</th>
                            <td><input type="text" class="form-control form-control-sm border-0 text-danger font-weight-bold" id="rf_numero_orden" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Empresa / Cliente:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rf_cliente" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Contacto de ref.:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rf_contacto" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Placa / Chasis:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rf_placa" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Clase de vehículo:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rf_clase" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Marca / Tipo:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rf_marca" readonly></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">Fecha:</th>
                            <td><input type="date" class="form-control form-control-sm" name="fecha" id="rf_fecha"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">NIT / C.I.:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rf_nit" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Teléfono / Correo:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rf_telefono" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Kilometraje:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rf_kilometraje" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Objeto de la contratación:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" name="objeto_contratacion" id="rf_objeto" value="MANTENIMIENTO PREVENTIVO Y CORRECTIVO"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Dirección de cliente:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="rf_direccion" readonly></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- FILAS DINÁMICAS -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">DETALLE DE FOTOS</h5>
                    <button type="button" class="btn btn-sm btn-success" onclick="agregarFilaFoto()"><i class="fas fa-plus"></i> Agregar Fila</button>
                </div>
                <div class="card-body bg-light" id="contenedor_filas_fotos">
                    <!-- Aquí se inyectan las filas -->
                </div>
            </div>

            <div class="form-group text-right mt-4">
                <a id="btnDescargarPdfRf" href="#" target="_blank" class="btn btn-danger" style="display:none;"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a id="btnDescargarExcelRf" href="#" class="btn btn-success" style="display:none;"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                <button type="button" class="btn btn-primary" onclick="guardarReporteFotografico()">Guardar Formulario</button>
            </div>
        </form>
    </div>
</div>

<script>
    let indiceFilaFoto = 0;

    function cargarDatosReporteFotografico(orden) {
        $('#rf_orden_recepcion_id').val(orden.id);
        
        let today = new Date().toISOString().split('T')[0];

        // Llenar cabecera (cliente heredado de grupoCliente, auto heredado)
        let cliente = @json($grupoCliente->cliente ?? null);
        if(cliente) {
            $('#rf_cliente').val(cliente.nombres);
            $('#rf_nit').val(cliente.nit);
            $('#rf_contacto').val(cliente.numero_celular || '');
            $('#rf_telefono').val(cliente.numero_celular || '');
            $('#rf_direccion').val(cliente.direccion || '');
        }

        $('#rf_placa').val(orden.auto ? orden.auto.placa : '');
        $('#rf_kilometraje').val(orden.kilometraje || '');
        $('#rf_clase').val(orden.auto ? orden.auto.modelo : '');
        $('#rf_marca').val(orden.auto && orden.auto.marca ? orden.auto.marca.nombre : '');

        $.ajax({
            url: "{{ route('grupoCliente.obtenerReporteFotografico') }}",
            type: 'POST',
            data: { orden_recepcion_id: orden.id },
            success: function(data) {
                $('#rf_numero_orden').val(data.num_ot || '');

                if(data.estado && data.rf) {
                    let rf = data.rf;
                    $('#rf_reporte_id').val(rf.id);
                    $('#rf_fecha').val(rf.fecha || today);
                    $('#rf_objeto').val(rf.objeto_contratacion || 'MANTENIMIENTO PREVENTIVO Y CORRECTIVO');

                    $('#contenedor_filas_fotos').empty();
                    indiceFilaFoto = 0;
                    if(rf.filas && rf.filas.length > 0) {
                        rf.filas.forEach(f => agregarFilaFoto(f));
                    } else {
                        agregarFilaFoto();
                    }

                    $('#btnDescargarPdfRf').attr('href', "{{ url('grupo-cliente/descargarPdfReporteFotografico') }}/" + rf.id).show();
                    $('#btnDescargarExcelRf').attr('href', "{{ url('grupo-cliente/descargarExcelReporteFotografico') }}/" + rf.id).show();
                } else {
                    $('#rf_reporte_id').val('');
                    $('#rf_fecha').val(today);
                    $('#rf_objeto').val('MANTENIMIENTO PREVENTIVO Y CORRECTIVO');
                    
                    $('#contenedor_filas_fotos').empty();
                    indiceFilaFoto = 0;
                    agregarFilaFoto();

                    $('#btnDescargarPdfRf').hide();
                    $('#btnDescargarExcelRf').hide();
                }
            }
        });
    }

    function agregarFilaFoto(datos = null) {
        let val1 = (datos && datos.foto1) ? `{{ asset('storage') }}/${datos.foto1}` : '';
        let val2 = (datos && datos.foto2) ? `{{ asset('storage') }}/${datos.foto2}` : '';
        let val3 = (datos && datos.foto3) ? `{{ asset('storage') }}/${datos.foto3}` : '';
        
        let path1 = (datos && datos.foto1) ? datos.foto1 : '';
        let path2 = (datos && datos.foto2) ? datos.foto2 : '';
        let path3 = (datos && datos.foto3) ? datos.foto3 : '';

        let desc = (datos && datos.descripcion) ? datos.descripcion : '';

        let index = indiceFilaFoto++;

        let imgTag1 = val1 ? `<img src="${val1}" class="img-thumbnail mt-2" style="max-height: 150px;">` : '';
        let imgTag2 = val2 ? `<img src="${val2}" class="img-thumbnail mt-2" style="max-height: 150px;">` : '';

        let html = `
        <div class="card mb-3 fila-foto" id="fila_foto_${index}">
            <div class="card-header bg-secondary text-white py-1 d-flex justify-content-between align-items-center">
                <span>Fila ${index + 1}</span>
                <button type="button" class="btn btn-sm btn-danger" onclick="$('#fila_foto_${index}').remove()"><i class="fas fa-trash"></i> Eliminar Fila</button>
            </div>
            <div class="card-body">
                <input type="hidden" name="filas[${index}][path_foto1]" value="${path1}">
                <input type="hidden" name="filas[${index}][path_foto2]" value="${path2}">
                <input type="hidden" name="filas[${index}][path_foto3]" value="${path3}">
                
                <div class="row text-center mb-3">
                    <div class="col-md-4 text-left">
                        <label class="font-weight-bold">DETALLE DEL SERVICIO / DESCRIPCIÓN</label>
                        <textarea class="form-control" name="filas[${index}][descripcion]" rows="6">${desc}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">RESPALDO DE SERVICIO o REPUESTO INICIAL</label>
                        <input type="file" class="form-control-file text-center mx-auto" name="filas_archivos[${index}][foto1]" accept="image/*" capture="environment">
                        ${imgTag1}
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">RESPALDO DE SERVICIO o REPUESTO ACTUAL</label>
                        <input type="file" class="form-control-file text-center mx-auto" name="filas_archivos[${index}][foto2]" accept="image/*" capture="environment">
                        ${imgTag2}
                    </div>
                </div>
            </div>
        </div>
        `;

        $('#contenedor_filas_fotos').append(html);
    }

    function guardarReporteFotografico() {
        let form = $('#formReporteFotografico')[0];
        let formData = new FormData(form);

        $.ajax({
            url: "{{ route('grupoCliente.guardarReporteFotografico') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                if(data.estado){
                    Swal.fire('Excelente!', 'Reporte Fotográfico guardado exitosamente.', 'success');
                    $('#rf_reporte_id').val(data.rf_id);
                    
                    // Recargar para mostrar fotos subidas
                    cargarDatosReporteFotografico({id: $('#rf_orden_recepcion_id').val()});

                    $('#btnDescargarPdfRf').attr('href', "{{ url('grupo-cliente/descargarPdfReporteFotografico') }}/" + data.rf_id).show();
                    $('#btnDescargarExcelRf').attr('href', "{{ url('grupo-cliente/descargarExcelReporteFotografico') }}/" + data.rf_id).show();
                } else {
                    Swal.fire('Error!', 'Ocurrió un error al guardar.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error en el servidor al guardar el formulario fotográfico.', 'error');
            }
        });
    }
</script>
