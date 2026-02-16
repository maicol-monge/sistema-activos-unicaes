<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\AsignacionActivoController;
use App\Http\Controllers\BajaActivoController;
use App\Http\Controllers\CategoriaActivoController;
use App\Http\Controllers\EliminacionActivoController;
use App\Http\Controllers\EncargadoController;
use App\Http\Controllers\MovimientoActivoController;
use App\Http\Controllers\ReporteActivoController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('encargados', EncargadoController::class);
Route::resource('categorias-activos', CategoriaActivoController::class);
Route::resource('activos', ActivoController::class);
Route::resource('reportes-activos', ReporteActivoController::class);
Route::resource('asignaciones-activos', AsignacionActivoController::class);
Route::resource('movimientos-activos', MovimientoActivoController::class);
Route::resource('bajas-activos', BajaActivoController::class);
Route::resource('eliminaciones-activos', EliminacionActivoController::class);
