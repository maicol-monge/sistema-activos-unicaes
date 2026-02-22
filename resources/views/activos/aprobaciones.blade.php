@extends('layouts.app')

@section('title', 'Aprobaciones de Activos - UNICAES')

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

    .btn-aprobar {
        background-color: #198754;
        color: #fff;
        border: none;
    }

    .btn-aprobar:hover {
        background-color: #146c43;
        color: #fff;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-circle-check me-2"></i> Aprobación de Activos
    </h2>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Registrado por</th>
                <th>Fecha</th>
                <th class="text-center pe-4">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendientes as $activo)
            <tr>
                <td class="fw-semibold">{{ $activo->codigo }}</td>
                <td>
                    <div class="fw-semibold text-dark">{{ $activo->nombre }}</div>
                    <small class="text-muted">Tipo: {{ $activo->tipo }} | Condición: {{ $activo->condicion }}</small>
                </td>
                <td>{{ $activo->categoria?->nombre }}</td>
                <td>{{ $activo->registrador?->nombre }}</td>
                <td>{{ \Carbon\Carbon::parse($activo->fecha_registro)->format('d/m/Y') }}</td>
                <td class="text-center pe-4">
                    <div class="d-flex justify-content-center gap-2">
                        <form method="POST" action="{{ route('activos.aprobar', $activo) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-aprobar">
                                <i class="fa-solid fa-check me-1"></i> Aprobar
                            </button>
                        </form>

                        <button type="button" class="btn btn-sm btn-danger btn-rechazar" data-id="{{ $activo->id_activo }}">
                            <i class="fa-solid fa-xmark me-1"></i> Rechazar
                        </button>

                        <form id="form-rechazo-{{ $activo->id_activo }}" method="POST" action="{{ route('activos.rechazar', $activo) }}" class="d-none">
                            @csrf
                            <input type="hidden" name="observaciones" value="">
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-circle-check fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No hay activos pendientes por aprobar.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $pendientes->links() }}
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('.btn-rechazar');
            if (!btn) return;

            const activoId = btn.dataset.id;
            const form = document.getElementById(`form-rechazo-${activoId}`);
            if (!form) return;

            const {
                value: observaciones
            } = await Swal.fire({
                title: 'Rechazar activo',
                input: 'textarea',
                inputLabel: 'Observaciones (obligatorio)',
                inputPlaceholder: 'Escribe el motivo del rechazo...',
                inputAttributes: {
                    'aria-label': 'Escribe el motivo del rechazo'
                },
                showCancelButton: true,
                confirmButtonText: 'Rechazar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return 'La observación es obligatoria.';
                    }
                }
            });

            if (observaciones) {
                form.querySelector('input[name="observaciones"]').value = observaciones;
                form.submit();
            }
        });
    });
</script>

@endsection
