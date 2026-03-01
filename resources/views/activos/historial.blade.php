@extends('layouts.app')

@section('title', 'Historial de Activo - UNICAES')

@section('content')

@php
    $asignadoActualNombre = $asignacionActual?->usuarioAsignado?->nombre ?? null;
    $estadoAsignacionActual = $asignacionActual?->estado_asignacion ?? null;
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-clock-rotate-left me-2"></i> Historial del Activo
    </h2>
    <div class="d-flex gap-2">

        {{-- Botón que abre el modal de previsualización --}}
        <button type="button"
                class="btn btn-light"
                style="color: var(--rojo-principal); font-weight: 700;"
                data-bs-toggle="modal"
                data-bs-target="#modalPdfPreview"
                data-preview-url="{{ route('activos.historial.pdf.preview', $activo) }}"
                data-download-url="{{ route('activos.historial.pdf', $activo) }}">
            <i class="fa-solid fa-file-pdf me-1"></i> Ver / Descargar PDF
        </button>

        <a href="{{ route('activos.index') }}" class="btn btn-light border">
            <i class="fa-solid fa-arrow-left-long me-1"></i> Volver a Activos
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-5">
        <div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
            <div class="card-body">
                <h5 class="card-title mb-3" style="color: var(--rojo-principal); font-weight: 700;">
                    <i class="fa-solid fa-box-open me-2"></i> Detalles del Activo
                </h5>
                <p class="mb-1"><strong>Código:</strong> {{ $activo->codigo }}</p>
                <p class="mb-1"><strong>Nombre:</strong> {{ $activo->nombre }}</p>
                <p class="mb-1"><strong>Tipo:</strong> {{ $activo->tipo }}</p>
                <p class="mb-1"><strong>Categoría:</strong> {{ $activo->categoria?->nombre ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Condición:</strong> {{ $activo->condicion }}</p>
                <p class="mb-1"><strong>Estado actual:</strong> {{ $activo->estado }}</p>
                <p class="mb-1"><strong>Valor de compra:</strong> ${{ number_format($activo->valor_compra, 2) }}</p>
                <p class="mb-1"><strong>Fecha de adquisición:</strong> {{ \Carbon\Carbon::parse($activo->fecha_adquisicion)->format('d/m/Y') }}</p>
                <p class="mb-1"><strong>Registrado por:</strong> {{ $activo->registrador?->nombre ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Aprobado por:</strong> {{ $activo->aprobador?->nombre ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-top: 4px solid var(--dorado); border-radius: 8px;">
            <div class="card-body">
                <h5 class="card-title mb-3" style="color: var(--rojo-principal); font-weight: 700;">
                    <i class="fa-solid fa-user-tag me-2"></i> Asignación actual
                </h5>
                @if($asignadoActualNombre)
                    <p class="mb-1"><strong>Asignado a:</strong> {{ $asignadoActualNombre }}</p>
                    @if($estadoAsignacionActual === 'ACEPTADO')
                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                            Hay una asignación ACEPTADA y activa para este usuario.
                        </p>
                    @elseif($estadoAsignacionActual === 'DEVOLUCION')
                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                            El usuario ha solicitado la devolución, pero el activo sigue a su cargo hasta que el administrador la apruebe o rechace.
                        </p>
                    @else
                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                            El activo se considera actualmente asignado a este usuario.
                        </p>
                    @endif
                @else
                    <p class="mb-0 text-muted">El activo no tiene una asignación aceptada y activa en este momento.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <h5 class="card-title mb-3" style="color: var(--rojo-principal); font-weight: 700;">
            <i class="fa-solid fa-people-arrows me-2"></i> Historial de Asignaciones
        </h5>

        @if($asignaciones->isEmpty())
            <p class="text-muted mb-0">No hay asignaciones registradas para este activo.</p>
        @else
            <div class="table-responsive">
                @php
                    $tieneMotivos = $asignaciones->contains(fn($a) => !empty($a->motivo_devolucion));
                @endphp
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha de asignación</th>
                            <th>Asignado a</th>
                            <th>Asignado por</th>
                            <th>Estado de asignación</th>
                            <th>Fecha de respuesta</th>
                            @if($tieneMotivos)
                                <th>Motivo devolución</th>
                            @endif
                            <th>Activo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asignaciones as $asignacion)
                            <tr>
                                <td>{{ $asignacion->fecha_asignacion ? \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>{{ $asignacion->usuarioAsignado?->nombre ?? 'N/A' }}</td>
                                <td>{{ $asignacion->usuarioAsignador?->nombre ?? 'N/A' }}</td>
                                <td>{{ $asignacion->estado_asignacion }}</td>
                                <td>
                                    @if($asignacion->fecha_respuesta)
                                        {{ \Carbon\Carbon::parse($asignacion->fecha_respuesta)->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                @if($tieneMotivos)
                                    <td>
                                        @if($asignacion->motivo_devolucion)
                                            <span class="badge text-wrap text-start fw-normal"
                                                style="background-color: #fff3cd; color: #856404;
                                                        border: 1px solid #ffc107; max-width: 220px;
                                                        white-space: normal; line-height: 1.4;">
                                                <i class="fa-solid fa-comment-dots me-1"></i>
                                                {{ $asignacion->motivo_devolucion }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td>{{ $asignacion->estado ? 'Sí' : 'No' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--dorado); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <h5 class="card-title mb-3" style="color: var(--rojo-principal); font-weight: 700;">
            <i class="fa-solid fa-arrows-rotate me-2"></i> Historial de Movimientos
        </h5>

        @if($movimientos->isEmpty())
            <p class="text-muted mb-0">No hay movimientos registrados para este activo.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Realizado por</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movimientos as $mov)
                            <tr>
                                <td>{{ $mov->fecha ? \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $mov->tipo }}</td>
                                <td>{{ $mov->usuario?->nombre ?? 'N/A' }}</td>
                                <td>{{ $mov->observaciones }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
{{-- ======================== MODAL PREVISUALIZACIÓN PDF ======================== --}}
<div class="modal fade" id="modalPdfPreview" tabindex="-1"
     aria-labelledby="modalPdfPreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px; overflow: hidden;">

            {{-- Header --}}
            <div class="modal-header text-white"
                 style="background-color: var(--rojo-principal);">
                <h5 class="modal-title fw-bold" id="modalPdfPreviewLabel">
                    <i class="fa-solid fa-file-pdf me-2"></i>
                    Vista previa — Historial del Activo
                    <span class="fw-normal opacity-75 ms-1">({{ $activo->codigo }})</span>
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- Body con iframe --}}
            <div class="modal-body p-0 position-relative">

                {{-- Spinner mientras carga el PDF --}}
                <div id="pdfLoadingSpinner"
                     class="position-absolute top-50 start-50 translate-middle text-center"
                     style="z-index: 10;">
                    <div class="spinner-border mb-2"
                         style="color: var(--rojo-principal); width: 3rem; height: 3rem;"
                         role="status"></div>
                    <p class="text-muted small">Cargando vista previa...</p>
                </div>

                {{-- iframe del PDF --}}
                <iframe id="pdfPreviewFrame"
                        src=""
                        style="width: 100%; height: 80vh; border: none; opacity: 0; transition: opacity 0.3s ease;"
                        title="Vista previa PDF">
                </iframe>
            </div>

            {{-- Footer con botón de descarga --}}
            <div class="modal-footer"
                 style="background-color: #f8f9fa; border-top: 2px solid var(--rojo-principal);">
                <span class="text-muted small me-auto">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Revisa el reporte antes de descargarlo.
                </span>
                <button type="button" class="btn btn-light border"
                        data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Cerrar
                </button>
                <a id="btnDescargarPdf"
                   href="{{ route('activos.historial.pdf', $activo) }}"
                   class="btn btn-danger fw-bold">
                    <i class="fa-solid fa-download me-1"></i> Descargar PDF
                </a>
            </div>

        </div>
    </div>
</div>

{{-- ======================== SCRIPT DEL MODAL ======================== --}}
@push('scripts')
<script>
    const modalEl = document.getElementById('modalPdfPreview');

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button       = event.relatedTarget;
        const previewUrl   = button.getAttribute('data-preview-url');
        const downloadUrl  = button.getAttribute('data-download-url');

        const iframe  = document.getElementById('pdfPreviewFrame');
        const spinner = document.getElementById('pdfLoadingSpinner');
        const btnDesc = document.getElementById('btnDescargarPdf');

        // Resetear estado
        iframe.style.opacity = '0';
        spinner.style.display = 'block';
        iframe.src = '';

        // Actualizar URL de descarga
        btnDesc.href = downloadUrl;

        // Cargar PDF en el iframe
        iframe.src = previewUrl;

        // Mostrar iframe cuando termine de cargar
        iframe.onload = function () {
            spinner.style.display = 'none';
            iframe.style.opacity  = '1';
        };
    });

    // Limpiar iframe al cerrar para liberar memoria
    modalEl.addEventListener('hidden.bs.modal', function () {
        const iframe = document.getElementById('pdfPreviewFrame');
        iframe.src   = '';
        iframe.style.opacity = '0';
        document.getElementById('pdfLoadingSpinner').style.display = 'block';
    });
</script>
@endpush

@endsection
