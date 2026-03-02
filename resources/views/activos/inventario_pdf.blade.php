<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Inventario de Activos</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 16px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .titulo {
            font-size: 16px;
            font-weight: 800;
            color: #7e0001;
        }

        .meta {
            font-size: 10px;
            color: #555;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 4px 6px;
        }

        th {
            background-color: #7e0001;
            color: #edbd3f;
            font-size: 10px;
            text-transform: uppercase;
        }

        td {
            font-size: 10px;
        }

        .small {
            font-size: 9px;
        }

        .filters {
            font-size: 9px;
            margin-top: 4px;
            color: #444;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>
            <div class="titulo">Inventario de activos</div>
            <div class="small">Sistema de Activos UNICAES</div>
            <div class="filters">
                @php $f = $filtros ?? []; @endphp
                @if(!empty($f['q']))
                    <div><strong>Búsqueda:</strong> {{ $f['q'] }}</div>
                @endif
                @if(!empty($f['estado']))
                    <div><strong>Estado:</strong> {{ $f['estado'] }}</div>
                @endif
                @if(!empty($f['tipo']))
                    <div><strong>Tipo:</strong> {{ $f['tipo'] }}</div>
                @endif
                @if(!empty($f['condicion']))
                    <div><strong>Condición:</strong> {{ $f['condicion'] }}</div>
                @endif
                @if(!empty($f['id_categoria_activo']))
                    <div><strong>Categoría:</strong> {{ $categoriaFiltroNombre ?? '—' }}</div>
                @endif
                @if(!empty($f['fecha_desde']) || !empty($f['fecha_hasta']))
                    <div>
                        <strong>Fecha adquisición:</strong>
                        @if(!empty($f['fecha_desde']) && !empty($f['fecha_hasta']))
                            {{ \Carbon\Carbon::parse($f['fecha_desde'])->format('d/m/Y') }}
                            -
                            {{ \Carbon\Carbon::parse($f['fecha_hasta'])->format('d/m/Y') }}
                        @elseif(!empty($f['fecha_desde']))
                            Desde {{ \Carbon\Carbon::parse($f['fecha_desde'])->format('d/m/Y') }}
                        @else
                            Hasta {{ \Carbon\Carbon::parse($f['fecha_hasta'])->format('d/m/Y') }}
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="meta">
            <div><strong>Generado por:</strong> {{ $usuario->nombre ?? '—' }}</div>
            <div><strong>Fecha:</strong> {{ $generadoEn->format('d/m/Y H:i') }}</div>
            <div><strong>Total activos:</strong> {{ $activos->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Categoría</th>
                <th>Condición</th>
                <th>Estado</th>
                <th>Registrado por</th>
                <th>Aprobado por</th>
                <th>Fecha adquisición</th>
                <th>Valor compra</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activos as $a)
            <tr>
                <td>{{ $a->codigo }}</td>
                <td>{{ $a->nombre }}</td>
                <td>{{ $a->tipo }}</td>
                <td>{{ $a->categoria->nombre ?? '—' }}</td>
                <td>{{ $a->condicion }}</td>
                <td>{{ $a->estado }}</td>
                <td>{{ $a->registrador->nombre ?? '—' }}</td>
                <td>{{ $a->aprobador->nombre ?? '—' }}</td>
                <td>{{ $a->fecha_adquisicion ? \Carbon\Carbon::parse($a->fecha_adquisicion)->format('d/m/Y') : '—' }}</td>
                <td>{{ number_format($a->valor_compra, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center; padding:10px;">No hay activos que coincidan con los filtros.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
