<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\CategoriaActivo;
use App\Models\MovimientoActivo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'fecha_adquisicion' => ['required', 'date'],
            'valor_compra' => ['required', 'numeric', 'min:0.01'],
            'condicion' => ['required', Rule::in(['BUENO', 'DANIADO', 'REGULAR'])],
        ], [
            'nombre.required' => 'El nombre del activo es obligatorio.',
            'tipo.required' => 'El tipo del activo es obligatorio.',
            'id_categoria_activo.required' => 'La categoría es obligatoria.',
            'fecha_adquisicion.required' => 'La fecha de adquisición es obligatoria.',
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
            $data['codigo'] = 'ACT-' . now()->format('YmdHis') . '-' . random_int(100, 999);
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

        if ($activo->estado === 'APROBADO') {
            return redirect()->route('activos.index')->with('err', 'No se puede editar un activo aprobado.');
        }

        if ($usuario->rol === 'INVENTARIADOR' && $activo->registrado_por !== $usuario->id_usuario) {
            return redirect()->route('activos.index')->with('err', 'Solo puedes editar activos registrados por ti.');
        }

        $categorias = CategoriaActivo::where('estado', 1)->orderBy('nombre')->get();
        return view('activos.edit', compact('activo', 'categorias'));
    }

    public function update(Request $request, Activo $activo): RedirectResponse
    {
        $usuario = auth()->user();

        if ($activo->estado === 'APROBADO') {
            return redirect()->route('activos.index')->with('err', 'No se puede editar un activo aprobado.');
        }

        if ($usuario->rol === 'INVENTARIADOR' && $activo->registrado_por !== $usuario->id_usuario) {
            return redirect()->route('activos.index')->with('err', 'Solo puedes editar activos registrados por ti.');
        }

        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:100', 'unique:activos,codigo,' . $activo->id_activo . ',id_activo'],
            'nombre' => ['required', 'string', 'max:50'],
            'serial' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', Rule::in(['FIJO', 'INTANGIBLE'])],
            'marca' => ['nullable', 'string', 'max:255'],
            'id_categoria_activo' => ['required', 'exists:categorias_activos,id_categoria_activo'],
            'fecha_adquisicion' => ['required', 'date'],
            'valor_compra' => ['required', 'numeric', 'min:0.01'],
            'condicion' => ['required', Rule::in(['BUENO', 'DANIADO', 'REGULAR'])],
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
}
