@extends('layouts.app')

@section('title', 'Editar Categoría - UNICAES')

@section('content')

<style>
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

    .btn-guardar {
        background-color: var(--rojo-principal);
        color: white;
        font-weight: 600;
    }

    .btn-guardar:hover {
        background-color: var(--rojo-oscuro);
        color: var(--dorado);
    }
</style>

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('categorias-activos.index') }}" class="btn btn-light border me-3" title="Volver">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-pen-to-square me-2"></i> Editar Categoría
    </h2>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('categorias-activos.update', $categoria) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label text-muted fw-bold">Nombre <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-tags"></i></span>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $categoria->nombre) }}" maxlength="50" required>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Estado <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-toggle-on"></i></span>
                        <select name="estado" class="form-select" required>
                            <option value="1" @selected(old('estado', (string)$categoria->estado) == '1')>Activa</option>
                            <option value="0" @selected(old('estado', (string)$categoria->estado) == '0')>Inactiva</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="text-muted">

            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('categorias-activos.index') }}" class="btn btn-light border me-2">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-guardar px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
