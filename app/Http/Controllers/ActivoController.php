<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\AsignacionActivo;
use App\Models\CategoriaActivo;
use App\Models\MovimientoActivo;
use App\Models\BajaActivo;
use App\Models\User;
use App\Services\FacturaActivoAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ActivoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['PENDIENTE', 'APROBADO', 'RECHAZADO', 'BAJA'])],
            'tipo' => ['nullable', Rule::in(['FIJO', 'INTANGIBLE'])],
            'condicion' => ['nullable', Rule::in(['BUENO', 'DANIADO', 'REGULAR'])],
            'id_categoria_activo' => ['nullable', 'integer', 'exists:categorias_activos,id_categoria_activo'],
        ]);

        $activos = Activo::query()
            ->with(['categoria', 'registrador', 'aprobador'])
            ->when(!empty($filtros['q']), function ($query) use ($filtros) {
                $texto = trim($filtros['q']);
                $query->where(function ($sub) use ($texto) {
                    $sub->where('codigo', 'like', "%{$texto}%")
                        ->orWhere('nombre', 'like', "%{$texto}%")
                        ->orWhere('serial', 'like', "%{$texto}%")
                        ->orWhere('marca', 'like', "%{$texto}%")
                        ->orWhere('descripcion', 'like', "%{$texto}%")
                        ->orWhereHas('categoria', function ($q) use ($texto) {
                            $q->where('nombre', 'like', "%{$texto}%");
                        });
                });
            })
            ->when(!empty($filtros['estado']), fn($query) => $query->where('estado', $filtros['estado']))
            ->when(!empty($filtros['tipo']), fn($query) => $query->where('tipo', $filtros['tipo']))
            ->when(!empty($filtros['condicion']), fn($query) => $query->where('condicion', $filtros['condicion']))
            ->when(!empty($filtros['id_categoria_activo']), fn($query) => $query->where('id_categoria_activo', $filtros['id_categoria_activo']))
            ->orderBy('id_activo', 'desc')
            ->paginate(10)
            ->withQueryString();

        $categorias = CategoriaActivo::query()
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get(['id_categoria_activo', 'nombre']);

        return view('activos.index', compact('activos', 'categorias', 'filtros'));
    }

    public function create()
    {
        $categorias = CategoriaActivo::where('estado', 1)->orderBy('nombre')->get();
        return view('activos.create', compact('categorias'));
    }

    /**
     * Analiza una factura o documento de compra con un servicio de IA externo
     * y devuelve posibles datos para precargar el formulario de activo.
     */
    public function analizarFactura(Request $request, FacturaActivoAiService $aiService)
    {
        try {
            $validator = Validator::make($request->all(), [
                'factura' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp'],
            ], [
                'factura.required' => 'Debes seleccionar un archivo de factura o documento.',
                'factura.mimes' => 'El archivo debe ser PDF o una imagen (jpg, jpeg, png, webp).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false,
                    'message' => $validator->errors()->first('factura') ?? 'El archivo de factura no es válido.',
                ], 422);
            }

            if (!$request->hasFile('factura')) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se recibió ningún archivo de factura.',
                ], 422);
            }

            $datos = $aiService->extraerDatos($request->file('factura'));

            return response()->json([
                'ok' => true,
                'data' => $datos,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => config('app.debug')
                    ? 'No se pudieron extraer datos de la factura. Detalle: ' . $e->getMessage()
                    : 'No se pudieron extraer datos de la factura. Inténtalo de nuevo o completa el formulario manualmente.',
            ], 500);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'codigo' => ['nullable', 'string', 'max:100', 'unique:activos,codigo'],
            'nombre' => ['required', 'string', 'max:50'],
            'serial' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', Rule::in(['FIJO', 'INTANGIBLE'])],
            'marca' => ['nullable', 'string', 'max:255'],
            'id_categoria_activo' => ['required', 'exists:categorias_activos,id_categoria_activo'],
            'fecha_adquisicion' => ['required', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'valor_compra' => ['required', 'numeric', 'min:0.01'],
            'condicion' => ['required', Rule::in(['BUENO', 'DANIADO', 'REGULAR'])],
        ], [
            'nombre.required' => 'El nombre del activo es obligatorio.',
            'tipo.required' => 'El tipo del activo es obligatorio.',
            'id_categoria_activo.required' => 'La categoría es obligatoria.',
            'fecha_adquisicion.required' => 'La fecha de adquisición es obligatoria.',
            'fecha_adquisicion.after_or_equal' => 'La fecha de adquisición no puede ser menor al 13/04/1982.',
            'fecha_adquisicion.before_or_equal' => 'La fecha de adquisición no puede ser futura.',
            'valor_compra.required' => 'El valor de compra es obligatorio.',
            'condicion.required' => 'La condición del activo es obligatoria.',
        ]);

        if (blank($data['serial'] ?? null) && blank($data['descripcion'] ?? null)) {
            return back()
                ->withErrors(['descripcion' => 'Si no ingresas serial, la descripción es obligatoria.'])
                ->withInput();
        }

        $usuario = auth()->user();
        $esAdmin = $usuario->rol === 'ADMIN';

        if (blank($data['codigo'] ?? null)) {
            $data['codigo'] = Activo::generarCodigo();
        }

        $data['estado'] = $esAdmin ? 'APROBADO' : 'PENDIENTE';
        $data['fecha_registro'] = now()->toDateString();
        $data['registrado_por'] = $usuario->id_usuario;
        $data['aprobado_por'] = $esAdmin ? $usuario->id_usuario : null;
        $data['observaciones'] = null;

        DB::transaction(function () use ($data, $usuario, $esAdmin) {
            $activo = Activo::create($data);

            MovimientoActivo::create([
                'id_activo' => $activo->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'CREACION',
                'observaciones' => $esAdmin
                    ? 'Creación de activo por administrador (aprobado automáticamente).'
                    : 'Creación de activo por inventariador, pendiente de aprobación.',
                'fecha' => now()->toDateString(),
                'estado' => 1,
            ]);
        });

        return redirect()->route('activos.index')->with('ok', 'Activo registrado correctamente.');
    }

    public function show(string $id)
    {
        abort(404);
    }

    public function edit(Activo $activo)
    {
        $usuario = auth()->user();

        if ($usuario->rol !== 'ADMIN' && $activo->estado === 'APROBADO') {
            return redirect()->route('activos.index')->with('err', 'No se puede editar un activo aprobado.');
        }

        if ($usuario->rol === 'INVENTARIADOR') {
            if ($activo->registrado_por !== $usuario->id_usuario) {
                return redirect()->route('activos.index')->with('err', 'Solo puedes editar activos registrados por ti.');
            }

            if ($activo->estado !== 'PENDIENTE') {
                return redirect()->route('activos.index')->with('err', 'Solo puedes editar activos que están en estado PENDIENTE.');
            }
        }

        $categorias = CategoriaActivo::where('estado', 1)->orderBy('nombre')->get();
        return view('activos.edit', compact('activo', 'categorias'));
    }

    public function update(Request $request, Activo $activo): RedirectResponse
    {
        $usuario = auth()->user();

        if ($usuario->rol !== 'ADMIN' && $activo->estado === 'APROBADO') {
            return redirect()->route('activos.index')->with('err', 'No se puede editar un activo aprobado.');
        }

        if ($usuario->rol === 'INVENTARIADOR') {
            if ($activo->registrado_por !== $usuario->id_usuario) {
                return redirect()->route('activos.index')->with('err', 'Solo puedes editar activos registrados por ti.');
            }

            if ($activo->estado !== 'PENDIENTE') {
                return redirect()->route('activos.index')->with('err', 'Solo puedes editar activos que están en estado PENDIENTE.');
            }
        }

        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:100', 'unique:activos,codigo,' . $activo->id_activo . ',id_activo'],
            'nombre' => ['required', 'string', 'max:50'],
            'serial' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', Rule::in(['FIJO', 'INTANGIBLE'])],
            'marca' => ['nullable', 'string', 'max:255'],
            'id_categoria_activo' => ['required', 'exists:categorias_activos,id_categoria_activo'],
            'fecha_adquisicion' => ['required', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'valor_compra' => ['required', 'numeric', 'min:0.01'],
            'condicion' => ['required', Rule::in(['BUENO', 'DANIADO', 'REGULAR'])],
        ], [
            'fecha_adquisicion.after_or_equal' => 'La fecha de adquisición no puede ser menor al 13/04/1982.',
            'fecha_adquisicion.before_or_equal' => 'La fecha de adquisición no puede ser futura.',
        ]);

        if (blank($data['serial'] ?? null) && blank($data['descripcion'] ?? null)) {
            return back()
                ->withErrors(['descripcion' => 'Si no ingresas serial, la descripción es obligatoria.'])
                ->withInput();
        }

        if ($usuario->rol === 'INVENTARIADOR') {
            $data['estado'] = 'PENDIENTE';
            $data['aprobado_por'] = null;
        }

        $data['observaciones'] = null;

        DB::transaction(function () use ($activo, $data, $usuario) {
            $activo->update($data);

            MovimientoActivo::create([
                'id_activo' => $activo->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'EDICION',
                'observaciones' => 'Edición de activo.',
                'fecha' => now()->toDateString(),
                'estado' => 1,
            ]);
        });

        return redirect()->route('activos.index')->with('ok', 'Activo actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        abort(404);
    }

    public function bajaDirecta(Request $request, Activo $activo): RedirectResponse
    {
        $usuario = auth()->user();

        if ($usuario->rol !== 'ADMIN') {
            abort(403, 'No autorizado');
        }

        if ($activo->estado === 'BAJA') {
            return back()->with('err', 'El activo ya se encuentra en estado BAJA.');
        }

        if ($activo->estado !== 'APROBADO') {
            return back()->with('err', 'Solo se pueden dar de baja directa activos en estado APROBADO.');
        }

        $data = $request->validate([
            'motivo_baja' => ['required', 'string', 'max:255'],
        ], [
            'motivo_baja.required' => 'El motivo de la baja es obligatorio.',
        ]);

        DB::transaction(function () use ($activo, $usuario, $data) {
            // Registrar solicitud de baja efectiva (tipo administrativa) para historial
            BajaActivo::create([
                'id_activo' => $activo->id_activo,
                'id_usuario_solicitante' => $usuario->id_usuario,
                'motivo' => $data['motivo_baja'],
                'estado' => 'APROBADA',
            ]);

            // Cambiar estado del activo
            $activo->estado = 'BAJA';
            $activo->observaciones = $data['motivo_baja'];
            $activo->save();

            // Cerrar cualquier asignación activa asociada a este activo
            $asignacionesActivas = AsignacionActivo::where('id_activo', $activo->id_activo)
                ->where('estado', 1)
                ->get();

            foreach ($asignacionesActivas as $asignacion) {
                // Si estaba pendiente, se marca como RECHAZADO; si no, se marca como CARGADO (cerrada)
                $nuevoEstadoAsignacion = $asignacion->estado_asignacion === 'PENDIENTE'
                    ? 'RECHAZADO'
                    : 'CARGADO';

                $asignacion->estado_asignacion = $nuevoEstadoAsignacion;
                $asignacion->estado = 0;
                $asignacion->fecha_respuesta = now();
                $asignacion->save();
            }

            // Registrar movimiento de baja
            MovimientoActivo::create([
                'id_activo' => $activo->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'BAJA',
                'observaciones' => 'Baja directa por administrador. Motivo: ' . $data['motivo_baja'],
                'fecha' => now()->toDateString(),
                'estado' => 1,
            ]);
        });

        return redirect()->route('activos.index')->with('ok', 'El activo ha sido dado de baja correctamente.');
    }

    public function aprobaciones(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', Rule::in(['FIJO', 'INTANGIBLE'])],
            'condicion' => ['nullable', Rule::in(['BUENO', 'DANIADO', 'REGULAR'])],
            'id_categoria_activo' => ['nullable', 'integer', 'exists:categorias_activos,id_categoria_activo'],
            'categoria_nombre' => ['nullable', 'string', 'max:100'],
            'registrado_por' => ['nullable', 'integer', 'exists:users,id_usuario'],
            'registrado_por_nombre' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        $pendientes = Activo::query()
            ->with(['categoria', 'registrador'])
            ->where('estado', 'PENDIENTE')
            ->when(!empty($filtros['q']), function ($query) use ($filtros) {
                $texto = trim($filtros['q']);
                $query->where(function ($sub) use ($texto) {
                    $sub->where('codigo', 'like', "%{$texto}%")
                        ->orWhere('nombre', 'like', "%{$texto}%")
                        ->orWhere('serial', 'like', "%{$texto}%")
                        ->orWhere('marca', 'like', "%{$texto}%")
                        ->orWhereHas('categoria', function ($q) use ($texto) {
                            $q->where('nombre', 'like', "%{$texto}%");
                        })
                        ->orWhereHas('registrador', function ($q) use ($texto) {
                            $q->where('nombre', 'like', "%{$texto}%")
                                ->orWhere('correo', 'like', "%{$texto}%");
                        });
                });
            })
            ->when(!empty($filtros['tipo']), fn($query) => $query->where('tipo', $filtros['tipo']))
            ->when(!empty($filtros['condicion']), fn($query) => $query->where('condicion', $filtros['condicion']))
            ->when(!empty($filtros['id_categoria_activo']), fn($query) => $query->where('id_categoria_activo', $filtros['id_categoria_activo']))
            ->when(empty($filtros['id_categoria_activo']) && !empty($filtros['categoria_nombre']), function ($query) use ($filtros) {
                $texto = trim($filtros['categoria_nombre']);
                $query->whereHas('categoria', fn($q) => $q->where('nombre', 'like', "%{$texto}%"));
            })
            ->when(!empty($filtros['registrado_por']), fn($query) => $query->where('registrado_por', $filtros['registrado_por']))
            ->when(empty($filtros['registrado_por']) && !empty($filtros['registrado_por_nombre']), function ($query) use ($filtros) {
                $texto = trim($filtros['registrado_por_nombre']);
                $query->whereHas('registrador', function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('correo', 'like', "%{$texto}%");
                });
            })
            ->when(!empty($filtros['fecha_desde']), fn($query) => $query->whereDate('fecha_registro', '>=', $filtros['fecha_desde']))
            ->when(!empty($filtros['fecha_hasta']), fn($query) => $query->whereDate('fecha_registro', '<=', $filtros['fecha_hasta']))
            ->orderBy('id_activo', 'desc')
            ->paginate(10)
            ->withQueryString();

        if (!empty($filtros['id_categoria_activo']) && empty($filtros['categoria_nombre'])) {
            $categoria = CategoriaActivo::find($filtros['id_categoria_activo']);
            if ($categoria) {
                $filtros['categoria_nombre'] = $categoria->nombre;
            }
        }

        if (!empty($filtros['registrado_por']) && empty($filtros['registrado_por_nombre'])) {
            $registrador = User::find($filtros['registrado_por']);
            if ($registrador) {
                $filtros['registrado_por_nombre'] = $registrador->nombre . ' (' . $registrador->correo . ')';
            }
        }

        $categorias = CategoriaActivo::query()
            ->where('estado', 1)
            ->orderBy('nombre')
            ->limit(30)
            ->get(['id_categoria_activo', 'nombre']);

        $registradores = User::query()
            ->whereIn('rol', ['ADMIN', 'INVENTARIADOR'])
            ->orderBy('nombre')
            ->limit(30)
            ->get(['id_usuario', 'nombre', 'correo']);

        return view('activos.aprobaciones', compact('pendientes', 'filtros', 'categorias', 'registradores'));
    }

    public function buscarCategoriasFiltro(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim($data['q'] ?? '');

        $categorias = CategoriaActivo::query()
            ->where('estado', 1)
            ->when($q !== '', fn($query) => $query->where('nombre', 'like', "%{$q}%"))
            ->orderBy('nombre')
            ->limit(15)
            ->get(['id_categoria_activo', 'nombre'])
            ->map(fn($categoria) => [
                'id' => $categoria->id_categoria_activo,
                'label' => $categoria->nombre,
            ])
            ->values();

        return response()->json($categorias);
    }

    public function buscarRegistradoresFiltro(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim($data['q'] ?? '');

        $registradores = User::query()
            ->whereIn('rol', ['ADMIN', 'INVENTARIADOR'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('correo', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->limit(15)
            ->get(['id_usuario', 'nombre', 'correo'])
            ->map(fn($usuario) => [
                'id' => $usuario->id_usuario,
                'label' => $usuario->nombre . ' (' . $usuario->correo . ')',
            ])
            ->values();

        return response()->json($registradores);
    }

    public function aprobar(Activo $activo): RedirectResponse
    {
        if ($activo->estado !== 'PENDIENTE') {
            return redirect()->route('activos.aprobaciones')
                ->with('err', 'Solo los activos pendientes pueden aprobarse.');
        }

        $usuario = auth()->user();

        DB::transaction(function () use ($activo, $usuario) {
            $activo->update([
                'estado' => 'APROBADO',
                'aprobado_por' => $usuario->id_usuario,
                'observaciones' => null,
            ]);

            MovimientoActivo::create([
                'id_activo' => $activo->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'EDICION',
                'observaciones' => 'Aprobación de activo por administrador.',
                'fecha' => now()->toDateString(),
                'estado' => 1,
            ]);
        });

        return redirect()->route('activos.aprobaciones')->with('ok', 'Activo aprobado correctamente.');
    }

    public function rechazar(Request $request, Activo $activo): RedirectResponse
    {
        if ($activo->estado !== 'PENDIENTE') {
            return redirect()->route('activos.aprobaciones')
                ->with('err', 'Solo los activos pendientes pueden rechazarse.');
        }

        $request->validate([
            'observaciones' => ['required', 'string'],
        ], [
            'observaciones.required' => 'Debes ingresar una observación para rechazar el activo.',
        ]);

        $usuario = auth()->user();

        DB::transaction(function () use ($activo, $request, $usuario) {
            $activo->update([
                'estado' => 'RECHAZADO',
                'aprobado_por' => $usuario->id_usuario,
                'observaciones' => $request->observaciones,
            ]);

            MovimientoActivo::create([
                'id_activo' => $activo->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'EDICION',
                'observaciones' => 'Rechazo de activo por administrador. Motivo: ' . $request->observaciones,
                'fecha' => now()->toDateString(),
                'estado' => 1,
            ]);
        });

        return redirect()->route('activos.aprobaciones')->with('ok', 'Activo rechazado correctamente.');
    }

    public function historial(Activo $activo)
    {
        $usuario = auth()->user();

        if ($usuario->rol !== 'ADMIN') {
            abort(403, 'No autorizado');
        }

        $activo->load(['categoria', 'registrador', 'aprobador']);

        $asignaciones = $activo->asignaciones()
            ->with(['usuarioAsignado', 'usuarioAsignador'])
            ->orderByDesc('fecha_asignacion')
            ->orderByDesc('id_asignacion')
            ->get();

        $asignacionActual = $activo->asignaciones()
            ->with(['usuarioAsignado'])
            ->where('estado', 1)
            ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION'])
            ->orderByDesc('fecha_asignacion')
            ->orderByDesc('id_asignacion')
            ->first();

        $movimientos = $activo->movimientos()
            ->with('usuario')
            ->orderByDesc('fecha')
            ->orderByDesc('id_movimiento')
            ->get();

        return view('activos.historial', compact('activo', 'asignacionActual', 'asignaciones', 'movimientos'));
    }
}
