<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">FORMULARIO DE APERTURA DE CAJA</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    </div>
    <div class="modal-body">
        <form id="formularioAperturaCaja">
            <input type="hidden" name="id" id="id">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">Usuario</label>
                        <span class="text-danger"><i class="mr-2 mdi mdi-alert-circle"></i></span>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $usuario->name }}" readonly>
                        <input type="hidden" id="usuario_id" name="usuario_id" value="{{ $usuario->id }}" required>
                        <div class="text-danger error-message" id="error-nombre"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Monto Apertura</label>
                        <span class="text-danger"><i class="mr-2 mdi mdi-alert-circle"></i></span>
                        <input type="number" class="form-control" id="monto_apertura" name="monto_apertura" min="1" required>
                        <div class="text-danger error-message" id="error-monto_apertura"></div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="control-label">Descripcion Apertura</label>
                        <span class="text-danger"><i class="mr-2 mdi mdi-alert-circle"></i></span>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                        <div class="text-danger error-message" id="error-descripcion"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn waves-effect waves-light btn-block btn-success" id="boton_abrir_caja" onclick="guardarAperturaCaja()">GUARDAR</button>
    </div>
</div>
