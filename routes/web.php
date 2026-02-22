<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
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

// Rutas protegidas
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::middleware('role:ADMIN')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('categorias-activos', CategoriaActivoController::class)->except(['show']);
        Route::get('/activos/aprobaciones', [ActivoController::class, 'aprobaciones'])
            ->name('activos.aprobaciones');
        Route::get('/activos/filtros/categorias', [ActivoController::class, 'buscarCategoriasFiltro'])
            ->name('activos.filtros.categorias');
        Route::get('/activos/filtros/registradores', [ActivoController::class, 'buscarRegistradoresFiltro'])
            ->name('activos.filtros.registradores');
        Route::post('/activos/{activo}/aprobar', [ActivoController::class, 'aprobar'])
            ->name('activos.aprobar');
        Route::post('/activos/{activo}/rechazar', [ActivoController::class, 'rechazar'])
            ->name('activos.rechazar');
    });

    Route::middleware('role:INVENTARIADOR')->group(function () {
        Route::get('/inventario', [ActivoController::class, 'index'])->name('inventario.index');
        Route::get('/asignaciones', [AsignacionActivoController::class, 'index'])
            ->name('asignaciones.index');
        Route::get('/asignaciones/create', [AsignacionActivoController::class, 'create'])->name('asignaciones.create');
        Route::post('/asignaciones', [AsignacionActivoController::class, 'store'])->name('asignaciones.store');
    });

    Route::middleware('role:ENCARGADO')->group(function () {
        Route::get('/mis-activos', fn() => 'Mis activos')->name('activos.mis');
        Route::get('/mis-asignaciones', [AsignacionActivoController::class, 'misAsignaciones'])
            ->name('asignaciones.mis');

        Route::post('/asignaciones/{asignacion}/aceptar', [AsignacionActivoController::class, 'aceptar'])
            ->name('asignaciones.aceptar');

        Route::post('/asignaciones/{asignacion}/rechazar', [AsignacionActivoController::class, 'rechazar'])
            ->name('asignaciones.rechazar');
    });

    Route::middleware('role:DECANO')->group(function () {
        Route::get('/reportes', fn() => 'Reportes')->name('reportes.index');
    });

    Route::middleware(['auth', 'role:ADMIN,INVENTARIADOR'])->group(function () {
        Route::resource('encargados', EncargadoController::class)->except(['show']);
        Route::resource('activos', ActivoController::class)->except(['show', 'destroy']);
    });

    Route::middleware('role:ADMIN')->group(function () {
        Route::resource('reportes-activos', ReporteActivoController::class);
        Route::resource('movimientos-activos', MovimientoActivoController::class);
        Route::resource('bajas-activos', BajaActivoController::class);
        Route::resource('eliminaciones-activos', EliminacionActivoController::class);
    });
});
