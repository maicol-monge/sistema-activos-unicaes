@extends('layouts.app')

@section('title', 'Aprobaciones de Activos - UNICAES')

@section('content')

<style>
    .table-custom th {
        background-color: var(--rojo-principal);
        color: var(--dorado);
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
    }

    .table-custom td {
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-custom tbody tr:hover {
        background-color: #fdfaf3;
    }

    .btn-aprobar {
        background-color: #198754;
        color: #fff;
        border: none;
    }

    .btn-aprobar:hover {
        background-color: #146c43;
        color: #fff;
    }

    .combo-label {
        font-size: 0.82rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
        display: block;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-circle-check me-2"></i> Aprobación de Activos
    </h2>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('activos.aprobaciones') }}" class="row g-3">

            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Búsqueda</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $filtros['q'] ?? '' }}"
                        placeholder="Código, nombre, serial, categoría...">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['FIJO', 'INTANGIBLE'] as $tipo)
                    <option value="{{ $tipo }}" @selected(($filtros['tipo'] ?? '' )===$tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Condición</label>
                <select name="condicion" class="form-select">
                    <option value="">Todas</option>
                    <option value="BUENO" @selected(($filtros['condicion'] ?? '' )==='BUENO')>BUENO</option>
                    <option value="DANIADO" @selected(($filtros['condicion'] ?? '' )==='DANIADO')>DAÑADO</option>
                    <option value="REGULAR" @selected(($filtros['condicion'] ?? '' )==='REGULAR')>REGULAR</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Categoría</label>
                <input type="text" id="categoria_nombre" name="categoria_nombre" class="form-control mb-2" value="{{ $filtros['categoria_nombre'] ?? '' }}" placeholder="Escribe para filtrar categorías..." autocomplete="off">
                <span class="combo-label">Opciones de categoría</span>
                <select id="id_categoria_activo" name="id_categoria_activo" class="form-select">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id_categoria_activo }}" @selected((string)($filtros['id_categoria_activo'] ?? '' )===(string)$categoria->id_categoria_activo)>
                        {{ $categoria->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Registrado por</label>
                <input type="text" id="registrado_por_nombre" name="registrado_por_nombre" class="form-control mb-2" value="{{ $filtros['registrado_por_nombre'] ?? '' }}" placeholder="Escribe para filtrar usuarios..." autocomplete="off">
                <span class="combo-label">Opciones de usuario</span>
                <select id="registrado_por" name="registrado_por" class="form-select">
                    <option value="">Todos</option>
                    @foreach($registradores as $registrador)
                    <option value="{{ $registrador->id_usuario }}" @selected((string)($filtros['registrado_por'] ?? '' )===(string)$registrador->id_usuario)>
                        {{ $registrador->nombre }} ({{ $registrador->correo }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ $filtros['fecha_desde'] ?? '' }}">
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ $filtros['fecha_hasta'] ?? '' }}">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route('activos.aprobaciones') }}" class="btn btn-light border">
                    <i class="fa-solid fa-broom me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-aprobar">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
            </div>

        </form>
    </div>
</div>

<div class="table-responsive bg-white rounded-3 shadow-sm border overflow-hidden">
    <table class="table table-custom table-hover mb-0">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Registrado por</th>
                <th>Fecha</th>
                <th class="text-center pe-4">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendientes as $activo)
            <tr>
                <td class="fw-semibold">{{ $activo->codigo }}</td>
                <td>
                    <div class="fw-semibold text-dark">{{ $activo->nombre }}</div>
                    <small class="text-muted">Tipo: {{ $activo->tipo }} | Condición: {{ $activo->condicion }}</small>
                </td>
                <td>{{ $activo->categoria?->nombre }}</td>
                <td>{{ $activo->registrador?->nombre }}</td>
                <td>{{ \Carbon\Carbon::parse($activo->fecha_registro)->format('d/m/Y') }}</td>
                <td class="text-center pe-4">
                    <div class="d-flex justify-content-center gap-2">
                        <form method="POST" action="{{ route('activos.aprobar', $activo) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-aprobar">
                                <i class="fa-solid fa-check me-1"></i> Aprobar
                            </button>
                        </form>

                        <button type="button" class="btn btn-sm btn-danger btn-rechazar" data-id="{{ $activo->id_activo }}">
                            <i class="fa-solid fa-xmark me-1"></i> Rechazar
                        </button>

                        <form id="form-rechazo-{{ $activo->id_activo }}" method="POST" action="{{ route('activos.rechazar', $activo) }}" class="d-none">
                            @csrf
                            <input type="hidden" name="observaciones" value="">
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-circle-check fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No hay activos pendientes por aprobar.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $pendientes->links() }}
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const setupComboFilter = ({
            inputId,
            selectId,
            endpoint,
            defaultLabel,
        }) => {
            const input = document.getElementById(inputId);
            const select = document.getElementById(selectId);
            if (!input || !select) return;

            const selectedValue = select.value;

            let debounceTimer;

            const populateSelect = (items) => {
                select.innerHTML = '';

                const firstOption = document.createElement('option');
                firstOption.value = '';
                firstOption.textContent = defaultLabel;
                select.appendChild(firstOption);

                items.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.label;
                    if (selectedValue && String(item.id) === String(selectedValue)) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            };

            input.addEventListener('input', () => {
                const q = input.value.trim();

                clearTimeout(debounceTimer);

                if (!q.length) {
                    return;
                }

                debounceTimer = setTimeout(async () => {
                    try {
                        const url = `${endpoint}?q=${encodeURIComponent(q)}`;
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            return;
                        }

                        const items = await response.json();
                        populateSelect(Array.isArray(items) ? items : []);
                    } catch (error) {
                        // ignore
                    }
                }, 250);
            });

            select.addEventListener('change', () => {
                const selected = select.options[select.selectedIndex];
                if (selected && selected.value) {
                    input.value = selected.text;
                }
            });
        };

        setupComboFilter({
            inputId: 'categoria_nombre',
            selectId: 'id_categoria_activo',
            endpoint: @json(route('activos.filtros.categorias')),
            defaultLabel: 'Todas',
        });

        setupComboFilter({
            inputId: 'registrado_por_nombre',
            selectId: 'registrado_por',
            endpoint: @json(route('activos.filtros.registradores')),
            defaultLabel: 'Todos',
        });

        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('.btn-rechazar');
            if (!btn) return;

            const activoId = btn.dataset.id;
            const form = document.getElementById(`form-rechazo-${activoId}`);
            if (!form) return;

            const {
                value: observaciones
            } = await Swal.fire({
                title: 'Rechazar activo',
                input: 'textarea',
                inputLabel: 'Observaciones (obligatorio)',
                inputPlaceholder: 'Escribe el motivo del rechazo...',
                inputAttributes: {
                    'aria-label': 'Escribe el motivo del rechazo'
                },
                showCancelButton: true,
                confirmButtonText: 'Rechazar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return 'La observación es obligatoria.';
                    }
                }
            });

            if (observaciones) {
                form.querySelector('input[name="observaciones"]').value = observaciones;
                form.submit();
            }
        });
    });
</script>

@endsection
