@extends('layouts.app')
@section('css')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .tamanio_boton {
            font-size: 6px;
        }
    </style>
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection
@section('content')

    <div id="modalIngreso" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">FORMULARIO DE <span id="text_tipoo_modal" class="text-info"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form id="formularioIngresoSalida">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Monto</label>
                                    <span class="text-danger"><i class="mr-2 mdi mdi-alert-circle"></i></span>
                                    <input type="number" id="monto" name="monto" class="form-control" min="0.1" step="0.01" value="0" required>
                                    <input type="hidden" id="tipo" name="tipo" required>
                                    <input type="hidden" value="{{ $cajaAbierta != null ? $cajaAbierta->id : 0 }}" id="caja_abierto_ingre_cerra" name="caja_abierto_ingre_cerra" required>
                                    <div class="text-danger error-message" id="error-monto"></div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="control-label">Descripcion</label>
                                    <span class="text-danger"><i class="mr-2 mdi mdi-alert-circle"></i></span>
                                    <input type="text" id="descripcion" name="descripcion" class="form-control" required>
                                    <div class="text-danger error-message" id="error-descripcion"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success" onclick="guardarTipoIngresoSalida()">GUARDAR</button>
                </div>
            </div>
        </div>
    </div>


    <div class="card border-info">
        <div class="card-header bg-info">
            <h4 class="mb-0 text-white">
                LISTADO DE PAGOS &nbsp;&nbsp;
                @if ($cajaAbierta)
                    <button type="button" class="btn waves-effect waves-light btn-sm btn-success ml-2" onclick="modalIngresoSalida('INGRESO')">
                        <i class="fas fa-plus"></i> &nbsp; NUEVO INGRESO
                    </button>
                    <button type="button" class="btn waves-effect waves-light btn-sm btn-danger ml-2" onclick="modalIngresoSalida('SALIDA')">
                        <i class="fas fa-minus"></i> &nbsp; NUEVA SALIDA
                    </button>
                @endif
            </h4>
        </div>
        <div class="card-body">
            <form id="formulario_busqueda">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Sucursal</label>
                            <select class="form-control" name="sucursal_id" id="sucursal_id" style="width: 100%;">
                                <option value="">SELECCIONE</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label">Fecha Ini</label>
                            <input type="date" class="form-control" id="fecha_ini" name="fecha_ini" value="{{ $fechaIni }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Usuario</label>
                            <select class="form-control" name="usuario_id" id="usuario_id" style="width: 100%;">
                                <option value="">SELECCIONE</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2" style="display: flex; align-items: center; padding-top: 15px;">
                        <button onclick="ajaxListado()" type="button" class="btn w-100 btn-success"><i class="fa fa-search"></i> BUSCAR</button>
                    </div>
                </div>
            </form>
            <div id="table_listado"></div>
        </div>
    </div>

@stop()

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
            $('#sucursal_id, #usuario_id').select2({
                placeholder: 'Seleccione...',
                width: '100%'
            });

            ajaxListado();
        });

        function ajaxListado() {
            // Mostrar SweetAlert2 antes de enviar la solicitud
            // Swal.fire({
            //     title: 'Generando Listado...',
            //     text: 'Por favor espera mientras generamos el listado.',
            //     allowOutsideClick: false, // Evitar que se cierre al hacer clic fuera
            //     didOpen: () => {
            //         Swal.showLoading(); // Mostrar el spinner de carga
            //     }
            // });

            let datos = $('#formulario_busqueda').serializeArray();
            $.ajax({
                url: "{{ url('pago/ajaxListado') }}",
                method: "POST",
                data: datos,
                success: function(resultado) {
                    if (resultado.estado) {
                        $('#table_listado').html(resultado.data.listado)
                    } else {

                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error inesperado.',
                    });
                }
            })
        }

        function limpiarErorres() {
            $(".invalid-feedback").remove();
            $(".is-invalid").removeClass("is-invalid");
        }

        function modalIngresoSalida(tipo) {
            $('#tipo').val(tipo)
            $('#text_tipoo_modal').text(tipo)
            $('#modalIngreso').modal('show')
            $('#monto').val(0)
            $('#descripcion').val('')
        }

        function guardarTipoIngresoSalida() {
            if ($("#formularioIngresoSalida")[0].checkValidity()) {
                datos = $("#formularioIngresoSalida").serializeArray()
                $.ajax({
                    url: "{{ url('pago/guardarTipoIngresoSalida') }}",
                    data: datos,
                    type: 'POST',
                    dataType: 'json',
                    success: function(data) {
                        if (data.estado) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Se registro con exito',
                                showConfirmButton: false, // No mostrar botón de confirmación
                                timer: 2000, // 5 segundos
                                timerProgressBar: true
                            });
                            $('#modalIngreso').modal('hide')
                            ajaxListado();
                        }
                    }
                });
            } else {
                $("#formularioIngresoSalida")[0].reportValidity()
            }
        }
    </script>
@endsection
