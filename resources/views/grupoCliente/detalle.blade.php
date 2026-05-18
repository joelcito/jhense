@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo perfil -->
<div id="modalRegistroMasivo" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">CARGAR SERVICIOS</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formulario_importar_servicios_excel">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="grupo_cliente_id" id="grupo_cliente_id" value="{{ $grupoCliente->id }}">
                    <div class="row">
                        <div class="col-md-1">
                            <button type="button" onclick="descargarFormatoImportarExcel()"
                            class="btn btn-success btn-icon btn-sm btn-circle" title="Descargar formato"><i
                                class="fa fa-download"></i></button>
                        </div>
                        <div class="col-md-11">
                            <input type="file" class="form-control" name="excel_servicio_masivo"
                                id="excel_servicio_masivo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarServicios()">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- fin modal nuevo perfil -->

<!-- fin modal editar perfil -->

<div class="row">
    <div class="col-12">
        <ul class="nav nav-pills custom-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pills-servicios-tab" data-toggle="pill" href="#pills-servicios" role="tab" aria-controls="pills-servicios" aria-selected="true">Servicios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-recepcion-tab" data-toggle="pill" href="#pills-recepcion" role="tab" aria-controls="pills-recepcion" aria-selected="false">Orden de Recepción</a>
            </li>
        </ul>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-servicios" role="tabpanel" aria-labelledby="pills-servicios-tab">
                <div class="card border-info">
    <div class="card-header bg-info">
        <h4 class="mb-0 text-white">
            SERVICIOS DE CLIENTE &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="cargarServicios()"><i
                    class="fas fa-plus"></i> &nbsp; CARGAR SERVICIOS</button>
        </h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <h4 class="text-info">Cliente: {{ $grupoCliente->cliente->nombres ?? '' }} {{ $grupoCliente->cliente->ap_paterno ?? '' }} {{ $grupoCliente->cliente->ap_materno ?? '' }}</h4>
                <h4 class="text-info">Grupo: {{ $grupo->nombre }}</h4>
                <h4 class="text-info">Monto Inicio: {{ $grupoCliente->monto_inicio ? number_format($grupoCliente->monto_inicio, 2) . ' Bs.' : '0.00 Bs.' }}</h4>
            </div>
        </div>
        <form id="formularioItemRangos">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="control-label">1° Rango Item</label>
                        <span class="text-danger">
                            <i class="mr-2 mdi mdi-alert-circle"></i>
                        </span>
                        <input type="number" name="item_1" id="item_1" class="form-control" >
                        <div class="text-danger error-message" id="error-item_1"></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="control-label">2° Rango Item</label>
                        <span class="text-danger">
                            <i class="mr-2 mdi mdi-alert-circle"></i>
                        </span>
                        <input type="number" name="item_2" id="item_2" class="form-control" >
                        <div class="text-danger error-message" id="error-item_2"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Categoria</label>
                        <span class="text-danger">
                            <i class="mr-2 mdi mdi-alert-circle"></i>
                        </span>
                        <select name="categoria" id="categoria" class="form-control"
                            required>
                            <option value="">Seleccione</option>
                            <option value="PREVENTIVO">PREVENTIVO</option>
                            <option value="CORRECTIVO">CORRECTIVO</option>
                            <option value="SUMINISTRO">SUMINISTRO</option>
                            <option value="REPUESTOS">REPUESTOS</option>
                            <option value="OTROS">OTROS</option>
                        </select>
                        <div class="text-danger error-message" id="error-categoria"></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="control-label text-white">.</label>
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarItemRangos()">GUARDAR</button>
                </div>
            </div>
        </form>
        <div id="tabla_detalles"></div>
    </div>
