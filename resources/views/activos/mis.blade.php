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
</style>

<div class="d-flex align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-laptop-file me-2"></i> Mis Activos
    </h2>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom mb-0">
        <thead>
            <tr>
                <th>Activo</th>
                <th>Código</th>
                <th>Tipo</th>
                <th>Asignado por</th>
                <th>Fecha de aceptación</th>
                <th class="text-center pe-4">Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asignaciones as $a)
            <tr>
                <td class="fw-semibold text-dark">{{ $a->activo?->nombre ?? 'Activo no disponible' }}</td>
                <td>{{ $a->activo?->codigo ?? 'N/A' }}</td>
                <td>{{ $a->activo?->tipo ?? 'N/A' }}</td>
                <td>{{ $a->usuarioAsignador?->nombre ?? 'N/A' }}</td>
                <td>
                    @if($a->fecha_respuesta)
                    {{ \Carbon\Carbon::parse($a->fecha_respuesta)->format('d/m/Y H:i') }}
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
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-box-open fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No tienes activos bajo tu responsabilidad actualmente.</p>
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
