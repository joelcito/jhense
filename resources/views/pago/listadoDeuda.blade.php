@extends('layouts.app')
@section('css')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .tamanio_boton{
            font-size: 6px;
        }
    </style>
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection
@section('content')

<div id="modalDeuda" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO DE CUENTA POR COBRAR <span class="text-info" id="nombre_busqueda"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="formulario_deuda">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn waves-effect waves-light btn-block btn-success" onclick="guardarDeuda()">GUARDAR</button>
            </div>
        </div>
    </div>
</div>

<div id="modalAperturaCaja" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        @include('caja.components.formularioAperturaCaja')
    </div>
</div>

@if ($cajaAbierta)
    <div class="card border-info">
        <div class="card-header bg-info">
            <h4 class="mb-0 text-white">
                CUENTAS POR COBRAR
            </h4>
        </div>
        <div class="card-body">
            <div id="table_listado"></div>
        </div>
    </div>
@else
    <div class="card border-danger">
        <div class="card-header bg-danger">
            <h4 class="mb-0 text-white">
                NO SE APERTURO LA CAJA &nbsp;&nbsp;
                <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="modalAperturaCaja()">
                    <i class="fas fa-plus"></i> &nbsp; ABRIR CAJA
                </button>
            </h4>
        </div>
    </div>
@endif


@stop()

@section('js')
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
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
                url: "{{ route('pago.ajaxListadoDeuda') }}",
                method: "POST",
                data: datos,
                success: function (resultado) {

                    if(resultado.estado){
                        $('#table_listado').html(resultado.data.listado)
                    }else{

                    }
                    // Ocultar SweetAlert2 cuando la solicitud sea exitosa
                    // Swal.close();
                }
            })
        }

        function limpiarErorres(){
            $('.error-message').html('');
            $('.is-invalid').removeClass('is-invalid');
        }

        //FORMULARIO DEUDAS
        function registrarPago(factura){
            $('#formulario_deuda').html('');
            limpiarErorres();

            datos = {factura_id: factura.id}
            $.ajax({
                url: "{{ route('pago.ajaxFormPagoDeuda') }}",
                method: "POST",
                data: datos,
                success: function (resultado) {
                    if(resultado.estado){
                        $('#formulario_deuda').html(resultado.data.formulario)
                        $('#modalDeuda').modal('show')
                    }
                }
            });
        }

        function guardarDeuda(){
            let datos = $('#formularioDeuda').serializeArray();
            $.ajax({
                url: "{{ route('pago.guardarPagoDeuda') }}",
                method: "POST",
                data: datos,
                success: function (resultado) {
                    if(resultado.estado){
                        Swal.fire({
                            title: "EL REGISTRO FUE EXITOSO.",
                            icon: "success",
                            timer: 2000, // Se cierra en 2 segundos
                            showConfirmButton: false
                        });
                        ajaxListado();
                        $('#modalDeuda').modal('hide')
                    }
                },
                error: function (xhr) {
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

        function modalAperturaCaja() {
            // $('#nombre').val('')
            $('#monto_apertura').val(0)
            $('#descripcion').val('')
            $('#modalAperturaCaja').modal('show')
        }

        function guardarAperturaCaja() {
            if($('#formularioAperturaCaja')[0].checkValidity()){
                $('#boton_abrir_caja').attr('disabled', true);
                let datos = $('#formularioAperturaCaja').serializeArray();
                $.ajax({
                    url: "{{ url('caja/guardarAperturaCaja') }}",
                    method: "POST",
                    data: datos,
                    success: function(resultado) {
                        if (resultado.estado) {
                            Swal.fire({
                                title: "EL REGISTRO FUE EXITOSO.",
                                icon: "success",
                                timer: 3000, // Se cierra en 3 segundos
                                showConfirmButton: false
                            });

                            location.reload();
                        } else {

                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error inesperado.'+xhr,
                        });
                    }
                });
            }else{
                $('#formularioAperturaCaja')[0].reportValidity()
            }
        }

   </script>
@endsection
