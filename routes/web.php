<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdenServicioController;

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home')
    ->middleware('auth');


Route::get('/ordenes/nueva', [OrdenServicioController::class, 'create'])->name('ordenes.create');
Route::post('/ordenes/guardar', [OrdenServicioController::class, 'store'])->name('ordenes.store');
Route::get('/ordenes', [OrdenServicioController::class, 'index'])->name('ordenes.index');