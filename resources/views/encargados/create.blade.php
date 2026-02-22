@extends('layouts.app')

@section('title', 'Registrar Encargado - UNICAES')

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

    .form-control:focus+.input-group-text,
    .form-select:focus+.input-group-text {
        border-color: var(--dorado);
    }

    /* Campos con error */
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545;
        background-image: none;
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
    <a href="{{ route('encargados.index') }}" class="btn btn-light border me-3" title="Volver al listado">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-user-plus me-2"></i> Registrar Encargado
    </h2>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4 p-md-5">

        <form method="POST" action="{{ route('encargados.store') }}">
            @csrf

            <div class="row g-4">

                <div class="col-12">
                    <label class="form-label text-muted fw-bold">Nombre del Encargado / Unidad <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-address-card"></i></span>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" placeholder="Ej. Laboratorio de Cómputo 1 o Ing. Carlos López">
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
                        <select name="tipo" class="form-select @error('tipo') is-invalid @enderror">
                            <option value="" disabled {{ old('tipo') ? '' : 'selected' }}>-- Seleccione --</option>
                            <option value="PERSONA" @selected(old('tipo')==='PERSONA' )>PERSONA</option>
                            <option value="UNIDAD" @selected(old('tipo')==='UNIDAD' )>UNIDAD</option>
                        </select>
                    </div>
                    @error('tipo')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Correo Electrónico <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ old('correo') }}" placeholder="encargado@unicaes.edu.sv">
                    </div>
                    @error('correo')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="contrasena" class="form-control @error('contrasena') is-invalid @enderror" placeholder="Mínimo 6 caracteres">
                    </div>
                    @error('contrasena')
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
                <button type="submit" class="btn btn-guardar px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Encargado
                </button>
            </div>

        </form>
    </div>
</div>

@endsection