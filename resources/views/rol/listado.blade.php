@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')


<!-- inicio modal nuevo perfil -->
<div id="nuevo_rol" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO ROL</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioRol">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Nombre</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="nombre_perfil" type="text" id="nombre_perfil" maxlength="30"
                                    class="form-control" required>
                                    <input type="hidden" name="rol_id" id="rol_id">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Descripcion</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="descripcion_perfil" type="text" id="descripcion_perfil" maxlength="100"
                                    class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardar()">GUARDAR ROL</button>
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
            LISTADO DE ROL &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevo_rol()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVO ROL</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_roles"></div>
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
                url: "{{ route('rol.ajaxListado') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_roles').html(resultado.data.listado)
                }else{

                }
            }
        })
    }

    // // Funcion que muestra el modal de nuevo Perfil
    function nuevo_rol()
    {
        $('#rol_id').val(0)
        $('#nombre_perfil').val("")
        $('#descripcion_perfil').val("")
        $("#nuevo_rol").modal('show');
    }

    // // Validacion antes de guardar un perfil nuevo
    function guardar()
    {
        // $("#id").val(id);
        // $("#nombre").val(nombre);
        // $("#descripcion").val(descripcion);

        let datos = $('#formularioRol').serializeArray();
        // Ajax Menus
        $.ajax({
            url: "{{ route('rol.guardar') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    $('#nuevo_rol').modal('hide')
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Rol creado correctamente.',
                        'success'
                    )
                }else{

                }
                // $("#ajaxListadoMenu").show('slow');
                // $("#ajaxListadoMenu").html(data);
            }
        });
        $("#editar_perfiles").modal('show');
    }

    function editarRol(rol){

        $('#rol_id').val(rol.id)
        $('#nombre_perfil').val(rol.nombre)
        $('#descripcion_perfil').val(rol.descripcion)

        $('#nuevo_rol').modal('show')
    }

    function eliminarRol(rol){
        Swal.fire({
            title: "Quieres eliminar "+rol.nombre,
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
                    url: "{{ route('rol.eliminarRol') }}",
                    method: "POST",
                    data: rol,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Rol eliminado correctamente.',
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
