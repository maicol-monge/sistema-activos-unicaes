<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\CategoriaActivo;
use App\Models\MovimientoActivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ActivoController extends Controller
{
    public function index()
    {
        $activos = Activo::query()
            ->with(['categoria', 'registrador', 'aprobador'])
            ->orderBy('id_activo', 'desc')
            ->paginate(10);

        return view('activos.index', compact('activos'));
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

    public function aprobaciones()
    {
        $pendientes = Activo::query()
            ->with(['categoria', 'registrador'])
            ->where('estado', 'PENDIENTE')
            ->orderBy('id_activo', 'desc')
            ->paginate(10);

        return view('activos.aprobaciones', compact('pendientes'));
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
