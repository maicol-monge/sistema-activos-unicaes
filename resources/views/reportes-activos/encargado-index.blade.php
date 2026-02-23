@extends('layouts.app')

@section('title', 'Reportes de Estado - UNICAES')

@section('content')

<style>
    .btn-nuevo {
        background-color: var(--dorado);
        color: var(--rojo-oscuro);
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-nuevo:hover {
        background-color: #dca72c;
        color: var(--rojo-oscuro);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(237, 189, 63, 0.4);
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
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-clipboard-check me-2"></i> Reporte de Estado del Activo
    </h2>
    <a href="{{ route('encargado.reportes.create') }}" class="btn btn-nuevo shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Nuevo Reporte
    </a>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('encargado.reportes.index') }}" class="row g-3">
            <div class="col-md-10">
                <label class="form-label text-muted fw-bold mb-1">Búsqueda</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" class="form-control" value="{{ $filtros['q'] ?? '' }}" placeholder="Nombre o código de activo...">
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end justify-content-end gap-2">
                <a href="{{ route('encargado.reportes.index') }}" class="btn btn-light border">
                    <i class="fa-solid fa-broom me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-nuevo">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <th>Activo</th>
                <th>Código</th>
                <th>Historial de reportes</th>
                <th class="text-center pe-4">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activos as $activo)
            <tr>
                <td class="fw-semibold text-dark">{{ $activo->nombre }}</td>
                <td>{{ $activo->codigo }}</td>
                <td>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                        {{ $activo->reportes_count }} reporte(s)
                    </span>
                </td>
                <td class="text-center pe-4">
                    <div class="btn-group" role="group">
                        <a href="{{ route('encargado.reportes.create', ['id_activo' => $activo->id_activo]) }}" class="btn btn-sm btn-light border" title="Reportar estado">
                            <i class="fa-solid fa-pen-to-square" style="color: var(--rojo-principal);"></i>
                        </a>
                        <a href="{{ route('encargado.reportes.historial', $activo) }}" class="btn btn-sm btn-light border" title="Ver historial">
                            <i class="fa-solid fa-clock-rotate-left" style="color: var(--dorado);"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No tienes activos asignados para reportar estado.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $activos->links() }}
</div>

@endsection
