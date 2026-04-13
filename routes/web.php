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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


Route::redirect('/', '/home');

Auth::routes(['reset' => false, 'confirm' => false]);

// ✅ SOLO UNA RUTA HOME
Route::get('/home', [HomeController::class , 'index'])
    ->name('home')
    ->middleware('auth');

// ÓRDENES
Route::get('/ordenes', [OrdenServicioController::class , 'index'])->name('ordenes.index');

// WIZARD (Solo admin y recepcionista pueden crear)
Route::middleware(['auth', 'role:admin,recepcionista'])->group(function () {
    Route::get('/ordenes/nueva/paso-1', [OrdenServicioController::class , 'createPaso1'])->name('ordenes.create_paso1');
    Route::post('/ordenes/nueva/paso-1', [OrdenServicioController::class , 'storePaso1'])->name('ordenes.store_paso1');
    Route::get('/ordenes/nueva/paso-2', [OrdenServicioController::class , 'createPaso2'])->name('ordenes.create_paso2');
    Route::post('/ordenes/nueva/paso-2', [OrdenServicioController::class , 'storePaso2'])->name('ordenes.store_paso2');
    Route::get('/ordenes/nueva/paso-3', [OrdenServicioController::class , 'createPaso3'])->name('ordenes.create_paso3');
    Route::post('/ordenes/nueva/paso-3', [OrdenServicioController::class , 'storePaso3'])->name('ordenes.store_paso3');
});
Route::get('/ordenes/{id}/recepcion', [OrdenServicioController::class , 'showRecepcion'])->name('ordenes.recepcion');
Route::put('/ordenes/{id}', [OrdenServicioController::class , 'update'])->name('ordenes.update');
Route::get('/ordenes/{id}/qr', [OrdenServicioController::class, 'verQR'])->name('ordenes.qr');

// DETALLE TÉCNICO (Protegido)
Route::middleware('auth')->group(function () {
    Route::get('/ordenes/{id}', [OrdenServicioController::class , 'show'])->name('ordenes.show');
    Route::post('/ordenes/{id}/detalle', [OrdenServicioController::class , 'storeDetalle'])->name('ordenes.detalle');
    Route::post('/ordenes/{id}/diagnostico', [OrdenServicioController::class , 'storeDiagnostico'])->name('ordenes.diagnostico');

    // State-machine transitions (explicit confirmation buttons)
    Route::post('/ordenes/{id}/confirmar-recepcion', [OrdenServicioController::class, 'confirmarRecepcion'])->name('ordenes.confirmarRecepcion');
    Route::post('/ordenes/{id}/iniciar-reparacion', [OrdenServicioController::class, 'iniciarReparacion'])->name('ordenes.iniciarReparacion');
    Route::post('/ordenes/{id}/cerrar-rechazada', [OrdenServicioController::class, 'cerrarRechazada'])->name('ordenes.cerrarRechazada');
    Route::post('/ordenes/{id}/confirmar-entrega', [OrdenServicioController::class, 'confirmarEntrega'])->name('ordenes.confirmarEntrega');
});

// SOLICITUDES DE COMPRA
Route::middleware(['auth', 'role:admin,almacenista,tecnico'])->group(function () {
    Route::post('/solicitudes', [SolicitudCompraController::class , 'store'])->name('solicitudes.store');
    Route::get('/solicitudes', [SolicitudCompraController::class , 'index'])->name('solicitudes.index');
    Route::post('/solicitudes/{id}/surtir', [SolicitudCompraController::class , 'surtir'])->name('solicitudes.surtir');
});

// SEGUIMIENTO PÚBLICO
Route::get('/seguimiento/{token_rastreo}', [TrackingController::class , 'show'])->name('seguimiento.show');
Route::post('/seguimiento/{token_rastreo}/aceptar', [TrackingController::class , 'aceptarPresupuesto'])->name('seguimiento.aceptar');
Route::post('/seguimiento/{token_rastreo}/rechazar', [TrackingController::class , 'rechazarPresupuesto'])->name('seguimiento.rechazar');

// DECISIÓN DEL CLIENTE (sin login, vía link de correo)
Route::get('/aprobar/{token}', [TrackingController::class , 'aceptar'])->name('orden.aceptar');
Route::get('/rechazar/{token}', [TrackingController::class , 'rechazar'])->name('orden.rechazar');

// INVENTARIO (Protegido con URL explícita para evitar conflictos)
Route::middleware(['auth', 'role:admin,almacenista'])->group(function () {
    Route::get('/control-inventario', [InventarioController::class, 'index'])->name('web.inventario.index');
    Route::get('/control-inventario/create', [InventarioController::class, 'create'])->name('web.inventario.create');
    Route::post('/control-inventario', [InventarioController::class, 'store'])->name('web.inventario.store');
    Route::get('/control-inventario/{inventario}/edit', [InventarioController::class, 'edit'])->name('web.inventario.edit');
    Route::put('/control-inventario/{inventario}', [InventarioController::class, 'update'])->name('web.inventario.update');
    Route::delete('/control-inventario/{inventario}', [InventarioController::class, 'destroy'])->name('web.inventario.destroy');
});

// PAGOS (Protegido)
Route::middleware(['auth', 'role:admin,recepcionista'])->group(function () {
    Route::resource('pagos', PagoController::class);
    Route::post('/ordenes/{id}/pago', [PagoController::class, 'storeDesdeOrden'])->name('ordenes.pago');
});

// DIRECTORIOS GENERALES (Protegido)
Route::middleware('auth')->group(function () {
    Route::resource('clientes', ClienteController::class);
    Route::resource('equipos', EquipoController::class);
});

// USUARIOS (index para todos los autenticados; edicion solo admin via controller)
use App\Http\Controllers\UsuarioController;
Route::middleware('auth')->resource('usuarios', UsuarioController::class);


// RUTA DE PRUEBA PARA NOTIFICACIONES (Eliminar después)
Route::get('/test-mail', function () {
    Mail::raw('PRUEBA REAL', function ($message) {
        $message->to('2123200534@soy.utj.edu.mx')
                ->subject('Prueba SMTP');
    });

    return 'Correo enviado';
});
