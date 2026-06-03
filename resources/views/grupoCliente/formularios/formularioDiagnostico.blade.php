<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">3. FORMULARIO PARA DIAGNÓSTICO DE MANTENIMIENTO</h4>
    </div>
    <div class="card-body">
        <form id="formFormularioDiagnostico">
            <input type="hidden" id="form3_formulario_diagnostico_id" name="formulario_diagnostico_id" value="">
            <input type="hidden" id="form3_orden_recepcion_id" name="orden_recepcion_id" value="">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Responsable del Vehículo:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="responsable_vehiculo" id="form3_responsable_vehiculo" value="{{ $grupoCliente->cliente->nombres ?? '' }} {{ $grupoCliente->cliente->ap_paterno ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Vehículo Asignado a:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="vehiculo_asignado_a" id="form3_vehiculo_asignado_a">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Vehículo (Ref.):</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="form3_vehiculo_readonly" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            
            <div class="row">
                <div class="col-md-8">
                    <h5 class="bg-secondary text-white p-2 text-center">REQUERIMIENTO DE SERVICIO</h5>
                    
                    @php
                        $servicios = $grupoCliente->servicios ?? [];
                        
                        $preventivos = [];
                        $correctivos = [];
                        $otros = [];
                        
                        /* foreach($servicios as $cs) {
                            $cat = strtoupper($cs->categoria ?? '');
                            if($cat == 'PREVENTIVO') {
                                $preventivos[] = $cs->nombre ?? '';
                            } elseif($cat == 'CORRECTIVO') {
                                $correctivos[] = $cs->nombre ?? '';
                            } else {
                                $otros[] = $cs->nombre ?? '';
                            }
                        } */
                    @endphp

                    <!-- PREVENTIVO -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold">Mantenimiento Preventivo</h6>
                        <table class="table table-sm table-bordered">
                            <tbody id="tabla_preventivos">
                                
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="agregarFila('tabla_preventivos', 'preventivos[]')">+ Agregar fila</button>
                    </div>

                    <!-- CORRECTIVO -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold">Mantenimiento Correctivo</h6>
                        <table class="table table-sm table-bordered">
                            <tbody id="tabla_correctivos">
                                
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="agregarFila('tabla_correctivos', 'correctivos[]')">+ Agregar fila</button>
                    </div>

                    <!-- OTROS -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold">Otros servicios requeridos</h6>
                        <table class="table table-sm table-bordered">
                            <tbody id="tabla_otros">
                                
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="agregarFila('tabla_otros', 'otros[]')">+ Agregar fila</button>
                    </div>
                </div>

                <div class="col-md-4">
                    <h5 class="bg-secondary text-white p-2 text-center">INVENTARIO</h5>
                    <p class="text-muted small text-center">(Heredado de la Orden de Recepción - Solo lectura)</p>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <tbody id="form3_inventario_list">
                            </tbody>
                        </table>
                    </div>

                    <h5 class="bg-secondary text-white p-2 text-center mt-4">Recepción del Taller</h5>
                    <textarea class="form-control" name="recepcion_taller" id="form3_recepcion_taller" rows="6" placeholder="Observaciones de recepción..."></textarea>
                </div>
            </div>

            <div class="form-group text-right mt-5">
                <a id="btnDescargarPdfForm3" href="#" target="_blank" class="btn btn-danger" style="display:none;"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a id="btnDescargarExcelForm3" href="#" class="btn btn-success" style="display:none;"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                <button type="button" class="btn btn-primary" onclick="guardarFormularioDiagnostico()">Guardar Formulario de Diagnóstico</button>
            </div>
        </form>
    </div>
</div>

