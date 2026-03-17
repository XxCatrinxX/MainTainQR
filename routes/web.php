<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdenServicioController;
use App\Http\Controllers\HomeController; // ✅ CORRECTO

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();

// ✅ SOLO UNA RUTA HOME
Route::get('/home', [HomeController::class, 'index'])
    ->name('home')
    ->middleware('auth');

// ÓRDENES
Route::get('/ordenes', [OrdenServicioController::class, 'index'])->name('ordenes.index');
Route::get('/ordenes/nueva', [OrdenServicioController::class, 'create'])->name('ordenes.create');
Route::post('/ordenes/guardar', [OrdenServicioController::class, 'store'])->name('ordenes.store');