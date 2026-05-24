<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">FORMULARIO DE CIERRE DE CAJA</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    </div>
    <div class="modal-body">
        <form id="formularioCerrarCaja">
            <input type="hidden" name="id" id="id">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">Usuario</label>
                        <span class="text-danger"><i class="mr-2 mdi mdi-alert-circle"></i></span>
                        <input type="text" class="form-control" id="nombre_cierre" name="nombre_cierre" value="{{ $usuario->name }}" readonly>
                        <input type="hidden" id="usuario_id_cierre" name="usuario_id_cierre" value="{{ $usuario->id }}">
                        <input type="hidden" id="caja_id_cierre" name="caja_id_cierre" value="{{ $cajaAbierta != null ?$cajaAbierta->id : 0  }}">
                        <div class="text-danger error-message" id="error-nombre_cierre"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Monto Efectivo</label>
                        <span class="text-danger"><i class="mr-2 mdi mdi-alert-circle"></i></span>
                        <input type="number" class="form-control" id="monto_cierre" name="monto_cierre" min="0" required>
                        <div class="text-danger error-message" id="error-monto_cierre"></div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="control-label">Descripcion Cerrar</label>
                        <span class="text-danger"><i class="mr-2 mdi mdi-alert-circle"></i></span>
                        <input type="text" class="form-control" id="descripcion_cierre" name="descripcion_cierre" required>
                        <div class="text-danger error-message" id="error-descripcion_cierre"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn waves-effect waves-light btn-block btn-success" id="boton_cerrar_caja" onclick="guardarCerrarCaja()">GUARDAR</button>
    </div>
</div>
