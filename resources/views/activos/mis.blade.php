@extends('layouts.app')

@section('title', 'Mis Activos - UNICAES')

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

    .btn-devolver {
        background-color: transparent;
        color: #0d6efd;
        border: 1px solid #0d6efd;
        transition: all 0.3s ease;
    }

    .btn-devolver:hover {
        background-color: #0d6efd;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
    }

    /* Botón de Filtrado (Dorado para consistencia) */
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

<div class="d-flex align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-laptop-file me-2"></i> Mis Activos
    </h2>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('activos.mis') }}" class="row g-3">

            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Búsqueda de Activo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $filtros['q'] ?? '' }}"
                        placeholder="Nombre, código o serie...">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Categoría</label>
                <select name="id_categoria_activo" class="form-select">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id_categoria_activo }}" @selected(($filtros['id_categoria_activo'] ?? '' ) == $cat->id_categoria_activo)>
                        {{ $cat->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-muted fw-bold mb-1">Tipo de Activo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="FIJO" @selected(($filtros['tipo'] ?? '') === 'FIJO')>FIJO</option>
                    <option value="INTANGIBLE" @selected(($filtros['tipo'] ?? '') === 'INTANGIBLE')>INTANGIBLE</option>
                </select>
            </div>

            <div class="col-md-3 d-flex justify-content-end gap-2 align-items-end">
                <a href="{{ route('activos.mis') }}" class="btn btn-light border text-muted">
                    <i class="fa-solid fa-broom me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-filtrar-custom px-4">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom mb-0">
        <thead>
            <tr>
                <th>Activo</th>
                <th>Código</th>
                <th>Categoría</th>
                <th>Tipo</th>
                <th>Asignado por</th>
                <th>Fecha de Aceptación</th>
                <th class="text-center pe-4">Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asignaciones as $a)
            <tr>
                <td class="fw-semibold text-dark">{{ $a->activo?->nombre ?? 'Activo no disponible' }}</td>
                <td>
                    <span class="badge bg-light text-dark border">{{ $a->activo?->codigo ?? 'N/A' }}</span>
                </td>
                <td>
                    <span class="text-muted small fw-bold">
                        {{ $a->activo?->categoria?->nombre ?? 'Sin categoría' }}
                    </span>
                </td>
                <td>{{ $a->activo?->tipo ?? 'N/A' }}</td>
                <td>{{ $a->usuarioAsignador?->nombre ?? 'N/A' }}</td>
                <td>
                    @if($a->fecha_respuesta)
                    <span class="text-muted small">
                        <i class="fa-regular fa-calendar-check me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_respuesta)->format('d/m/Y H:i') }}
                    </span>
                    @else
                    —
                    @endif
                </td>
                <td class="text-center pe-4">
                    <form method="POST" action="{{ route('asignaciones.devolver', $a) }}" class="m-0">
                        @csrf
                        <button type="button" class="btn btn-sm btn-devolver fw-bold swal-devolver" data-message="¿Confirmas la devolución de este activo? Esta acción cerrará tu asignación activa.">
                            <i class="fa-solid fa-rotate-left me-1"></i> Devolver
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-box-open fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No se encontraron activos con los filtros seleccionados.</p>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.swal-devolver').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = btn.closest('form');
                const message = btn.getAttribute('data-message') || '¿Deseas devolver este activo?';

                Swal.fire({
                    title: 'Devolver activo',
                    text: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, devolver',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed && form) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
