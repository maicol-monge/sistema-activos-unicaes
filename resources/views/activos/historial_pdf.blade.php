<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2d2d2d;
        }

        /* ── ENCABEZADO ── */
        .header {
            background-color: #8B0000;
            color: white;
            padding: 18px 24px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header p {
            font-size: 10px;
            margin-top: 3px;
            opacity: 0.85;
        }
        .header-meta {
            font-size: 9px;
            margin-top: 6px;
            opacity: 0.75;
        }

        /* ── SECCIONES ── */
        .section {
            margin: 0 24px 16px 24px;
        }

        .section-title {
            background-color: #8B0000;
            color: white;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px 4px 0 0;
        }

        .section-title.gold {
            background-color: #C9A84C;
        }

        .section-body {
            border: 1px solid #ddd;
            border-top: none;
            padding: 12px;
            border-radius: 0 0 4px 4px;
            background-color: #fafafa;
        }

        /* ── GRID DE DETALLES ── */
        .detail-grid {
            width: 100%;
        }
        .detail-grid td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
            width: 40%;
        }
        .detail-value {
            color: #222;
        }

        /* ── TABLAS ── */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table.data-table thead tr {
            background-color: #8B0000;
            color: white;
        }
        table.data-table thead tr.gold {
            background-color: #C9A84C;
        }
        table.data-table th {
            padding: 7px 10px;
            text-align: left;
            font-weight: bold;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f5f0f0;
        }
        table.data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        table.data-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e9e9e9;
        }

        /* ── BADGE DE ESTADO ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }
        .badge-aceptado   { background-color: #28a745; }
        .badge-pendiente  { background-color: #ffc107; color: #333; }
        .badge-devolucion { background-color: #fd7e14; }
        .badge-rechazado  { background-color: #dc3545; }

        /* ── PIE DE PÁGINA ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0; right: 0;
            background-color: #8B0000;
            color: white;
            text-align: center;
            font-size: 9px;
            padding: 6px;
        }

        .empty-msg {
            color: #999;
            font-style: italic;
            padding: 8px 0;
        }

        .two-col { width: 48%; display: inline-block; vertical-align: top; }
        .gap { width: 4%; display: inline-block; }
    </style>
</head>
<body>

{{-- PIE FIJO --}}
<div class="footer">
    UNICAES &mdash; Sistema de Gestión de Activos &bull;
    Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
</div>

{{-- ENCABEZADO --}}
<div class="header">
    <h1><i>&#128203;</i> Historial del Activo</h1>
    <p>Universidad Católica de El Salvador &mdash; UNICAES</p>
    <div class="header-meta">
        Código: {{ $activo->codigo }} &bull;
        Generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</div>

{{-- DETALLES DEL ACTIVO --}}
<div class="section">
    <div class="section-title">&#128230; Detalles del Activo</div>
    <div class="section-body">
        <table class="detail-grid">
            <tr>
                <td class="detail-label">Código:</td>
                <td class="detail-value">{{ $activo->codigo }}</td>
                <td class="detail-label">Nombre:</td>
                <td class="detail-value">{{ $activo->nombre }}</td>
            </tr>
            <tr>
                <td class="detail-label">Tipo:</td>
                <td class="detail-value">{{ $activo->tipo }}</td>
                <td class="detail-label">Categoría:</td>
                <td class="detail-value">{{ $activo->categoria?->nombre ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="detail-label">Condición:</td>
                <td class="detail-value">{{ $activo->condicion }}</td>
                <td class="detail-label">Estado actual:</td>
                <td class="detail-value">{{ $activo->estado }}</td>
            </tr>
            <tr>
                <td class="detail-label">Valor de compra:</td>
                <td class="detail-value">${{ number_format($activo->valor_compra, 2) }}</td>
                <td class="detail-label">Fecha de adquisición:</td>
                <td class="detail-value">{{ \Carbon\Carbon::parse($activo->fecha_adquisicion)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="detail-label">Registrado por:</td>
                <td class="detail-value">{{ $activo->registrador?->nombre ?? 'N/A' }}</td>
                <td class="detail-label">Aprobado por:</td>
                <td class="detail-value">{{ $activo->aprobador?->nombre ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
</div>

{{-- ASIGNACIÓN ACTUAL --}}
@php
    $asignadoActualNombre = $asignacionActual?->usuarioAsignado?->nombre ?? null;
    $estadoAsignacionActual = $asignacionActual?->estado_asignacion ?? null;
@endphp
<div class="section">
    <div class="section-title gold">&#128100; Asignación Actual</div>
    <div class="section-body">
        @if($asignadoActualNombre)
            <table class="detail-grid">
                <tr>
                    <td class="detail-label">Asignado a:</td>
                    <td class="detail-value">{{ $asignadoActualNombre }}</td>
                    <td class="detail-label">Estado:</td>
                    <td class="detail-value">
                        @php
                            $badgeClass = match($estadoAsignacionActual) {
                                'ACEPTADO'   => 'badge-aceptado',
                                'DEVOLUCION' => 'badge-devolucion',
                                'PENDIENTE'  => 'badge-pendiente',
                                default      => 'badge-rechazado',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $estadoAsignacionActual }}</span>
                    </td>
                </tr>
            </table>
        @else
            <p class="empty-msg">El activo no tiene una asignación aceptada y activa en este momento.</p>
        @endif
    </div>
</div>

{{-- HISTORIAL DE ASIGNACIONES --}}
<div class="section">
    <div class="section-title">&#128101; Historial de Asignaciones</div>
    <div class="section-body" style="padding: 0;">
        @if($asignaciones->isEmpty())
            <p class="empty-msg" style="padding: 10px 12px;">No hay asignaciones registradas.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha Asignación</th>
                        <th>Asignado a</th>
                        <th>Asignado por</th>
                        <th>Estado</th>
                        <th>Fecha Respuesta</th>
                        <th>Activo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asignaciones as $asignacion)
                        <tr>
                            <td>{{ $asignacion->fecha_asignacion
                                ? \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y H:i')
                                : 'N/A' }}</td>
                            <td>{{ $asignacion->usuarioAsignado?->nombre ?? 'N/A' }}</td>
                            <td>{{ $asignacion->usuarioAsignador?->nombre ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $bc = match($asignacion->estado_asignacion) {
                                        'ACEPTADO'   => 'badge-aceptado',
                                        'DEVOLUCION' => 'badge-devolucion',
                                        'PENDIENTE'  => 'badge-pendiente',
                                        default      => 'badge-rechazado',
                                    };
                                @endphp
                                <span class="badge {{ $bc }}">{{ $asignacion->estado_asignacion }}</span>
                            </td>
                            <td>{{ $asignacion->fecha_respuesta
                                ? \Carbon\Carbon::parse($asignacion->fecha_respuesta)->format('d/m/Y H:i')
                                : '-' }}</td>
                            <td>{{ $asignacion->estado ? 'Sí' : 'No' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- HISTORIAL DE MOVIMIENTOS --}}
<div class="section">
    <div class="section-title gold">&#128260; Historial de Movimientos</div>
    <div class="section-body" style="padding: 0;">
        @if($movimientos->isEmpty())
            <p class="empty-msg" style="padding: 10px 12px;">No hay movimientos registrados.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr class="gold">
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Realizado por</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimientos as $mov)
                        <tr>
                            <td>{{ $mov->fecha
                                ? \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y')
                                : 'N/A' }}</td>
                            <td>{{ $mov->tipo }}</td>
                            <td>{{ $mov->usuario?->nombre ?? 'N/A' }}</td>
                            <td>{{ $mov->observaciones }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

</body>
</html>