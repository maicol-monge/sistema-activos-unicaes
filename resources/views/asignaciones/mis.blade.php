@extends('layouts.app')

@section('title', 'Mis Asignaciones - UNICAES')

@section('content')

<style>
    /* Estilos de Tabla Uniformes */
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

    /* Botones de Acción con Efectos */
    .btn-aceptar {
        background-color: #198754;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-aceptar:hover {
        background-color: #146c43;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(25, 135, 84, 0.3);
    }

    .btn-rechazar {
        background-color: transparent;
        color: #dc3545;
        border: 1px solid #dc3545;
        transition: all 0.3s ease;
    }

    .btn-rechazar:hover {
        background-color: #dc3545;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
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

    .btn-detalle {
        background-color: transparent;
        color: var(--rojo-principal);
        border: 1px solid var(--rojo-principal);
        transition: all 0.3s ease;
    }

    .btn-detalle:hover {
        background-color: var(--rojo-principal);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(126, 0, 1, 0.18);
    }

    /* Estilo para Fila Pendiente */
    .fila-pendiente {
        background-color: rgba(237, 189, 63, 0.03);
        border-left: 4px solid var(--dorado) !important;
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
        <i class="fa-solid fa-laptop-file me-2"></i> Mis Asignaciones
    </h2>
    <div class="ms-auto">
        <a href="{{ route('asignaciones.create') }}" class="btn btn-filtrar-custom">
            <i class="fa-solid fa-link me-1"></i> Nueva Asignación
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('asignaciones.mis') }}" class="row g-3">

            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Búsqueda de Activo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $filtros['q'] ?? '' }}"
                        placeholder="Nombre, código o serie del activo...">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Estado de Asignación</label>
                <select name="estado_asignacion" class="form-select">
                    <option value="" @selected(($filtros['estado_asignacion'] ?? 'PENDIENTE' )==='' )>Todos los estados</option>
                    @foreach(['PENDIENTE', 'ACEPTADO', 'RECHAZADO', 'DEVOLUCION', 'BAJA'] as $estado)
                    <option value="{{ $estado }}" @selected(($filtros['estado_asignacion'] ?? 'PENDIENTE' )===$estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-4 d-flex justify-content-end gap-2 pt-2 align-items-end">
                <a href="{{ route('asignaciones.mis') }}" class="btn btn-light border text-muted">
                    <i class="fa-solid fa-broom me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-filtrar-custom px-4">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar Asignaciones
                </button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom mb-0">
        <thead>
            <tr>
                <th class="ps-4">Activo Asignado</th>
                <th class="text-center">Estado Actual</th>
                <th>Fecha Asignación</th>
                <th>Fecha Respuesta</th>
                <th class="text-center pe-4">Mi Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asignaciones as $a)
            <tr class="{{ $a->estado_asignacion === 'PENDIENTE' ? 'fila-pendiente' : '' }}">
                <td class="ps-4">
                    <div class="fw-bold text-dark">
                        <i class="fa-solid fa-box me-1" style="color: var(--dorado);"></i>
                        {{ $a->activo?->nombre ?? 'Activo no disponible' }}
                    </div>
                    <small class="text-muted text-uppercase" style="font-size: 0.75rem;">{{ $a->activo?->codigo }}</small>
                </td>

                <td class="text-center">
                    @if($a->estado_asignacion === 'PENDIENTE')
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning" style="font-size: 0.8rem;">
                        <i class="fa-regular fa-clock me-1"></i> PENDIENTE
                    </span>
                    @elseif($a->estado_asignacion === 'ACEPTADO')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-check-double me-1"></i> ACEPTADO
                    </span>
                    @elseif($a->estado_asignacion === 'RECHAZADO')
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-xmark me-1"></i> RECHAZADO
                    </span>
                    @elseif($a->estado_asignacion === 'DEVOLUCION')
                    <span class="badge bg-info bg-opacity-10 text-info border border-info" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-rotate-left me-1"></i> DEVOLUCIÓN PENDIENTE
                    </span>
                    @elseif($a->estado_asignacion === 'CARGADO')
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-box-archive me-1"></i> DEVUELTO (CERRADO)
                    </span>
                    @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size: 0.8rem;">
                        {{ $a->estado_asignacion }}
                    </span>
                    @endif
                </td>

                <td>
                    <div class="text-muted" style="font-size: 0.85em;">
                        <i class="fa-regular fa-calendar-plus me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y') }}
                        <br>
                        <i class="fa-regular fa-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_asignacion)->format('H:i') }}
                    </div>
                </td>

                <td>
                    @if($a->fecha_respuesta)
                    <div class="text-muted" style="font-size: 0.85em;">
                        <i class="fa-regular fa-calendar-check me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_respuesta)->format('d/m/Y') }}
                        <br>
                        <i class="fa-regular fa-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_respuesta)->format('H:i') }}
                    </div>
                    @else
                    <span class="text-muted fst-italic" style="font-size: 0.85em;">Pendiente...</span>
                    @endif
                </td>

                <td class="text-center pe-4">
                    @if($a->estado_asignacion === 'PENDIENTE')
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('asignaciones.detalle', $a) }}" class="btn btn-sm btn-detalle fw-bold" title="Ver detalle">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <form method="POST" action="{{ route('asignaciones.aceptar', $a) }}" class="m-0">
                            @csrf
                            <button type="button" class="btn btn-sm btn-aceptar fw-bold swal-aceptar" title="Aceptar" data-message="¿Confirmas que has recibido y aceptas la responsabilidad de este activo?">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('asignaciones.rechazar', $a) }}" class="m-0">
                            @csrf
                            <button type="button" class="btn btn-sm btn-rechazar fw-bold swal-rechazar" title="Rechazar" data-message="¿Estás seguro de rechazar esta asignación?">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </form>
                    </div>
                    @elseif($a->estado_asignacion === 'ACEPTADO' && (int)$a->estado === 1)
                    <form method="POST" action="{{ route('asignaciones.devolver', $a) }}" class="m-0">
                        @csrf
                        <button type="button" class="btn btn-sm btn-devolver fw-bold swal-devolver" data-message="¿Confirmas la devolución de este activo?">
                            <i class="fa-solid fa-rotate-left me-1"></i> Devolver
                        </button>
                    </form>
                    @elseif($a->estado_asignacion === 'DEVOLUCION' && (int)$a->estado === 1)
                    <span class="text-muted small">
                        <i class="fa-solid fa-hourglass-half me-1"></i> Devolución en revisión del administrador
                    </span>
                    @else
                    <span class="text-muted small">
                        <i class="fa-solid fa-lock me-1"></i> Finalizado
                    </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-filter-circle-xmark fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No se encontraron asignaciones registradas.</p>
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
        function bindSwalSubmit(selector, title, confirmText, icon = 'question') {
            document.querySelectorAll(selector).forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    if (!form) return;

                    const message = this.dataset.message || '';

                    Swal.fire({
                        title: title,
                        text: message,
                        icon: icon,
                        showCancelButton: true,
                        confirmButtonColor: '#7e0001',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: confirmText,
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        }

        bindSwalSubmit('.swal-aceptar', '¿Aceptar asignación?', 'Sí, aceptar', 'question');
        bindSwalSubmit('.swal-rechazar', '¿Rechazar asignación?', 'Sí, rechazar', 'warning');
        bindSwalSubmit('.swal-devolver', '¿Devolver activo?', 'Sí, devolver', 'info');
    });
</script>
@endpush