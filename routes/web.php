<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
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
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ADMIN
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/users', fn() => 'Listado users')->name('users.index');
        // aquí luego pones tu CRUD Users
    });

    // INVENTARIADOR
    Route::middleware('role:INVENTARIADOR')->group(function () {
        Route::get('/inventario', fn() => 'Inventario')->name('inventario.index');
    });

    // ENCARGADO
    Route::middleware('role:ENCARGADO')->group(function () {
        Route::get('/mis-activos', fn() => 'Mis activos')->name('activos.mis');
    });

    // DECANO
    Route::middleware('role:DECANO')->group(function () {
        Route::get('/reportes', fn() => 'Reportes')->name('reportes.index');
    });
});

Route::resource('encargados', EncargadoController::class);
Route::resource('categorias-activos', CategoriaActivoController::class);
Route::resource('activos', ActivoController::class);
Route::resource('reportes-activos', ReporteActivoController::class);
Route::resource('asignaciones-activos', AsignacionActivoController::class);
Route::resource('movimientos-activos', MovimientoActivoController::class);
Route::resource('bajas-activos', BajaActivoController::class);
Route::resource('eliminaciones-activos', EliminacionActivoController::class);
