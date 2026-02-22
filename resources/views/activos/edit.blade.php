@extends('layouts.app')

@section('title', 'Editar Activo - UNICAES')

@section('content')

<style>
    .form-control:focus,
    .form-select:focus,
    .form-control:focus,
    .form-check-input:focus {
        border-color: var(--dorado);
        box-shadow: 0 0 0 0.25rem rgba(237, 189, 63, 0.25);
    }

    .input-group-text {
        background-color: #f8f9fa;
        color: var(--rojo-principal);
        border-right: none;
    }

    .form-control,
    .form-select {
        border-left: none;
    }

    .btn-guardar {
        background-color: var(--rojo-principal);
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-guardar:hover {
        background-color: var(--rojo-oscuro);
        color: var(--dorado);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(92, 0, 1, 0.2);
    }
</style>

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('activos.index') }}" class="btn btn-light border me-3" title="Volver">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-pen-to-square me-2"></i> Editar Activo
    </h2>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('activos.update', $activo) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Código <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                        <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $activo->codigo) }}" required>
                    </div>
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label text-muted fw-bold">Nombre <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-font"></i></span>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $activo->nombre) }}" maxlength="50" required>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Tipo <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-layer-group"></i></span>
                        <select name="tipo" class="form-select" required>
                            <option value="FIJO" @selected(old('tipo', $activo->tipo) === 'FIJO')>FIJO</option>
                            <option value="INTANGIBLE" @selected(old('tipo', $activo->tipo) === 'INTANGIBLE')>INTANGIBLE</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Categoría <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-tags"></i></span>
                        <select name="id_categoria_activo" class="form-select" required>
                            @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id_categoria_activo }}" @selected((string)old('id_categoria_activo', $activo->id_categoria_activo) === (string)$categoria->id_categoria_activo)>
                                {{ $categoria->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Condición <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-circle-info"></i></span>
                        <select name="condicion" class="form-select" required>
                            <option value="BUENO" @selected(old('condicion', $activo->condicion) === 'BUENO')>BUENO</option>
                            <option value="DANIADO" @selected(old('condicion', $activo->condicion) === 'DANIADO')>DAÑADO</option>
                            <option value="REGULAR" @selected(old('condicion', $activo->condicion) === 'REGULAR')>REGULAR</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Fecha de adquisición <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                        <input type="date" name="fecha_adquisicion" class="form-control" value="{{ old('fecha_adquisicion', $activo->fecha_adquisicion) }}" min="1982-04-13" max="{{ now()->toDateString() }}" required>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Valor de compra <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-dollar-sign"></i></span>
                        <input type="number" name="valor_compra" class="form-control" step="0.01" min="0.01" value="{{ old('valor_compra', $activo->valor_compra) }}" required>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Serial (opcional)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-barcode"></i></span>
                        <input type="text" name="serial" id="serial" class="form-control" value="{{ old('serial', $activo->serial) }}">
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Marca</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-industry"></i></span>
                        <input type="text" name="marca" class="form-control" value="{{ old('marca', $activo->marca) }}">
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label text-muted fw-bold">Descripción <span id="descRequired" class="text-danger" style="display:none;">*</span></label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3" placeholder="Obligatoria si no ingresas serial">{{ old('descripcion', $activo->descripcion) }}</textarea>
                </div>
            </div>

            @if($activo->estado === 'RECHAZADO' && $activo->observaciones)
            <div class="alert alert-warning mt-2 mb-0">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                Observación de rechazo: <strong>{{ $activo->observaciones }}</strong>
            </div>
            @endif

            <hr class="text-muted">

            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('activos.index') }}" class="btn btn-light border me-2">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-guardar px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serial = document.getElementById('serial');
        const descripcion = document.getElementById('descripcion');
        const descRequired = document.getElementById('descRequired');

        function toggleDescripcionRequired() {
            const serialVacio = !serial.value || serial.value.trim() === '';
            descripcion.required = serialVacio;
            descRequired.style.display = serialVacio ? 'inline' : 'none';
        }

        serial.addEventListener('input', toggleDescripcionRequired);
        toggleDescripcionRequired();
    });
</script>

@endsection
