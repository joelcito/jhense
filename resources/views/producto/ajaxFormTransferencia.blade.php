<form id="formularioTransferenciaSucursal">
    <input type="hidden" name="producto_transferencia_id" id="producto_transferencia_id" value="{{ $producto_id }}">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">De Sucursal</label>
                <span class="text-danger">
                    <i class="mr-2 mdi mdi-alert-circle"></i>
                </span>
                <select name="sucursal1_id" id="sucursal1_id" class="form-control" required>
                    <option value="">Seleccione</option>
                    @foreach ($sucursalesSalida as $su)
                    @php
                        $cantidadAlmacen = optional($su->movimientos[0])->cantidaDisponile($su->id, $producto_id);
                        $cantidadDisponible = $cantidadAlmacen;
                        // $cantidadDisponible = optional($su->movimientos[0])->cantidaDisponile($su->id, $producto_id);
                    @endphp
                        {{-- <option value="{{ $su->id }}">{{ $su->nombre.' (Cantidad Maxima: '.optional($su->movimientos[0])->cantidaDisponile($su->id, $producto_id).')' }}</option> --}}
                        <option value="{{ $su->id }}">{{ $su->nombre.' (Cantidad Maxima: '.$cantidadDisponible.')' }}</option>
                    @endforeach
                </select>
                <div class="text-danger error-message" id="error-sucursal1_id"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">A Sucursal</label>
                <span class="text-danger">
                    <i class="mr-2 mdi mdi-alert-circle"></i>
                </span>
                <select name="sucursal2_id" id="sucursal2_id" class="form-control" required>
                    <option value="">Seleccione</option>
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
                <div class="text-danger error-message" id="error-sucursal2_id"></div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">Salida</label>
                <span class="text-danger">
                    <i class="mr-2 mdi mdi-alert-circle"></i>
                </span>
                <input name="salida" type="number" id="salida" class="form-control" required>
                <div class="text-danger error-message" id="error-salida"></div>
            </div>
        </div>
    </div>
</form>