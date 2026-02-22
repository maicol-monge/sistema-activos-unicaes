@extends('layouts.app')

@section('title', 'Historial de Reportes - UNICAES')

@section('content')

<style>
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
            <i class="fa-solid fa-clock-rotate-left me-2"></i> Historial de Reportes
        </h2>
        <p class="mb-0 text-muted">Activo: <strong>{{ $activo->nombre }}</strong> ({{ $activo->codigo }})</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('encargado.reportes.index') }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
        <a href="{{ route('encargado.reportes.create', ['id_activo' => $activo->id_activo]) }}" class="btn" style="background-color: var(--dorado); color: var(--rojo-oscuro); font-weight:700;">
            <i class="fa-solid fa-plus me-1"></i> Nuevo Reporte
        </a>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Comentario</th>
                <th>Reportado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportes as $reporte)
            <tr>
                <td>{{ \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y') }}</td>
                <td>
                    @if($reporte->estado_reporte === 'BUENO')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">BUENO</span>
                    @elseif($reporte->estado_reporte === 'DANIADO')
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">DAÑADO</span>
                    @else
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">PERDIDO</span>
                    @endif
                </td>
                <td>{{ $reporte->comentario }}</td>
                <td>{{ $reporte->usuario?->nombre ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No hay reportes registrados para este activo.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $reportes->links() }}
</div>

@endsection
