<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">4. COTIZACIÓN</h4>
    </div>
    <div class="card-body">
        <form id="formCotizacion">
            <input type="hidden" id="cotizacion_id" name="cotizacion_id" value="">
            <input type="hidden" id="cotizacion_orden_recepcion_id" name="orden_recepcion_id" value="">

            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-25">VEHICULOS:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="cotiz_vehiculo" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">PLACAS:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="cotiz_placas" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">EMPRESA:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="cotiz_empresa" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">SERVICIO/TALLER:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" name="servicio_taller" id="cotiz_servicio_taller"></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th class="bg-light w-40">ORDEN DE SERVICIO: N°</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="cotiz_orden_nro" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">KILOMETRAJE ACTUAL:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="cotiz_kilometraje" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">FECHA DE INGRESO:</th>
                            <td><input type="text" class="form-control form-control-sm border-0" id="cotiz_fecha_ingreso" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">FECHA DE SALIDA:</th>
                            <td>
                                <input type="date" class="form-control form-control-sm border-0" name="fecha_salida" id="cotiz_fecha_salida">
                            </td>
                            <td class="bg-light text-center" style="width: 150px; font-size: 11px;">
                                Días hábiles, una vez aprobada la cotización:
                                <input type="text" class="form-control form-control-sm text-center mt-1" name="dias_habiles" id="cotiz_dias_habiles" placeholder="Ej: 5 días">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @php
                $preventivos_full = [];
                $correctivos_full = [];
                $repuestos_full = [];
                
                $servicios = $grupoCliente->servicios ?? [];
                foreach($servicios as $cs) {
                    $cat = strtoupper($cs->categoria ?? '');
                    $item = [
                        'item' => $cs->item ?? '',
                        'nombre' => $cs->nombre ?? '',
                        'cantidad' => $cs->cantidad ?? 1,
                        'unidad_medida' => $cs->unidad_medida ?? 'Unidad',
                        'costo' => $cs->costo ?? 0,
                        'total' => ($cs->cantidad ?? 1) * ($cs->costo ?? 0)
                    ];
                    if($cat == 'PREVENTIVO') {
                        $preventivos_full[] = $item;
                    } elseif($cat == 'CORRECTIVO') {
                        $correctivos_full[] = $item;
                    } else {
                        $repuestos_full[] = $item;
                    }
                }
            @endphp

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-light border-info">
                        <div class="card-body py-2">
                            <label class="font-weight-bold text-info"><i class="fas fa-search"></i> Buscador de Servicios (Catálogo):</label>
                            <div class="row align-items-end">
                                <div class="col-md-7">
                                    <select class="form-control select2-servicios w-100" id="cotiz_buscador_servicios" onchange="actualizarMaximoCatalogo()">
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
                                        <small id="cotiz_max_info" class="text-muted d-block mt-1"></small>
                                    </label>
                                    <input type="number" id="cotiz_cantidad_input" class="form-control text-center" value="1" min="1">
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-info btn-block" type="button" onclick="agregarServicioDesdeCatalogo()">Agregar a la tabla</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MANTENIMIENTO PREVENTIVO -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm text-center" id="tabla_cotiz_preventivo">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%">ITEM</th>
                            <th width="45%">MANTENIMIENTO PREVENTIVO</th>
                            <th width="10%">Cantidad</th>
                            <th width="15%">Unidad</th>
                            <th width="12%">Precio Uni.</th>
                            <th width="13%">TOTAL</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right bg-light">Sub. TOTAL</th>
                            <th class="bg-light">
                                <input type="text" class="form-control form-control-sm text-right bg-white font-weight-bold" name="subtotal_preventivos" id="subtotal_preventivos" readonly value="0.00">
                            </th>
                            <th class="bg-light"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- MANTENIMIENTO CORRECTIVO -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm text-center" id="tabla_cotiz_correctivo">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%">ITEM</th>
                            <th width="45%">MANTENIMIENTO CORRECTIVO</th>
                            <th width="10%">Cantidad</th>
                            <th width="15%">Unidad</th>
                            <th width="12%">Precio Uni.</th>
                            <th width="13%">TOTAL</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right bg-light">Sub. TOTAL</th>
                            <th class="bg-light">
                                <input type="text" class="form-control form-control-sm text-right bg-white font-weight-bold" name="subtotal_correctivos" id="subtotal_correctivos" readonly value="0.00">
                            </th>
                            <th class="bg-light"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- REPUESTOS DE VEHICULOS -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm text-center" id="tabla_cotiz_repuesto">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%">ITEM</th>
                            <th width="45%">REPUESTOS DE VEHICULOS</th>
                            <th width="10%">Cantidad</th>
                            <th width="15%">Unidad</th>
                            <th width="12%">Precio Uni.</th>
                            <th width="13%">TOTAL</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right bg-light">Sub. TOTAL</th>
                            <th class="bg-light">
                                <input type="text" class="form-control form-control-sm text-right bg-white font-weight-bold" name="subtotal_repuestos" id="subtotal_repuestos" readonly value="0.00">
                            </th>
                            <th class="bg-light"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light text-right w-75">SUMA TOTAL Bs.</th>
                            <th class="bg-light text-right">
                                <input type="text" class="form-control text-right bg-white font-weight-bold text-danger" name="total_general" id="total_general_cotiz" readonly value="0.00">
                            </th>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="form-group text-right mt-5">
                <a id="btnDescargarPdfCotiz" href="#" target="_blank" class="btn btn-danger" style="display:none;"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a id="btnDescargarExcelCotiz" href="#" class="btn btn-success" style="display:none;"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                <button type="button" class="btn btn-primary" onclick="guardarCotizacion()">Guardar Cotización</button>
            </div>
        </form>
    </div>
