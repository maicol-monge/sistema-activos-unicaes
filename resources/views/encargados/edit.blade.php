@extends('layouts.app')

@section('title', 'Editar Encargado - UNICAES')

@section('content')

<style>
    /* Estilos consistentes con el resto del sistema */
    .form-control:focus,
    .form-select:focus {
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

    .form-control:focus+.input-group-text,
    .form-select:focus+.input-group-text {
        border-color: var(--dorado);
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545;
        background-image: none;
    }

    /* Botón Actualizar */
    .btn-actualizar {
        background-color: var(--rojo-principal);
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-actualizar:hover {
        background-color: var(--rojo-oscuro);
        color: var(--dorado);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(92, 0, 1, 0.2);
    }
</style>

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('encargados.index') }}" class="btn btn-light border me-3" title="Volver al listado">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
            <i class="fa-solid fa-user-pen me-2"></i> Editar Encargado
        </h2>
        <p class="text-muted mb-0 mt-1">Actualizando datos de: <strong>{{ $encargado->nombre }}</strong></p>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--dorado); border-radius: 8px;">
    <div class="card-body p-4 p-md-5">

        <form method="POST" action="{{ route('encargados.update', $encargado) }}">
            @csrf
            @method('PUT')

            <div class="row g-4">

                <div class="col-12">
                    <label class="form-label text-muted fw-bold">Nombre del Encargado / Unidad <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-address-card"></i></span>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $encargado->nombre) }}" required>
                    </div>
                    @error('nombre')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Tipo de Encargado <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-tag"></i></span>
                        <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                            <option value="PERSONA" @selected(old('tipo', $encargado->tipo) === 'PERSONA')>PERSONA</option>
                            <option value="UNIDAD" @selected(old('tipo', $encargado->tipo) === 'UNIDAD')>UNIDAD</option>
                        </select>
                    </div>
                    @error('tipo')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Estado del Encargado <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-toggle-on"></i></span>
                        <select name="estado" class="form-select @error('estado') is-invalid @enderror" required>
                            <option value="1" @selected(old('estado', (string)$encargado->estado) === '1')>Activo (Habilitado para asignaciones)</option>
                            <option value="0" @selected(old('estado', (string)$encargado->estado) === '0')>Inactivo (Deshabilitado)</option>
                        </select>
                    </div>
                    @error('estado')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label text-muted fw-bold">Usuario Vinculado en el Sistema <span class="text-muted fw-normal">(Opcional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
                        <select name="id_usuario" class="form-select @error('id_usuario') is-invalid @enderror">
                            <option value="">-- Ningún usuario vinculado --</option>
                            @foreach($usuarios as $u)
                            <option value="{{ $u->id_usuario }}" @selected(old('id_usuario', $encargado->id_usuario) == $u->id_usuario)>
                                {{ $u->nombre }} - {{ $u->correo }} ({{ $u->rol }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text mt-1" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-info me-1 text-info"></i> Modifica este campo si deseas reasignar la cuenta de sistema para este encargado.
                    </div>
                    @error('id_usuario')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

            </div>

            <hr class="text-muted my-4">

            <div class="d-flex justify-content-end">
                <a href="{{ route('encargados.index') }}" class="btn btn-light border me-2">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-actualizar px-4">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Actualizar Encargado
                </button>
            </div>

        </form>
    </div>
</div>

@endsection