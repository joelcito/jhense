<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">12. SOLICITUD DE REPUESTOS</h4>
    </div>
    <div class="card-body">
        <form id="formularioSolicitudRepuesto">
            <input type="hidden" name="solicitud_repuesto_id" id="solicitud_repuesto_id">
            
            <div class="row mb-4 bg-light p-3 border rounded">
                <div class="col-md-12">
                    <h5 class="text-info border-bottom pb-2"><i class="fas fa-search"></i> BUSCADOR DE REPUESTOS</h5>
                    <div class="row align-items-end mb-2">
                        <div class="col-md-5">
                            <label>Buscar Producto</label>
                            <select id="buscador_producto" class="form-control"></select>
                            <input type="hidden" id="prod_sel_id">
                            <input type="hidden" id="prod_sel_texto">
                        </div>
                        <div class="col-md-2">
                            <label>Precio Unit.</label>
                            <input type="text" id="prod_sel_precio" class="form-control text-right" readonly value="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="text-primary">Stock Local</label>
                            <input type="text" id="prod_sel_stock_local" class="form-control font-weight-bold text-center text-primary" readonly value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="text-secondary">Stock Central</label>
                            <input type="text" id="prod_sel_stock_central" class="form-control font-weight-bold text-center text-secondary" readonly value="0">
                        </div>
                    </div>
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Cantidad Solicitada</label>
                            <input type="number" id="prod_sel_cantidad" class="form-control text-center" min="1" value="1">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary btn-block" onclick="agregarDesdeBuscador('local')">
                                <i class="fas fa-download"></i> Solicitar a SUCURSAL LOCAL
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-secondary btn-block" onclick="agregarDesdeBuscador('central')">
                                <i class="fas fa-building"></i> Solicitar a SUCURSAL CENTRAL
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <h5 class="text-info border-bottom pb-2">1. PRODUCTOS SOLICITADOS A SUCURSAL LOCAL</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center" id="tabla_solicitud_local">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th width="50%">Producto</th>
                                    <th width="15%">Cantidad</th>
                                    <th width="15%">Precio Unit.</th>
                                    <th width="15%">Subtotal</th>
                                    <th width="5%">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filas locales -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right font-weight-bold">Subtotal Local:</td>
                                    <td><input type="text" class="form-control form-control-sm text-right font-weight-bold" id="solicitud_total_local" name="total_local" readonly value="0.00"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <h5 class="text-info border-bottom pb-2">2. PRODUCTOS SOLICITADOS A SUCURSAL CENTRAL</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center" id="tabla_solicitud_central">
                            <thead class="bg-secondary text-white">
                                <tr>
                                    <th width="50%">Producto</th>
                                    <th width="15%">Cantidad</th>
                                    <th width="15%">Precio Unit.</th>
                                    <th width="15%">Subtotal</th>
                                    <th width="5%">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filas centrales -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right font-weight-bold">Subtotal Central:</td>
                                    <td><input type="text" class="form-control form-control-sm text-right font-weight-bold" id="solicitud_total_central" name="total_central" readonly value="0.00"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <h5 class="text-info border-bottom pb-2">3. COMPRA EXTERNA (Conocimiento)</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center" id="tabla_solicitud_externo">
                            <thead class="bg-light">
                                <tr>
                                    <th width="40%">Descripción / Nombre</th>
                                    <th width="15%">Cantidad</th>
                                    <th width="15%">Precio Unit.</th>
                                    <th width="15%">Subtotal</th>
                                    <th width="10%">Respaldo (PDF/Img)</th>
                                    <th width="5%"><button type="button" class="btn btn-sm btn-success" onclick="agregarFilaSolicitud('externo')"><i class="fas fa-plus"></i></button></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filas externas -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right font-weight-bold">Subtotal Externos:</td>
                                    <td><input type="text" class="form-control form-control-sm text-right font-weight-bold" id="solicitud_total_externo" name="total_externos" readonly value="0.00"></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-8 text-right">
                    <h4 class="font-weight-bold">TOTAL GENERAL DE LA SOLICITUD:</h4>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-lg text-right font-weight-bold text-danger" id="solicitud_total_general" name="total_general" readonly value="0.00">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <button type="button" class="btn btn-primary btn-block" onclick="guardarSolicitudRepuesto()">
                        <i class="fas fa-save"></i> GUARDAR SOLICITUD
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-danger btn-block" onclick="descargarPdfSolicitudRepuesto()" id="btnPdfSolicitudRepuesto" style="display: none;">
                        <i class="fas fa-file-pdf"></i> DESCARGAR PDF
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-success btn-block" onclick="descargarExcelSolicitudRepuesto()" id="btnExcelSolicitudRepuesto" style="display: none;">
                        <i class="fas fa-file-excel"></i> DESCARGAR EXCEL
                    </button>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mt-2">
                    <div class="alert alert-info" id="estado_solicitud_container" style="display: none;">
                        <strong>Estado de Solicitud:</strong> <span id="estado_solicitud_label" class="badge badge-primary"></span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let indiceSolicitudLocal = 0;
    let indiceSolicitudCentral = 0;
    let indiceSolicitudExterno = 0;

    function limpiarBuscadorRepuesto() {
        $('#buscador_producto').val(null).trigger('change');
        $('#prod_sel_id').val('');
        $('#prod_sel_texto').val('');
        $('#prod_sel_precio').val('0.00');
        $('#prod_sel_stock_local').val('0');
        $('#prod_sel_stock_central').val('0');
        $('#prod_sel_cantidad').val(1);
    }

    function agregarDesdeBuscador(tipo) {
        let id_prod = $('#prod_sel_id').val();
        let text_prod = $('#prod_sel_texto').val();
        let precio = parseFloat($('#prod_sel_precio').val()) || 0;
        let cantidad = parseInt($('#prod_sel_cantidad').val()) || 0;
        let stock_local = parseInt($('#prod_sel_stock_local').val()) || 0;
        let stock_central = parseInt($('#prod_sel_stock_central').val()) || 0;

        if (!id_prod) {
            Swal.fire('Atención', 'Debe buscar y seleccionar un producto primero', 'warning');
            return;
        }

        if (cantidad <= 0) {
            Swal.fire('Atención', 'La cantidad requerida debe ser mayor a cero', 'warning');
            return;
        }

        if (tipo === 'local') {
            if (cantidad > stock_local) {
                Swal.fire('Stock Insuficiente', 'La cantidad solicitada supera el stock disponible en SUCURSAL LOCAL (' + stock_local + ')', 'error');
                return;
            }
        } else if (tipo === 'central') {
            if (cantidad > stock_central) {
                Swal.fire('Stock Insuficiente', 'La cantidad solicitada supera el stock disponible en SUCURSAL CENTRAL (' + stock_central + ')', 'error');
                return;
            }
        }

        let data = {
            producto_id: id_prod,
            producto_texto: text_prod,
            cantidad: cantidad,
            precio: precio,
            subtotal: (cantidad * precio).toFixed(2),
            aprobado: false
        };

        agregarFilaSolicitud(tipo, data);
        limpiarBuscadorRepuesto();
    }

    function cargarSolicitudRepuesto(orden_id) {
        $.ajax({
            url: "{{ route('grupoCliente.obtenerSolicitudRepuesto') }}",
            type: "POST",
            data: { orden_recepcion_id: orden_id },
            success: function(response) {
                if (response.estado && response.data) {
                    let sol = response.data;
                    $('#solicitud_repuesto_id').val(sol.id);
                    $('#solicitud_total_local').val(sol.total_local || '0.00');
                    $('#solicitud_total_central').val(sol.total_central || '0.00');
                    $('#solicitud_total_externo').val(sol.total_externos || '0.00');
                    $('#solicitud_total_general').val(sol.total_general || '0.00');
                    
                    $('#estado_solicitud_container').show();
                    $('#estado_solicitud_label').text(sol.estado);

                    $('#btnPdfSolicitudRepuesto').show();
                    $('#btnExcelSolicitudRepuesto').show();

                    $('#tabla_solicitud_local tbody').empty();
                    indiceSolicitudLocal = 0;
                    if (sol.local_productos) {
                        sol.local_productos.forEach(prod => agregarFilaSolicitud('local', prod));
                    }

                    $('#tabla_solicitud_central tbody').empty();
                    indiceSolicitudCentral = 0;
                    if (sol.central_productos) {
                        sol.central_productos.forEach(prod => agregarFilaSolicitud('central', prod));
                    }

                    $('#tabla_solicitud_externo tbody').empty();
                    indiceSolicitudExterno = 0;
                    if (sol.externos_productos) {
                        sol.externos_productos.forEach(prod => agregarFilaSolicitud('externo', prod));
                    }
                } else {
                    limpiarSolicitudRepuesto();
                }
            }
        });
    }

    function limpiarSolicitudRepuesto() {
        $('#solicitud_repuesto_id').val('');
        $('#solicitud_total_local').val('0.00');
        $('#solicitud_total_central').val('0.00');
        $('#solicitud_total_externo').val('0.00');
        $('#solicitud_total_general').val('0.00');
        $('#tabla_solicitud_local tbody').empty();
        $('#tabla_solicitud_central tbody').empty();
        $('#tabla_solicitud_externo tbody').empty();
        $('#btnPdfSolicitudRepuesto').hide();
        $('#btnExcelSolicitudRepuesto').hide();
        $('#estado_solicitud_container').hide();
        indiceSolicitudLocal = 0;
        indiceSolicitudCentral = 0;
        indiceSolicitudExterno = 0;
        limpiarBuscadorRepuesto();
        // Agregar una fila vacía solo en externos (ya que los locales/centrales se agregan por buscador)
        agregarFilaSolicitud('externo');
    }

    function agregarFilaSolicitud(tipo, data = null) {
        let html = '';
        let index = 0;
        
        let id_prod = data ? data.producto_id : '';
        let text_prod = data ? data.producto_texto : '';
        let cant = data ? data.cantidad : '1';
        let prec = data ? data.precio : '0.00';
        let subt = data ? data.subtotal : '0.00';

        if (tipo === 'local' || tipo === 'central') {
            if (tipo === 'local') {
                index = indiceSolicitudLocal++;
            } else {
                index = indiceSolicitudCentral++;
            }

            let soloLectura = false;
            let idDetalleHidden = '';
            let estadoLabel = '';
            
            if (data && data.detalle_id) {
                idDetalleHidden = `<input type="hidden" name="${tipo}[${index}][detalle_id]" value="${data.detalle_id}">`;
                if (data.solicitud_estado && data.solicitud_estado !== 'PENDIENTE') {
                    soloLectura = true;
                    let badgeClass = data.solicitud_estado === 'APROBADO' ? 'badge-success' : 'badge-danger';
                    estadoLabel = `<br><span class="badge ${badgeClass} mt-1">${data.solicitud_estado}</span>`;
                }
            }
            
            let btnOpciones = soloLectura ? '' : `<button type="button" class="btn btn-sm btn-danger" onclick="eliminarFilaSolicitud(this)"><i class="fas fa-trash"></i></button>`;
            let attrReadonly = soloLectura ? 'readonly' : '';
            let attrEvent = soloLectura ? '' : 'onkeyup="calcularFilaSolicitud(this)" onchange="calcularFilaSolicitud(this)"';

            html = `
            <tr class="${soloLectura ? 'table-secondary' : ''}">
                <td class="text-left font-weight-bold">
                    ${text_prod}
                    ${idDetalleHidden}
                    <input type="hidden" name="${tipo}[${index}][producto_id]" value="${id_prod}">
                    <input type="hidden" name="${tipo}[${index}][producto_texto]" value="${text_prod}">
                    ${estadoLabel}
                </td>
                <td><input type="number" class="form-control form-control-sm input-cantidad text-center" name="${tipo}[${index}][cantidad]" value="${cant}" min="1" ${attrEvent} ${attrReadonly}></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm input-precio text-right" name="${tipo}[${index}][precio]" value="${prec}" ${attrEvent} ${attrReadonly}></td>
                <td><input type="text" class="form-control form-control-sm input-subtotal text-right font-weight-bold" name="${tipo}[${index}][subtotal]" value="${subt}" readonly></td>
                <td>${btnOpciones}</td>
            </tr>`;
            
            let $tr = $(html);
            $(`#tabla_solicitud_${tipo} tbody`).append($tr);

        } else if (tipo === 'externo') {
            index = indiceSolicitudExterno++;
            let desc = data ? data.descripcion : '';
            
            let soloLectura = false;
            let idDetalleHidden = '';
            let estadoLabel = '';
            
            if (data && data.detalle_id) {
                idDetalleHidden = `<input type="hidden" name="externo[${index}][detalle_id]" value="${data.detalle_id}">`;
                if (data.solicitud_estado && data.solicitud_estado !== 'PENDIENTE') {
                    soloLectura = true;
                    let badgeClass = data.solicitud_estado === 'APROBADO' ? 'badge-success' : 'badge-danger';
                    estadoLabel = `<span class="badge ${badgeClass} mt-1">${data.solicitud_estado}</span>`;
                }
            }

            let descField = soloLectura ? `<input type="text" class="form-control form-control-sm" name="externo[${index}][descripcion]" value="${desc}" readonly><br>${estadoLabel}` : `<input type="text" class="form-control form-control-sm" name="externo[${index}][descripcion]" value="${desc}">`;
            let inputArchivo = soloLectura ? '' : `<input type="file" class="form-control-file text-center mb-1" name="externo_archivos[${index}][archivo]" accept=".pdf,image/*" capture="environment">`;
            let attrReadonly = soloLectura ? 'readonly' : '';
            let attrEvent = soloLectura ? '' : 'onkeyup="calcularFilaSolicitud(this)" onchange="calcularFilaSolicitud(this)"';
            let btnOpciones = soloLectura ? '' : `<button type="button" class="btn btn-sm btn-danger" onclick="eliminarFilaSolicitud(this)"><i class="fas fa-trash"></i></button>`;

            let fileInfo = data && data.path_archivo ? `<a href="/storage/${data.path_archivo}" target="_blank" class="badge badge-info">Ver Archivo</a>
            <input type="hidden" name="externo[${index}][path_archivo]" value="${data.path_archivo}">` : '';
            
            html = `
            <tr class="${soloLectura ? 'table-secondary' : ''}">
                <td>
                    ${idDetalleHidden}
                    ${descField}
                </td>
                <td><input type="number" class="form-control form-control-sm input-cantidad" name="externo[${index}][cantidad]" value="${cant}" min="1" ${attrEvent} ${attrReadonly}></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm input-precio" name="externo[${index}][precio]" value="${prec}" ${attrEvent} ${attrReadonly}></td>
                <td><input type="text" class="form-control form-control-sm input-subtotal" name="externo[${index}][subtotal]" value="${subt}" readonly></td>
                <td>
                    ${inputArchivo}
                    ${fileInfo}
                </td>
                <td>${btnOpciones}</td>
            </tr>`;
            $(`#tabla_solicitud_externo tbody`).append(html);
        }
        
        calcularTotalesSolicitud();
    }

    function eliminarFilaSolicitud(btn) {
        $(btn).closest('tr').remove();
        calcularTotalesSolicitud();
    }

    function calcularFilaSolicitud(elem) {
        let tr = $(elem).closest('tr');
        let cant = parseFloat(tr.find('.input-cantidad').val()) || 0;
        let prec = parseFloat(tr.find('.input-precio').val()) || 0;
        let subt = cant * prec;
        tr.find('.input-subtotal').val(subt.toFixed(2));
        calcularTotalesSolicitud();
    }

    function calcularTotalesSolicitud() {
        let totalLocal = 0;
        $('#tabla_solicitud_local .input-subtotal').each(function() {
            totalLocal += parseFloat($(this).val()) || 0;
        });
        $('#solicitud_total_local').val(totalLocal.toFixed(2));

        let totalCentral = 0;
        $('#tabla_solicitud_central .input-subtotal').each(function() {
            totalCentral += parseFloat($(this).val()) || 0;
        });
        $('#solicitud_total_central').val(totalCentral.toFixed(2));

        let totalExterno = 0;
        $('#tabla_solicitud_externo .input-subtotal').each(function() {
            totalExterno += parseFloat($(this).val()) || 0;
        });
        $('#solicitud_total_externo').val(totalExterno.toFixed(2));

        let totalGeneral = totalLocal + totalCentral + totalExterno;
        $('#solicitud_total_general').val(totalGeneral.toFixed(2));
    }

    function guardarSolicitudRepuesto() {
        let formData = new FormData($('#formularioSolicitudRepuesto')[0]);
        let orden_recepcion_id = $('#orden_recepcion_id').val();
        formData.append('orden_recepcion_id', orden_recepcion_id);
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarSolicitudRepuesto') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if(response.estado) {
                    Swal.fire('Guardado', 'Solicitud guardada correctamente', 'success');
                    cargarSolicitudRepuesto(orden_recepcion_id);
                } else {
                    Swal.fire('Error', response.mensaje || 'Error al guardar', 'error');
                }
            }
        });
    }

    function descargarPdfSolicitudRepuesto() {
        let id = $('#orden_recepcion_id').val();
        if(id) {
            window.open("{{ url('grupo-cliente/descargarPdfSolicitudRepuesto') }}/" + id, "_blank");
        }
    }

    function descargarExcelSolicitudRepuesto() {
        let id = $('#orden_recepcion_id').val();
        if(id) {
            window.open("{{ url('grupo-cliente/descargarExcelSolicitudRepuesto') }}/" + id, "_blank");
        }
    }
</script>
