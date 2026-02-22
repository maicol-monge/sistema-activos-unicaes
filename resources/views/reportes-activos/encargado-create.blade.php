@extends('layouts.app')

@section('title', 'Nuevo Reporte de Estado - UNICAES')

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
    <a href="{{ route('encargado.reportes.index') }}" class="btn btn-light border me-3" title="Volver">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-clipboard-plus me-2"></i> Registrar Reporte de Estado
    </h2>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('encargado.reportes.store') }}">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Activo asignado <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-box"></i></span>
                        <select name="id_activo" class="form-select" required>
                            <option value="" disabled {{ old('id_activo', $activoPreseleccionado) ? '' : 'selected' }}>Selecciona un activo...</option>
                            @foreach($activos as $activo)
                            <option value="{{ $activo->id_activo }}" @selected((string)old('id_activo', $activoPreseleccionado) === (string)$activo->id_activo)>
                                [{{ $activo->codigo }}] {{ $activo->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Estado reportado <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-circle-info"></i></span>
                        <select name="estado_reporte" class="form-select" required>
                            <option value="" disabled {{ old('estado_reporte') ? '' : 'selected' }}>Selecciona estado...</option>
                            <option value="BUENO" @selected(old('estado_reporte') === 'BUENO')>BUENO</option>
                            <option value="DANIADO" @selected(old('estado_reporte') === 'DANIADO')>DAÑADO</option>
                            <option value="PERDIDO" @selected(old('estado_reporte') === 'PERDIDO')>PERDIDO</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Fecha del reporte <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', now()->toDateString()) }}" min="1982-04-13" max="{{ now()->toDateString() }}" required>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label text-muted fw-bold">Comentario <span class="text-danger">*</span></label>
                    <textarea name="comentario" class="form-control" rows="4" placeholder="Describe el estado del activo..." required>{{ old('comentario') }}</textarea>
                </div>
            </div>

            <hr class="text-muted my-4">

            <div class="d-flex justify-content-end">
                <a href="{{ route('encargado.reportes.index') }}" class="btn btn-light border me-2">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-guardar px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Reporte
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
