@extends('layouts.app')

@section('title', 'Asignaciones de Activos - UNICAES')

@section('content')

<style>
    /* Botón de creación principal */
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

    /* Estilos de la tabla institucional */
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

    /* Custom Badges para los estados de asignación */
    .badge-estado {
        font-size: 0.85em;
        padding: 0.4em 0.8em;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-clipboard-list me-2"></i> Asignaciones de Activos
    </h2>
    <a href="{{ route('asignaciones.create') }}" class="btn btn-nuevo shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Nueva Asignación
    </a>
</div>

@if(session('ok'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #198754;">
    <i class="fa-solid fa-circle-check me-2"></i> {{ session('ok') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <th class="ps-4">ID</th>
                <th>Activo</th>
                <th>Encargado</th>
                <th class="text-center">Estado</th>
                <th>Asignado por</th>
                <th>Fecha y Hora</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asignaciones as $a)
            <tr>
                <td class="ps-4 fw-bold text-muted">#{{ $a->id_asignacion }}</td>

                <td>
                    <span class="fw-semibold text-dark">
                        <i class="fa-solid fa-box-open me-1 text-muted"></i>
                        {{ $a->activo?->nombre ?? 'Activo no encontrado' }}
                    </span>
                </td>

                <td>
                    <span class="text-dark">
                        <i class="fa-solid fa-user-tie me-1" style="color: var(--dorado);"></i>
                        {{ $a->encargado?->nombre ?? 'Encargado no encontrado' }}
                    </span>
                </td>

                <td class="text-center">
                    @if($a->estado_asignacion === 'PENDIENTE')
                    <span class="badge badge-estado bg-warning bg-opacity-10 text-warning border border-warning">
                        <i class="fa-regular fa-clock me-1"></i> PENDIENTE
                    </span>
                    @elseif($a->estado_asignacion === 'ACEPTADO')
                    <span class="badge badge-estado bg-success bg-opacity-10 text-success border border-success">
                        <i class="fa-solid fa-check-double me-1"></i> ACEPTADO
                    </span>
                    @elseif($a->estado_asignacion === 'RECHAZADO')
                    <span class="badge badge-estado bg-danger bg-opacity-10 text-danger border border-danger">
                        <i class="fa-solid fa-xmark me-1"></i> RECHAZADO
                    </span>
                    @else
                    <span class="badge badge-estado bg-secondary bg-opacity-10 text-secondary border border-secondary">
                        {{ $a->estado_asignacion }}
                    </span>
                    @endif
                </td>

                <td>
                    <span class="text-muted" style="font-size: 0.9em;">
                        <i class="fa-solid fa-user-gear me-1"></i> {{ $a->usuarioAsignador?->nombre ?? 'Sistema' }}
                    </span>
                </td>

                <td>
                    <div class="text-muted" style="font-size: 0.9em;">
                        <i class="fa-regular fa-calendar-days me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y') }}
                        <br>
                        <i class="fa-regular fa-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_asignacion)->format('H:i') }}
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-clipboard-question fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No hay asignaciones registradas en el sistema.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $asignaciones->links() }}
</div>

@endsection