@extends('layouts.app')

@section('title', 'Gestión de Usuarios - UNICAES')

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

    .pagination {
        margin-bottom: 0;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-users-gear me-2"></i> Usuarios
    </h2>
    <a href="{{ route('users.create') }}" class="btn btn-nuevo shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Nuevo Usuario
    </a>
</div>

@if(session('err'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #dc3545;">
    <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('err') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-auto" style="overflow-x:auto;">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <!-- <th class="ps-4">ID</th> -->
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th class="text-center pe-4">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr>
                <!-- <td class="ps-4 fw-bold text-muted">#{{ $u->id_usuario }}</td> -->
                <td>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex justify-content-center align-items-center me-3"
                            style="width: 35px; height: 35px; background-color: rgba(126, 0, 1, 0.1); color: var(--rojo-principal); font-weight: bold;">
                            {{ substr($u->nombre, 0, 1) }}
                        </div>
                        <span class="fw-semibold text-dark">{{ $u->nombre }}</span>
                    </div>
                </td>
                <td class="text-muted">{{ $u->correo }}</td>
                <td>
                    <span class="badge" style="background-color: #e9ecef; color: var(--rojo-oscuro); border: 1px solid #ced4da;">
                        <i class="fa-solid fa-id-badge me-1"></i> {{ $u->rol }}
                    </span>
                </td>
                <td>
                    @if($u->estado)
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
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-light border" title="Editar">
                            <i class="fa-solid fa-pen" style="color: var(--dorado);"></i>
                        </a>

                        <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline delete-user-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-light border text-danger btn-delete-user" title="Eliminar" data-name="{{ $u->nombre }}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No hay usuarios registrados aún.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $users->links() }}
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-delete-user');
            if (!btn) return;
            e.preventDefault();
            const name = btn.dataset.name || 'este usuario';
            const form = btn.closest('form');

            Swal.fire({
                title: '¿Eliminar usuario?',
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