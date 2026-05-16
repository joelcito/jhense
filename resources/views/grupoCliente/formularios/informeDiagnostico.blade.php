<div class="card border-primary">
    <div class="card-header bg-primary">
        <h4 class="mb-0 text-white">2. INFORME TÉCNICO DE DIAGNÓSTICO</h4>
    </div>
    <div class="card-body">
        <form id="formInformeDiagnostico" enctype="multipart/form-data">
            <input type="hidden" id="informe_diagnostico_id" name="informe_diagnostico_id" value="">
            <input type="hidden" id="informe_orden_recepcion_id" name="orden_recepcion_id" value="">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Elaborado por (Mecánico):</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="mecanico_id" id="mecanico_id" required>
                                <option value="">Seleccione...</option>
                                @foreach($mecanicos ?? [] as $mecanico)
                                    <option value="{{ $mecanico->id }}">{{ $mecanico->nombres }} {{ $mecanico->ap_paterno }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Vehículo (Solo Ref.):</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="diag_vehiculo_readonly" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            
            <h5>3.2 Inspección Exterior</h5>
            <div class="form-group">
                <textarea class="form-control" name="inspeccion_exterior_texto" id="inspeccion_exterior_texto" rows="3" placeholder="Descripción de la inspección exterior..."></textarea>
            </div>
            <div class="form-group">
                <label>Reporte Fotográfico Exterior (Imagen):</label>
                <input type="file" class="form-control-file" name="inspeccion_exterior_imagen" id="inspeccion_exterior_imagen" accept="image/*">
                <div id="preview_exterior" class="mt-2"></div>
            </div>

            <hr>

            <h5>3.3 Inspección Interior</h5>
            <div class="form-group">
                <textarea class="form-control" name="inspeccion_interior_texto" id="inspeccion_interior_texto" rows="3" placeholder="Descripción de la inspección interior..."></textarea>
            </div>
            <div class="form-group">
                <label>Reporte Fotográfico Interior (Imagen):</label>
                <input type="file" class="form-control-file" name="inspeccion_interior_imagen" id="inspeccion_interior_imagen" accept="image/*">
                <div id="preview_interior" class="mt-2"></div>
            </div>

            <hr>

            <h5>3.4 Diagnóstico del Estado Automotriz</h5>
            
            <div id="contenedorDiagnosticos">
                <!-- Aquí se agregarán dinámicamente los bloques de diagnóstico -->
            </div>
            
            <button type="button" class="btn btn-outline-primary mt-2" onclick="agregarDiagnostico()"><i class="fas fa-plus"></i> Agregar Diagnóstico</button>

            <div class="form-group text-right mt-5">
                <a id="btnDescargarPdfDiag" href="#" target="_blank" class="btn btn-danger" style="display:none;"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                <a id="btnDescargarExcelDiag" href="#" class="btn btn-success" style="display:none;"><i class="fas fa-file-excel"></i> Descargar Excel</a>
                <button type="button" class="btn btn-primary" onclick="guardarInformeDiagnostico()">Guardar Informe de Diagnóstico</button>
            </div>
        </form>
    </div>
</div>

<!-- Template para un bloque de diagnóstico (oculto) -->
<template id="templateDiagnostico">
    <div class="diagnostico-item border p-3 mb-3 bg-light position-relative">
        <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: 10px; right: 10px;" onclick="eliminarDiagnostico(this)"><i class="fas fa-trash"></i></button>
        
        <div class="row">
            <div class="col-md-12 mb-2">
                <label>Imagen del Componente:</label>
                <input type="file" class="form-control-file diagnostico-imagen-input" name="diagnostico_imagenes[]" accept="image/*">
            </div>
            
            <div class="col-md-12 mb-3">
                <label>Buscar Consulta Predefinida:</label>
                <select class="form-control select-consulta" onchange="seleccionarConsulta(this)">
                    <option value="">-- Seleccione para auto-completar --</option>
                    @foreach($consultas ?? [] as $consulta)
                        <option value="{{ $consulta->id }}" 
                            data-componente="{{ $consulta->componente }}"
                            data-sintoma="{{ $consulta->sintoma }}"
                            data-tipofalla="{{ $consulta->tipo_falla }}"
                            data-causaprobable="{{ $consulta->causa_probable }}"
                            data-estadoactual="{{ $consulta->estado_actual }}"
                            data-recomendacion="{{ $consulta->recomendacion }}"
                            data-riesgo="{{ $consulta->riesgo_asociado }}"
                            data-justificacion="{{ $consulta->justificacion }}">
                            {{ $consulta->componente }} - {{ $consulta->sintoma }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm table-white bg-white">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th>Componente</th>
                        <th>Síntoma</th>
                        <th>Tipo Falla</th>
                        <th>Causa Probable</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" class="form-control form-control-sm" name="diag_componente[]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="diag_sintoma[]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="diag_tipo_falla[]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="diag_causa_probable[]"></td>
                    </tr>
                </tbody>
                <thead class="bg-secondary text-white">
                    <tr>
                        <th>Estado Actual</th>
                        <th>Recomendación</th>
                        <th>Riesgos Asociados</th>
                        <th>Justificación</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" class="form-control form-control-sm" name="diag_estado_actual[]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="diag_recomendacion[]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="diag_riesgo_asociado[]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="diag_justificacion[]"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    function agregarDiagnostico(datos = null) {
        let template = document.getElementById('templateDiagnostico').innerHTML;
        let container = document.getElementById('contenedorDiagnosticos');
        
        let div = document.createElement('div');
        div.innerHTML = template;
        
        if(datos) {
            // Llenar datos si vienen de la BD
            let t = div;
            t.querySelector('[name="diag_componente[]"]').value = datos.componente || '';
            t.querySelector('[name="diag_sintoma[]"]').value = datos.sintoma || '';
            t.querySelector('[name="diag_tipo_falla[]"]').value = datos.tipo_falla || '';
            t.querySelector('[name="diag_causa_probable[]"]').value = datos.causa_probable || '';
            t.querySelector('[name="diag_estado_actual[]"]').value = datos.estado_actual || '';
            t.querySelector('[name="diag_recomendacion[]"]').value = datos.recomendacion || '';
            t.querySelector('[name="diag_riesgo_asociado[]"]').value = datos.riesgo_asociado || '';
            t.querySelector('[name="diag_justificacion[]"]').value = datos.justificacion || '';
            
            if(datos.imagen_url) {
                let imgPreview = document.createElement('img');
                imgPreview.src = datos.imagen_url;
                imgPreview.style.maxHeight = '100px';
                imgPreview.className = 'mt-2 d-block';
                t.querySelector('.diagnostico-imagen-input').parentNode.appendChild(imgPreview);
                
                // Keep the existing image path in a hidden input so we don't lose it if they don't upload a new one
                let hiddenImg = document.createElement('input');
                hiddenImg.type = 'hidden';
                hiddenImg.name = 'diag_imagen_existente[]';
                hiddenImg.value = datos.imagen_path || '';
                t.querySelector('.diagnostico-imagen-input').parentNode.appendChild(hiddenImg);
            } else {
                let hiddenImg = document.createElement('input');
                hiddenImg.type = 'hidden';
                hiddenImg.name = 'diag_imagen_existente[]';
                hiddenImg.value = '';
                t.querySelector('.diagnostico-imagen-input').parentNode.appendChild(hiddenImg);
            }
        } else {
            let hiddenImg = document.createElement('input');
            hiddenImg.type = 'hidden';
            hiddenImg.name = 'diag_imagen_existente[]';
            hiddenImg.value = '';
            div.querySelector('.diagnostico-imagen-input').parentNode.appendChild(hiddenImg);
        }
        
        container.appendChild(div.firstElementChild);
    }

    function eliminarDiagnostico(btn) {
        $(btn).closest('.diagnostico-item').remove();
    }

    function seleccionarConsulta(select) {
        let option = $(select).find('option:selected');
        if(option.val() !== "") {
            let item = $(select).closest('.diagnostico-item');
            item.find('[name="diag_componente[]"]').val(option.data('componente'));
            item.find('[name="diag_sintoma[]"]').val(option.data('sintoma'));
            item.find('[name="diag_tipo_falla[]"]').val(option.data('tipofalla'));
            item.find('[name="diag_causa_probable[]"]').val(option.data('causaprobable'));
            item.find('[name="diag_estado_actual[]"]').val(option.data('estadoactual'));
            item.find('[name="diag_recomendacion[]"]').val(option.data('recomendacion'));
            item.find('[name="diag_riesgo_asociado[]"]').val(option.data('riesgo'));
            item.find('[name="diag_justificacion[]"]').val(option.data('justificacion'));
        }
    }

    function cargarDatosDiagnostico(orden_id) {
        $('#informe_orden_recepcion_id').val(orden_id);
        $('#formInformeDiagnostico')[0].reset();
        $('#contenedorDiagnosticos').empty();
        $('#preview_exterior').empty();
        $('#preview_interior').empty();
        $('#informe_diagnostico_id').val('');

        $.ajax({
            url: "{{ route('grupoCliente.obtenerInformeDiagnostico') }}",
            type: 'POST',
            data: { orden_recepcion_id: orden_id },
            success: function(data) {
                if(data.estado && data.informe) {
                    let info = data.informe;
                    $('#informe_diagnostico_id').val(info.id);
                    $('#mecanico_id').val(info.mecanico_id);
                    $('#inspeccion_exterior_texto').val(info.inspeccion_exterior_texto);
                    $('#inspeccion_interior_texto').val(info.inspeccion_interior_texto);

                    if(info.inspeccion_exterior_imagen_url) {
                        $('#preview_exterior').html(`<img src="${info.inspeccion_exterior_imagen_url}" style="max-height:150px;" class="img-thumbnail">`);
                    }
                    if(info.inspeccion_interior_imagen_url) {
                        $('#preview_interior').html(`<img src="${info.inspeccion_interior_imagen_url}" style="max-height:150px;" class="img-thumbnail">`);
                    }

                    if(info.diagnosticos && Array.isArray(info.diagnosticos)) {
                        info.diagnosticos.forEach(diag => agregarDiagnostico(diag));
                    }
                    
                    // Update download links
                    $('#btnDescargarPdfDiag').attr('href', "{{ url('grupo-cliente/descargarPdfInformeDiagnostico') }}/" + orden_id).show();
                    $('#btnDescargarExcelDiag').attr('href', "{{ url('grupo-cliente/descargarExcelInformeDiagnostico') }}/" + orden_id).show();

                } else {
                    // Start with one blank diagnostic if no info exists
                    agregarDiagnostico();
                    $('#btnDescargarPdfDiag').hide();
                    $('#btnDescargarExcelDiag').hide();
                }
            }
        });
    }

    function guardarInformeDiagnostico() {
        let formData = new FormData($('#formInformeDiagnostico')[0]);
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarInformeDiagnostico') }}",
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data) {
                if(data.estado){
                    Swal.fire(
                        'Excelente!',
                        'Se guardó el Informe de Diagnóstico.',
                        'success'
                    );
                    $('#informe_diagnostico_id').val(data.informe_diagnostico_id);
                    cargarDatosDiagnostico($('#informe_orden_recepcion_id').val()); // Refresh to get image URLs
                }else{
                    Swal.fire('Error!', 'Ocurrió un error al guardar el informe.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error en el servidor al guardar el informe.' });
            }
        });
    }
</script>
