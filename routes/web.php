<?php

use App\Http\Controllers\AutoController;
use App\Http\Controllers\AutoSucursalController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ClienteSucursalController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\GrupoClienteController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\RolController;
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
    Route::prefix('/sucursal')->group(function () {
        Route::get('/listado', [SucursalController::class, 'listado'])->name('sucursal.listado');
        Route::post('/ajaxListado', [SucursalController::class, 'ajaxListado'])->name('sucursal.ajaxListado');
        Route::post('/guardarSucursal', [SucursalController::class, 'guardarSucursal'])->name('sucursal.guardarSucursal');
        Route::post('/eliminarSucursal', [SucursalController::class, 'eliminarSucursal'])->name('sucursal.eliminarSucursal');
    });
    //USUARIO
    Route::prefix('/usuario')->group(function () {
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
    Route::prefix('/cliente')->group(function () {
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
    // GRUPO
    Route::prefix('/grupo')->group(function () {
        Route::get('/listado', [GrupoController::class, 'listado'])->name('grupo.listado');
        Route::post('/ajaxListado', [GrupoController::class, 'ajaxListado'])->name('grupo.ajaxListado');
        Route::post('/guardarGrupo', [GrupoController::class, 'guardarGrupo'])->name('grupo.guardarGrupo');
        Route::post('/eliminarGrupo', [GrupoController::class, 'eliminarGrupo'])->name('grupo.eliminarGrupo');
    });
    // CONSULTA
    Route::prefix('/consulta')->group(function () {
        Route::get('/listado', [ConsultaController::class, 'listado'])->name('consulta.listado');
        Route::post('/ajaxListado', [ConsultaController::class, 'ajaxListado'])->name('consulta.ajaxListado');
        Route::post('/guardarConsulta', [ConsultaController::class, 'guardarConsulta'])->name('consulta.guardarConsulta');
        Route::post('/eliminarConsulta', [ConsultaController::class, 'eliminarConsulta'])->name('consulta.eliminarConsulta');
    });

    //ADICICONES POR SUCURSAL
    // CLIENTE
    Route::prefix('/cliente-sucursal')->group(function () {
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
    // GRUPO-CLIENTE
    Route::prefix('/grupo-cliente')->group(function () {
        Route::get('/listado/{sucursal_id}/{grupo_id}', [GrupoClienteController::class, 'listado'])->name('grupoCliente.listado');
        Route::post('/ajaxListado', [GrupoClienteController::class, 'ajaxListado'])->name('grupoCliente.ajaxListado');
        Route::post('/guardarGrupo', [GrupoClienteController::class, 'guardarGrupo'])->name('grupoCliente.guardarGrupo');
        Route::post('/eliminarGrupo', [GrupoClienteController::class, 'eliminarGrupo'])->name('grupoCliente.eliminarGrupo');

        Route::get('/detalle/{grupo_cliente_id}', [GrupoClienteController::class, 'detalle'])->name('grupoCliente.detalle');
        Route::post('/ajaxDetalle', [GrupoClienteController::class, 'ajaxDetalle'])->name('grupoCliente.ajaxDetalle');
        Route::post('/guardarItemRangos', [GrupoClienteController::class, 'guardarItemRangos'])->name('grupoCliente.guardarItemRangos');
        Route::post('/descargarFormatoImportarExcel', [GrupoClienteController::class, 'descargarFormatoImportarExcel'])->name('grupoCliente.descargarFormatoImportarExcel');
        Route::post('/importarServiciosExcel', [GrupoClienteController::class, 'importarServiciosExcel'])->name('grupoCliente.importarServiciosExcel');
        Route::post('/guardarOrdenRecepcion', [GrupoClienteController::class, 'guardarOrdenRecepcion'])->name('grupoCliente.guardarOrdenRecepcion');
        Route::post('/obtenerOrdenRecepcion', [GrupoClienteController::class, 'obtenerOrdenRecepcion'])->name('grupoCliente.obtenerOrdenRecepcion');

        Route::post('/guardarInformeDiagnostico', [GrupoClienteController::class, 'guardarInformeDiagnostico'])->name('grupoCliente.guardarInformeDiagnostico');
        Route::post('/obtenerInformeDiagnostico', [GrupoClienteController::class, 'obtenerInformeDiagnostico'])->name('grupoCliente.obtenerInformeDiagnostico');

        Route::post('/guardarFormularioDiagnostico', [GrupoClienteController::class, 'guardarFormularioDiagnostico'])->name('grupoCliente.guardarFormularioDiagnostico');
        Route::post('/obtenerFormularioDiagnostico', [GrupoClienteController::class, 'obtenerFormularioDiagnostico'])->name('grupoCliente.obtenerFormularioDiagnostico');

        Route::post('/guardarCotizacion', [GrupoClienteController::class, 'guardarCotizacion'])->name('grupoCliente.guardarCotizacion');
        Route::post('/obtenerCotizacion', [GrupoClienteController::class, 'obtenerCotizacion'])->name('grupoCliente.obtenerCotizacion');
        Route::get('/descargarPdfCotizacion/{id}', [GrupoClienteController::class, 'descargarPdfCotizacion'])->name('grupoCliente.descargarPdfCotizacion');
        Route::get('/descargarExcelCotizacion/{id}', [GrupoClienteController::class, 'descargarExcelCotizacion'])->name('grupoCliente.descargarExcelCotizacion');

        Route::get('/descargarPdfOrdenTrabajo/{id}', [GrupoClienteController::class, 'descargarPdfOrdenTrabajo'])->name('grupoCliente.descargarPdfOrdenTrabajo');
        Route::get('/descargarExcelOrdenTrabajo/{id}', [GrupoClienteController::class, 'descargarExcelOrdenTrabajo'])->name('grupoCliente.descargarExcelOrdenTrabajo');

        Route::post('/guardarOrdenTrabajoOficial', [GrupoClienteController::class, 'guardarOrdenTrabajoOficial'])->name('grupoCliente.guardarOrdenTrabajoOficial');
        Route::post('/obtenerOrdenTrabajoOficial', [GrupoClienteController::class, 'obtenerOrdenTrabajoOficial'])->name('grupoCliente.obtenerOrdenTrabajoOficial');
        Route::get('/descargarPdfOrdenTrabajoOficial/{id}', [GrupoClienteController::class, 'descargarPdfOrdenTrabajoOficial'])->name('grupoCliente.descargarPdfOrdenTrabajoOficial');
        Route::get('/descargarExcelOrdenTrabajoOficial/{id}', [GrupoClienteController::class, 'descargarExcelOrdenTrabajoOficial'])->name('grupoCliente.descargarExcelOrdenTrabajoOficial');

        Route::get('/descargarPdfOrdenRecepcion/{id}', [GrupoClienteController::class, 'descargarPdfOrdenRecepcion'])->name('grupoCliente.descargarPdfOrdenRecepcion');
        Route::get('/descargarExcelOrdenRecepcion/{id}', [GrupoClienteController::class, 'descargarExcelOrdenRecepcion'])->name('grupoCliente.descargarExcelOrdenRecepcion');

        Route::get('/descargarPdfInformeDiagnostico/{id}', [GrupoClienteController::class, 'descargarPdfInformeDiagnostico'])->name('grupoCliente.descargarPdfInformeDiagnostico');
        Route::get('/descargarExcelInformeDiagnostico/{id}', [GrupoClienteController::class, 'descargarExcelInformeDiagnostico'])->name('grupoCliente.descargarExcelInformeDiagnostico');

        Route::get('/descargarPdfFormularioDiagnostico/{id}', [GrupoClienteController::class, 'descargarPdfFormularioDiagnostico'])->name('grupoCliente.descargarPdfFormularioDiagnostico');
        Route::get('/descargarExcelFormularioDiagnostico/{id}', [GrupoClienteController::class, 'descargarExcelFormularioDiagnostico'])->name('grupoCliente.descargarExcelFormularioDiagnostico');
    });
});

require __DIR__ . '/auth.php';
