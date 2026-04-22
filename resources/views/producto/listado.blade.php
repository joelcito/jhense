@extends('layouts.app')

@section('css')

<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endsection
@section('metadatos')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<!-- inicio modal nuevo perfil -->
<div id="modalProducto" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">FORMULARIO PRODUCTO</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="formularioProducto">
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
                                <input name="nombre" type="text" id="nombre" class="form-control" required>
                                <div class="text-danger error-message" id="error-nombre"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Codigo</label>
                                <input name="codigo" type="text" id="codigo" class="form-control" required>
                                <div class="text-danger error-message" id="error-codigo"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Precio Compra</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="precio_compra" type="number" id="precio_compra" class="form-control" required>
                                <div class="text-danger error-message" id="error-precio_compra"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Precio Venta</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="precio_venta" type="number" id="precio_venta" class="form-control" required>
                                <div class="text-danger error-message" id="error-precio_venta"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Minimo Stock</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="minimo_stock" type="number" id="minimo_stock" class="form-control" required>
                                <div class="text-danger error-message" id="error-minimo_stock"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarProducto()">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- fin modal nuevo perfil -->
<div id="modalStockSucursal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">CANTIDAD STOCK POR SUCUSAL DEL PRODUCTO: <span class="text-info" id="nombre_producto"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div id="tabla_stock"></div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<div id="modalStockSucursalProducto" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">INGRESO DE STOCK: <span class="text-info" id="nombre_producto_stock"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form id="formularioStockSucursal">
                    <input type="hidden" name="producto_id" id="producto_id">
                    <input type="hidden" name="sucursal_id" id="sucursal_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Sucursal</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="nombre_sucursal" type="text" id="nombre_sucursal" class="form-control" required @readonly(true)>
                                <div class="text-danger error-message" id="error-nombre_sucursal"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Fecha de Registro</label>
                                <input name="fecha" type="date" id="fecha" class="form-control" required value="{{ date('Y-m-d') }}" @readonly(true)>
                                <div class="text-danger error-message" id="error-fecha"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Ingreso la cantidad</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="cantidad_ingreso" type="number" id="cantidad_ingreso" class="form-control" required>
                                <div class="text-danger error-message" id="error-cantidad_ingreso"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Precio Compra</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="f_precio_compra" type="number" id="f_precio_compra" class="form-control" required>
                                <div class="text-danger error-message" id="error-f_precio_compra"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Precio Venta</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="f_precio_venta" type="number" id="f_precio_venta" class="form-control" required>
                                <div class="text-danger error-message" id="error-f_precio_venta"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Descripcion</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <textarea class="form-control" name="descripcion" id="descripcion" cols="30" rows="3"></textarea>
                                <div class="text-danger error-message" id="error-descripcion"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarStockSucursal()">GUARDAR</button>
            </div>
        </div>
    </div>
</div>

<div id="modalSalidaSucursalProducto" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">SALIDA DE STOCK: <span class="text-info" id="nombre_producto_salida"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form id="formularioSalidaSucursal">
                    <input type="hidden" name="salida_producto_id" id="salida_producto_id">
                    <input type="hidden" name="salida_sucursal_id" id="salida_sucursal_id">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Sucursal</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="salida_nombre_sucursal" type="text" id="salida_nombre_sucursal" class="form-control" required @readonly(true)>
                                <div class="text-danger error-message" id="error-salida_nombre_sucursal"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Fecha de Registro</label>
                                <input name="salida_fecha" type="date" id="salida_fecha" class="form-control" required value="{{ date('Y-m-d') }}" @readonly(true)>
                                <div class="text-danger error-message" id="error-salida_fecha"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Salida de la cantidad</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <input name="cantidad_salida" type="number" id="cantidad_salida" class="form-control" required>
                                <div class="text-danger error-message" id="error-cantidad_salida"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Descripcion</label>
                                <span class="text-danger">
                                    <i class="mr-2 mdi mdi-alert-circle"></i>
                                </span>
                                <textarea class="form-control" name="salida_descripcion" id="salida_descripcion" cols="30" rows="3"></textarea>
                                <div class="text-danger error-message" id="error-salida_descripcion"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarSalidaSucursal()">GUARDAR</button>
            </div>
        </div>
    </div>
</div>

<div id="modalTransferenciaSucursal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">TRANSFERENCIA DE PRODUCTO: <span class="text-info" id="nombre_producto_transferencia"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="formulario_transferencia">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn waves-effect waves-light btn-block btn-success"
                        onclick="guardarTransferenciaSucursal()">GUARDAR</button>
            </div>
        </div>
    </div>
</div>

