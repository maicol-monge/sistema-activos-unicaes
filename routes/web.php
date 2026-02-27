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
        Route::post('/activos/{activo}/baja-directa', [ActivoController::class, 'bajaDirecta'])
            ->name('activos.baja-directa');
        Route::get('/activos/{activo}/historial', [ActivoController::class, 'historial'])
            ->name('activos.historial');
        Route::get('/activos/{activo}/historial/pdf', [ActivoController::class, 'descargarHistorialPdf'])
            ->name('activos.historial.pdf');
        Route::get('/activos/{activo}/historial/pdf/preview', [ActivoController::class, 'previsualizarHistorialPdf'])
            ->name('activos.historial.pdf.preview');
    });

    Route::middleware('role:INVENTARIADOR')->group(function () {
        Route::get('/inventario', [ActivoController::class, 'index'])->name('inventario.index');
    });

    // ADMIN, ENCARGADO, INVENTARIADOR y DECANO pueden ver sus activos y sus asignaciones,
    // y gestionar la aceptación / rechazo / devolución de lo que les asignen.
    Route::middleware('role:ADMIN,ENCARGADO,INVENTARIADOR,DECANO')->group(function () {
        Route::get('/mis-activos', [AsignacionActivoController::class, 'misActivos'])->name('activos.mis');
        Route::get('/mis-asignaciones', [AsignacionActivoController::class, 'misAsignaciones'])
            ->name('asignaciones.mis');

        Route::get('/asignaciones/{asignacion}/detalle', [AsignacionActivoController::class, 'detalle'])
            ->name('asignaciones.detalle');
        Route::get('/mis-reportes-activos', [ReporteActivoController::class, 'misReportes'])
            ->name('encargado.reportes.index');
        Route::get('/mis-reportes-activos/crear', [ReporteActivoController::class, 'createEncargado'])
            ->name('encargado.reportes.create');
        Route::post('/mis-reportes-activos', [ReporteActivoController::class, 'storeEncargado'])
            ->name('encargado.reportes.store');
        Route::get('/mis-reportes-activos/activo/{activo}', [ReporteActivoController::class, 'historialPorActivo'])
            ->name('encargado.reportes.historial');

        Route::post('/asignaciones/{asignacion}/aceptar', [AsignacionActivoController::class, 'aceptar'])
            ->name('asignaciones.aceptar');

        Route::post('/asignaciones/{asignacion}/rechazar', [AsignacionActivoController::class, 'rechazar'])
            ->name('asignaciones.rechazar');

        Route::post('/asignaciones/{asignacion}/devolver', [AsignacionActivoController::class, 'devolver'])
            ->name('asignaciones.devolver');

        Route::get('/asignaciones/{asignacion}/comprobante', [AsignacionActivoController::class, 'comprobante'])
            ->name('asignaciones.comprobante');

        Route::get('/asignaciones/{asignacion}/comprobante/preview', [AsignacionActivoController::class, 'comprobantePreview'])
            ->name('asignaciones.comprobante.preview');
    });

    Route::middleware('role:DECANO')->group(function () {
        Route::get('/reportes', fn() => 'Reportes')->name('reportes.index');
    });

    Route::middleware(['auth', 'role:ADMIN,INVENTARIADOR'])->group(function () {
        Route::resource('encargados', EncargadoController::class)->except(['show']);
        Route::resource('activos', ActivoController::class)->except(['show', 'destroy']);
        Route::post('/activos/analizar-factura', [ActivoController::class, 'analizarFactura'])
            ->name('activos.analizar-factura');
    });

    // ADMIN, INVENTARIADOR y ENCARGADO pueden crear solicitudes de asignación
    Route::middleware('role:ADMIN,INVENTARIADOR,ENCARGADO')->group(function () {
        Route::get('/asignaciones/create', [AsignacionActivoController::class, 'create'])->name('asignaciones.create');
        Route::post('/asignaciones', [AsignacionActivoController::class, 'store'])->name('asignaciones.store');
    });

    // ADMIN, ENCARGADO, INVENTARIADOR y DECANO pueden crear solicitudes de baja
    Route::middleware('role:ADMIN,ENCARGADO,INVENTARIADOR,DECANO')->group(function () {
        Route::get('/bajas-activos/create', [BajaActivoController::class, 'create'])
            ->name('bajas-activos.create');
        Route::post('/bajas-activos', [BajaActivoController::class, 'store'])
            ->name('bajas-activos.store');
    });

    Route::middleware('role:ADMIN')->group(function () {
        Route::resource('reportes-activos', ReporteActivoController::class);
        Route::resource('movimientos-activos', MovimientoActivoController::class);

        // Solo ADMIN ve listado completo de asignaciones y gestiona bajas
        Route::get('/asignaciones', [AsignacionActivoController::class, 'index'])
            ->name('asignaciones.index');

        Route::get('/asignaciones/{asignacion}/detalle-admin', [AsignacionActivoController::class, 'detalleAdmin'])
            ->name('asignaciones.detalle-admin');

        // ADMIN gestiona devoluciones de activos
        Route::post('/asignaciones/{asignacion}/devolucion/aceptar', [AsignacionActivoController::class, 'aceptarDevolucion'])
            ->name('asignaciones.devolucion.aceptar');
        Route::post('/asignaciones/{asignacion}/devolucion/rechazar', [AsignacionActivoController::class, 'rechazarDevolucion'])
            ->name('asignaciones.devolucion.rechazar');

        Route::get('/bajas-activos/solicitudes', [BajaActivoController::class, 'solicitudes'])
            ->name('bajas-activos.solicitudes');
        Route::post('/bajas-activos/{baja}/rechazar', [BajaActivoController::class, 'rechazar'])
            ->name('bajas-activos.rechazar');

        // ADMIN puede forzar la devolución / cierre de una asignación
        Route::post('/asignaciones/{asignacion}/forzar-devolucion', [AsignacionActivoController::class, 'forzarDevolucion'])
            ->name('asignaciones.forzar-devolucion');
        Route::resource('bajas-activos', BajaActivoController::class)->except(['create', 'store']);

        Route::resource('eliminaciones-activos', EliminacionActivoController::class);
    });
});