</div>
            </div>
            
            <div class="tab-pane fade" id="pills-recepcion" role="tabpanel" aria-labelledby="pills-recepcion-tab">
                <div id="listaOrdenesRecepcion">
                    <div class="card border-info">
                        <div class="card-header bg-info">
                            <h4 class="mb-0 text-white">
                                ÓRDENES DE RECEPCIÓN &nbsp;&nbsp;
                                <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevaOrden()"><i class="fas fa-plus"></i> &nbsp; NUEVA ORDEN</button>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-center">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Vehículo</th>
                                            <th>Tipo</th>
                                            <th>Kilometraje</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ordenesRecepcion as $orden)
                                            <tr>
                                                <td>{{ $orden->fecha_recepcion ? \Carbon\Carbon::parse($orden->fecha_recepcion)->format('d/m/Y') : '' }}</td>
                                                <td>{{ $orden->auto->placa ?? '' }} - {{ $orden->auto->marca->nombre ?? '' }}</td>
                                                <td>{{ $orden->tipo_unidad }}</td>
                                                <td>{{ $orden->kilometraje }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" onclick="editarOrden({{ $orden->id }})" title="Ver / Editar"><i class="fas fa-edit"></i></button>
                                                    <a href="{{ route('grupoCliente.descargarPdfOrdenRecepcion', $orden->id) }}" target="_blank" class="btn btn-sm btn-danger" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
                                                    <a href="{{ route('grupoCliente.descargarExcelOrdenRecepcion', $orden->id) }}" class="btn btn-sm btn-success" title="Descargar Excel"><i class="fas fa-file-excel"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">No hay órdenes registradas.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenedor oculto para los formularios de la orden -->
                <div id="contenedorFormularioOrden" style="display:none;">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h4 class="text-primary mb-0" id="tituloOrdenActual">NUEVA ORDEN DE TRABAJO</h4>
                        <button class="btn btn-secondary" onclick="cancelarOrden()"><i class="fas fa-arrow-left"></i> Volver a la Lista</button>
                    </div>
                    
                    <!-- Sub-tabs para los 13 formularios -->
                    <ul class="nav nav-pills mb-3" id="pills-tab-orden" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-form1-tab" data-toggle="pill" href="#pills-form1" role="tab" aria-controls="pills-form1" aria-selected="true">1. Recepción</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-form2-tab" data-toggle="pill" href="#pills-form2" role="tab" aria-controls="pills-form2" aria-selected="false">2. Diagnóstico</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-form3-tab" data-toggle="pill" href="#pills-form3" role="tab" aria-controls="pills-form3" aria-selected="false">3. Formulario Diagnóstico</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-form4-tab" data-toggle="pill" href="#pills-form4" role="tab" aria-controls="pills-form4" aria-selected="false">4. Cotización</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-form5-tab" data-toggle="pill" href="#pills-form5" role="tab" aria-controls="pills-form5" aria-selected="false">5. Orden de Trabajo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-form6-tab" data-toggle="pill" href="#pills-form6" role="tab" aria-controls="pills-form6" aria-selected="false">6. Form. Autorización</a>
                        </li>
                        <!-- Aquí se agregarán los demás formularios -->
                    </ul>
                    
                    <div class="tab-content" id="pills-tabContent-orden">
                        <!-- FORM 1: RECEPCIÓN -->
                        <div class="tab-pane fade show active" id="pills-form1" role="tabpanel" aria-labelledby="pills-form1-tab">
                            @include('grupoCliente.formularios.ordenRecepcion')
                        </div>
                        
                        <!-- FORM 2: DIAGNÓSTICO -->
                        <div class="tab-pane fade" id="pills-form2" role="tabpanel" aria-labelledby="pills-form2-tab">
                            @include('grupoCliente.formularios.informeDiagnostico')
                        </div>

                        <!-- FORM 3: FORMULARIO DIAGNOSTICO -->
                        <div class="tab-pane fade" id="pills-form3" role="tabpanel" aria-labelledby="pills-form3-tab">
                            @include('grupoCliente.formularios.formularioDiagnostico')
                        </div>
                        
                        <!-- FORM 4: COTIZACION -->
                        <div class="tab-pane fade" id="pills-form4" role="tabpanel" aria-labelledby="pills-form4-tab">
                            @include('grupoCliente.formularios.cotizacion')
                        </div>

                        <!-- FORM 5/6: ORDEN DE TRABAJO OFICIAL -->
                        <div class="tab-pane fade" id="pills-form5" role="tabpanel" aria-labelledby="pills-form5-tab">
                            @include('grupoCliente.formularios.ordenTrabajo')
                        </div>

                        <!-- FORM 7: FORMULARIO DE AUTORIZACIÓN -->
                        <div class="tab-pane fade" id="pills-form6" role="tabpanel" aria-labelledby="pills-form6-tab">
                            @include('grupoCliente.formularios.formularioAutorizacion')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="{{ asset('assets/libs/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('dist/js/pages/datatable/custom-datatable.js') }}"></script>
<script>

    $.ajaxSetup({
        // definimos cabecera donde estarra el token y poder hacer nuestras operaciones de put,post...
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })

    const sucursal = @json($sucursal);
    const grupo = @json($grupo);
    const grupoCliente = @json($grupoCliente);

    $(document).ready(function() {
        $('#modalGrupo').on('shown.bs.modal', function () {
            $('#cliente_id').select2({
                placeholder: 'Seleccione...',
                dropdownParent: $('#modalGrupo'),
                width: '100%'
            });
        });

        $('#cotiz_buscador_servicios').select2({
            placeholder: 'Seleccione...',
            width: '100%'
        });

        $('#oto_buscador_servicios').select2({
            placeholder: 'Seleccione...',
            width: '100%'
        });

        ajaxListado();
    });

    function ajaxListado(){
        let datos = { grupo_cliente_id: grupoCliente.id };
            $.ajax({
                url: "{{ route('grupoCliente.ajaxDetalle') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_detalles').html(resultado.data.listado)
                }else{

                }
            }
        })
    }

    function limpiarErorres() {
        $('.error-message').html('');
        $('.is-invalid').removeClass('is-invalid');
    }

    function limpiarFormularioItem() {
        $('#item_1').val('');
        $('#item_2').val('');
        $('#categoria').val('');
    }

    function guardarItemRangos(){
        let datos = $('#formularioItemRangos').serializeArray();
        datos.push({
            name: 'sucursal_id',
            value: sucursal.id
        });
        datos.push({
            name: 'grupo_id',
            value: grupo.id
        });
        datos.push({
            name: 'grupo_cliente_id',
            value: grupoCliente.id
        });

        $.ajax({
            url: "{{ route('grupoCliente.guardarItemRangos') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    limpiarFormularioItem();
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Se cambio exitosamente las categorias de items.',
                        'success'
                    )
                }else{
                    Swal.fire(
                        'Error!',
                        'Ocurrió un error al guardar los rangos de items, verifique que los datos sean correctos.',
                        'error'
                    )
                }
            },
            error: function(xhr) {
                limpiarErorres();

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, messages) {
                        let input = $('[name="' + key + '"]');
                        let errorDiv = $('#error-' + key);

                        if (input.length > 0) {
                            input.addClass('is-invalid'); // Agregar clase de error
                            errorDiv.html('<span>' + messages[0] + '</span>'); // Mostrar mensaje
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error inesperado.',
                    });
                }
            }
        });
    }

    //EXCEL
    function cargarServicios() {
        $('#excel_servicio_masivo').val('');
        $('#modalRegistroMasivo').modal('show');
    }

    function descargarFormatoImportarExcel() {
        // Mostrar SweetAlert2 antes de enviar la solicitud
        Swal.fire({
            title: 'Generando Excel...',
            text: 'Por favor espera mientras generamos el archivo.',
            allowOutsideClick: false, // Evitar que se cierre al hacer clic fuera
            didOpen: () => {
                Swal.showLoading(); // Mostrar el spinner de carga
            }
        });

        $.ajax({
            url: "{{ route('grupoCliente.descargarFormatoImportarExcel') }}",
            method: "POST",
            // data: datos,
            xhrFields: {
                responseType: 'blob' // Esto le dice a jQuery que espere un archivo binario (PDF)
            },
            success: function(data, status, xhr) {
                // // Ocultar SweetAlert2 cuando la solicitud sea exitosa
                Swal.close();

                // Assume `data` contains the binary response from the server
                var blob = new Blob([data], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'ImportarServicios.xlsx'; // Nombre del archivo Excel
                document.body.appendChild(link); // Required for Firefox
                link.click();
                document.body.removeChild(link);
            },
            error: function(xhr, status, error) {
                // Mostrar error si algo falla
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo generar el EXCEL. Inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    function guardarServicios() {

        $('#boton_importar_datos_excel').prop('disabled', true);

        if ($("#formulario_importar_servicios_excel")[0].checkValidity()) {
            let datos = new FormData($("#formulario_importar_servicios_excel")[0]);
            $.ajax({
                url: "{{ route('grupoCliente.importarServiciosExcel') }}",
                method: "POST",
                data: datos,
                contentType: false,
                processData: false,
                success: function(data) {

                    if (data.estado === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: "EXITO!",
                            text: "SE REGISTRO CON EXITO",
                        })
                        $('#modalRegistroMasivo').modal('hide');
                    } else if (data.estado === 'warning') {

                        // Supongamos que `datosErroneos` es tu array con las observaciones
                        const datosErroneos = data.errores;

                        // Crea un string HTML a partir del array
                        const erroresHtml = datosErroneos.map(error =>
                            `<li>${error.texto} en el fila [ ${error.fila} ]</li>`).join('');

                        Swal.fire({
                            icon: 'warning',
                            title: data.titulo,
                            text: data.text,
                            html: `
                                SE REGISTRÓ, PERO HAY OBSERVACIONES:<br>
                                <ul>${erroresHtml}</ul>
                            `,
                        })

                        $('#modalRegistroMasivo').modal('hide');

                    } else if (data.estado === 'error') {
                        Swal.fire({
                            icon: 'error',
                            title: "ERROR!",
                            text: data.text,
                        })
                        $('#modalRegistroMasivo').modal('hide');
                    }

                    ajaxListado();

                    $('#boton_importar_datos_excel').prop('disabled', false);
                }
            })

        } else {
            $("#formulario_importar_servicios_excel")[0].reportValidity();
        }
    }

</script>
@endsection
