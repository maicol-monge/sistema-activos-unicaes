@extends('layouts.app')

@section('title', 'Detalle de Asignación - UNICAES')

@section('content')

<style>
    .kv-label {
        color: #6c757d;
        font-weight: 700;
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }

    .kv-value {
        font-weight: 600;
        color: #212529;
    }

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
</style>

@php
$a = $asignacion;
$activo = $a->activo;
@endphp

<div class="d-flex align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
            <i class="fa-solid fa-circle-info me-2"></i> Detalle de asignación
        </h2>
        <div class="text-muted" style="font-size: 0.95rem;">
            Revisa la información del activo antes de aceptar o rechazar.
        </div>
    </div>

    <div class="ms-auto">
        <a href="{{ route('asignaciones.mis') }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a mis asignaciones
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card content-card">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0" style="color: var(--rojo-principal); font-weight: 800;">
                        <i class="fa-solid fa-box me-2" style="color: var(--dorado);"></i> Información del activo
                    </h5>
                    @if($activo)
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                        {{ $activo->tipo ?? '—' }}
                    </span>
                    @endif
                </div>

                @if(!$activo)
                <div class="alert alert-warning mb-0">
                    El activo asociado a esta asignación no está disponible.
                </div>
                @else
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="kv-label">Código</div>
                        <div class="kv-value">{{ $activo->codigo ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="kv-label">Serial</div>
                        <div class="kv-value">{{ $activo->serial ?? '—' }}</div>
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Nombre</div>
                        <div class="kv-value">{{ $activo->nombre ?? '—' }}</div>
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Descripción</div>
                        <div class="kv-value">{{ $activo->descripcion ?? '—' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="kv-label">Marca</div>
                        <div class="kv-value">{{ $activo->marca ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="kv-label">Categoría</div>
                        <div class="kv-value">{{ $activo->categoria?->nombre ?? '—' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="kv-label">Condición</div>
                        <div class="kv-value">{{ $activo->condicion ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="kv-label">Estado del activo</div>
                        <div class="kv-value">{{ $activo->estado ?? '—' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="kv-label">Fecha de adquisición</div>
                        <div class="kv-value">
                            {{ $activo->fecha_adquisicion ? \Carbon\Carbon::parse($activo->fecha_adquisicion)->format('d/m/Y') : '—' }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="kv-label">Valor de compra</div>
                        <div class="kv-value">
                            {{ is_null($activo->valor_compra) ? '—' : '$ ' . number_format((float)$activo->valor_compra, 2) }}
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Observaciones</div>
                        <div class="kv-value">{{ $activo->observaciones ?? '—' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="kv-label">Registrado por</div>
                        <div class="kv-value">{{ $activo->registrador?->nombre ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="kv-label">Aprobado por</div>
                        <div class="kv-value">{{ $activo->aprobador?->nombre ?? '—' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="kv-label">Fecha de registro</div>
                        <div class="kv-value">
                            {{ $activo->fecha_registro ? \Carbon\Carbon::parse($activo->fecha_registro)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card content-card">
            <div class="card-body p-3 p-md-4">
                <h5 class="mb-3" style="color: var(--rojo-principal); font-weight: 800;">
                    <i class="fa-solid fa-clipboard-list me-2" style="color: var(--dorado);"></i> Información de asignación
                </h5>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="kv-label">Estado de asignación</div>
                        <div class="kv-value">
                            @if($a->estado_asignacion === 'PENDIENTE')
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">
                                <i class="fa-regular fa-clock me-1"></i> PENDIENTE
                            </span>
                            @elseif($a->estado_asignacion === 'ACEPTADO')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                <i class="fa-solid fa-check-double me-1"></i> ACEPTADO
                            </span>
                            @elseif($a->estado_asignacion === 'RECHAZADO')
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                <i class="fa-solid fa-xmark me-1"></i> RECHAZADO
                            </span>
                            @elseif($a->estado_asignacion === 'DEVOLUCION')
                            <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                <i class="fa-solid fa-rotate-left me-1"></i> DEVOLUCIÓN
                            </span>
                            @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                {{ $a->estado_asignacion }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Asignado por</div>
                        <div class="kv-value">{{ $a->usuarioAsignador?->nombre ?? '—' }}</div>
                        @if($a->usuarioAsignador?->correo)
                        <div class="text-muted" style="font-size: 0.85rem;">{{ $a->usuarioAsignador->correo }}</div>
                        @endif
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Fecha de asignación</div>
                        <div class="kv-value">
                            {{ $a->fecha_asignacion ? \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Fecha de respuesta</div>
                        <div class="kv-value">
                            {{ $a->fecha_respuesta ? \Carbon\Carbon::parse($a->fecha_respuesta)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                @if($a->estado_asignacion === 'PENDIENTE')
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('asignaciones.aceptar', $a) }}" class="m-0">
                        @csrf
                        <button type="button" class="btn btn-aceptar fw-bold swal-aceptar" data-message="¿Confirmas que has recibido y aceptas la responsabilidad de este activo?">
                            <i class="fa-solid fa-check me-1"></i> Aceptar
                        </button>
                    </form>

                    <form method="POST" action="{{ route('asignaciones.rechazar', $a) }}" class="m-0">
                        @csrf
                        <button type="button" class="btn btn-rechazar fw-bold swal-rechazar" data-message="¿Estás seguro de rechazar esta asignación?">
                            <i class="fa-solid fa-xmark me-1"></i> Rechazar
                        </button>
                    </form>
                </div>
                @else
                <div class="text-muted">
                    <i class="fa-solid fa-lock me-1"></i> Esta asignación ya fue respondida.
                </div>
                @endif
            </div>
        </div>
    </div>
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
    });
</script>
@endpush