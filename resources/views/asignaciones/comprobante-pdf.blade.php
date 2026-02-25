<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>{{ $numero ?? 'Comprobante' }}</title>
    <style>
        :root {
            --rojo-principal: #7e0001;
            --dorado: #edbd3f;
            --gris-claro: #f4f4f6;
            --texto: #222;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: var(--texto);
            margin: 0;
            padding: 12px;
        }

        .card {
            border: 1px solid #e6e6e6;
            border-radius: 6px;
            padding: 14px;
            background: #fff;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand .logo {
            width: 56px;
            height: 56px;
            border-radius: 6px;
            background: var(--rojo-principal);
            display: inline-block;
            vertical-align: middle;
        }

        .logo-img {
            width: 95px;
            height: 56px;
            border-radius: 6px;
            object-fit: cover;
            display: inline-block;
            vertical-align: middle;
        }

        .brand .titulo {
            font-size: 16px;
            font-weight: 800;
            color: var(--rojo-principal);
        }

        .subtitle {
            font-size: 10px;
            color: #666;
        }

        .comprobante-meta {
            text-align: right;
        }

        .numero {
            font-weight: 800;
            color: var(--rojo-principal);
            font-size: 18px;
        }

        .badge-estado {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 6px;
            background: var(--gris-claro);
            font-weight: 700;
            color: var(--rojo-principal);
            font-size: 11px;
        }

        .section {
            margin-top: 12px;
        }

        .kv {
            width: 100%;
            display: table;
            table-layout: fixed;
        }

        .kv>div {
            display: table-row;
        }

        .kv .pair {
            display: table-cell;
            padding: 6px 8px;
            vertical-align: top;
        }

        .kv-label {
            font-size: 9px;
            color: #666;
            font-weight: 700;
            text-transform: uppercase;
        }

        .kv-value {
            font-size: 12px;
            color: var(--texto);
            font-weight: 700;
            margin-top: 4px;
        }

        .two-cols {
            display: flex;
            gap: 12px;
        }

        .two-cols .col {
            flex: 1;
        }

        .hr {
            height: 1px;
            background: #eee;
            margin: 12px 0;
            border-radius: 2px;
        }

        .detalle {
            background: linear-gradient(90deg, rgba(237, 189, 63, 0.06), transparent);
            padding: 10px;
            border-radius: 6px;
        }

        .sign {
            height: 48px;
            border-bottom: 1px solid #bbb;
            margin-top: 18px;
        }

        .footer-note {
            font-size: 10px;
            color: #666;
            margin-top: 12px;
        }
    </style>
</head>

<body>
    @php
    $a = $asignacion;
    $activo = $a->activo;
    $aceptacion = $fechaAceptacion ? \Carbon\Carbon::parse($fechaAceptacion) : null;
    @endphp

    <div class="header">
        <div class="brand">
            @if(file_exists(public_path('images/LogoU.png')))
            <img src="{{ public_path('images/LogoU.png') }}" alt="Logo UNICAES" class="logo-img" />
            @else
            <span class="logo"></span>
            @endif
            <div>
                <div class="titulo">UNICAES</div>
                <div class="subtitle">Sistema de Activos</div>
            </div>
        </div>

        <div class="comprobante-meta">
            <div class="numero">{{ $numero ?? 'Comprobante' }}</div>
            <div style="margin-top:6px;">Fecha: {{ now()->format('d/m/Y') }}</div>
        </div>
    </div>
    <div class="card">
        <div class="two-cols">
            <div class="col">
                <div class="kv">
                    <div>
                        <div class="pair">
                            <div class="kv-label">Encargado (recibe)</div>
                            <div class="kv-value">{{ $a->usuarioAsignado?->nombre ?? '—' }}</div>
                        </div>
                        <div class="pair">
                            <div class="kv-label">Asignado por</div>
                            <div class="kv-value">{{ $a->usuarioAsignador?->nombre ?? 'Sistema' }}</div>
                        </div>
                        <div class="pair">
                            <div class="kv-label">Fecha de asignación</div>
                            <div class="kv-value">{{ $a->fecha_asignacion ? \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y H:i') : '—' }}</div>
                        </div>
                        <div class="pair">
                            <div class="kv-label">Fecha de aceptación</div>
                            <div class="kv-value">{{ $aceptacion ? $aceptacion->format('d/m/Y H:i') : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col" style="text-align:right;">
                <div class="numero">{{ $numero ?? '—' }}</div>
                <div style="height:8px"></div>
                <div class="badge-estado">{{ $a->estado_asignacion ?? '—' }}</div>
            </div>
        </div>

        <div class="hr"></div>

        <div class="section">
            <div class="kv-label">Detalle del activo</div>
            <div class="detalle">
                <div class="two-cols">
                    <div class="col">
                        <div class="kv-label">Nombre</div>
                        <div class="kv-value">{{ $activo?->nombre ?? '—' }}</div>
                        <div class="kv-label" style="margin-top:8px">Categoría</div>
                        <div class="kv-value">{{ $activo?->categoria?->nombre ?? '—' }}</div>
                    </div>
                    <div class="col">
                        <div class="kv-label">Código</div>
                        <div class="kv-value">{{ $activo?->codigo ?? '—' }}</div>
                        <div class="kv-label" style="margin-top:8px">Serial</div>
                        <div class="kv-value">{{ $activo?->serial ?? '—' }}</div>
                    </div>
                </div>

                <div class="two-cols" style="margin-top:10px">
                    <div class="col">
                        <div class="kv-label">Marca</div>
                        <div class="kv-value">{{ $activo?->marca ?? '—' }}</div>
                    </div>
                    <div class="col">
                        <div class="kv-label">Tipo</div>
                        <div class="kv-value">{{ $activo?->tipo ?? '—' }}</div>
                    </div>
                </div>

                <div style="margin-top:10px">
                    <div class="kv-label">Descripción</div>
                    <div class="kv-value" style="font-weight:400">{{ $activo?->descripcion ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="hr"></div>

        <div class="two-cols">
            <div class="col">
                <div class="kv-label">Firma encargado</div>
                <div class="sign"></div>
            </div>
            <div class="col">
                <div class="kv-label">Firma asignador</div>
                <div class="sign"></div>
            </div>
        </div>

        <div class="footer-note">Este comprobante certifica la aceptación y responsabilidad del activo indicado.</div>
    </div>

</body>

</html>