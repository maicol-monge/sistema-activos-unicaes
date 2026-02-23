@extends('layouts.app')

@section('title', 'Solicitar Baja de Activo - UNICAES')

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

    .btn-solicitar {
        background-color: var(--rojo-principal);
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-solicitar:hover {
        background-color: var(--rojo-oscuro);
        color: var(--dorado);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(92, 0, 1, 0.2);
    }
</style>

<div class="d-flex align-items-center mb-4">
    @php $rol = auth()->user()->rol; @endphp
    <a href="{{ $rol === 'ENCARGADO' ? route('activos.mis') : route('activos.index') }}" class="btn btn-light border me-3" title="Volver">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-arrow-down-square-wide-short me-2"></i> Solicitar Baja de Activo
    </h2>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('bajas-activos.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label text-muted fw-bold">Activo a dar de baja <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-box"></i></span>
                        <select name="id_activo" class="form-select" required>
                            <option value="" disabled selected>Seleccione un activo...</option>
                            @foreach($activos as $a)
                            <option value="{{ $a->id_activo }}" @selected(old('id_activo')==$a->id_activo)>
                                [{{ $a->codigo ?? 'S/C' }}] {{ $a->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label text-muted fw-bold">Motivo de la baja <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-file-alt"></i></span>
                        <textarea name="motivo" class="form-control" rows="4" required placeholder="Explique detalladamente el motivo por el cual solicita la baja de este activo.">{{ old('motivo') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-solicitar px-4 py-2">
                    <i class="fa-solid fa-paper-plane me-2"></i> Enviar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