</div>

<script>
    let initPrevFull = @json($preventivos_full ?? []);
    let initCorrFull = @json($correctivos_full ?? []);
    let initRepFull = @json($repuestos_full ?? []);

    function agregarFilaCotizacion(tablaId, arrName, item_data = null) {
        let tbody = $('#' + tablaId + ' tbody');
        let index = tbody.find('tr').length;
        
        let nro_item = item_data ? item_data.item : '';
        let nombre = item_data ? item_data.nombre : '';
        let cantidad = item_data ? item_data.cantidad : 1;
        let max_cantidad = item_data ? (item_data.max_cantidad || item_data.cantidad) : 1;
        let unidad = item_data ? item_data.unidad_medida : 'Unidad';
        let costo = item_data ? item_data.costo : 0;
        let total = item_data ? item_data.total : 0;

        let tr = `
            <tr>
                <td class="align-middle">
                    <input type="text" class="form-control form-control-sm border-0 text-center font-weight-bold bg-white" name="${arrName}[${index}][item]" value="${nro_item}" readonly>
                </td>
                <td><input type="text" class="form-control form-control-sm border-0 bg-white" name="${arrName}[${index}][nombre]" value="${nombre}" readonly></td>
                <td><input type="number" class="form-control form-control-sm border-0 text-center bg-white" name="${arrName}[${index}][cantidad]" value="${cantidad}" readonly></td>
                <td><input type="text" class="form-control form-control-sm border-0 text-center bg-white" name="${arrName}[${index}][unidad_medida]" value="${unidad}" readonly></td>
                <td><input type="number" class="form-control form-control-sm border-0 text-right bg-white" name="${arrName}[${index}][costo]" value="${costo}" step="0.01" readonly></td>
                <td><input type="number" class="form-control form-control-sm border-0 text-right row-total bg-white" name="${arrName}[${index}][total]" value="${total}" readonly></td>
                <td class="align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="quitarFilaCotizacion(this)"><i class="fas fa-times"></i></button></td>
            </tr>
        `;
        tbody.append(tr);
        recalcularCotizacion();
    }

    function quitarFilaCotizacion(btn) {
        let tbody = $(btn).closest('tbody');
        $(btn).closest('tr').remove();
        // Re-index names to avoid PHP holes
        tbody.find('tr').each(function(i, tr) {
            $(tr).find('input').each(function() {
                let name = $(this).attr('name');
                if(name) {
                    name = name.replace(/\[\d+\]/, '[' + i + ']');
                    $(this).attr('name', name);
                }
            });
        });
        recalcularCotizacion();
    }

    function actualizarMaximoCatalogo() {
        let sel = $('#cotiz_buscador_servicios');
        let option = sel.find('option:selected');
        let inputCant = $('#cotiz_cantidad_input');
        
        if (option.val()) {
            let max_cantidad = parseFloat(option.data('maxcantidad')) || 1;
            inputCant.attr('max', max_cantidad);
            inputCant.val(1);
            $('#cotiz_max_info').text('Máx: ' + max_cantidad);
        } else {
            inputCant.removeAttr('max');
            inputCant.val(1);
            $('#cotiz_max_info').text('');
        }
    }

    function recalcularCotizacion() {
        let subPrev = 0;
        $('#tabla_cotiz_preventivo tbody .row-total').each(function() { subPrev += parseFloat($(this).val()) || 0; });
        $('#subtotal_preventivos').val(subPrev.toFixed(2));

        let subCorr = 0;
        $('#tabla_cotiz_correctivo tbody .row-total').each(function() { subCorr += parseFloat($(this).val()) || 0; });
        $('#subtotal_correctivos').val(subCorr.toFixed(2));

        let subRep = 0;
        $('#tabla_cotiz_repuesto tbody .row-total').each(function() { subRep += parseFloat($(this).val()) || 0; });
        $('#subtotal_repuestos').val(subRep.toFixed(2));

        let total = subPrev + subCorr + subRep;
        $('#total_general_cotiz').val(total.toFixed(2));
    }

    function agregarServicioDesdeCatalogo() {
        let sel = $('#cotiz_buscador_servicios');
        let option = sel.find('option:selected');
        
        if (!option.val()) {
            Swal.fire('Atención', 'Debe seleccionar un servicio del buscador', 'warning');
            return;
        }

        let cat = option.data('categoria') ? option.data('categoria').toString().toUpperCase() : '';
        let max_cantidad = parseFloat(option.data('maxcantidad')) || 1;
        let costo = parseFloat(option.data('costo')) || 0;
        
        let cantSeleccionada = parseFloat($('#cotiz_cantidad_input').val());
        if (isNaN(cantSeleccionada) || cantSeleccionada < 1) {
            cantSeleccionada = 1;
        }
        if (cantSeleccionada > max_cantidad) {
            cantSeleccionada = max_cantidad;
            Swal.fire('Atención', 'Se ajustó la cantidad al máximo permitido (' + max_cantidad + ')', 'info');
            $('#cotiz_cantidad_input').val(max_cantidad);
        }

        let item = {
            item: option.data('item'),
            nombre: option.val(),
            cantidad: cantSeleccionada,
            max_cantidad: max_cantidad,
            unidad_medida: option.data('unidad') || 'Unidad',
            costo: costo,
            total: cantSeleccionada * costo
        };

        if (cat === 'PREVENTIVO') {
            agregarFilaCotizacion('tabla_cotiz_preventivo', 'preventivos', item);
        } else if (cat === 'CORRECTIVO') {
            agregarFilaCotizacion('tabla_cotiz_correctivo', 'correctivos', item);
        } else {
            // Repuestos y otros
            agregarFilaCotizacion('tabla_cotiz_repuesto', 'repuestos', item);
        }

        sel.val('').trigger('change');
    }

    function cargarDatosCotizacion(orden) {
        $('#cotizacion_orden_recepcion_id').val(orden.id);
        
        // Cabecera prellenada
        let autoText = $('#auto_id option:selected').text();
        $('#cotiz_vehiculo').val(autoText.trim());
        $('#cotiz_placas').val(orden.placa); // Si se tiene
        $('#cotiz_empresa').val("{{ $grupoCliente->cliente->nombres ?? '' }} {{ $grupoCliente->cliente->ap_paterno ?? '' }}");
        $('#cotiz_orden_nro').val(orden.id);
        $('#cotiz_kilometraje').val(orden.kilometraje);
        
        let fechaIngreso = orden.created_at ? orden.created_at.split('T')[0] : '';
        $('#cotiz_fecha_ingreso').val(fechaIngreso);

        // Fetch Cotización
        $.ajax({
            url: "{{ route('grupoCliente.obtenerCotizacion') }}",
            type: 'POST',
            data: { orden_recepcion_id: orden.id },
            success: function(data) {
                $('#tabla_cotiz_preventivo tbody').empty();
                $('#tabla_cotiz_correctivo tbody').empty();
                $('#tabla_cotiz_repuesto tbody').empty();

                if(data.estado && data.cotizacion) {
                    let c = data.cotizacion;
                    $('#cotizacion_id').val(c.id);
                    $('#cotiz_servicio_taller').val(c.servicio_taller);
                    $('#cotiz_fecha_salida').val(c.fecha_salida);
                    $('#cotiz_dias_habiles').val(c.dias_habiles);

                    let prevs = c.preventivos || [];
                    let corrs = c.correctivos || [];
                    let reps = c.repuestos || [];

                    if(prevs.length > 0) prevs.forEach(i => agregarFilaCotizacion('tabla_cotiz_preventivo', 'preventivos', i));
                    if(corrs.length > 0) corrs.forEach(i => agregarFilaCotizacion('tabla_cotiz_correctivo', 'correctivos', i));
                    if(reps.length > 0) reps.forEach(i => agregarFilaCotizacion('tabla_cotiz_repuesto', 'repuestos', i));

                    // Links
                    $('#btnDescargarPdfCotiz').attr('href', "{{ url('grupo-cliente/descargarPdfCotizacion') }}/" + orden.id).show();
                    $('#btnDescargarExcelCotiz').attr('href', "{{ url('grupo-cliente/descargarExcelCotizacion') }}/" + orden.id).show();
                } else {
                    $('#cotizacion_id').val('');
                    $('#cotiz_servicio_taller').val('');
                    $('#cotiz_fecha_salida').val('');
                    $('#cotiz_dias_habiles').val('');

                    // Ya no pre-cargamos automáticamente las filas para que el usuario las seleccione
                    // initPrevFull.forEach(i => agregarFilaCotizacion('tabla_cotiz_preventivo', 'preventivos', i));
                    // initCorrFull.forEach(i => agregarFilaCotizacion('tabla_cotiz_correctivo', 'correctivos', i));
                    // initRepFull.forEach(i => agregarFilaCotizacion('tabla_cotiz_repuesto', 'repuestos', i));

                    $('#btnDescargarPdfCotiz').hide();
                    $('#btnDescargarExcelCotiz').hide();
                }
                recalcularCotizacion();
            }
        });
    }

    function guardarCotizacion() {
        let formData = $('#formCotizacion').serialize();
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarCotizacion') }}",
            type: 'POST',
            data: formData,
            success: function(data) {
                if(data.estado){
                    Swal.fire('Excelente!', 'Cotización guardada exitosamente.', 'success');
                    $('#cotizacion_id').val(data.cotizacion_id);
                    
                    $('#btnDescargarPdfCotiz').attr('href', "{{ url('grupo-cliente/descargarPdfCotizacion') }}/" + $('#cotizacion_orden_recepcion_id').val()).show();
                    $('#btnDescargarExcelCotiz').attr('href', "{{ url('grupo-cliente/descargarExcelCotizacion') }}/" + $('#cotizacion_orden_recepcion_id').val()).show();
                } else {
                    Swal.fire('Error!', 'Ocurrió un error al guardar.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error en el servidor al guardar cotización.', 'error');
            }
        });
    }
</script>
