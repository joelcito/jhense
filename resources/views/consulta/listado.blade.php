@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo perfil -->
<div id="modalConsulta" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO CONSULTA</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioConsulta">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Componente</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="componente" type="text" id="componente" class="form-control" required>
                                <div class="text-danger error-message" id="error-componente"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Sintoma</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="sintoma" type="text" id="sintoma" class="form-control" required>
                                <div class="text-danger error-message" id="error-sintoma"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Tipo de Falla</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="tipo_falla" type="text" id="tipo_falla" class="form-control" required>
                                <div class="text-danger error-message" id="error-tipo_falla"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Causa Probable</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="causa_probable" type="text" id="causa_probable" class="form-control" required>
                                <div class="text-danger error-message" id="error-causa_probable"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Estado Actual</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="estado_actual" type="text" id="estado_actual" class="form-control" required>
                                <div class="text-danger error-message" id="error-estado_actual"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Recomendación</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="recomendacion" type="text" id="recomendacion" class="form-control" required>
                                <div class="text-danger error-message" id="error-recomendacion"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Riesgo Asociado</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="riesgo_asociado" type="text" id="riesgo_asociado" class="form-control" required>
                                <div class="text-danger error-message" id="error-riesgo_asociado"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Justificación</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="justificacion" type="text" id="justificacion" class="form-control" required>
                                <div class="text-danger error-message" id="error-justificacion"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarConsulta()">GUARDAR</button>
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
            LISTADO DE CONSULTAS &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevaConsulta()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVA CONSULTA</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_consultas"></div>
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
                url: "{{ route('consulta.ajaxListado') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_consultas').html(resultado.data.listado)
                }else{

                }
            }
        })
    }

    function limpiarErorres() {
        $('.error-message').html('');
        $('.is-invalid').removeClass('is-invalid');
    }

    function nuevaConsulta()
    {
        limpiarErorres();

        $('#id').val(0);
        $('#componente').val('');
        $('#sintoma').val('');
        $('#tipo_falla').val('');
        $('#causa_probable').val('');
        $('#estado_actual').val('');
        $('#recomendacion').val('');
        $('#riesgo_asociado').val('');
        $('#justificacion').val('');
        $('#modalConsulta').modal('show');
    }

    function guardarConsulta(){
        let datos = $('#formularioConsulta').serializeArray();
        $.ajax({
            url: "{{ route('consulta.guardarConsulta') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    $('#modalConsulta').modal('hide')
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Consulta creada correctamente.',
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

    function editarConsulta(consulta) {
        limpiarErorres();

        Object.keys(consulta).forEach(key => {
            let input = $(`#${key}`);

            // Saltar los inputs de tipo file
            if (input.attr('type') === 'file') {
                return; // ignorar este campo
            }

            if (input.is(':checkbox')) {
                //  si el valor es 1, true o "on"
                input.prop('checked', consulta[key] == 1 || consulta[key] === true || consulta[key] === "on");
            } else if (input.is('select')) {
                // Para selects con librerías como Select2
                input.val(consulta[key]).trigger('change');
            } else if (input.length) {
                // Para inputs normales (text, number, email, etc.)
                input.val(consulta[key]);
            }
        });
        $('#modalConsulta').modal('show');
    }

    function eliminarConsulta(consulta){
        Swal.fire({
            title: "Quieres eliminar la consulta "+consulta.componente,
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
                    url: "{{ route('consulta.eliminarConsulta') }}",
                    method: "POST",
                    data: consulta,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Consulta eliminada correctamente.',
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
