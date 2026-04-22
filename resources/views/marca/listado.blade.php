@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo perfil -->
<div id="modalMarca" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO MARCA</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioMarca">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Nombre</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="nombre" type="text" id="nombre" maxlength="30" class="form-control" required>
                                <div class="text-danger error-message" id="error-nombre"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarMarca()">GUARDAR</button>
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
            LISTADO DE MARCAS &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevoMarca()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVA MARCA</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_marcas"></div>
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
                url: "{{ route('marca.ajaxListado') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_marcas').html(resultado.data.listado)
                }else{

                }
            }
        })
    }

    function limpiarErorres() {
        $('.error-message').html('');
        $('.is-invalid').removeClass('is-invalid');
    }

    function nuevoMarca()
    {
        limpiarErorres();

        $('#id').val(0);
        $('#nombre').val("");
        $("#modalMarca").modal('show');
    }

    function guardarMarca(){
        let datos = $('#formularioMarca').serializeArray();
        $.ajax({
            url: "{{ route('marca.guardarMarca') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    $('#modalMarca').modal('hide')
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Marca creado correctamente.',
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

    function editarMarca(marca) {
        limpiarErorres();

        Object.keys(marca).forEach(key => {
            let input = $(`#${key}`);

            // Saltar los inputs de tipo file
            if (input.attr('type') === 'file') {
                return; // ignorar este campo
            }

            if (input.is(':checkbox')) {
                // Marcar si el valor es 1, true o "on"
                input.prop('checked', marca[key] == 1 || marca[key] === true || marca[key] === "on");
            } else if (input.is('select')) {
                // Para selects con librerías como Select2
                input.val(marca[key]).trigger('change');
            } else if (input.length) {
                // Para inputs normales (text, number, email, etc.)
                input.val(marca[key]);
            }
        });
        $('#modalMarca').modal('show');
    }

    function eliminarMarca(marca){
        Swal.fire({
            title: "Quieres eliminar "+marca.nombre,
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
                    url: "{{ route('marca.eliminarMarca') }}",
                    method: "POST",
                    data: marca,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Marca eliminado correctamente.',
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
