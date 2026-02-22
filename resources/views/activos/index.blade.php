@extends('layouts.app')

@section('title', 'Activos - UNICAES')

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
        <i class="fa-solid fa-boxes-stacked me-2"></i> Activos
    </h2>
    <a href="{{ route('activos.create') }}" class="btn btn-nuevo shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Nuevo Activo
    </a>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Categoría</th>
                <th>Condición</th>
                <th class="text-center">Estado</th>
                <th class="text-center pe-4">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activos as $activo)
            <tr>
                <td class="fw-semibold">{{ $activo->codigo }}</td>
                <td>
                    <div class="fw-semibold text-dark">{{ $activo->nombre }}</div>
                    <small class="text-muted">Registrado por: {{ $activo->registrador?->nombre ?? 'N/A' }}</small>
                    @if($activo->estado === 'RECHAZADO' && $activo->observaciones)
                    <br><small class="text-danger">Obs: {{ $activo->observaciones }}</small>
                    @endif
                </td>
                <td>{{ $activo->tipo }}</td>
                <td>{{ $activo->categoria?->nombre }}</td>
                <td>{{ $activo->condicion }}</td>
                <td class="text-center">
                    @if($activo->estado === 'PENDIENTE')
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">PENDIENTE</span>
                    @elseif($activo->estado === 'APROBADO')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">APROBADO</span>
                    @elseif($activo->estado === 'RECHAZADO')
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">RECHAZADO</span>
                    @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">{{ $activo->estado }}</span>
                    @endif
                </td>
                <td class="text-center pe-4">
                    @php
                        $canEdit = auth()->user()->rol === 'ADMIN'
                            || (auth()->user()->rol === 'INVENTARIADOR' && $activo->registrado_por === auth()->user()->id_usuario);
                    @endphp

                    @if($canEdit && $activo->estado !== 'APROBADO')
                    <a href="{{ route('activos.edit', $activo) }}" class="btn btn-sm btn-light border" title="Editar">
                        <i class="fa-solid fa-pen" style="color: var(--dorado);"></i>
                    </a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No hay activos registrados.</p>
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
