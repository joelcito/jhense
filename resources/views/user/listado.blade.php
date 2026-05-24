@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo perfil -->
<div id="modalUsuario" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO USUARIO</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioUsuario">
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
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
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
                                <label class="control-label">Celular</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="celular" type="text" id="celular" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-celular"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Rol</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <select name="rol_id" id="rol_id" class="form-control" required>
                                        @foreach ($roles as $rol)
                                            <option value={{ $rol->id }}>{{ $rol->nombre }}</option>
                                        @endforeach
                                    </select>
                                <div class="text-danger error-message" id="error-rol_id"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">E-mail (USUARIO DE INGRESO)</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="email" type="text" id="email" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-email"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Sucursal</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <select name="sucursal_id" id="sucursal_id" class="form-control"
                                    required>
                                    <option value="">Seleccione</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value={{ $sucursal->id }}>{{ $sucursal->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-message" id="error-sucursal_id"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
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
                        onclick="guardarUsuario()">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- fin modal nuevo perfil -->

<div id="modalResetPassword" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Restablecer Contraseña</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formResetPassword">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="usuario_id_reset" name="usuario_id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Nueva Contraseña</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="password" type="password" id="password" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-password"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Confirmar Contraseña</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="password_confirmation" type="password" id="password_confirmation" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-password_confirmation"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="resetPassword()">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card border-info">
    <div class="card-header bg-info">
        <h4 class="mb-0 text-white">
            LISTADO DE USUARIOS &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevoUsuario()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVO USUARIO</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_usuarios"></div>
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
                url: "{{ route('usuario.ajaxListado') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_usuarios').html(resultado.data.listado)
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
    function nuevoUsuario()
    {
        limpiarErorres();

        $('#id').val(0);
        $('#nombres').val('');
        $('#ap_paterno').val('');
        $('#ap_materno').val('');
        $('#cedula').val('');
        $('#direccion').val('');
        $('#email').val('');
        $('#celular').val('');
        $('#rol_id').val('');
        $('#sucursal_id').val('');
        $('#select_puntos_ventas').html('');
        $('#modalUsuario').modal('show');
    }

    function guardarUsuario()
    {
        let datos = $('#formularioUsuario').serializeArray();
        $.ajax({
            url: "{{ route('usuario.guardarUsuario') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    $('#modalUsuario').modal('hide')
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Usuario creado correctamente.',
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

    function editarUsuario(usuario) {
        limpiarErorres();

        Object.keys(usuario).forEach(key => {
            let input = $(`#${key}`);
            if (input.length) {
                input.val(usuario[key]);
            }
        });

        if(usuario.punto_venta){
            $("#sucursal_id").val(usuario.punto_venta.sucursal_id);
        }

        $('#modalUsuario').modal('show');
    }

    function eliminarUsuario(usuario){
        Swal.fire({
            title: "Quieres eliminar "+usuario.name,
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
                    url: "{{ route('usuario.eliminarUsuario') }}",
                    method: "POST",
                    data: usuario,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Usuario eliminado correctamente.',
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

    function abrirModalResetPassword(usuarioId) {
        $('#usuario_id_reset').val(usuarioId);
        $('#password').val('');
        $('#password_confirmation').val('');
        $('#modalResetPassword').modal('show');
    }

    function resetPassword() {
        let usuarioId = $('#usuario_id_reset').val();
        let password = $('#password').val();
        let passwordConfirmation = $('#password_confirmation').val();

        if (password !== passwordConfirmation) {
            Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
            return;
        }

        $.ajax({
            url: "{{ route('usuario.resetPassword') }}",
            type: "POST",
            data: {
                usuario_id: usuarioId,
                password: password,
                password_confirmation: passwordConfirmation,
            },
            success: function(response) {
                Swal.fire({
                    title: "Contraseña Actualizada con Exito!",
                    icon: "success",
                    timer: 2000, // Se cierra en 2 segundos
                    showConfirmButton: false
                });
                $('#modalResetPassword').modal('hide');
            },
            error: function(xhr) {
                Swal.fire('Error', 'Hubo un problema al actualizar la contraseña', 'error');
            }
        });
    }

</script>
@endsection
