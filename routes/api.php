<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrdenTecnicoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/ordenes', [OrdenTecnicoController::class, 'index']);
    Route::get('/ordenes/id/{id}', [OrdenTecnicoController::class, 'showById']);
    Route::get('/ordenes/qr/{query}', [OrdenTecnicoController::class, 'showByQrOrSerie']);
    Route::get('/ordenes/{qr_token}', [OrdenTecnicoController::class, 'show']);
    Route::post('/ordenes/{id}/diagnostico', [OrdenTecnicoController::class, 'storeDiagnostico']);
    Route::post('/ordenes/{id}/reparacion', [OrdenTecnicoController::class, 'storeReparacion']);
    Route::post('/ordenes/{id}/finalizar', [OrdenTecnicoController::class, 'finalizar']);

    Route::get('/refacciones', [OrdenTecnicoController::class, 'refacciones']);
    Route::post('/refacciones/usar', [OrdenTecnicoController::class, 'usarRefaccion']);
});
// | Aquí es donde puedes registrar las rutas API para tu aplicación. Estas rutas son cargadas por el RouteServiceProvider dentro de un grupo que
