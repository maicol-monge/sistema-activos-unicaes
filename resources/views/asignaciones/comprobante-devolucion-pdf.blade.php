<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>{{ $numero ?? 'Comprobante de Devolución' }}</title>
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
    $devolucion = $fechaDevolucion ? \Carbon\Carbon::parse($fechaDevolucion) : null;
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
            <div class="numero">{{ $numero ?? 'Comprobante de Devolución' }}</div>
            <div style="margin-top:6px;">Fecha: {{ now()->format('d/m/Y') }}</div>
        </div>
    </div>
    <div class="card">
        <div class="two-cols">
            <div class="col">
                <div class="kv">
                    <div>
                        <div class="pair">
                            <div class="kv-label">Encargado (entrega)</div>
                            <div class="kv-value">{{ $a->usuarioAsignado?->nombre ?? '—' }}</div>
                        </div>
                        <div class="pair">
                            <div class="kv-label">Asignado originalmente por</div>
                            <div class="kv-value">{{ $a->usuarioAsignador?->nombre ?? 'Sistema' }}</div>
                        </div>
                        <div class="pair">
                            <div class="kv-label">Fecha de asignación</div>
                            <div class="kv-value">
                                @if($a->fecha_asignacion)
                                    {{ \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y H:i') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="pair">
                            <div class="kv-label">Fecha de devolución</div>
                            <div class="kv-value">
                                @if($devolucion)
                                    {{ $devolucion->format('d/m/Y H:i') }}
                                @elseif($a->fecha_respuesta)
                                    {{ \Carbon\Carbon::parse($a->fecha_respuesta)->format('d/m/Y H:i') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col" style="text-align:right;">
                <div class="numero">{{ $numero ?? '—' }}</div>
                <div style="height:8px"></div>
                <div class="badge-estado">DEVUELTO</div>
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
                        <div class="kv-value">{{ $activo?->serial ?? 'd' }}</div>
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

        <div class="section" style="margin-top:12px;">
            <div class="kv-label">Motivo de la devolución</div>
            <div class="detalle" style="margin-top:6px;">
                <div class="kv-value" style="font-weight:400; white-space:pre-line;">
                    {{ $a->motivo_devolucion ?? '—' }}
                </div>
            </div>
        </div>

        <div class="hr"></div>

        <div class="two-cols">
            <div class="col">
                <div class="kv-label">Firma de quien entrega</div>
                <div class="sign"></div>
                <div class="kv-value" style="text-align:center; margin-top:4px; font-size:11px;">
                    {{ $a->usuarioAsignado?->nombre ?? '—' }}
                </div>
            </div>
            <div class="col">
                <div class="kv-label">Firma de quien recibe</div>
                <div class="sign"></div>
                <div class="kv-value" style="text-align:center; margin-top:4px; font-size:11px;">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="footer-note">Este comprobante certifica que el encargado ha devuelto el activo indicado y que la devolución ha sido registrada en el sistema.</div>
    </div>

</body>

</html>
