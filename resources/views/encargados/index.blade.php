@extends('layouts.app')

@section('title', 'Gestión de Encargados - UNICAES')

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

    .badge-tipo {
        background-color: rgba(237, 189, 63, 0.15);
        color: var(--rojo-oscuro);
        border: 1px solid var(--dorado);
    }

    /* Botón de Filtrado (Consistencia con el sistema) */
    .btn-filtrar-custom {
        background-color: var(--dorado);
        color: var(--rojo-oscuro);
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-filtrar-custom:hover {
        background-color: #dca72c;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(237, 189, 63, 0.3);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-user-tie me-2"></i> Encargados de Activos
    </h2>
    <a href="{{ route('encargados.create') }}" class="btn btn-nuevo shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Nuevo Encargado
    </a>
</div>

{{-- Panel de Filtros --}}
<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('encargados.index') }}" class="row g-3">

            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Nombre o Correo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $filtros['q'] ?? '' }}"
                        placeholder="Buscar por nombre o correo...">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="PERSONA" @selected(($filtros['tipo'] ?? '') === 'PERSONA')>PERSONA</option>
                    <option value="UNIDAD" @selected(($filtros['tipo'] ?? '') === 'UNIDAD')>UNIDAD</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" @selected(($filtros['estado'] ?? '') === '1')>Activo</option>
                    <option value="0" @selected(($filtros['estado'] ?? '') === '0')>Inactivo</option>
                </select>
            </div>

            <div class="col-md-3 d-flex justify-content-end gap-2 align-items-end">
                <a href="{{ route('encargados.index') }}" class="btn btn-light border text-muted">
                    <i class="fa-solid fa-broom me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-filtrar-custom px-4">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-auto" style="overflow-x:auto;">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <th>Nombre del Encargado</th>
                <th>Tipo</th>
                <th>Usuario Asociado</th>
                <th>Estado</th>
                <th class="text-center pe-4" style="width: 140px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($encargados as $e)
            <tr>
                <td>
                    <span class="fw-semibold text-dark">{{ $e->nombre }}</span>
                </td>
                <td>
                    <span class="badge badge-tipo">
                        <i class="fa-solid fa-tag me-1"></i> {{ $e->tipo }}
                    </span>
                </td>
                <td>
                    <span class="text-muted"><i class="fa-regular fa-envelope me-1"></i> {{ $e->correo }}</span>
                </td>
                <td>
                    @if($e->estado)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">
                        <i class="fa-solid fa-circle-check me-1"></i> Activo
                    </span>
                    @else
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                        <i class="fa-solid fa-ban me-1"></i> Inactivo
                    </span>
                    @endif
                </td>
                <td class="text-center pe-4">
                    <div class="btn-group shadow-sm" role="group">
                        <a href="{{ route('encargados.edit', $e) }}" class="btn btn-sm btn-light border" title="Editar">
                            <i class="fa-solid fa-pen" style="color: var(--dorado);"></i>
                        </a>

                        <form method="POST" action="{{ route('encargados.destroy', $e) }}" class="d-inline delete-encargado-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-light border text-danger btn-delete-encargado" title="Eliminar" data-name="{{ $e->nombre }}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-user-slash fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No se encontraron encargados con los filtros seleccionados.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $encargados->links() }}
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-delete-encargado');
            if (!btn) return;
            e.preventDefault();
            const name = btn.dataset.name || 'este encargado';
            const form = btn.closest('form');

            Swal.fire({
                title: '¿Eliminar encargado?',
                text: `¿Estás seguro de que deseas eliminar a ${name}? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (form) form.submit();
                }
            });
        });
    });
</script>

@endsection