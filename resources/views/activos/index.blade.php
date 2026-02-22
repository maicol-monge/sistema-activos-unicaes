@extends('layouts.app')

@section('title', 'Activos - UNICAES')

@section('content')

<style>
    .btn-nuevo {
        background-color: var(--dorado);
        color: var(--rojo-oscuro);
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-nuevo:hover {
        background-color: #dca72c;
        color: var(--rojo-oscuro);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(237, 189, 63, 0.4);
    }

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
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-boxes-stacked me-2"></i> Activos
    </h2>
    <a href="{{ route('activos.create') }}" class="btn btn-nuevo shadow-sm">
        <i class="fa-solid fa-plus me-1"></i> Nuevo Activo
    </a>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('activos.index') }}" class="row g-3">

            <div class="col-md-4">
                <label class="form-label text-muted fw-bold mb-1">Búsqueda</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $filtros['q'] ?? '' }}"
                        placeholder="Código, nombre, serial, marca...">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['PENDIENTE', 'APROBADO', 'RECHAZADO', 'BAJA'] as $estado)
                    <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '' )===$estado)>{{ $estado }}</option>
                    @endforeach
                </select>
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

            <div class="col-md-2">
                <label class="form-label text-muted fw-bold mb-1">Categoría</label>
                <select name="id_categoria_activo" class="form-select">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id_categoria_activo }}" @selected((string)($filtros['id_categoria_activo'] ?? '' )===(string)$categoria->id_categoria_activo)>
                        {{ $categoria->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route('activos.index') }}" class="btn btn-light border">
                    <i class="fa-solid fa-broom me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-nuevo">
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
                <th>Tipo</th>
                <th>Categoría</th>
                <th>Condición</th>
                <th class="text-center">Estado</th>
                <th class="text-center pe-4">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activos as $activo)
            <tr>
                <td class="fw-semibold">{{ $activo->codigo }}</td>
                <td>
                    <div class="fw-semibold text-dark">{{ $activo->nombre }}</div>
                    <small class="text-muted">Registrado por: {{ $activo->registrador?->nombre ?? 'N/A' }}</small>
                    @if($activo->estado === 'RECHAZADO' && $activo->observaciones)
                    <br><small class="text-danger">Obs: {{ $activo->observaciones }}</small>
                    @endif
                </td>
                <td>{{ $activo->tipo }}</td>
                <td>{{ $activo->categoria?->nombre }}</td>
                <td>{{ $activo->condicion }}</td>
                <td class="text-center">
                    @if($activo->estado === 'PENDIENTE')
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">PENDIENTE</span>
                    @elseif($activo->estado === 'APROBADO')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">APROBADO</span>
                    @elseif($activo->estado === 'RECHAZADO')
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">RECHAZADO</span>
                    @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">{{ $activo->estado }}</span>
                    @endif
                </td>
                <td class="text-center pe-4">
                    @php
                        $canEdit = auth()->user()->rol === 'ADMIN'
                            || (auth()->user()->rol === 'INVENTARIADOR' && $activo->registrado_por === auth()->user()->id_usuario);
                    @endphp

                    @if($canEdit && $activo->estado !== 'APROBADO')
                    <a href="{{ route('activos.edit', $activo) }}" class="btn btn-sm btn-light border" title="Editar">
                        <i class="fa-solid fa-pen" style="color: var(--dorado);"></i>
                    </a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p class="mb-0">No hay activos registrados.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $activos->links() }}
</div>

@endsection
