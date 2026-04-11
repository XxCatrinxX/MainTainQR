<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrdenTecnicoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\EquipoController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\SolicitudCompraController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\OrderAuditController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Rutas públicas (sin autenticación)
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
    Route::apiResource('inventario', InventarioController::class);

    // Solicitudes de Compra
    Route::get('/solicitudes', [SolicitudCompraController::class, 'index']);
    Route::post('/solicitudes', [SolicitudCompraController::class, 'store']);
    Route::post('/solicitudes/{id}/surtir', [SolicitudCompraController::class, 'surtir']);

    // Órdenes & Pagos
    // 🔥 QR primero
    Route::get('/ordenes/qr/{qr_token}', [OrdenTecnicoController::class, 'showByQr']);

    // 🔥 Órdenes del técnico
    Route::get('/ordenes', [OrdenTecnicoController::class, 'index']);
    Route::get('/ordenes/{id}', [OrdenTecnicoController::class, 'show']);

    // 🔥 Acciones
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/ordenes/{id}/confirmar-recepcion', [OrdenTecnicoController::class, 'confirmarRecepcion'])->name('ordenes.confirmarRecepcion');
        Route::post('/ordenes/{id}/rechazar', [OrdenTecnicoController::class, 'rechazarOrden']);
        Route::post('/ordenes/{id}/diagnostico', [OrdenTecnicoController::class, 'storeDiagnostico']);
    });

    Route::post('/ordenes/{id}/pago', [PagoController::class, 'storeDesdeOrden']);
    Route::apiResource('pagos', PagoController::class);
    Route::apiResource('pagos', PagoController::class);
});
// | Aquí es donde puedes registrar las rutas API para tu aplicación. Estas rutas son cargadas por el RouteServiceProvider dentro de un grupo que
