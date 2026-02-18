@extends('layouts.app')

@section('title', 'Nuevo Usuario - UNICAES')

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
    <a href="{{ route('users.index') }}" class="btn btn-light border me-3" title="Volver">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-user-plus me-2"></i> Crear Nuevo Usuario
    </h2>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4">

        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-left: 4px solid #842029;">
            <div class="d-flex align-items-center mb-2">
                <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                <strong>Por favor, corrige los siguientes errores:</strong>
            </div>
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted fw-bold">Nombre Completo <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej. Juan Pérez" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted fw-bold">Correo Electrónico <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="correo" class="form-control" value="{{ old('correo') }}" placeholder="usuario@unicaes.edu.sv" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted fw-bold">Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="contrasena" class="form-control" placeholder="Mínimo 8 caracteres" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted fw-bold">Rol del Sistema <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user-shield"></i></span>
                        <select name="rol" class="form-select" required>
                            <option value="" disabled {{ old('rol') ? '' : 'selected' }}>Selecciona un rol...</option>
                            @foreach(['ADMIN','INVENTARIADOR','ENCARGADO','DECANO'] as $r)
                            <option value="{{ $r }}" @selected(old('rol')===$r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label text-muted fw-bold">Estado del Usuario <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-toggle-on"></i></span>
                        <select name="estado" class="form-select" required>
                            <option value="1" @selected(old('estado', '1' )=='1' )>Activo (Puede iniciar sesión)</option>
                            <option value="0" @selected(old('estado')=='0' )>Inactivo (Bloqueado)</option>
                        </select>
                    </div>
                </div>

            </div>

            <hr class="text-muted">

            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('users.index') }}" class="btn btn-light border me-2">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-guardar px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Usuario
                </button>
            </div>

        </form>
    </div>
</div>

@endsection