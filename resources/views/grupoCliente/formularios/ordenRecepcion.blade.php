<div class="card border-info">
    <div class="card-header bg-info">
        <h4 class="mb-0 text-white">1. ORDEN DE RECEPCIÓN DE VEHÍCULOS</h4>
    </div>
    <div class="card-body">
        <form id="formOrdenRecepcion">
            <!-- Hidden Fields -->
            <input type="hidden" name="grupo_cliente_id" value="{{ $grupoCliente->id }}">
            <input type="hidden" id="orden_recepcion_id" name="orden_recepcion_id" value="">

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Fecha Solicitud:</label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" name="fecha_recepcion" id="fecha_recepcion" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Empresa - Cliente:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" value="{{ $grupoCliente->cliente->nombres }} {{ $grupoCliente->cliente->ap_paterno }} {{ $grupoCliente->cliente->ap_materno }} {{ $grupoCliente->cliente->razon_social }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">NIT - C.I.:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" value="{{ $grupoCliente->cliente->cedula }} {{ $grupoCliente->cliente->nit }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Contacto de ref.:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" value="{{ $grupoCliente->cliente->numero_celular }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Teléfono - Correo:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" value="{{ $grupoCliente->cliente->numero_celular }} - {{ $grupoCliente->cliente->correo }}" readonly>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Vehículo (Placa - Chasis):</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="auto_id" id="auto_id" required>
                                <option value="">Seleccione un vehículo...</option>
                                @foreach($grupoCliente->cliente->autos ?? [] as $auto)
                                    <option value="{{ $auto->id }}" data-marca="{{ $auto->marca->nombre ?? '' }} - {{ $auto->modelo }}">
                                        {{ $auto->placa }} - {{ $auto->marca->nombre ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Clase de vehículo:</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="tipo_unidad" id="tipo_unidad" onchange="cambiarImagenVehiculo()" required>
                                <option value="LIVIANO">LIVIANO</option>
                                <option value="PESADO">PESADO</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Marca - Tipo:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="marca_tipo_readonly" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Kilometraje:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="kilometraje" id="kilometraje">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Objeto de la contratación:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="objeto_contratacion" id="objeto_contratacion">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right font-weight-bold">Porcentaje de combustible (%):</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" name="porcentaje_combustible" id="porcentaje_combustible" min="0" max="100" placeholder="Ej: 50">
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            
            <h5 class="text-center bg-warning p-2">DESCRIPCIÓN DE ELEMENTOS</h5>
            <p class="text-muted small">Referencia para Check: Buen estado / Tiene / Si (v) | Mal estado / No tiene / No (X) | No Aplica (N/A)</p>
            
            @php
                $elementos = [
                    'cenicero' => 'CENICERO', 'encendedor' => 'ENCENDEDOR', 'manual' => 'MANUAL', 'espejos' => 'ESPEJOS',
                    'radio' => 'RADIO', 'pisos' => 'PISOS', 'control_alarma' => 'CONTROL ALARMA', 'limpia_parabrisas' => 'LIMPIA PARABRISAS',
                    'herramientas' => 'HERRAMIENTAS', 'gato' => 'GATO', 'llave_rueda' => 'LLAVE DE RUEDA', 'arresta_llama' => 'ARRESTA LLAMA',
                    'auxilio' => 'AUXILIO', 'varilla' => 'VARILLA', 'tapacubos' => 'TAPACUBOS', 'halogenos' => 'HALOGENOS',
                    'antena' => 'ANTENA', 'tapa_combustible' => 'TAPA COMBUSTIBLE', 'extintor' => 'EXTINTOR', 'triangulo' => 'TRIANGULO',
                    'llave_seguridad' => 'LLAVE DE SEGURIDAD', 'placas' => 'PLACAS', 'botiquin' => 'BOTIQUIN', 'usb' => 'USB'
                ];
                
                // Dividir en dos columnas para la vista
                $col1 = array_slice($elementos, 0, 12, true);
                $col2 = array_slice($elementos, 12, 12, true);
            @endphp

            <div class="row">
                <div class="col-md-7">
                    <div class="row">
                        <!-- Columna 1 Elementos -->
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    @foreach($col1 as $key => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td width="30%">
                                            <select class="form-control form-control-sm" name="checklist[{{ $key }}]" id="checklist_{{ $key }}">
                                                <option value="SI">SI</option>
                                                <option value="NO">NO</option>
                                                <option value="N/A" selected>N/A</option>
                                            </select>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Columna 2 Elementos -->
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    @foreach($col2 as $key => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td width="30%">
                                            <select class="form-control form-control-sm" name="checklist[{{ $key }}]" id="checklist_{{ $key }}">
                                                <option value="SI">SI</option>
                                                <option value="NO">NO</option>
                                                <option value="N/A" selected>N/A</option>
                                            </select>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-5 text-center d-flex align-items-center justify-content-center border">
                    <!-- Image container for Liviano / Pesado -->
                    <div id="imagen_vehiculo_container">
                        <img id="img_vehiculo" src="{{ asset('assets/images/liviano.png') }}" alt="Vehiculo" class="img-fluid" style="max-height: 400px;">
                    </div>
                </div>
            </div>

            <h5 class="text-center bg-light p-2 mt-3 border">OBSERVACION GENERAL</h5>
            <div class="form-group">
                <textarea class="form-control" name="observacion_general" id="observacion_general" rows="3" placeholder="Reporte adicional..."></textarea>
            </div>

            <div class="form-group text-right mt-3">
                <button type="button" class="btn btn-success" onclick="guardarOrdenRecepcion()">Guardar Orden de Recepción</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('auto_id').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        document.getElementById('marca_tipo_readonly').value = selectedOption ? selectedOption.getAttribute('data-marca') : '';
    });

    function cambiarImagenVehiculo() {
        var tipo = document.getElementById('tipo_unidad').value;
        var img = document.getElementById('img_vehiculo');
        if (tipo === 'PESADO') {
            img.src = "{{ asset('assets/images/pesado.png') }}";
        } else {
            img.src = "{{ asset('assets/images/liviano.png') }}";
        }
    }

    function guardarOrdenRecepcion() {
        let datos = $('#formOrdenRecepcion').serialize();
        
        $.ajax({
            url: "{{ route('grupoCliente.guardarOrdenRecepcion') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    Swal.fire(
                        'Excelente!',
                        'Se guardó la Orden de Recepción.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                }else{
                    Swal.fire(
                        'Error!',
                        'Ocurrió un error al guardar la orden.',
                        'error'
                    );
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error en el servidor al guardar la orden.',
                });
            }
        });
    }

    function nuevaOrden() {
        $('#formOrdenRecepcion')[0].reset();
        $('#orden_recepcion_id').val('');
        $('#fecha_recepcion').val("{{ date('Y-m-d') }}");
        $('#auto_id').trigger('change');
        cambiarImagenVehiculo();
        
        // Reset checklist to N/A
        $('select[name^="checklist"]').val('N/A');

        $('#listaOrdenesRecepcion').hide();
        $('#contenedorFormularioOrden').fadeIn();
    }

    function editarOrden(id) {
        $.ajax({
            url: "{{ route('grupoCliente.obtenerOrdenRecepcion') }}",
            type: 'POST',
            data: { orden_recepcion_id: id },
            success: function(data) {
                if(data.estado) {
                    let orden = data.orden;
                    $('#formOrdenRecepcion')[0].reset();
                    $('#orden_recepcion_id').val(orden.id);
                    
                    if(orden.fecha_recepcion) {
                        $('#fecha_recepcion').val(orden.fecha_recepcion.split('T')[0]);
                    }
                    
                    $('#auto_id').val(orden.auto_id).trigger('change');
                    $('#tipo_unidad').val(orden.tipo_unidad);
                    $('#kilometraje').val(orden.kilometraje);
                    $('#objeto_contratacion').val(orden.objeto_contratacion);
                    $('#porcentaje_combustible').val(orden.porcentaje_combustible);
                    $('#observacion_general').val(orden.observacion_general);
                    
                    cambiarImagenVehiculo();

                    // Cargar checklist
                    if (orden.checklist) {
                        let checklistObj = typeof orden.checklist === 'string' ? JSON.parse(orden.checklist) : orden.checklist;
                        $.each(checklistObj, function(key, value) {
                            $('#checklist_' + key).val(value);
                        });
                    } else {
                        $('select[name^="checklist"]').val('N/A');
                    }

                    $('#listaOrdenesRecepcion').hide();
                    $('#contenedorFormularioOrden').fadeIn();

                    // Llenar datos de solo lectura para el Formulario 2 y 3
                    let autoText = $('#auto_id option:selected').text();
                    $('#diag_vehiculo_readonly').val(autoText.trim());
                    $('#form3_vehiculo_readonly').val(autoText.trim());
                    
                    // Cargar Formulario 2 (Diagnóstico)
                    cargarDatosDiagnostico(orden.id);

                    // Cargar Formulario 3 (Formulario Diagnóstico)
                    cargarDatosFormularioDiagnostico(orden.id);
                    
                    // Cargar Formulario 4 (Cotización)
                    cargarDatosCotizacion(orden);
                    
                    // Mostrar tab 1 por defecto
                    $('#pills-form1-tab').tab('show');
                    $('#tituloOrdenActual').text('EDITANDO ORDEN DE TRABAJO N° ' + orden.id);
                } else {
                    Swal.fire('Error', 'No se pudo obtener la orden', 'error');
                }
            }
        });
    }

    function cancelarOrden() {
        $('#contenedorFormularioOrden').hide();
        $('#listaOrdenesRecepcion').fadeIn();
    }
</script>
