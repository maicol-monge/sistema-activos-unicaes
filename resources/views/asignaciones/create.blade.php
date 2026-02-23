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

@php
    $rol = auth()->user()->rol ?? null;
    if ($rol === 'ADMIN') {
        $rutaVolver = route('asignaciones.index');
    } elseif ($rol === 'INVENTARIADOR') {
        $rutaVolver = route('activos.index');
    } elseif ($rol === 'ENCARGADO') {
        $rutaVolver = route('asignaciones.mis');
    } else {
        $rutaVolver = route('dashboard');
    }
@endphp

<div class="d-flex align-items-center mb-4">
    <a href="{{ $rutaVolver }}" class="btn btn-light border me-3" title="Volver">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="mb-0" style="color: var(--rojo-principal); font-weight: 700;">
        <i class="fa-solid fa-link me-2"></i> Nueva Asignación de Activo
    </h2>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4 p-md-5">

        <form method="POST" action="{{ route('asignaciones.store') }}" id="form-asignacion">
            @csrf

            <div class="row g-4">

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Activo a asignar <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="buscar-activo"
                        class="form-control form-control-sm mb-2"
                        placeholder="Buscar por código o nombre...">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-box-open"></i></span>
                        <select name="id_activo" id="select-activo" class="form-select @error('id_activo') is-invalid @enderror" required>
                            <option value="" disabled {{ old('id_activo') ? '' : 'selected' }}>-- Seleccione el activo --</option>
                            @foreach($activos as $a)
                            <option value="{{ $a->id_activo }}" @selected(old('id_activo')==$a->id_activo)>
                                [{{ $a->codigo ?? 'S/C' }}] {{ $a->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text mt-1" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-info me-1 text-info"></i>
                        @if(!empty($esEncargado))
                        Solo se muestran los activos que tienes actualmente asignados.
                        @else
                        Solo se muestran activos disponibles sin asignación activa.
                        @endif
                    </div>
                    @error('id_activo')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Usuario destinatario <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="buscar-destinatario"
                        class="form-control form-control-sm mb-2"
                        placeholder="Buscar por nombre o rol...">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user-tie"></i></span>

                        <select name="asignado_a" id="select-destinatario" class="form-select @error('asignado_a') is-invalid @enderror" required>
                            <option value="" disabled {{ old('asignado_a') ? '' : 'selected' }}>-- Seleccione el usuario destino --</option>
                            @foreach($destinatarios as $e)
                            <option value="{{ $e->id_usuario }}" @selected(old('asignado_a')==$e->id_usuario)>
                                {{ $e->nombre }} ({{ $e->rol }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-text mt-1" style="font-size: 0.85em;">
                        <i class="fa-solid fa-bell me-1 text-warning"></i> Se notificará al encargado para su aceptación.
                    </div>
                    @error('asignado_a')
                    <div class="text-danger mt-1 fw-semibold" style="font-size: 0.85em;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

            </div>

            <hr class="text-muted my-4">

            <div class="d-flex justify-content-end">
                <a href="{{ $rutaVolver }}" class="btn btn-light border me-2">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        function filtrarSelect(inputId, selectId) {
            const input = document.getElementById(inputId);
            const select = document.getElementById(selectId);
            if (!input || !select) return;

            const opcionesOriginales = Array.from(select.options).map(opt => ({
                value: opt.value,
                text: opt.text,
                selected: opt.selected,
                disabled: opt.disabled
            }));

            input.addEventListener('input', () => {
                const termino = input.value.toLowerCase().trim();

                // Limpiar opciones
                while (select.options.length > 0) {
                    select.remove(0);
                }

                opcionesOriginales.forEach(optData => {
                    if (optData.disabled && optData.value === '') {
                        // Siempre mantener la opción placeholder
                        const opt = new Option(optData.text, optData.value, optData.selected, optData.selected);
                        opt.disabled = true;
                        select.add(opt);
                        return;
                    }

                    if (!termino || optData.text.toLowerCase().includes(termino)) {
                        const opt = new Option(optData.text, optData.value, optData.selected, optData.selected);
                        opt.disabled = optData.disabled && optData.value !== '';
                        select.add(opt);
                    }
                });
            });
        }

        filtrarSelect('buscar-activo', 'select-activo');
        filtrarSelect('buscar-destinatario', 'select-destinatario');

        // Confirmación antes de generar la asignación
        const form = document.getElementById('form-asignacion');
        const selectActivo = document.getElementById('select-activo');
        const selectDest = document.getElementById('select-destinatario');

        if (form && selectActivo && selectDest) {
            form.addEventListener('submit', function(e) {
                // Validar campos requeridos primero
                if (!form.checkValidity()) {
                    return; // dejar que el navegador muestre los mensajes nativos
                }

                e.preventDefault();

                const optActivo = selectActivo.options[selectActivo.selectedIndex];
                const optDest = selectDest.options[selectDest.selectedIndex];

                const textoActivo = optActivo ? optActivo.text.trim() : '';
                const textoDest = optDest ? optDest.text.trim() : '';

                Swal.fire({
                    title: '¿Confirmar asignación?',
                    html: `¿Estás seguro de asignar <strong>${textoActivo}</strong><br>al usuario <strong>${textoDest}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#7e0001',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, asignar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endpush