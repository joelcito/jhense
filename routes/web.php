<?php

use App\Http\Controllers\AutoController;
use App\Http\Controllers\AutoSucursalController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ClienteSucursalController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    //1: Admin | 2: Supervisor | 3: Jefe Mecanico | 4: Mecanico | 5: Secretaria

    // ROL
    Route::prefix('/rol')->group(function () {
        Route::get('/listado', [RolController::class, 'listado'])->name('rol.listado');
        Route::post('/ajaxListado', [RolController::class, 'ajaxListado'])->name('rol.ajaxListado');
        Route::post('/guardar', [rolController::class, 'guardar'])->name('rol.guardar');
        Route::post('/eliminarRol', [RolController::class, 'eliminarRol'])->name('rol.eliminarRol');
    });
    // SUCURSALES
    Route::prefix('/sucursal')->group(function(){
        Route::get('/listado', [SucursalController::class, 'listado'])->name('sucursal.listado');
        Route::post('/ajaxListado', [SucursalController::class, 'ajaxListado'])->name('sucursal.ajaxListado');
        Route::post('/guardarSucursal', [SucursalController::class, 'guardarSucursal'])->name('sucursal.guardarSucursal');
        Route::post('/eliminarSucursal', [SucursalController::class, 'eliminarSucursal'])->name('sucursal.eliminarSucursal');
    });
    //USUARIO
    Route::prefix('/usuario')->group(function(){
        Route::get('/listado', [UserController::class, 'listado'])->name('usuario.listado');
        Route::post('/ajaxListado', [UserController::class, 'ajaxListado'])->name('usuario.ajaxListado');
        Route::post('/guardarUsuario', [UserController::class, 'guardarUsuario'])->name('usuario.guardarUsuario');
        Route::post('/eliminarUsuario', [UserController::class, 'eliminarUsuario'])->name('usuario.eliminarUsuario');
        Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('usuario.resetPassword');
        Route::post('/cambiaSucursal', [UserController::class, 'cambiaSucursal'])->name('usuario.cambiaSucursal');
    });
    // MARCA
    Route::prefix('/marca')->group(function () {
        Route::get('/listado', [MarcaController::class, 'listado'])->name('marca.listado');
        Route::post('/ajaxListado', [MarcaController::class, 'ajaxListado'])->name('marca.ajaxListado');
        Route::post('/guardarMarca', [MarcaController::class, 'guardarMarca'])->name('marca.guardarMarca');
        Route::post('/eliminarMarca', [MarcaController::class, 'eliminarMarca'])->name('marca.eliminarMarca');
    });
    // CLIENTE
    Route::prefix('/cliente')->group(function(){
        Route::get('/listado', [ClienteController::class, 'listado'])->name('cliente.listado');
        Route::post('/ajaxListado', [ClienteController::class, 'ajaxListado'])->name('cliente.ajaxListado');
        Route::post('/guardarCliente', [ClienteController::class, 'guardarCliente'])->name('cliente.guardarCliente');
        Route::post('/eliminarCliente', [ClienteController::class, 'eliminarCliente'])->name('cliente.eliminarCliente');
    });
    // AUTO
    Route::prefix('/auto')->group(function () {
        Route::get('/listado', [AutoController::class, 'listado'])->name('auto.listado');
        Route::post('/ajaxListado', [AutoController::class, 'ajaxListado'])->name('auto.ajaxListado');
        Route::post('/guardarAuto', [AutoController::class, 'guardarAuto'])->name('auto.guardarAuto');
        Route::post('/eliminarAuto', [AutoController::class, 'eliminarAuto'])->name('auto.eliminarAuto');
    });
    // SERVICIO
    Route::prefix('/servicio')->group(function(){
        Route::get('/listado', [ServicioController::class, 'listado'])->name('servicio.listado');
        Route::post('/ajaxListado', [ServicioController::class, 'ajaxListado'])->name('servicio.ajaxListado');
        Route::post('/guardarServicio', [ServicioController::class, 'guardarServicio'])->name('servicio.guardarServicio');
        Route::post('/eliminarServicio', [ServicioController::class, 'eliminarServicio'])->name('servicio.eliminarServicio');
    });
    // PRODUCTO
    Route::prefix('/producto')->group(function(){
        Route::get('/listado', [ProductoController::class, 'listado'])->name('producto.listado');
        Route::post('/ajaxListado', [ProductoController::class, 'ajaxListado'])->name('producto.ajaxListado');
        Route::post('/guardarProducto', [ProductoController::class, 'guardarProducto'])->name('producto.guardarProducto');
        Route::post('/eliminarProducto', [ProductoController::class, 'eliminarProducto'])->name('producto.eliminarProducto');

        Route::post('/guardarStockSucursal', [ProductoController::class, 'guardarStockSucursal'])->name('producto.guardarStockSucursal');
        Route::post('/ajaxStockSucursal', [ProductoController::class, 'ajaxStockSucursal'])->name('producto.ajaxStockSucursal');
        Route::post('/ajaxFormTransferencia', [ProductoController::class, 'ajaxFormTransferencia'])->name('producto.ajaxFormTransferencia');
        Route::post('/guardarTransferenciaSucursal', [ProductoController::class, 'guardarTransferenciaSucursal'])->name('producto.guardarTransferenciaSucursal');
        Route::post('/guardarSalidaSucursal', [ProductoController::class, 'guardarSalidaSucursal'])->name('producto.guardarSalidaSucursal');

        Route::get('/pdfProductoStock', [ProductoController::class, 'pdfProductoStock'])->name('producto.pdfProductoStock');

        Route::post('/generarReporteIngreso', [ProductoController::class, 'generarReporteIngreso'])->name('producto.generarReporteIngreso');
        Route::post('/generarReporteSalida', [ProductoController::class, 'generarReporteSalida'])->name('producto.generarReporteSalida');

        Route::post('/importarServiciosProductosExcel', [ProductoController::class, 'importarServiciosProductosExcel'])->name('producto.importarServiciosProductosExcel');
    });

    //ADICICONES POR SUCURSAL
    // CLIENTE
    Route::prefix('/cliente-sucursal')->group(function(){
        Route::get('/listado/{sucursal_id}', [ClienteSucursalController::class, 'listado'])->name('clienteSucursal.listado');
        Route::post('/ajaxListado', [ClienteSucursalController::class, 'ajaxListado'])->name('clienteSucursal.ajaxListado');
        Route::post('/guardarCliente', [ClienteSucursalController::class, 'guardarCliente'])->name('clienteSucursal.guardarCliente');
        Route::post('/eliminarCliente', [ClienteSucursalController::class, 'eliminarCliente'])->name('clienteSucursal.eliminarCliente');
    });
    // AUTO
    Route::prefix('/auto-sucursal')->group(function () {
        Route::get('/listado/{sucursal_id}', [AutoSucursalController::class, 'listado'])->name('autoSucursal.listado');
        Route::post('/ajaxListado', [AutoSucursalController::class, 'ajaxListado'])->name('autoSucursal.ajaxListado');
        Route::post('/guardarAuto', [AutoSucursalController::class, 'guardarAuto'])->name('autoSucursal.guardarAuto');
        Route::post('/eliminarAuto', [AutoSucursalController::class, 'eliminarAuto'])->name('autoSucursal.eliminarAuto');
    });
});

require __DIR__.'/auth.php';
