<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2d2d2d;
        }
        .header {
            background-color: #8B0000;
            color: #fff;
            padding: 14px 20px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .header p {
            margin: 2px 0 0;
            font-size: 10px;
        }
        .filters {
            margin: 0 20px 10px 20px;
            font-size: 10px;
        }
        .filters strong {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin: 0 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
        }
        thead tr {
            background-color: #8B0000;
            color: #fff;
        }
        tbody tr:nth-child(even) {
            background-color: #f7f1f1;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #8B0000;
            color: #fff;
            text-align: center;
            font-size: 9px;
            padding: 4px 0;
        }
    </style>
</head>
<body>

<div class="footer">
    UNICAES — Sistema de Gestión de Activos • Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
</div>

<div class="header">
    <h1>Reporte de Estado de Activos - Administración</h1>
    <p>Universidad Católica de El Salvador — UNICAES</p>
</div>

<div class="filters">
    <p>
        <strong>Filtros aplicados:</strong>
        @php
            $partes = [];
            if (!empty($filtros['q'] ?? null)) $partes[] = 'Búsqueda: "' . $filtros['q'] . '"';
            if (!empty($filtros['fecha_desde'] ?? null)) $partes[] = 'Desde: ' . \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y');
            if (!empty($filtros['fecha_hasta'] ?? null)) $partes[] = 'Hasta: ' . \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y');
            if (!empty($filtros['estado_reporte'] ?? null)) $partes[] = 'Estado: ' . $filtros['estado_reporte'];
        @endphp
        {{ empty($partes) ? 'Ninguno (todos los registros).' : implode(' • ', $partes) }}
    </p>
</div>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Activo</th>
            <th>Código</th>
            <th>Estado</th>
            <th>Responsable</th>
            <th>Comentario</th>
        </tr>
    </thead>
    <tbody>
        @forelse($reportes as $reporte)
        <tr>
            <td>{{ \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y') }}</td>
            <td>{{ $reporte->activo?->nombre ?? 'N/A' }}</td>
            <td>{{ $reporte->activo?->codigo ?? 'N/A' }}</td>
            <td>{{ $reporte->estado_reporte }}</td>
            <td>{{ $reporte->usuario?->nombre ?? 'N/A' }}</td>
            <td>{{ $reporte->comentario }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center; font-style:italic;">No se encontraron reportes con los filtros seleccionados.</td>
        </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
