@extends('layouts.app')

@section('title', 'Mis Asignaciones - UNICAES')

@section('content')

<style>
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

    /* Badges de estado */
    .badge-estado {
        font-size: 0.85em;
        padding: 0.5em 0.8em;
    }

    /* Botones de acción del encargado */
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

    /* Resaltar visualmente las filas pendientes */
    .fila-pendiente {
        background-color: rgba(237, 189, 63, 0.05);
        /* Un fondo doradito muy suave */
        border-left: 4px solid var(--dorado);
    }
</style>

<div class="d-flex align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-laptop-file me-2"></i> Mis Asignaciones
    </h2>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom mb-0">
        <thead>
            <tr>
                <th>Activo Asignado</th>
                <th class="text-center">Estado Actual</th>
                <th>Fecha de Asignación</th>
                <th>Fecha de Respuesta</th>
                <th class="text-center pe-4" style="min-width: 180px;">Mi Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asignaciones as $a)
            <tr class="{{ $a->estado_asignacion === 'PENDIENTE' ? 'fila-pendiente' : '' }}">

                <td>
                    <span class="fw-semibold text-dark">
                        <i class="fa-solid fa-box me-1" style="color: var(--dorado);"></i>
                        {{ $a->activo?->nombre ?? 'Activo no disponible' }}
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
                    <div class="text-muted" style="font-size: 0.9em;">
                        <i class="fa-regular fa-calendar-plus me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y') }}
                        <br>
                        <i class="fa-regular fa-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_asignacion)->format('H:i') }}
                    </div>
                </td>

                <td>
                    @if($a->fecha_respuesta)
                    <div class="text-muted" style="font-size: 0.9em;">
                        <i class="fa-regular fa-calendar-check me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_respuesta)->format('d/m/Y') }}
                        <br>
                        <i class="fa-regular fa-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($a->fecha_respuesta)->format('H:i') }}
                    </div>
                    @else
                    <span class="text-muted fst-italic" style="font-size: 0.9em;">— Esperando —</span>
                    @endif
                </td>

                <td class="text-center pe-4">
                    @if($a->estado_asignacion === 'PENDIENTE')
                    <div class="d-flex justify-content-center gap-2">
                        <form method="POST" action="{{ route('asignaciones.aceptar', $a) }}" class="m-0">
                            @csrf
                            <button type="button" class="btn btn-sm btn-aceptar fw-bold shadow-sm swal-aceptar" data-message="¿Confirmas que has recibido y aceptas la responsabilidad de este activo?">
                                <i class="fa-solid fa-check me-1"></i> Aceptar
                            </button>
                        </form>

                        <form method="POST" action="{{ route('asignaciones.rechazar', $a) }}" class="m-0">
                            @csrf
                            <button type="button" class="btn btn-sm btn-rechazar fw-bold swal-rechazar" data-message="¿Estás seguro de rechazar esta asignación? Deberás justificarlo con el administrador.">
                                <i class="fa-solid fa-xmark me-1"></i> Rechazar
                            </button>
                        </form>
                    </div>
                    @else
                    <span class="text-muted" style="font-size: 0.85em;">
                        <i class="fa-solid fa-lock me-1"></i> Acción registrada
                    </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-mug-hot fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">Excelente, no tienes asignaciones pendientes por revisar.</p>
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
        function attachSwal(selector, options) {
            document.querySelectorAll(selector).forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const message = btn.getAttribute('data-message') || options.text;
                    Swal.fire({
                        title: options.title || '¿Confirmar?',
                        text: message,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No',
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = btn.closest('form');
                            if (form) form.submit();
                        }
                    });
                });
            });
        }

        attachSwal('.swal-aceptar', {
            title: 'Aceptar asignación'
        });
        attachSwal('.swal-rechazar', {
            title: 'Rechazar asignación'
        });
    });
</script>
@endpush