@extends('layouts.app')

@section('css')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo proveedor -->
<div id="modalProveedor" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO DE PROVEEDOR</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioProveedor">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Nombre</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input type="text" class="form-control" id="nombre" name="nombre">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Razon Social</label>
                                <input type="text" class="form-control" id="razon_social" name="razon_social">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Nit</label>
                                <input type="number" class="form-control" id="nit" name="nit">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Celular</label>
                                <input type="number" class="form-control" id="celular" name="celular">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="control-label">Direccion</label>
                                <input type="text" class="form-control" id="direccion" name="direccion">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarProveedor()">GUARDAR PROVEEDOR</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- fin modal nuevo proveedor -->

<div class="card border-info">
    <div class="card-header bg-info">
        <h4 class="mb-0 text-white">
            LISTADO DE PROVEEDORES &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="modalNuevoProveedor()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVO PROVEEDOR</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="table_listado"></div>
    </div>
</div>
@stop

@section('js')
<script src="{{ asset('assets/libs/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('dist/js/pages/datatable/custom-datatable.js') }}"></script>
<script>
    $.ajaxSetup({
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
            url: "{{ route('proveedor.ajaxListado') }}",
            method: "POST",
            data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#table_listado').html(resultado.data.listado)
                }
            }
        })
    }

    function limpiarErorres(){
        $(".invalid-feedback").remove();
        $(".is-invalid").removeClass("is-invalid");
    }

    function modalNuevoProveedor(){
        limpiarErorres();

        $('#id').val(0);
        $('#nombre').val('');
        $('#razon_social').val('');
        $('#nit').val('');
        $('#celular').val('');
        $('#direccion').val('');
        $('#modalProveedor').modal('show');
    }

    function guardarProveedor(){
        let datos = $('#formularioProveedor').serializeArray();
        $.ajax({
            url: "{{ route('proveedor.guardarProveedor') }}",
            method: "POST",
            data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    Swal.fire({
                        title: "Excelente!",
                        text: "Proveedor guardado exitosamente.",
                        type: "success",
                        timer: 2000,
                        showConfirmButton: false
                    });
                    ajaxListado();
                    $('#modalProveedor').modal('hide')
                }
            },
            error: function (xhr) {
                limpiarErorres();

                if (xhr.status === 422) {
                    let errores = xhr.responseJSON.errors;

                    for (let campo in errores) {
                        let mensaje = errores[campo][0];

                        let input = $(`[name="${campo}"]`);
                        input.addClass("is-invalid");
                        input.after(`<div class="invalid-feedback text-danger">${mensaje}</div>`);
                    }
                } else {
                    Swal.fire({
                        type: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error inesperado.',
                    });
                }
            }
        });
    }

    function editarProveedor(proveedor){
        limpiarErorres();

        Object.keys(proveedor).forEach(key => {
            let input = $(`#${key}`);
            if (input.length) {
                input.val(proveedor[key]);
            }
        });
        $('#modalProveedor').modal('show')
    }

    function eliminar(proveedor){
        Swal.fire({
            title: "Quieres eliminar "+proveedor.nombre,
            text: "Ya no podras recuperarlo!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, estoy seguro!',
            cancelButtonText: "Cancelar",
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "{{ route('proveedor.eliminarProveedor') }}",
                    method: "POST",
                    data: proveedor,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Proveedor eliminado correctamente.',
                                'success'
                            )
                            ajaxListado();
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            type: 'error',
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
