<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrdenTecnicoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\EquipoController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\SolicitudCompraController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\OrderAuditController;
use App\Http\Controllers\OrdenServicioController;
use Illuminate\Support\Facades\Route;

// Rutas publicas (sin autenticacion)
Route::get('/orders/{token_rastreo}/audits', [OrderAuditController::class, 'index']);
Route::get('/orders/{token_rastreo}/audits/recent', [OrderAuditController::class, 'recent']);
Route::get('/orders/{token_rastreo}/audits/stream', [OrderAuditController::class, 'stream']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/fcm-token', [AuthController::class, 'saveFcmToken']);

    // Clientes & Equipos
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('equipos', EquipoController::class);

    // Inventario
    Route::get('/inventario/disponible', [OrdenServicioController::class, 'inventarioDisponibleApi']);
    Route::apiResource('inventario', InventarioController::class);

    // Solicitudes de Compra
    Route::get('/solicitudes', [SolicitudCompraController::class, 'index']);
    Route::post('/solicitudes', [SolicitudCompraController::class, 'store']);
    Route::post('/solicitudes/{id}/surtir', [SolicitudCompraController::class, 'surtir']);

    // Ordenes & Pagos
    Route::get('/ordenes/qr/{qr_token}', [OrdenTecnicoController::class, 'showByQr']);
    Route::get('/ordenes', [OrdenTecnicoController::class, 'index']);
    Route::get('/ordenes/{id}', [OrdenTecnicoController::class, 'show']);

    // Acciones
    Route::put('/ordenes/{id}/confirmar-recepcion', [OrdenTecnicoController::class, 'confirmarRecepcion'])->name('api.ordenes.confirmarRecepcion');
    Route::post('/ordenes/{id}/rechazar', [OrdenTecnicoController::class, 'rechazarOrden']);
    Route::post('/ordenes/{id}/diagnostico', [OrdenServicioController::class, 'storeDiagnosticoApi']);
    Route::post('/ordenes/{id}/detalle', [OrdenServicioController::class, 'storeDetalle']);

    Route::post('/ordenes/{id}/pago', [PagoController::class, 'storeDesdeOrden']);
    Route::apiResource('pagos', PagoController::class);
});
