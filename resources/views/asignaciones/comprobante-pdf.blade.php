<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $numero ?? 'Comprobante' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { margin-bottom: 12px; }
        .title { font-size: 16px; font-weight: 700; }
        .muted { color: #555; }
        .box { border: 1px solid #ccc; padding: 10px; }
        .row { width: 100%; }
        .col { display: inline-block; vertical-align: top; }
        .col-6 { width: 49%; }
        .kv-label { font-size: 10px; font-weight: 700; color: #555; text-transform: uppercase; margin-bottom: 2px; }
        .kv-value { font-size: 12px; font-weight: 600; }
        .hr { border-top: 1px solid #ddd; margin: 12px 0; }
        .sign { height: 26px; border-bottom: 1px solid #888; }
        .mt-8 { margin-top: 8px; }
        .mt-12 { margin-top: 12px; }
    </style>
</head>
<body>
@php
    $a = $asignacion;
    $activo = $a->activo;
    $aceptacion = $fechaAceptacion ? \Carbon\Carbon::parse($fechaAceptacion) : null;
@endphp

<div class="header">
    <div class="title">Comprobante de aceptación</div>
    <div class="muted">UNICAES - Sistema de Activos</div>
</div>

<div class="box">
    <div class="row">
        <div class="col col-6">
            <div class="kv-label">Comprobante</div>
            <div class="kv-value">{{ $numero }}</div>
        </div>
        <div class="col col-6" style="text-align:right;">
            <div class="kv-label">Estado</div>
            <div class="kv-value">{{ $a->estado_asignacion ?? '—' }}</div>
        </div>
    </div>

    <div class="hr"></div>

    <div class="row">
        <div class="col col-6">
            <div class="kv-label">Encargado (recibe)</div>
            <div class="kv-value">{{ $a->usuarioAsignado?->nombre ?? '—' }}</div>
        </div>
        <div class="col col-6">
            <div class="kv-label">Asignado por</div>
            <div class="kv-value">{{ $a->usuarioAsignador?->nombre ?? 'Sistema' }}</div>
        </div>
    </div>

    <div class="row mt-8">
        <div class="col col-6">
            <div class="kv-label">Fecha de asignación</div>
            <div class="kv-value">{{ $a->fecha_asignacion ? \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y H:i') : '—' }}</div>
        </div>
        <div class="col col-6">
            <div class="kv-label">Fecha de aceptación</div>
            <div class="kv-value">{{ $aceptacion ? $aceptacion->format('d/m/Y H:i') : '—' }}</div>
        </div>
    </div>

    <div class="hr"></div>

    <div class="kv-label">Detalle del activo</div>

    <div class="row mt-8">
        <div class="col col-6">
            <div class="kv-label">Nombre</div>
            <div class="kv-value">{{ $activo?->nombre ?? '—' }}</div>
        </div>
        <div class="col col-6">
            <div class="kv-label">Categoría</div>
            <div class="kv-value">{{ $activo?->categoria?->nombre ?? '—' }}</div>
        </div>
    </div>

    <div class="row mt-8">
        <div class="col col-6">
            <div class="kv-label">Código</div>
            <div class="kv-value">{{ $activo?->codigo ?? '—' }}</div>
        </div>
        <div class="col col-6">
            <div class="kv-label">Serial</div>
            <div class="kv-value">{{ $activo?->serial ?? '—' }}</div>
        </div>
    </div>

    <div class="row mt-8">
        <div class="col col-6">
            <div class="kv-label">Marca</div>
            <div class="kv-value">{{ $activo?->marca ?? '—' }}</div>
        </div>
        <div class="col col-6">
            <div class="kv-label">Tipo</div>
            <div class="kv-value">{{ $activo?->tipo ?? '—' }}</div>
        </div>
    </div>

    <div class="row mt-8">
        <div class="kv-label">Descripción</div>
        <div class="kv-value">{{ $activo?->descripcion ?? '—' }}</div>
    </div>

    <div class="hr"></div>

    <div class="row mt-12">
        <div class="col col-6">
            <div class="muted">Firma encargado</div>
            <div class="sign"></div>
        </div>
        <div class="col col-6">
            <div class="muted">Firma asignador</div>
            <div class="sign"></div>
        </div>
    </div>

    <div class="muted mt-12">
        Este comprobante certifica la aceptación y responsabilidad del activo indicado.
    </div>
</div>

</body>
</html>