<!-- fin modal editar perfil -->
<div class="card border-info">
    <div class="card-header bg-info">
        <h4 class="mb-0 text-white">
            LISTADO DE PRODUCTOS &nbsp;&nbsp;
            <button type="button" class="btn waves-effect waves-light btn-sm btn-success" onclick="nuevoProducto()"><i
                    class="fas fa-plus"></i> &nbsp; NUEVO PRODUCTO</button>
        </h4>
    </div>
    <div class="card-body">
        <div id="tabla_productos"></div>
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
                url: "{{ route('producto.ajaxListado') }}",
                method: "POST",
                data: datos,
            success: function (resultado) {
                if(resultado.estado){
                    $('#tabla_productos').html(resultado.data.listado)
                }else{

                }
            }
        })
    }

    function limpiarErorres() {
        $('.error-message').html('');
        $('.is-invalid').removeClass('is-invalid');
    }

    function nuevoProducto()
    {
        limpiarErorres();

        $('#id').val(0);
        $('#nombre').val('');
        $('#codigo').val('');
        $('#precio_compra').val('');
        $('#precio_venta').val('');
        $('#minimo_stock').val('');
        $('#modalProducto').modal('show');
    }

    function guardarProducto(){
        let datos = $('#formularioProducto').serializeArray();
        $.ajax({
            url: "{{ route('producto.guardarProducto') }}",
            data: datos,
            type: 'POST',
            success: function(data) {
                if(data.estado){
                    $('#modalProducto').modal('hide')
                    ajaxListado();
                    Swal.fire(
                        'Excelente!',
                        'Producto creado correctamente.',
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
                    Swal.fire(
                        'Error!',
                        'Ocurrió un error inesperado.',
                        'error'
                    )
                }
            }
        });
    }

    function editarProducto(prod) {
        limpiarErorres();

        Object.keys(prod).forEach(key => {
            let input = $(`#${key}`);

            // Saltar los inputs de tipo file
            if (input.attr('type') === 'file') {
                return; // ignorar este campo
            }

            if (input.is(':checkbox')) {
                //  si el valor es 1, true o "on"
                input.prop('checked', prod[key] == 1 || prod[key] === true || prod[key] === "on");
            } else if (input.is('select')) {
                // Para selects con librerías como Select2
                input.val(prod[key]).trigger('change');
            } else if (input.length) {
                // Para inputs normales (text, number, email, etc.)
                input.val(prod[key]);
            }
        });
        $('#modalProducto').modal('show');
    }

    function eliminarProducto(prod){
        Swal.fire({
            title: "Quieres eliminar el producto "+prod.nombre,
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
                    url: "{{ route('producto.eliminarProducto') }}",
                    method: "POST",
                    data: prod,
                    success: function (resultado) {
                        if(resultado.estado){
                            Swal.fire(
                                'Excelente!',
                                'Producto eliminado correctamente.',
                                'success'
                            );
                            ajaxListado();
                        }
                    },
                    error: function (xhr) {
                        Swal.fire(
                            'Error!',
                            'Ocurrió un error inesperado.',
                            'error'
                        );
                    }
                });
            } else if (result.dismiss === "cancel") {
                Swal.fire(
                    "Cancelado",
                    "La operacion fue cancelada",
                    "error"
                );
            }
        });

    }

    //ADICIONAR STOCK
    function adicionarStockSucursal(producto) {

        $('#nombre_producto').html('')
        $('#tabla_stock').html('');

        $('#nombre_producto_salida').html('')
        $.ajax({
            url: "{{ route('producto.ajaxStockSucursal') }}",
            method: "POST",
            data: {
                producto_id: producto.id
            },
            success: function(resultado) {

                if (resultado.estado) {
                    $('#nombre_producto').html(producto.nombre);
                    $('#tabla_stock').html(resultado.data.listado);

                    $('#nombre_producto_salida').html(producto.nombre);

                    $('#modalStockSucursal').modal('show');
                } else {
                    Swal.fire(
                        'Error!',
                        'Ocurrió un error inesperado.',
                        'error'
                    );
                }
            },
            error: function(xhr) {
                Swal.fire(
                    'Error!',
                    'Ocurrió un error inesperado.',
                    'error'
                );
            }
        })
    }

    function adicionarStockSucursalProducto(sucursal, producto) {
        $('#nombre_producto_stock').html('');
        limpiarErorres();

        $('#nombre_producto_stock').html(producto.nombre);
        $('#nombre_sucursal').val(sucursal.nombre);
        $('#producto_id').val(producto.id);
        $('#sucursal_id').val(sucursal.id);
        $('#cantidad_ingreso').val('');
        $('#f_precio_compra').val('');
        $('#f_precio_venta').val('');
        $('#descripcion').val('');
        $('#modalStockSucursalProducto').modal('show');

    }

    function guardarStockSucursal() {
        let datos = $('#formularioStockSucursal').serializeArray();
        $.ajax({
            url: "{{ route('producto.guardarStockSucursal') }}",
            method: "POST",
            data: datos,
            success: function(resultado) {
                if (resultado.estado) {
                    Swal.fire(
                        'Excelente!',
                        'EL REGISTRO FUE EXITOSO.',
                        'success'
                    );
                    adicionarStockSucursal(resultado.data);
                    //ajaxListado();//nuevo
                    $('#modalStockSucursalProducto').modal('hide');
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
                    Swal.fire(
                        'Error!',
                        'Ocurrió un error inesperado.',
                        'error'
                    );
                }
            }
        });
    }

    //ADICIONAR SALIDA
    function adicionarSalidaSucursalProducto(sucursal, producto) {
        $('#nombre_producto_salida').html('')
        limpiarErorres();

        $('#nombre_producto_salida').html(producto.nombre)
        $('#salida_nombre_sucursal').val(sucursal.nombre)
        $('#salida_producto_id').val(producto.id)
        $('#salida_sucursal_id').val(sucursal.id)
        $('#salida_stock').val('')
        // $('#fecha').val('')
        $('#salida_descripcion').val('')
        $('#modalSalidaSucursalProducto').modal('show')

    }

    function guardarSalidaSucursal() {
        let datos = $('#formularioSalidaSucursal').serializeArray();
        $.ajax({
            url: "{{ route('producto.guardarSalidaSucursal') }}",
            method: "POST",
            data: datos,
            success: function(resultado) {
                if (resultado.estado) {
                    Swal.fire(
                        'Excelente!',
                        'EL REGISTRO FUE EXITOSO',
                        'success'
                    );
                    adicionarStockSucursal(resultado.data);
                    //ajaxListado();//nuevo
                    $('#modalSalidaSucursalProducto').modal('hide');
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
                    Swal.fire(
                        'Error!',
                        'Ocurrió un error inesperado.',
                        'error'
                    );
                }
            }
        });
    }

    //TRANFERENCIA SUCURSALES
    function transferenciaSucursal(producto) {
        $('#nombre_producto_transferencia').html('')
        $('#mensaje-salida').html('')
        limpiarErorres();

        $('#nombre_producto_transferencia').html(producto.nombre)
        datos = {
            producto_id: producto.id
        }
        $.ajax({
            url: "{{ route('producto.ajaxFormTransferencia') }}",
            method: "POST",
            data: datos,
            success: function(resultado) {
                if (resultado.estado) {
                    $('#formulario_transferencia').html(resultado.data.formulario)
                    // Re-inicializamos Select2 en los nuevos selects
                    $('#sucursal1_id, #sucursal2_id').select2({
                        placeholder: 'Seleccione...',
                        dropdownParent: $('#modalTransferenciaSucursal'),
                        width: '100%'                        
                    });

                    // Configuramos el evento después de insertar el HTML
                    configurarEventoSucursal();

                    $('#modalTransferenciaSucursal').modal('show')
                }
            }
        });
    }
    
    function configurarEventoSucursal() {
        const selectSucursal1 = $('#sucursal1_id');
        const selectSucursal2 = $('#sucursal2_id');

        // Guardamos las opciones originales por única vez
        const opcionesSucursal2 = selectSucursal2.html();

        // Evento cuando se selecciona una sucursal de salida
        selectSucursal1.off('select2:select').on('select2:select', function (e) {
            const sucursalSeleccionada = e.params.data.id;

            // Restauramos las opciones originales
            selectSucursal2.html(opcionesSucursal2);

            // Removemos la opción seleccionada en sucursal1 del select sucursal2
            selectSucursal2.find('option[value="' + sucursalSeleccionada + '"]').remove();

            // Refrescamos Select2
            selectSucursal2.val(null).trigger('change');
        });
    }

    function guardarTransferenciaSucursal() {
        let datos = $('#formularioTransferenciaSucursal').serializeArray();
        $.ajax({
            url: "{{ route('producto.guardarTransferenciaSucursal') }}",
            method: "POST",
            data: datos,
            success: function(resultado) {
                if (resultado.estado) {
                    Swal.fire(
                        'Excelente!',
                        'EL REGISTRO FUE EXITOSO.',
                        'success'
                    );
                    ajaxListado();
                    $('#modalTransferenciaSucursal').modal('hide')
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
                    Swal.fire(
                        'Error!',
                        'Ocurrió un error inesperado.',
                        'error'
                    );
                }
            }
        });
    }

    //REPORTES
    function obtenerReporte(producto) {
        limpiarErorres();

        $('#ingreso_producto_id').val(producto.id)
        $('#ingreso_fecha_ini').val('')
        $('#ingreso_fecha_fin').val('')
        $('#ingreso_tipo').val(null).trigger('change')
        $('#salida_producto_id').val(producto.id)
        $('#salida_fecha_ini').val('')
        $('#salida_fecha_fin').val('')
        $('#salida_tipo').val(null).trigger('change')
        $('#modalReporte').modal('show')
    }

</script>
@endsection
