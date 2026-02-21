@extends('layouts.app')

@section('title', 'Nueva Asignación - UNICAES')

@section('content')

<style>
    /* Estilos consistentes para los selectores */
    .form-select:focus {
        border-color: var(--dorado);
        box-shadow: 0 0 0 0.25rem rgba(237, 189, 63, 0.25);
    }

    .input-group-text {
        background-color: #f8f9fa;
        color: var(--rojo-principal);
        border-right: none;
    }

    .form-select {
        border-left: none;
    }

    .form-select:focus+.input-group-text {
        border-color: var(--dorado);
    }

    .form-select.is-invalid {
        border-color: #dc3545;
        background-image: none;
    }

    /* Botón de Asignar */
    .btn-asignar {
        background-color: var(--rojo-principal);
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-asignar:hover {
        background-color: var(--rojo-oscuro);
        color: var(--dorado);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(92, 0, 1, 0.2);
    }
</style>

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('asignaciones.index') }}" class="btn btn-light border me-3" title="Volver al listado">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-link me-2"></i> Asignar Activo a Encargado
    </h2>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4 p-md-5">

        <form method="POST" action="{{ route('asignaciones.store') }}">
            @csrf

            <div class="row g-4">

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Activo a Asignar <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-box-open"></i></span>
                        <select name="id_activo" class="form-select @error('id_activo') is-invalid @enderror" required>
                            <option value="" disabled {{ old('id_activo') ? '' : 'selected' }}>-- Seleccione el activo --</option>
                            @foreach($activos as $a)
                            <option value="{{ $a->id_activo }}" @selected(old('id_activo')==$a->id_activo)>
                                [{{ $a->codigo ?? 'S/C' }}] {{ $a->nombre }} - ({{ $a->estado }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text mt-1" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-info me-1 text-info"></i> Solo se muestran activos disponibles.
                    </div>
                    @error('id_activo')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Encargado Responsable <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user-tie"></i></span>
                        <select name="id_encargado" class="form-select @error('id_encargado') is-invalid @enderror" required>
                            <option value="" disabled {{ old('id_encargado') ? '' : 'selected' }}>-- Seleccione el encargado --</option>
                            @foreach($encargados as $e)
                            <option value="{{ $e->id_encargado }}" @selected(old('id_encargado')==$e->id_encargado)>
                                {{ $e->nombre }} (Tipo: {{ $e->tipo }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text mt-1" style="font-size: 0.85em;">
                        <i class="fa-solid fa-bell me-1 text-warning"></i> Se notificará al encargado para su aceptación.
                    </div>
                    @error('id_encargado')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

            </div>

            <hr class="text-muted my-4">

            <div class="d-flex justify-content-end">
                <a href="{{ route('asignaciones.index') }}" class="btn btn-light border me-2">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-asignar px-4">
                    <i class="fa-solid fa-clipboard-check me-1"></i> Generar Asignación
                </button>
            </div>

        </form>
    </div>
</div>

@endsection