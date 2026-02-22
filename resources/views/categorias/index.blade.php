@extends('layouts.app')

@section('title', 'Categorías de Activos - UNICAES')

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
        <i class="fa-solid fa-tags me-2"></i> Categorías de Activos
    </h2>
    <a href="{{ route('categorias-activos.create') }}" class="btn btn-nuevo shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Nueva Categoría
    </a>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <th>Nombre</th>
                <th class="text-center">Estado</th>
                <th class="text-center pe-4">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categorias as $categoria)
            <tr>
                <td class="fw-semibold text-dark">{{ $categoria->nombre }}</td>
                <td class="text-center">
                    @if($categoria->estado)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">ACTIVA</span>
                    @else
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">INACTIVA</span>
                    @endif
                </td>
                <td class="text-center pe-4">
                    <div class="btn-group shadow-sm" role="group">
                        <a href="{{ route('categorias-activos.edit', $categoria) }}" class="btn btn-sm btn-light border" title="Editar">
                            <i class="fa-solid fa-pen" style="color: var(--dorado);"></i>
                        </a>
                        <form method="POST" action="{{ route('categorias-activos.destroy', $categoria) }}" class="d-inline delete-categoria-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-light border text-danger btn-delete-categoria" data-name="{{ $categoria->nombre }}" title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No hay categorías registradas.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $categorias->links() }}
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-delete-categoria');
            if (!btn) return;

            e.preventDefault();
            const name = btn.dataset.name || 'esta categoría';
            const form = btn.closest('form');

            Swal.fire({
                title: '¿Eliminar categoría?',
                text: `¿Deseas eliminar ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && form) {
                    form.submit();
                }
            });
        });
    });
</script>

@endsection
