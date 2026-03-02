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
    @php
        $titulos = [
            'reportes' => 'Reporte ejecutivo de estado de activos',
            'activos' => 'Listado de activos del sistema',
            'asignaciones' => 'Listado de asignaciones de activos',
            'movimientos' => 'Listado de movimientos de activos',
            'bajas' => 'Listado de bajas de activos',
            'usuarios' => 'Listado de usuarios del sistema',
        ];
    @endphp
    <h1>{{ $titulos[$tipo] ?? 'Consulta ejecutiva' }} - Decanatura</h1>
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
            if (!empty($filtros['estado_reporte'] ?? null) && $tipo === 'reportes') $partes[] = 'Estado: ' . $filtros['estado_reporte'];
        @endphp
        {{ empty($partes) ? 'Ninguno (todos los registros).' : implode(' • ', $partes) }}
    </p>
</div>

@if($tipo === 'reportes')
<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Activo</th>
            <th>Código</th>
            <th>Estado</th>
            <th>Reportado por</th>
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
            <td colspan="6" style="text-align:center; font-style:italic;">No se encontraron reportes.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@elseif($tipo === 'activos')
<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Condición</th>
            <th>Estado</th>
            <th>Categoría</th>
            <th>Valor compra</th>
            <th>Fecha adquisición</th>
        </tr>
    </thead>
    <tbody>
        @forelse($activos as $activo)
        <tr>
            <td>{{ $activo->codigo }}</td>
            <td>{{ $activo->nombre }}</td>
            <td>{{ $activo->tipo }}</td>
            <td>{{ $activo->condicion }}</td>
            <td>{{ $activo->estado }}</td>
            <td>{{ $activo->categoria?->nombre ?? 'N/A' }}</td>
            <td>{{ number_format($activo->valor_compra, 2) }}</td>
            <td>{{ $activo->fecha_adquisicion ? \Carbon\Carbon::parse($activo->fecha_adquisicion)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" style="text-align:center; font-style:italic;">No se encontraron activos.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@elseif($tipo === 'asignaciones')
<table>
    <thead>
        <tr>
            <th>Fecha asignación</th>
            <th>Activo</th>
            <th>Código</th>
            <th>Asignado a</th>
            <th>Asignado por</th>
            <th>Estado asignación</th>
        </tr>
    </thead>
    <tbody>
        @forelse($asignaciones as $asignacion)
        <tr>
            <td>{{ $asignacion->fecha_asignacion ? \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y H:i') : 'N/A' }}</td>
            <td>{{ $asignacion->activo?->nombre ?? 'N/A' }}</td>
            <td>{{ $asignacion->activo?->codigo ?? 'N/A' }}</td>
            <td>{{ $asignacion->usuarioAsignado?->nombre ?? 'N/A' }}</td>
            <td>{{ $asignacion->usuarioAsignador?->nombre ?? 'N/A' }}</td>
            <td>{{ $asignacion->estado_asignacion }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center; font-style:italic;">No se encontraron asignaciones.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@elseif($tipo === 'movimientos')
<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Activo</th>
            <th>Código</th>
            <th>Usuario</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($movimientos as $movimiento)
        <tr>
            <td>{{ $movimiento->fecha ? \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y H:i') : 'N/A' }}</td>
            <td>{{ $movimiento->tipo }}</td>
            <td>{{ $movimiento->activo?->nombre ?? 'N/A' }}</td>
            <td>{{ $movimiento->activo?->codigo ?? 'N/A' }}</td>
            <td>{{ $movimiento->usuario?->nombre ?? 'N/A' }}</td>
            <td>{{ $movimiento->observaciones }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center; font-style:italic;">No se encontraron movimientos.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@elseif($tipo === 'bajas')
<table>
    <thead>
        <tr>
            <th>Fecha solicitud</th>
            <th>Activo</th>
            <th>Código</th>
            <th>Solicitante</th>
            <th>Estado</th>
            <th>Motivo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($bajas as $baja)
        <tr>
            <td>{{ $baja->created_at ? $baja->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
            <td>{{ $baja->activo?->nombre ?? 'N/A' }}</td>
            <td>{{ $baja->activo?->codigo ?? 'N/A' }}</td>
            <td>{{ $baja->solicitante?->nombre ?? 'N/A' }}</td>
            <td>{{ $baja->estado }}</td>
            <td>{{ $baja->motivo }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center; font-style:italic;">No se encontraron bajas.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@elseif($tipo === 'usuarios')
<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Tipo</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($usuarios as $usuario)
        <tr>
            <td>{{ $usuario->nombre }}</td>
            <td>{{ $usuario->correo }}</td>
            <td>{{ $usuario->rol }}</td>
            <td>{{ $usuario->tipo }}</td>
            <td>{{ $usuario->estado }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align:center; font-style:italic;">No se encontraron usuarios.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endif

</body>
</html>
