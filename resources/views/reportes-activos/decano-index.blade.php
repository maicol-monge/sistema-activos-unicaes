@extends('layouts.app')

@section('title', 'Reportes y Estadísticas - Decanatura - UNICAES')

@section('content')

<style>
    .card-kpi {
        border: none;
        border-radius: 10px;
        padding: 16px 18px;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .kpi-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .kpi-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--rojo-principal);
    }
    .table-custom th {
        background-color: var(--rojo-principal);
        color: var(--dorado);
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
    }
    .table-custom td {
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    .table-custom tbody tr:hover {
        background-color: #fdfaf3;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1" style="color: var(--rojo-principal); font-weight: 700;">
            <i class="fa-solid fa-chart-line me-2"></i> Reportes y estadísticas del sistema de activos
        </h2>
        <p class="mb-0 text-muted">Análisis consolidado de todos los activos y movimientos registrados en el sistema institucional.</p>
    </div>
</div>

@if(($tipo ?? 'reportes') === 'reportes' && !empty($resumen))
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-kpi">
            <div class="kpi-label">Total de reportes</div>
            <div class="kpi-value">{{ $resumen['total'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-kpi">
            <div class="kpi-label">Reportes BUENO</div>
            <div class="kpi-value" style="color:#198754;">{{ $resumen['buenos'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-kpi">
            <div class="kpi-label">Reportes DAÑADO</div>
            <div class="kpi-value" style="color:#ffc107;">{{ $resumen['daniados'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-kpi">
            <div class="kpi-label">Reportes PERDIDO</div>
            <div class="kpi-value" style="color:#dc3545;">{{ $resumen['perdidos'] }}</div>
        </div>
    </div>
</div>
@endif

@if(!empty($charts ?? []))
<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <h5 class="fw-bold mb-3" style="color: var(--rojo-oscuro);">
            <i class="fa-solid fa-chart-column me-2"></i> Visión global del sistema
        </h5>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Activos por categoría</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartActivosPorCategoria" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartActivosPorCategoria" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartActivosPorCategoria"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Activos por estado</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartActivosPorEstado" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartActivosPorEstado" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartActivosPorEstado"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Activos por condición</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartActivosPorCondicion" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartActivosPorCondicion" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartActivosPorCondicion"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Reportes por estado</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartReportesPorEstado" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartReportesPorEstado" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartReportesPorEstado"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Reportes por mes</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartReportesPorMes" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartReportesPorMes" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartReportesPorMes"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Bajas por estado</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartBajasPorEstado" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartBajasPorEstado" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartBajasPorEstado"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Asignaciones por estado</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartAsignacionesPorEstado" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartAsignacionesPorEstado" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartAsignacionesPorEstado"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Valor total por categoría</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartValorPorCategoria" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartValorPorCategoria" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartValorPorCategoria"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Distribución de valor de activos</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartHistogramaValorActivos" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartHistogramaValorActivos" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartHistogramaValorActivos"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Valor del activo vs reportes</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-light border btn-download-png" data-target="chartScatterValorVsReportes" title="Descargar PNG">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button" class="btn btn-light border btn-download-pdf" data-target="chartScatterValorVsReportes" title="Descargar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 160px;">
                    <canvas id="chartScatterValorVsReportes"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('reportes.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Tipo de consulta</label>
                <select name="tipo" class="form-select">
                    @php $tipoActual = $tipo ?? 'reportes'; @endphp
                    <option value="reportes" @selected($tipoActual === 'reportes')>Reportes de estado</option>
                    <option value="activos" @selected($tipoActual === 'activos')>Activos</option>
                    <option value="asignaciones" @selected($tipoActual === 'asignaciones')>Asignaciones</option>
                    <option value="movimientos" @selected($tipoActual === 'movimientos')>Movimientos</option>
                    <option value="bajas" @selected($tipoActual === 'bajas')>Bajas de activos</option>
                    <option value="usuarios" @selected($tipoActual === 'usuarios')>Usuarios</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Búsqueda</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" class="form-control" value="{{ $filtros['q'] ?? '' }}" placeholder="Nombre o código de activo...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ $filtros['fecha_desde'] ?? '' }}" min="1982-04-13" max="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ $filtros['fecha_hasta'] ?? '' }}" min="1982-04-13" max="{{ now()->toDateString() }}">
            </div>
            @if(($tipo ?? 'reportes') === 'reportes')
            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Estado</label>
                <select name="estado_reporte" class="form-select">
                    <option value="">Todos</option>
                    <option value="BUENO" @selected(($filtros['estado_reporte'] ?? '') === 'BUENO')>BUENO</option>
                    <option value="DANIADO" @selected(($filtros['estado_reporte'] ?? '') === 'DANIADO')>DAÑADO</option>
                    <option value="PERDIDO" @selected(($filtros['estado_reporte'] ?? '') === 'PERDIDO')>PERDIDO</option>
                </select>
            </div>
            @endif
            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <a href="{{ route('reportes.index') }}" class="btn btn-light border">
                    <i class="fa-solid fa-broom me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-warning fw-bold" style="color: var(--rojo-oscuro);">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

@if(($tipo ?? 'reportes') === 'reportes')
    <div class="d-flex justify-content-end mb-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('reportes.pdf', request()->query()) }}" class="btn btn-warning fw-bold shadow-sm" style="color: var(--rojo-oscuro);">
                <i class="fa-solid fa-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('reportes.excel', request()->query()) }}" class="btn btn-success fw-bold shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
            </a>
            <a href="{{ route('reportes.csv', request()->query()) }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                <i class="fa-solid fa-file-csv me-1"></i> CSV
            </a>
        </div>
    </div>

    <div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Activo</th>
                    <th>Código</th>
                    <th>Estado reportado</th>
                    <th>Reportado por</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportes as $reporte)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $reporte->activo?->nombre ?? 'N/A' }}</td>
                    <td>{{ $reporte->activo?->codigo ?? 'N/A' }}</td>
                    <td>
                        @if($reporte->estado_reporte === 'BUENO')
                        <span class="badge bg-success bg-opacity-10 text-success border border-success">BUENO</span>
                        @elseif($reporte->estado_reporte === 'DANIADO')
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">DAÑADO</span>
                        @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">PERDIDO</span>
                        @endif
                    </td>
                    <td>{{ $reporte->usuario?->nombre ?? 'N/A' }}</td>
                    <td>{{ $reporte->comentario }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                        <p class="mb-0">No hay reportes que coincidan con los filtros seleccionados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end mt-4">
        {{ $reportes->links() }}
    </div>
@elseif($tipo === 'activos')
    <div class="d-flex justify-content-end mb-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('reportes.pdf', request()->query()) }}" class="btn btn-warning fw-bold shadow-sm" style="color: var(--rojo-oscuro);">
                <i class="fa-solid fa-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('reportes.excel', request()->query()) }}" class="btn btn-success fw-bold shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
            </a>
            <a href="{{ route('reportes.csv', request()->query()) }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                <i class="fa-solid fa-file-csv me-1"></i> CSV
            </a>
        </div>
    </div>

    <div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Condición</th>
                    <th>Estado</th>
                    <th>Categoría</th>
                    <th>Valor compra</th>
                    <th>Fecha adquisición</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activos as $activo)
                <tr>
                    <td>{{ $activo->codigo }}</td>
                    <td>{{ $activo->nombre }}</td>
                    <td>{{ $activo->tipo }}</td>
                    <td>{{ $activo->condicion }}</td>
                    <td>{{ $activo->estado }}</td>
                    <td>{{ $activo->categoria?->nombre ?? 'N/A' }}</td>
                    <td>${{ number_format($activo->valor_compra, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($activo->fecha_adquisicion)->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                        <p class="mb-0">No hay activos que coincidan con los filtros seleccionados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end mt-4">
        {{ $activos->links() }}
    </div>
@elseif($tipo === 'asignaciones')
    <div class="d-flex justify-content-end mb-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('reportes.pdf', request()->query()) }}" class="btn btn-warning fw-bold shadow-sm" style="color: var(--rojo-oscuro);">
                <i class="fa-solid fa-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('reportes.excel', request()->query()) }}" class="btn btn-success fw-bold shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
            </a>
            <a href="{{ route('reportes.csv', request()->query()) }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                <i class="fa-solid fa-file-csv me-1"></i> CSV
            </a>
        </div>
    </div>

    <div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha asignación</th>
                    <th>Activo</th>
                    <th>Código</th>
                    <th>Asignado a</th>
                    <th>Asignado por</th>
                    <th>Estado asignación</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asignaciones as $asignacion)
                <tr>
                    <td>{{ $asignacion->fecha_asignacion ? \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>{{ $asignacion->activo?->nombre ?? 'N/A' }}</td>
                    <td>{{ $asignacion->activo?->codigo ?? 'N/A' }}</td>
                    <td>{{ $asignacion->usuarioAsignado?->nombre ?? 'N/A' }}</td>
                    <td>{{ $asignacion->usuarioAsignador?->nombre ?? 'N/A' }}</td>
                    <td>{{ $asignacion->estado_asignacion }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                        <p class="mb-0">No hay asignaciones que coincidan con los filtros seleccionados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end mt-4">
        {{ $asignaciones->links() }}
    </div>
@elseif($tipo === 'movimientos')
    <div class="d-flex justify-content-end mb-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('reportes.pdf', request()->query()) }}" class="btn btn-warning fw-bold shadow-sm" style="color: var(--rojo-oscuro);">
                <i class="fa-solid fa-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('reportes.excel', request()->query()) }}" class="btn btn-success fw-bold shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
            </a>
            <a href="{{ route('reportes.csv', request()->query()) }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                <i class="fa-solid fa-file-csv me-1"></i> CSV
            </a>
        </div>
    </div>

    <div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Activo</th>
                    <th>Código</th>
                    <th>Usuario</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimientos as $movimiento)
                <tr>
                    <td>{{ $movimiento->fecha ? \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>{{ $movimiento->tipo }}</td>
                    <td>{{ $movimiento->activo?->nombre ?? 'N/A' }}</td>
                    <td>{{ $movimiento->activo?->codigo ?? 'N/A' }}</td>
                    <td>{{ $movimiento->usuario?->nombre ?? 'N/A' }}</td>
                    <td>{{ $movimiento->observaciones }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                        <p class="mb-0">No hay movimientos que coincidan con los filtros seleccionados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end mt-4">
        {{ $movimientos->links() }}
    </div>
@elseif($tipo === 'bajas')
    <div class="d-flex justify-content-end mb-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('reportes.pdf', request()->query()) }}" class="btn btn-warning fw-bold shadow-sm" style="color: var(--rojo-oscuro);">
                <i class="fa-solid fa-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('reportes.excel', request()->query()) }}" class="btn btn-success fw-bold shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
            </a>
            <a href="{{ route('reportes.csv', request()->query()) }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                <i class="fa-solid fa-file-csv me-1"></i> CSV
            </a>
        </div>
    </div>

    <div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha solicitud</th>
                    <th>Activo</th>
                    <th>Código</th>
                    <th>Solicitante</th>
                    <th>Estado</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bajas as $baja)
                <tr>
                    <td>{{ $baja->created_at ? $baja->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>{{ $baja->activo?->nombre ?? 'N/A' }}</td>
                    <td>{{ $baja->activo?->codigo ?? 'N/A' }}</td>
                    <td>{{ $baja->solicitante?->nombre ?? 'N/A' }}</td>
                    <td>{{ $baja->estado }}</td>
                    <td>{{ $baja->motivo }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                        <p class="mb-0">No hay bajas que coincidan con los filtros seleccionados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end mt-4">
        {{ $bajas->links() }}
    </div>
@elseif($tipo === 'usuarios')
    <div class="d-flex justify-content-end mb-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('reportes.pdf', request()->query()) }}" class="btn btn-warning fw-bold shadow-sm" style="color: var(--rojo-oscuro);">
                <i class="fa-solid fa-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('reportes.excel', request()->query()) }}" class="btn btn-success fw-bold shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
            </a>
            <a href="{{ route('reportes.csv', request()->query()) }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                <i class="fa-solid fa-file-csv me-1"></i> CSV
            </a>
        </div>
    </div>

    <div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->nombre }}</td>
                    <td>{{ $usuario->correo }}</td>
                    <td>{{ $usuario->rol }}</td>
                    <td>{{ $usuario->tipo }}</td>
                    <td>{{ $usuario->estado }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                        <p class="mb-0">No hay usuarios que coincidan con los filtros seleccionados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end mt-4">
        {{ $usuarios->links() }}
    </div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
    (function () {
        const charts = @json($charts ?? []);
        if (!charts || Object.keys(charts).length === 0) return;

        function createChart(id, type, data, title, extraOptions = {}) {
            const el = document.getElementById(id);
            if (!el) return;
            const baseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: !!title, text: title }
                }
            };
            const options = Object.assign(baseOptions, extraOptions || {});
            new Chart(el.getContext('2d'), {
                type,
                data,
                options
            });
        }

        const palette = [
            '#8B0000', '#C9A84C', '#198754', '#0d6efd', '#fd7e14',
            '#20c997', '#6f42c1', '#6610f2', '#e83e8c', '#ffc107'
        ];

        createChart('chartActivosPorCategoria', 'bar', {
            labels: charts.activosPorCategoria.labels,
            datasets: [{
                label: 'Activos',
                data: charts.activosPorCategoria.data,
                backgroundColor: palette,
            }]
        }, 'Activos por categoría');

        createChart('chartActivosPorEstado', 'pie', {
            labels: charts.activosPorEstado.labels,
            datasets: [{
                data: charts.activosPorEstado.data,
                backgroundColor: palette,
            }]
        }, 'Activos por estado');

        createChart('chartActivosPorCondicion', 'doughnut', {
            labels: charts.activosPorCondicion.labels,
            datasets: [{
                data: charts.activosPorCondicion.data,
                backgroundColor: palette,
            }]
        }, 'Activos por condición');

        createChart('chartReportesPorEstado', 'bar', {
            labels: charts.reportesPorEstado.labels,
            datasets: [{
                label: 'Reportes',
                data: charts.reportesPorEstado.data,
                backgroundColor: palette,
            }]
        }, 'Reportes por estado');

        createChart('chartReportesPorMes', 'line', {
            labels: charts.reportesPorMes.labels,
            datasets: [{
                label: 'Reportes por mes',
                data: charts.reportesPorMes.data,
                fill: false,
                borderColor: '#8B0000',
                tension: 0.2,
            }]
        }, 'Reportes por mes', { scales: { y: { beginAtZero: true } } });

        createChart('chartBajasPorEstado', 'bar', {
            labels: charts.bajasPorEstado.labels,
            datasets: [{
                label: 'Bajas',
                data: charts.bajasPorEstado.data,
                backgroundColor: palette,
            }]
        }, 'Bajas por estado');

        createChart('chartAsignacionesPorEstado', 'bar', {
            labels: charts.asignacionesPorEstado.labels,
            datasets: [{
                label: 'Asignaciones',
                data: charts.asignacionesPorEstado.data,
                backgroundColor: palette,
            }]
        }, 'Asignaciones por estado', { indexAxis: 'y' });

        createChart('chartValorPorCategoria', 'bar', {
            labels: charts.valorPorCategoria.labels,
            datasets: [{
                label: 'Valor total (USD)',
                data: charts.valorPorCategoria.data,
                backgroundColor: palette,
            }]
        }, 'Valor total por categoría', { scales: { y: { beginAtZero: true } } });

        createChart('chartHistogramaValorActivos', 'bar', {
            labels: charts.histogramaValorActivos.labels,
            datasets: [{
                label: 'Número de activos',
                data: charts.histogramaValorActivos.data,
                backgroundColor: '#8B0000',
            }]
        }, 'Distribución de valor de activos', { scales: { y: { beginAtZero: true } } });

        createChart('chartScatterValorVsReportes', 'scatter', {
            datasets: [{
                label: 'Activos',
                data: charts.scatterValorVsReportes.data,
                backgroundColor: '#C9A84C',
            }]
        }, 'Valor del activo vs reportes', {
            scales: {
                x: { title: { display: true, text: 'Valor de compra (USD)' } },
                y: { title: { display: true, text: 'Cantidad de reportes' }, beginAtZero: true }
            }
        });

        function downloadPng(canvasId, filename) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png', 1.0);
            link.download = filename || (canvasId + '.png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function downloadPdf(canvasId, filename) {
            const canvas = document.getElementById(canvasId);
            if (!canvas || !window.jspdf) return;
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('landscape', 'pt', 'a4');
            const imgData = canvas.toDataURL('image/png', 1.0);
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 20;
            const availableWidth = pageWidth - margin * 2;
            const availableHeight = pageHeight - margin * 2;
            const imgWidth = availableWidth;
            const imgHeight = canvas.height * (imgWidth / canvas.width);
            const finalHeight = imgHeight > availableHeight ? availableHeight : imgHeight;
            pdf.addImage(imgData, 'PNG', margin, margin, imgWidth, finalHeight);
            pdf.save(filename || (canvasId + '.pdf'));
        }

        document.querySelectorAll('.btn-download-png').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.target;
                downloadPng(id, id + '.png');
            });
        });

        document.querySelectorAll('.btn-download-pdf').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.target;
                downloadPdf(id, id + '.pdf');
            });
        });
    })();
</script>
@endpush
