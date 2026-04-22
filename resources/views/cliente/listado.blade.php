@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo perfil -->
<div id="modalCliente" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO CLIENTE</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioCliente">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Nombres</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="nombres" type="text" id="nombres" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-nombres"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Ap. Paterno</label>
                                <input name="ap_paterno" type="text" id="ap_paterno" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-ap_paterno"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Ap. Materno</label>
                                <input name="ap_materno" type="text" id="ap_materno" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-ap_materno"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Cedula</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="cedula" type="text" id="cedula" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-cedula"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Complemento</label>
                                <input name="complemento" type="text" id="complemento" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-complemento"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Nit</label>
                                <input name="nit" type="text" id="nit" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-nit"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Razon Social</label>
                                <input name="razon_social" type="text" id="razon_social" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-razon_social"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Correo</label>
                                <input name="correo" type="text" id="correo" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-correo"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Celular</label>
                                <input name="numero_celular" type="text" id="numero_celular" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-numero_celular"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Direccion</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="direccion" type="text" id="direccion" maxlength="100" class="form-control" required>
                                <div class="text-danger error-message" id="error-direccion"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarCliente()">GUARDAR</button>
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
            LISTADO DE CLIENTES &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevoCliente()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVO CLIENTE</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_clientes"></div>
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
        ajaxListado();
    });

    function ajaxListado(){
        let datos = {};
            $.ajax({
                url: "{{ route('cliente.ajaxListado') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_clientes').html(resultado.data.listado)
                }else{

                }
            }
        })
    }

    function limpiarErorres() {
        $('.error-message').html('');
        $('.is-invalid').removeClass('is-invalid');
    }

    // // Funcion que muestra el modal de nuevo Perfil
    function nuevoCliente()
    {
        limpiarErorres();

        $('#id').val(0);
        $('#nombres').val('');
        $('#ap_paterno').val('');
        $('#ap_materno').val('');
        $('#cedula').val('');
        $('#complemento').val('');
        $('#nit').val('');
        $('#razon_social').val('');
        $('#correo').val('');
        $('#numero_celular').val('');
        $('#direccion').val('');
        $('#modalCliente').modal('show');
    }

    // // Validacion antes de guardar un perfil nuevo
    function guardarCliente()
    {
        let datos = $('#formularioCliente').serializeArray();
        $.ajax({
            url: "{{ route('cliente.guardarCliente') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    $('#modalCliente').modal('hide')
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Cliente creado correctamente.',
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

    function editarCliente(cli) {
        limpiarErorres();

        Object.keys(cli).forEach(key => {
            let input = $(`#${key}`);

            // Saltar los inputs de tipo file
            if (input.attr('type') === 'file') {
                return; // ignorar este campo
            }

            if (input.is(':checkbox')) {
                // Marcar si el valor es 1, true o "on"
                input.prop('checked', cli[key] == 1 || cli[key] === true || cli[key] === "on");
            } else if (input.is('select')) {
                // Para selects con librerías como Select2
                input.val(cli[key]).trigger('change');
            } else if (input.length) {
                // Para inputs normales (text, number, email, etc.)
                input.val(cli[key]);
            }
        });
        $('#modalCliente').modal('show');
    }


    function eliminarCliente(cliente){
        Swal.fire({
            title: "Quieres eliminar "+cliente.nombres,
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
                    url: "{{ route('cliente.eliminarCliente') }}",
                    method: "POST",
                    data: cliente,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Cliente eliminado correctamente.',
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
