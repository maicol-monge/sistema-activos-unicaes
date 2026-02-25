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
</style>

@php
$a = $asignacion;
$activo = $a->activo;

$fechaDevolucion = null;
if ($a->estado_asignacion === 'CARGADO' && $a->fecha_respuesta) {
$fechaDevolucion = \Carbon\Carbon::parse($a->fecha_respuesta)->format('d/m/Y H:i');
}
@endphp

<div class="d-flex align-items-center mb-4">
    <div>
        <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
            <i class="fa-solid fa-circle-info me-2"></i> Detalle de asignación
        </h2>
        <div class="text-muted" style="font-size: 0.95rem;">
            Información del activo y trazabilidad de la asignación.
        </div>
    </div>

    <div class="ms-auto">
        <a href="{{ route('asignaciones.index') }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
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
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card content-card">
            <div class="card-body p-3 p-md-4">
                <h5 class="mb-3" style="color: var(--rojo-principal); font-weight: 800;">
                    <i class="fa-solid fa-clipboard-list me-2" style="color: var(--dorado);"></i> Datos de la asignación
                </h5>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="kv-label">Encargado (asignado a)</div>
                        <div class="kv-value">{{ $a->usuarioAsignado?->nombre ?? '—' }}</div>
                        @if($a->usuarioAsignado?->correo)
                        <div class="text-muted" style="font-size: 0.85rem;">{{ $a->usuarioAsignado->correo }}</div>
                        @endif
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Asignado por</div>
                        <div class="kv-value">{{ $a->usuarioAsignador?->nombre ?? 'Sistema' }}</div>
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Estado de asignación</div>
                        <div class="kv-value">{{ $a->estado_asignacion ?? '—' }}</div>
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Fecha de asignación</div>
                        <div class="kv-value">
                            {{ $a->fecha_asignacion ? \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Fecha de devolución</div>
                        <div class="kv-value">{{ $fechaDevolucion ?? '—' }}</div>
                        <div class="text-muted" style="font-size: 0.85rem;">
                            Se completa cuando la asignación queda en DEVUELTO (CARGADO).
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="kv-label">Fecha de respuesta/cierre</div>
                        <div class="kv-value">
                            {{ $a->fecha_respuesta ? \Carbon\Carbon::parse($a->fecha_respuesta)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection