<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">5. ORDEN DE TRABAJO</h4>
    </div>
    <div class="card-body">
        <form id="formOrdenTrabajoOficial">
            <input type="hidden" id="orden_trabajo_oficial_id" name="orden_trabajo_oficial_id" value="">
            <input type="hidden" id="oto_orden_recepcion_id" name="orden_recepcion_id" value="">

            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-25">VEHICULOS:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="oto_vehiculo" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">PLACAS / CHASIS:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="oto_placas" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">EMPRESA / CLIENTE:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="oto_empresa" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">CONTACTO REF.:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" name="contacto_ref" id="oto_contacto_ref"></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">N° DE ORDEN:</th>
                            <td><input type="text" class="form-control form-control-sm border-0 text-danger font-weight-bold" id="oto_numero_orden" readonly placeholder="Se generará al guardar"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">NIT / C.I.:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="oto_nit" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">KILOMETRAJE:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="oto_kilometraje" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">FECHA DE EMISIÓN:</th>
                            <td><input type="date" class="form-control form-control-sm border-0" name="fecha_emision" id="oto_fecha_emision"></td>
                        </tr>
                    </table>
                </div>
            </div>
            @php
                $servicios = $grupoCliente->servicios ?? [];
            @endphp

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-light border-info">
                        <div class="card-body py-2">
                            <label class="font-weight-bold text-info"><i class="fas fa-search"></i> Buscador de Servicios (Catálogo):</label>
                            <div class="row align-items-end">
                                <div class="col-md-7">
                                    <select class="form-control select2-servicios w-100" id="oto_buscador_servicios" onchange="actualizarMaximoCatalogoOto()">
                                        <option value="">-- Seleccione un servicio --</option>
                                        @foreach($servicios ?? [] as $serv)
                                            <option value="{{ $serv->nombre }}" 
                                                data-item="{{ $serv->item }}"
                                                data-categoria="{{ $serv->categoria }}" 
                                                data-unidad="{{ $serv->unidad_medida }}" 
                                                data-costo="{{ $serv->costo }}"
                                                data-maxcantidad="{{ $serv->cantidad }}">
                                                {{ $serv->nombre }} ({{ $serv->categoria ?? 'S/C' }}) - Máx: {{ $serv->cantidad ?? 1 }} {{ $serv->unidad_medida }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="font-weight-bold text-info mb-0">
                                        Cantidad: 
                                        <small id="oto_max_info" class="text-muted d-block mt-1"></small>
                                    </label>
                                    <input type="number" id="oto_cantidad_input" class="form-control text-center" value="1" min="1">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-info btn-block" onclick="agregarServicioDesdeCatalogoOto()">
                                        <i class="fas fa-plus"></i> Agregar a Lista
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLAS DE SERVICIOS -->
            <div class="table-responsive mb-4">
                <h5 class="bg-primary text-white p-2 mb-0 text-center">TIPO DE SERVICIO: MANTENIMIENTO</h5>
                
                <!-- 1. MANO DE OBRA -->
                <table class="table table-bordered table-sm mb-0" id="tabla_oto_mano_obra">
                    <thead class="bg-light">
                        <tr><th colspan="7" class="text-center text-success">MANO DE OBRA</th></tr>
                        <tr>
                            <th width="10%">N° Item Contrato</th>
                            <th width="35%">Descripción</th>
                            <th width="10%">Unidad Medida</th>
                            <th width="10%">Cantidad Solicitada</th>
                            <th width="12%">Precio Unitario</th>
                            <th width="15%">Precio Total (Bs.)</th>
                            <th width="8%">Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold">sub TOTAL Bs.</td>
                            <td><input type="text" class="form-control form-control-sm text-right bg-white font-weight-bold subtotal-input" name="subtotal_mano_obra" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- 2. REPUESTOS -->
                <table class="table table-bordered table-sm mb-0" id="tabla_oto_repuestos">
                    <thead class="bg-light">
                        <tr><th colspan="7" class="text-center text-success">REPUESTOS</th></tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold">sub TOTAL Bs.</td>
                            <td><input type="text" class="form-control form-control-sm text-right bg-white font-weight-bold subtotal-input" name="subtotal_repuestos" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- 3. INSUMOS -->
                <table class="table table-bordered table-sm mb-0" id="tabla_oto_insumos">
                    <thead class="bg-light">
                        <tr><th colspan="7" class="text-center text-success">INSUMOS</th></tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold">sub TOTAL Bs.</td>
                            <td><input type="text" class="form-control form-control-sm text-right bg-white font-weight-bold subtotal-input" name="subtotal_insumos" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- 4. TRABAJOS A TERCERO -->
                <table class="table table-bordered table-sm mb-4" id="tabla_oto_trabajos_tercero">
                    <thead class="bg-light">
                        <tr><th colspan="7" class="text-center text-success">TRABAJOS A TERCERO</th></tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-weight-bold">sub TOTAL Bs.</td>
                            <td><input type="text" class="form-control form-control-sm text-right bg-white font-weight-bold subtotal-input" name="subtotal_trabajos_tercero" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light text-right w-75">Total (Bs.):</th>
                            <th class="bg-light text-right">
                                <input type="text" class="form-control text-right bg-white font-weight-bold text-danger" name="total_general" id="total_general_oto" readonly value="0.00">
                            </th>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="form-group text-right mt-5">
                <a id="btnDescargarPdfOto" href="#" target="_blank" class="btn btn-danger" style="display:none;"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a id="btnDescargarExcelOto" href="#" class="btn btn-success" style="display:none;"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                <button type="button" class="btn btn-primary" onclick="guardarOrdenTrabajoOficial()">Guardar Orden</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        if ($('#oto_buscador_servicios').length > 0) {
            $('#oto_buscador_servicios').select2({
                placeholder: "-- Seleccione un servicio para agregarlo --",
                allowClear: true,
                width: 'resolve'
            });
        }
    });

    function agregarFilaOto(tablaId, arrName, item_data = null) {
        let tbody = $('#' + tablaId + ' tbody');
        let index = tbody.find('tr').length;
        
        let nro_item = item_data ? item_data.item : '';
        let nombre = item_data ? item_data.nombre : '';
        let cantidad = item_data ? item_data.cantidad : 1;
        let max_cantidad = item_data ? (item_data.max_cantidad || item_data.cantidad) : 1;
        let unidad = item_data ? item_data.unidad_medida : 'Unidad';
        let costo = item_data ? item_data.costo : 0;
        let total = item_data ? item_data.total : costo;

        let tr = `
            <tr>
                <td><input type="text" class="form-control form-control-sm" name="${arrName}[${index}][item]" value="${nro_item}" readonly></td>
                <td><input type="text" class="form-control form-control-sm" name="${arrName}[${index}][nombre]" value="${nombre}" readonly></td>
                <td><input type="text" class="form-control form-control-sm" name="${arrName}[${index}][unidad_medida]" value="${unidad}" readonly></td>
                <td>
                    <input type="number" class="form-control form-control-sm text-center row-cantidad" 
                        name="${arrName}[${index}][cantidad]" value="${cantidad}" min="1" max="${max_cantidad}" step="1" 
                        onchange="recalcularFilaOto(this)">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-right row-costo" 
                        name="${arrName}[${index}][costo]" value="${parseFloat(costo).toFixed(2)}" step="0.01" 
                        readonly>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-right row-total bg-white" 
                        name="${arrName}[${index}][total]" value="${parseFloat(total).toFixed(2)}" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="quitarFilaOto(this)"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        tbody.append(tr);
        recalcularOto();
    }

    function quitarFilaOto(btn) {
        $(btn).closest('tr').remove();
        recalcularOto();
    }

    function recalcularFilaOto(input) {
        let tr = $(input).closest('tr');
        let cantInput = tr.find('.row-cantidad');
        let valInput = parseInt(cantInput.val());
        let maxInput = parseInt(cantInput.attr('max'));
        
        if(valInput > maxInput) {
            Swal.fire('Atención', 'La cantidad no puede superar el límite del catálogo (' + maxInput + ').', 'warning');
            cantInput.val(maxInput);
            valInput = maxInput;
        }
        if(valInput < 1 || isNaN(valInput)) {
            cantInput.val(1);
            valInput = 1;
        }

        let costo = parseFloat(tr.find('.row-costo').val()) || 0;
        let total = valInput * costo;
        tr.find('.row-total').val(total.toFixed(2));
        recalcularOto();
    }

    function recalcularOto() {
        let sumarSubtotal = function(tablaId) {
            let sum = 0;
            $('#' + tablaId + ' tbody .row-total').each(function() {
                sum += parseFloat($(this).val()) || 0;
            });
            return sum;
        };

        let subMano = sumarSubtotal('tabla_oto_mano_obra');
        let subRep = sumarSubtotal('tabla_oto_repuestos');
        let subIns = sumarSubtotal('tabla_oto_insumos');
        let subTerc = sumarSubtotal('tabla_oto_trabajos_tercero');

        $('#tabla_oto_mano_obra tfoot .subtotal-input').val(subMano.toFixed(2));
        $('#tabla_oto_repuestos tfoot .subtotal-input').val(subRep.toFixed(2));
        $('#tabla_oto_insumos tfoot .subtotal-input').val(subIns.toFixed(2));
        $('#tabla_oto_trabajos_tercero tfoot .subtotal-input').val(subTerc.toFixed(2));

        let totalG = subMano + subRep + subIns + subTerc;
        $('#total_general_oto').val(totalG.toFixed(2));
    }

    function actualizarMaximoCatalogoOto() {
        let sel = $('#oto_buscador_servicios').find(':selected');
        let max = sel.data('maxcantidad');
        let unidad = sel.data('unidad') || 'Unidad';
        let info = $('#oto_max_info');
        let cantInput = $('#oto_cantidad_input');

        if(sel.val()) {
            info.text('(Máx disp: ' + max + ' ' + unidad + ')');
            cantInput.attr('max', max);
            cantInput.val(1);
        } else {
            info.text('');
            cantInput.removeAttr('max');
            cantInput.val(1);
        }
    }

    function agregarServicioDesdeCatalogoOto() {
        let sel = $('#oto_buscador_servicios').find(':selected');
        if (!sel.val()) {
            Swal.fire('Atención', 'Debe seleccionar un servicio del catálogo.', 'warning');
            return;
        }

        let cat = (sel.data('categoria') || '').toUpperCase();
        let cantSeleccionada = parseInt($('#oto_cantidad_input').val()) || 1;
        let cantMax = parseInt(sel.data('maxcantidad')) || 1;
        let costo = parseFloat(sel.data('costo')) || 0;

        if (cantSeleccionada > cantMax) {
            Swal.fire('Atención', 'La cantidad seleccionada ('+cantSeleccionada+') supera el máximo disponible ('+cantMax+').', 'warning');
            return;
        }
        if (cantSeleccionada < 1) {
            Swal.fire('Atención', 'La cantidad debe ser al menos 1.', 'warning');
            return;
        }

        let item = {
            item: sel.data('item'),
            nombre: sel.val(),
            unidad_medida: sel.data('unidad'),
            cantidad: cantSeleccionada,
            max_cantidad: cantMax,
            costo: costo,
            total: cantSeleccionada * costo
        };

        if (cat === 'PREVENTIVO' || cat === 'CORRECTIVO') {
            agregarFilaOto('tabla_oto_mano_obra', 'mano_obra', item);
        } else if (cat === 'REPUESTOS') {
            agregarFilaOto('tabla_oto_repuestos', 'repuestos', item);
        } else if (cat === 'SUMINISTRO') {
            agregarFilaOto('tabla_oto_insumos', 'insumos', item);
        } else {
            // Otros
            agregarFilaOto('tabla_oto_trabajos_tercero', 'trabajos_tercero', item);
        }

        $('#oto_buscador_servicios').val('').trigger('change');
    }

    function cargarDatosOrdenTrabajo(orden) {
        $('#oto_orden_recepcion_id').val(orden.id);
        
        // Cabecera
        let autoText = $('#auto_id option:selected').text();
        $('#oto_vehiculo').val(autoText.trim());
        $('#oto_placas').val(orden.placa);
        $('#oto_nit').val("{{ $grupoCliente->cliente->nit ?? '' }}");
        $('#oto_empresa').val("{{ $grupoCliente->cliente->nombres ?? '' }} {{ $grupoCliente->cliente->ap_paterno ?? '' }}");
        $('#oto_kilometraje').val(orden.kilometraje);

        let today = new Date().toISOString().split('T')[0];
        $('#oto_fecha_emision').val(today);

        $.ajax({
            url: "{{ route('grupoCliente.obtenerOrdenTrabajoOficial') }}",
            type: 'POST',
            data: { orden_recepcion_id: orden.id },
            success: function(data) {
                $('#tabla_oto_mano_obra tbody').empty();
                $('#tabla_oto_repuestos tbody').empty();
                $('#tabla_oto_insumos tbody').empty();
                $('#tabla_oto_trabajos_tercero tbody').empty();

                if(data.estado && data.orden) {
                    let ot = data.orden;
                    $('#orden_trabajo_oficial_id').val(ot.id);
                    $('#oto_fecha_emision').val(ot.fecha_emision || today);
                    
                    let numDisplay = '';
                    if(ot.numero_orden_secuencial) {
                        numDisplay = 'SCZ-TGM-OT-' + ot.numero_orden_secuencial + '/' + ot.anio;
                    }
                    $('#oto_numero_orden').val(numDisplay);

                    let mO = ot.mano_obra || [];
                    let rE = ot.repuestos || [];
                    let iN = ot.insumos || [];
                    let tT = ot.trabajos_tercero || [];

                    if(mO.length > 0) mO.forEach(i => agregarFilaOto('tabla_oto_mano_obra', 'mano_obra', i));
                    if(rE.length > 0) rE.forEach(i => agregarFilaOto('tabla_oto_repuestos', 'repuestos', i));
                    if(iN.length > 0) iN.forEach(i => agregarFilaOto('tabla_oto_insumos', 'insumos', i));
                    if(tT.length > 0) tT.forEach(i => agregarFilaOto('tabla_oto_trabajos_tercero', 'trabajos_tercero', i));

                    $('#btnDescargarPdfOto').attr('href', "{{ url('grupo-cliente/descargarPdfOrdenTrabajoOficial') }}/" + ot.id).show();
                    $('#btnDescargarExcelOto').attr('href', "{{ url('grupo-cliente/descargarExcelOrdenTrabajoOficial') }}/" + ot.id).show();
                } else if(data.estado && data.cotizacion) {
                    $('#orden_trabajo_oficial_id').val('');
                    $('#oto_numero_orden').val('');
                    
                    // Preload from Cotizacion
                    let prevs = data.cotizacion.preventivos || [];
                    let corrs = data.cotizacion.correctivos || [];
                    let reps = data.cotizacion.repuestos || [];
                    let insumos = data.cotizacion.insumos || []; // assuming controller does this mapping
                    let otros = data.cotizacion.otros || [];

                    let mO = [].concat(prevs, corrs);
                    if(mO.length > 0) mO.forEach(i => agregarFilaOto('tabla_oto_mano_obra', 'mano_obra', i));
                    if(reps.length > 0) reps.forEach(i => agregarFilaOto('tabla_oto_repuestos', 'repuestos', i));
                    if(insumos.length > 0) insumos.forEach(i => agregarFilaOto('tabla_oto_insumos', 'insumos', i));
                    if(otros.length > 0) otros.forEach(i => agregarFilaOto('tabla_oto_trabajos_tercero', 'trabajos_tercero', i));
                    
                    $('#btnDescargarPdfOto').hide();
                    $('#btnDescargarExcelOto').hide();
                } else {
                    $('#orden_trabajo_oficial_id').val('');
                    $('#oto_numero_orden').val('');
                    $('#btnDescargarPdfOto').hide();
                    $('#btnDescargarExcelOto').hide();
                }
                recalcularOto();
            }
        });
    }

    function guardarOrdenTrabajoOficial() {
        let formData = $('#formOrdenTrabajoOficial').serialize();
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarOrdenTrabajoOficial') }}",
            type: 'POST',
            data: formData,
            success: function(data) {
                if(data.estado){
                    Swal.fire('Excelente!', 'Orden de Trabajo guardada exitosamente.', 'success');
                    $('#orden_trabajo_oficial_id').val(data.orden_id);
                    if(data.numero_orden) {
                        $('#oto_numero_orden').val(data.numero_orden);
                    }
                    
                    $('#btnDescargarPdfOto').attr('href', "{{ url('grupo-cliente/descargarPdfOrdenTrabajoOficial') }}/" + data.orden_id).show();
                    $('#btnDescargarExcelOto').attr('href', "{{ url('grupo-cliente/descargarExcelOrdenTrabajoOficial') }}/" + data.orden_id).show();
                } else {
                    Swal.fire('Error!', 'Ocurrió un error al guardar.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error en el servidor al guardar la Orden de Trabajo.', 'error');
            }
        });
    }
</script>
