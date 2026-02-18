<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\AsignacionActivoController;
use App\Http\Controllers\BajaActivoController;
use App\Http\Controllers\CategoriaActivoController;
use App\Http\Controllers\EliminacionActivoController;
use App\Http\Controllers\EncargadoController;
use App\Http\Controllers\MovimientoActivoController;
use App\Http\Controllers\ReporteActivoController;

//Login routes
Route::get('/', [AuthController::class, 'showLogin'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post')
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Ruta protegida de prueba (dashboard)
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');

Route::resource('encargados', EncargadoController::class);
Route::resource('categorias-activos', CategoriaActivoController::class);
Route::resource('activos', ActivoController::class);
Route::resource('reportes-activos', ReporteActivoController::class);
Route::resource('asignaciones-activos', AsignacionActivoController::class);
Route::resource('movimientos-activos', MovimientoActivoController::class);
Route::resource('bajas-activos', BajaActivoController::class);
Route::resource('eliminaciones-activos', EliminacionActivoController::class);
