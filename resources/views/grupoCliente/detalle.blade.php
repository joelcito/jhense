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
            </div>
        </div>
        <div id="tabla_detalles"></div>
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
