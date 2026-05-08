@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo perfil -->
<div id="modalGrupo" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO GRUPO CLIENTE</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioGrupo">
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarGrupo()">GUARDAR</button>
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
            LISTADO DE GRUPOS DE CLIENTES &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevoGrupo()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVO</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_grupos"></div>
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

    $(document).ready(function() {
        $('#modalGrupo').on('shown.bs.modal', function () {
            $('#cliente_id').select2({
                placeholder: 'Seleccione...',
                dropdownParent: $('#modalGrupo'),
                width: '100%'
            });
        });

        ajaxListado();
    });

    function ajaxListado(){
        let datos = { sucursal_id: sucursal.id, grupo_id: grupo.id };
            $.ajax({
                url: "{{ route('grupoCliente.ajaxListado') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_grupos').html(resultado.data.listado)
                }else{

                }
            }
        })
    }

    function limpiarErorres() {
        $('.error-message').html('');
        $('.is-invalid').removeClass('is-invalid');
    }

    function nuevoGrupo()
    {
        limpiarErorres();

        $('#id').val(0);
        $('#cliente_id').val(null).trigger('change');
        $("#modalGrupo").modal('show');
    }

    function guardarGrupo(){
        let datos = $('#formularioGrupo').serializeArray();
        datos.push({
            name: 'sucursal_id',
            value: sucursal.id
        });
        datos.push({
            name: 'grupo_id',
            value: grupo.id
        });

        $.ajax({
            url: "{{ route('grupoCliente.guardarGrupo') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    $('#modalGrupo').modal('hide')
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Grupo - Cliente creado correctamente.',
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

    function editarGrupo(grupo) {
        limpiarErorres();

        Object.keys(grupo).forEach(key => {
            let input = $(`#${key}`);

            // Saltar los inputs de tipo file
            if (input.attr('type') === 'file') {
                return; // ignorar este campo
            }

            if (input.is(':checkbox')) {
                //  si el valor es 1, true o "on"
                input.prop('checked', grupo[key] == 1 || grupo[key] === true || grupo[key] === "on");
            } else if (input.is('select')) {
                // Para selects con librerías como Select2
                input.val(grupo[key]).trigger('change');
            } else if (input.length) {
                // Para inputs normales (text, number, email, etc.)
                input.val(grupo[key]);
            }
        });
        $('#modalGrupo').modal('show');
    }

    function eliminarGrupo(grupo){
        Swal.fire({
            title: "Quieres eliminar el cliente con nombre "+grupo.nombre,
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
                    url: "{{ route('grupoCliente.eliminarGrupo') }}",
                    method: "POST",
                    data: grupo,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Grupo - Cliente eliminado correctamente.',
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
