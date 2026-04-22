@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo perfil -->
<div id="modalSucursal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO SUCURSAL</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioSucursal">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Nombre</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="nombre" type="text" id="nombre" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-nombre"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Codigo</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="codigo_sucursal" type="text" id="codigo_sucursal" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-codigo_sucursal"></div>
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
                        onclick="guardar()">GUARDAR</button>
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
            LISTADO DE SUCURSALES &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevoSucursal()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVA SUCURSAL</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_sucursales"></div>
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
                url: "{{ route('sucursal.ajaxListado') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_sucursales').html(resultado.data.listado)
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
    function nuevoSucursal()
    {
        limpiarErorres();

        $('#id').val(0);
        $('#nombre').val("");
        $('#codigo_sucursal').val("");
        $('#direccion').val("");
        $("#modalSucursal").modal('show');
    }

    // // Validacion antes de guardar un perfil nuevo
    function guardar()
    {
        let datos = $('#formularioSucursal').serializeArray();
        $.ajax({
            url: "{{ route('sucursal.guardarSucursal') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    $('#modalSucursal').modal('hide')
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Sucursal creado correctamente.',
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

    function editarSucursal(sucursal){
        limpiarErorres();

        $('#id').val(sucursal.id)
        $('#nombre').val(sucursal.nombre)
        $('#codigo_sucursal').val(sucursal.codigo_sucursal)
        $('#direccion').val(sucursal.direccion)

        $('#modalSucursal').modal('show')
    }

    function eliminarSucursal(sucursal){
        Swal.fire({
            title: "Quieres eliminar "+sucursal.nombre,
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
                    url: "{{ route('sucursal.eliminarSucursal') }}",
                    method: "POST",
                    data: sucursal,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Sucursal eliminado correctamente.',
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