<script>
    function agregarFila(tablaId, inputName) {
        let tbody = document.getElementById(tablaId);
        let rowCount = tbody.rows.length;
        let tr = document.createElement('tr');
        tr.innerHTML = `
            <td width="5%" class="text-center font-weight-bold">${rowCount + 1}</td>
            <td><input type="text" class="form-control form-control-sm border-0" name="${inputName}" value=""></td>
        `;
        tbody.appendChild(tr);
    }

    let inicialesPreventivos = @json($preventivos ?? []);
    let inicialesCorrectivos = @json($correctivos ?? []);
    let inicialesOtros = @json($otros ?? []);

    function cargarDatosFormularioDiagnostico(orden_id) {
        $('#form3_orden_recepcion_id').val(orden_id);
        
        // Fetch existing data for form 3 and checklist from form 1
        $.ajax({
            url: "{{ route('grupoCliente.obtenerFormularioDiagnostico') }}",
            type: 'POST',
            data: { orden_recepcion_id: orden_id },
            success: function(data) {
                if(data.estado) {
                    if(data.formulario) {
                        let f = data.formulario;
                        $('#form3_formulario_diagnostico_id').val(f.id);
                        $('#form3_responsable_vehiculo').val(f.responsable_vehiculo);
                        $('#form3_vehiculo_asignado_a').val(f.vehiculo_asignado_a);
                        $('#form3_recepcion_taller').val(f.recepcion_taller);
                        
                        // Si ya tiene guardado json, sobreescribir las tablas
                        renderizarTablaForm3('tabla_preventivos', 'preventivos[]', (f.servicios_preventivos && f.servicios_preventivos.length > 0) ? f.servicios_preventivos : inicialesPreventivos);
                        renderizarTablaForm3('tabla_correctivos', 'correctivos[]', (f.servicios_correctivos && f.servicios_correctivos.length > 0) ? f.servicios_correctivos : inicialesCorrectivos);
                        renderizarTablaForm3('tabla_otros', 'otros[]', (f.servicios_otros && f.servicios_otros.length > 0) ? f.servicios_otros : inicialesOtros);

                        $('#btnDescargarPdfForm3').attr('href', "{{ url('grupo-cliente/descargarPdfFormularioDiagnostico') }}/" + orden_id).show();
                        $('#btnDescargarExcelForm3').attr('href', "{{ url('grupo-cliente/descargarExcelFormularioDiagnostico') }}/" + orden_id).show();
                    } else {
                        // Limpiar si no hay datos (pero mantener los defaults cargados en blade)
                        $('#form3_formulario_diagnostico_id').val('');
                        $('#form3_vehiculo_asignado_a').val('');
                        $('#form3_recepcion_taller').val('');

                        renderizarTablaForm3('tabla_preventivos', 'preventivos[]', inicialesPreventivos);
                        renderizarTablaForm3('tabla_correctivos', 'correctivos[]', inicialesCorrectivos);
                        renderizarTablaForm3('tabla_otros', 'otros[]', inicialesOtros);

                        $('#btnDescargarPdfForm3').hide();
                        $('#btnDescargarExcelForm3').hide();
                    }

                    // Renderizar checklist
                    if(data.checklist) {
                        let html = '';
                        $.each(data.checklist, function(key, val) {
                            if(key !== 'firma_entrega') {
                                let label = key.replace(/_/g, ' ');
                                label = label.charAt(0).toUpperCase() + label.slice(1);
                                html += `<tr>
                                    <td>${label}</td>
                                    <td width="15%" class="text-center font-weight-bold">${val}</td>
                                </tr>`;
                            }
                        });
                        $('#form3_inventario_list').html(html);
                    }
                }
            }
        });
    }

    function renderizarTablaForm3(tablaId, inputName, datos) {
        let tbody = document.getElementById(tablaId);
        tbody.innerHTML = '';
        datos.forEach((val, index) => {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td width="5%" class="text-center font-weight-bold">${index + 1}</td>
                <td><input type="text" class="form-control form-control-sm border-0" name="${inputName}" value="${val}"></td>
            `;
            tbody.appendChild(tr);
        });
    }

    function guardarFormularioDiagnostico() {
        let formData = $('#formFormularioDiagnostico').serialize();
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarFormularioDiagnostico') }}",
            type: 'POST',
            data: formData,
            success: function(data) {
                if(data.estado){
                    Swal.fire('Excelente!', 'Se guardó el Formulario de Diagnóstico.', 'success');
                    $('#form3_formulario_diagnostico_id').val(data.formulario_diagnostico_id);
                }else{
                    Swal.fire('Error!', 'Ocurrió un error al guardar.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Error en el servidor al guardar.', 'error');
            }
        });
    }
</script>
