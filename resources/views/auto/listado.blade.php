@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo perfil -->
<div id="modalAuto" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO AUTOMOVIL</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioAuto">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Cliente</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <select name="cliente_id" id="cliente_id" class="form-control"
                                    required>
                                    <option value="">Seleccione</option>
                                    @foreach ($clientes as $cli)
                                        <option value={{ $cli->id }}>{{ $cli->cedula }} - {{ $cli->nombres }} {{ $cli->ap_paterno ?? '' }} {{ $cli->ap_materno ?? '' }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-message" id="error-cliente_id"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Marca</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <select name="marca_id" id="marca_id" class="form-control"
                                    required>
                                    <option value="">Seleccione</option>
                                    @foreach ($marcas as $ma)
                                        <option value={{ $ma->id }}>{{ $ma->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-message" id="error-marca_id"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Placa</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="placa" type="text" id="placa" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-placa"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Modelo</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="modelo" type="text" id="modelo" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-modelo"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Motor</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="motor" type="text" id="motor" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-motor"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Año Fab.</label>
                                <input name="anio_fab" type="number" id="anio_fab" class="form-control" required>
                                <div class="text-danger error-message" id="error-anio_fab"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Vin</label>
                                <input name="vin" type="text" id="vin" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-vin"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarAuto()">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- fin modal nuevo perfil -->

<!-- fin modal editar perfil -->
<div class="card border-info">
    <div class="card-header bg-info">
        <h4 class="mb-0 text-white">
            LISTADO DE AUTOMOVILES &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevoAuto()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVO AUTOMOVIL</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_autos"></div>
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

    $(document).ready(function() {
        $('#modalAuto').on('shown.bs.modal', function () {
            $('#cliente_id, #marca_id').select2({
                placeholder: 'Seleccione...',
                dropdownParent: $('#modalAuto'),
                width: '100%'
            });
        });

        ajaxListado();
    });

    function ajaxListado(){
        let datos = {};
            $.ajax({
                url: "{{ route('auto.ajaxListado') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_autos').html(resultado.data.listado)
                }else{

                }
            }
        })
    }

    function limpiarErorres() {
        $('.error-message').html('');
        $('.is-invalid').removeClass('is-invalid');
    }

    function nuevoAuto()
    {
        limpiarErorres();

        $('#id').val(0);
        $('#cliente_id').val(null).trigger('change');
        $('#marca_id').val(null).trigger('change');
        $('#placa').val("");
        $('#modelo').val("");
        $('#motor').val("");
        $('#anio_fab').val("");
        $('#vin').val("");
        $("#modalAuto").modal('show');
    }

    function guardarAuto(){
        let datos = $('#formularioAuto').serializeArray();
        $.ajax({
            url: "{{ route('auto.guardarAuto') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    $('#modalAuto').modal('hide')
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Auto creado correctamente.',
                        'success'
                    )
                }else{

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

    function editarAuto(auto) {
        limpiarErorres();

        Object.keys(auto).forEach(key => {
            let input = $(`#${key}`);

            // Saltar los inputs de tipo file
            if (input.attr('type') === 'file') {
                return; // ignorar este campo
            }

            if (input.is(':checkbox')) {
                //  si el valor es 1, true o "on"
                input.prop('checked', auto[key] == 1 || auto[key] === true || auto[key] === "on");
            } else if (input.is('select')) {
                // Para selects con librerías como Select2
                input.val(auto[key]).trigger('change');
            } else if (input.length) {
                // Para inputs normales (text, number, email, etc.)
                input.val(auto[key]);
            }
        });
        $('#modalAuto').modal('show');
    }

    function eliminarAuto(auto){
        Swal.fire({
            title: "Quieres eliminar el automovil con placa "+auto.placa,
            text: "Luego no podras recuperarlo!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, estoy seguro!',
            cancelButtonText: "Cancelar",
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "{{ route('auto.eliminarAuto') }}",
                    method: "POST",
                    data: auto,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Auto eliminado correctamente.',
                                'success'
                            )
                            ajaxListado();
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error inesperado.',
                        });
                    }
                });
            } else if (result.dismiss === "cancel") {
                Swal.fire(
                    "Cancelado",
                    "La operacion fue cancelada",
                    "error"
                )
            }
        });

    }

</script>
@endsection
