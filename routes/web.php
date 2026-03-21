<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdenServicioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\SolicitudCompraController;

Route::redirect('/', '/home');

Auth::routes(['reset' => false, 'confirm' => false]);

// ✅ SOLO UNA RUTA HOME
Route::get('/home', [HomeController::class , 'index'])
    ->name('home')
    ->middleware('auth');

// ÓRDENES
Route::get('/ordenes', [OrdenServicioController::class , 'index'])->name('ordenes.index');

// WIZARD
Route::get('/ordenes/nueva/paso-1', [OrdenServicioController::class , 'createPaso1'])->name('ordenes.create_paso1');
Route::post('/ordenes/nueva/paso-1', [OrdenServicioController::class , 'storePaso1'])->name('ordenes.store_paso1');

Route::get('/ordenes/nueva/paso-2', [OrdenServicioController::class , 'createPaso2'])->name('ordenes.create_paso2');
Route::post('/ordenes/nueva/paso-2', [OrdenServicioController::class , 'storePaso2'])->name('ordenes.store_paso2');

Route::get('/ordenes/nueva/paso-3', [OrdenServicioController::class , 'createPaso3'])->name('ordenes.create_paso3');
Route::post('/ordenes/nueva/paso-3', [OrdenServicioController::class , 'storePaso3'])->name('ordenes.store_paso3');
Route::get('/ordenes/{id}/recepcion', [OrdenServicioController::class , 'showRecepcion'])->name('ordenes.recepcion');
Route::put('/ordenes/{id}', [OrdenServicioController::class , 'update'])->name('ordenes.update');

// DETALLE TÉCNICO (Protegido)
Route::middleware('auth')->group(function () {
    Route::get('/ordenes/{id}', [OrdenServicioController::class , 'show'])->name('ordenes.show');
    Route::post('/ordenes/{id}/pago', [OrdenServicioController::class , 'storePago'])->name('ordenes.pago');
    Route::post('/ordenes/{id}/detalle', [OrdenServicioController::class , 'storeDetalle'])->name('ordenes.detalle');



});

// SOLICITUDES DE COMPRA
Route::middleware(['auth', 'role:admin,almacenista'])->group(function () {
    Route::post('/solicitudes', [SolicitudCompraController::class , 'store'])->name('solicitudes.store');
    Route::get('/solicitudes', [SolicitudCompraController::class , 'index'])->name('solicitudes.index');
    Route::post('/solicitudes/{id}/surtir', [SolicitudCompraController::class , 'surtir'])->name('solicitudes.surtir');
    Route::post('/ordenes/{id}/diagnostico', [OrdenServicioController::class , 'storeDiagnostico'])->name('ordenes.diagnostico');
    Route::post('/ordenes/{id}/pago', [PagoController::class , 'storeDesdeOrden'])->name('ordenes.pago');
});

// SEGUIMIENTO PÚBLICO
Route::get('/seguimiento/{token_rastreo}', [TrackingController::class , 'show'])->name('seguimiento.show');
Route::post('/seguimiento/{token_rastreo}/aceptar', [TrackingController::class , 'aceptarPresupuesto'])->name('seguimiento.aceptar');

// DECISIÓN DEL CLIENTE (sin login, vía link de correo)
Route::get('/aprobar/{token}', [TrackingController::class , 'aceptar'])->name('orden.aceptar');
Route::get('/rechazar/{token}', [TrackingController::class , 'rechazar'])->name('orden.rechazar');

// INVENTARIO (Protegido)
Route::middleware(['auth', 'role:admin,almacenista'])->group(function () {
    Route::resource('inventario', InventarioController::class);
});

// PAGOS (Protegido)
Route::middleware(['auth', 'role:admin,recepcionista'])->group(function () {
    Route::resource('pagos', PagoController::class);
});

// DIRECTORIOS GENERALES (Protegido)
Route::middleware('auth')->group(function () {
    Route::resource('clientes', ClienteController::class);
    Route::resource('equipos', EquipoController::class);
});
#Fix