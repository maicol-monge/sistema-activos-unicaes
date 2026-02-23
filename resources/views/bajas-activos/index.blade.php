@extends('layouts.app')

@section('title', 'Solicitudes de Baja - UNICAES')

@section('content')

<style>
    .table-custom th {
        background-color: var(--rojo-principal);
        color: var(--dorado);
        font-weight: 600;
    }

    .table-custom tbody tr:hover {
        background-color: #fdfaf3;
    }

    .btn-eliminar {
        background-color: var(--rojo-principal);
        color: white;
    }

    .btn-eliminar:hover {
        background-color: var(--rojo-oscuro);
        color: var(--dorado);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-arrow-down-square-wide-short me-2"></i> Solicitudes de Baja de Activos
    </h2>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('bajas-activos.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Búsqueda</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $filtros['q'] ?? '' }}"
                        placeholder="Activo, código, solicitante o motivo...">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="PENDIENTE" @selected(($filtros['estado'] ?? '' )==='PENDIENTE')>PENDIENTE</option>
                    <option value="RECHAZADO" @selected(($filtros['estado'] ?? '' )==='RECHAZADO')>RECHAZADO</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ $filtros['fecha_desde'] ?? '' }}">
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ $filtros['fecha_hasta'] ?? '' }}">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route('bajas-activos.index') }}" class="btn btn-light border">
                    <i class="fa-solid fa-broom me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-primary" style="background-color: var(--dorado); color: var(--rojo-oscuro); border: none;">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4">
        @if($solicitudes->isEmpty())
            <div class="alert alert-info text-center">
                No hay solicitudes de baja que coincidan con los filtros.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Activo</th>
                            <th>Código del Activo</th>
                            <th>Solicitante</th>
                            <th>Fecha de Solicitud</th>
                            <th>Estado</th>
                            <th>Motivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($solicitudes as $solicitud)
                            <tr>
                                <td>{{ $solicitud->activo->nombre }}</td>
                                <td>{{ $solicitud->activo->codigo }}</td>
                                <td>{{ $solicitud->solicitante->nombre }}</td>
                                <td>{{ $solicitud->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if($solicitud->estado === 'PENDIENTE')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">PENDIENTE</span>
                                    @elseif($solicitud->estado === 'APROBADA')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">APROBADA</span>
                                    @elseif($solicitud->estado === 'RECHAZADO')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">RECHAZADA</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">{{ $solicitud->estado }}</span>
                                    @endif
                                </td>
                                <td>{{ $solicitud->motivo }}</td>
                                <td>
                                    @if($solicitud->estado === 'PENDIENTE')
                                        <button type="button"
                                            class="btn btn-eliminar btn-sm mb-1 btn-baja-directa-solicitud"
                                            data-id="{{ $solicitud->id_baja }}"
                                            data-activo="{{ $solicitud->activo->nombre }} ({{ $solicitud->activo->codigo }})">
                                            <i class="fa-solid fa-trash-alt me-1"></i> Proceder con la Baja
                                        </button>

                                        <button type="button"
                                            class="btn btn-secondary btn-sm btn-rechazar-solicitud-baja"
                                            data-id="{{ $solicitud->id_baja }}">
                                            <i class="fa-solid fa-ban me-1"></i> Rechazar Solicitud
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif

                                    <form id="form-baja-solicitud-{{ $solicitud->id_baja }}" action="{{ route('bajas-activos.destroy', $solicitud) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                    <form id="form-rechazar-solicitud-{{ $solicitud->id_baja }}" action="{{ route('bajas-activos.rechazar', $solicitud) }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-baja-directa-solicitud').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const activo = this.getAttribute('data-activo');

                Swal.fire({
                    title: '¿Dar de baja el activo?',
                    html: `Se dará de baja el activo:<br><strong>${activo}</strong><br><br>Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, dar de baja',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(`form-baja-solicitud-${id}`);
                        if (form) form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('.btn-rechazar-solicitud-baja').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Rechazar solicitud de baja',
                    text: '¿Está seguro de que desea rechazar esta solicitud de baja?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, rechazar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(`form-rechazar-solicitud-${id}`);
                        if (form) form.submit();
                    }
                });
            });
        });
    });
    </script>
@endpush

</div>

<div class="d-flex justify-content-end mt-3">
    {{ $solicitudes->links() }}
</div>
@endsection
