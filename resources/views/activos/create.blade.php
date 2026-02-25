@extends('layouts.app')

@section('title', 'Registrar Activo - UNICAES')

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
        <i class="fa-solid fa-box-open me-2"></i> Registrar Activo
    </h2>
</div>

<div class="card shadow-sm border-0" style="border-top: 4px solid var(--rojo-principal); border-radius: 8px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('activos.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label text-muted fw-bold">Factura / Documento del activo (opcional)</label>
                    <input type="file" name="factura" id="factura" class="form-control" accept="application/pdf,image/*">
                    <div class="form-text" style="font-size: 0.85em;">
                        <i class="fa-solid fa-wand-magic-sparkles me-1 text-warning"></i>
                        Puedes cargar la factura o documento de compra para intentar completar automáticamente los datos con IA.
                    </div>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="button" id="btn-extraer-ia" class="btn btn-outline-warning w-100">
                        <i class="fa-solid fa-robot me-1"></i> Extraer datos con IA
                    </button>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label text-muted fw-bold">Nombre <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-font"></i></span>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" maxlength="50" required>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Tipo <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-layer-group"></i></span>
                        <select name="tipo" class="form-select" required>
                            <option value="" disabled {{ old('tipo') ? '' : 'selected' }}>Seleccione...</option>
                            <option value="FIJO" @selected(old('tipo') === 'FIJO')>FIJO</option>
                            <option value="INTANGIBLE" @selected(old('tipo') === 'INTANGIBLE')>INTANGIBLE</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Categoría <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-tags"></i></span>
                        <select name="id_categoria_activo" class="form-select" required>
                            <option value="" disabled {{ old('id_categoria_activo') ? '' : 'selected' }}>Seleccione...</option>
                            @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id_categoria_activo }}" @selected((string)old('id_categoria_activo') === (string)$categoria->id_categoria_activo)>
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
                            <option value="" disabled {{ old('condicion') ? '' : 'selected' }}>Seleccione...</option>
                            <option value="BUENO" @selected(old('condicion') === 'BUENO')>BUENO</option>
                            <option value="DANIADO" @selected(old('condicion') === 'DANIADO')>DAÑADO</option>
                            <option value="REGULAR" @selected(old('condicion') === 'REGULAR')>REGULAR</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Fecha de adquisición <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                        <input type="date" name="fecha_adquisicion" class="form-control" value="{{ old('fecha_adquisicion') }}" min="1982-04-13" max="{{ now()->toDateString() }}" required>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Valor de compra <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-dollar-sign"></i></span>
                        <input type="number" name="valor_compra" class="form-control" step="0.01" min="0.01" value="{{ old('valor_compra') }}" required>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Serial (opcional)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-barcode"></i></span>
                        <input type="text" name="serial" id="serial" class="form-control" value="{{ old('serial') }}" placeholder="Opcional">
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted fw-bold">Marca</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-industry"></i></span>
                        <input type="text" name="marca" class="form-control" value="{{ old('marca') }}">
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label text-muted fw-bold">Descripción <span id="descRequired" class="text-danger" style="display:none;">*</span></label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3" placeholder="Obligatoria si no ingresas serial">{{ old('descripcion') }}</textarea>
                </div>
            </div>

            <div class="alert alert-info mt-2 mb-0">
                <i class="fa-solid fa-circle-info me-1"></i>
                Si registras como INVENTARIADOR, el activo quedará en estado <strong>PENDIENTE</strong> hasta aprobación de ADMINISTRADOR.
            </div>

            <hr class="text-muted">

            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('activos.index') }}" class="btn btn-light border me-2">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-guardar px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Activo
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

        // --------- IA: extraer datos desde la factura ---------
        const facturaInput = document.getElementById('factura');
        const btnExtraerIa = document.getElementById('btn-extraer-ia');

        if (btnExtraerIa && facturaInput) {
            btnExtraerIa.addEventListener('click', function(e) {
                e.preventDefault();

                if (!facturaInput.files || !facturaInput.files.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin archivo',
                        text: 'Primero selecciona la factura o documento que deseas analizar.',
                    });
                    return;
                }

                const formData = new FormData();
                formData.append('factura', facturaInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                btnExtraerIa.disabled = true;
                const originalText = btnExtraerIa.innerHTML;
                btnExtraerIa.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Analizando...';

                fetch('{{ route('activos.analizar-factura') }}', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(async (response) => {
                        const contentType = response.headers.get('content-type') || '';
                        const raw = await response.text();
                        let data = null;

                        if (contentType.includes('application/json')) {
                            try {
                                data = JSON.parse(raw);
                            } catch (e) {
                                // Ignorar error de parseo, se manejará abajo
                            }
                        }

                        if (!response.ok || !data || data.ok === false) {
                            let message = (data && data.message) ? data.message : 'No se pudieron extraer datos de la factura.';

                            if (response.status === 419 || response.status === 401) {
                                message = 'Tu sesión ha expirado o no está autorizada para esta acción. Vuelve a iniciar sesión e inténtalo de nuevo.';
                            }

                            if (!contentType.includes('application/json') && raw.startsWith('<')) {
                                // Respuesta HTML (por ejemplo, error de Laravel)
                                message = 'El servidor devolvió una respuesta inesperada. Revisa la consola o el log para más detalles.';
                            }

                            throw new Error(message);
                        }

                        const sugeridos = data.data || {};

                        // Rellenar campos si vienen en la respuesta
                        if (sugeridos.nombre) {
                            document.querySelector('input[name="nombre"]').value = sugeridos.nombre;
                        }
                        if (sugeridos.marca) {
                            document.querySelector('input[name="marca"]').value = sugeridos.marca;
                        }
                        if (sugeridos.serial) {
                            document.querySelector('input[name="serial"]').value = sugeridos.serial;
                        }
                        if (sugeridos.descripcion) {
                            document.querySelector('textarea[name="descripcion"]').value = sugeridos.descripcion;
                        }
                        if (sugeridos.fecha_adquisicion) {
                            document.querySelector('input[name="fecha_adquisicion"]').value = sugeridos.fecha_adquisicion;
                        }
                        if (sugeridos.valor_compra) {
                            document.querySelector('input[name="valor_compra"]').value = sugeridos.valor_compra;
                        }
                        if (sugeridos.tipo && ['FIJO', 'INTANGIBLE'].includes(sugeridos.tipo)) {
                            document.querySelector('select[name="tipo"]').value = sugeridos.tipo;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Datos sugeridos cargados',
                            text: 'Revisa y ajusta los datos antes de guardar el activo.',
                        });
                    })
                    .catch((error) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al analizar factura',
                            text: error.message || 'Ocurrió un error al procesar el archivo.',
                        });
                    })
                    .finally(() => {
                        btnExtraerIa.disabled = false;
                        btnExtraerIa.innerHTML = originalText;
                    });
            });
        }
    });
</script>

@endsection
