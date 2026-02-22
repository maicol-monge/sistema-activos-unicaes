<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\AsignacionActivo;
use App\Models\ReporteActivo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReporteActivoController extends Controller
{
    public function index()
    {
        abort(404);
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function show(string $id)
    {
        abort(404);
    }

    public function edit(string $id)
    {
        abort(404);
    }

    public function update(Request $request, string $id)
    {
        abort(404);
    }

    public function destroy(string $id)
    {
        abort(404);
    }

    public function misReportes(Request $request)
    {
        $usuarioId = auth()->user()->id_usuario;

        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $activosIds = AsignacionActivo::query()
            ->where('id_usuario', $usuarioId)
            ->where('estado', 1)
            ->where('estado_asignacion', 'ACEPTADO')
            ->pluck('id_activo');

        $activos = Activo::query()
            ->whereIn('id_activo', $activosIds)
            ->when(!empty($filtros['q']), function ($query) use ($filtros) {
                $texto = trim($filtros['q']);
                $query->where(function ($sub) use ($texto) {
                    $sub->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('codigo', 'like', "%{$texto}%");
                });
            })
            ->withCount('reportes')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('reportes-activos.encargado-index', compact('activos', 'filtros'));
    }

    public function createEncargado(Request $request)
    {
        $usuarioId = auth()->user()->id_usuario;

        $activosIds = AsignacionActivo::query()
            ->where('id_usuario', $usuarioId)
            ->where('estado', 1)
            ->where('estado_asignacion', 'ACEPTADO')
            ->pluck('id_activo');

        $activos = Activo::query()
            ->whereIn('id_activo', $activosIds)
            ->orderBy('nombre')
            ->get(['id_activo', 'codigo', 'nombre']);

        $activoPreseleccionado = null;
        if ($request->filled('id_activo')) {
            $activoPreseleccionado = (string) $request->id_activo;
        }

        return view('reportes-activos.encargado-create', compact('activos', 'activoPreseleccionado'));
    }

    public function storeEncargado(Request $request)
    {
        $usuarioId = auth()->user()->id_usuario;

        $activosIds = AsignacionActivo::query()
            ->where('id_usuario', $usuarioId)
            ->where('estado', 1)
            ->where('estado_asignacion', 'ACEPTADO')
            ->pluck('id_activo')
            ->all();

        $data = $request->validate([
            'id_activo' => ['required', 'integer', Rule::in($activosIds)],
            'estado_reporte' => ['required', Rule::in(['BUENO', 'DANIADO', 'PERDIDO'])],
            'comentario' => ['required', 'string', 'max:1000'],
            'fecha' => ['required', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
        ], [
            'id_activo.required' => 'Debes seleccionar un activo asignado.',
            'id_activo.in' => 'Solo puedes reportar activos que tienes asignados.',
            'estado_reporte.required' => 'El estado del reporte es obligatorio.',
            'comentario.required' => 'El comentario es obligatorio.',
            'fecha.required' => 'La fecha del reporte es obligatoria.',
            'fecha.before_or_equal' => 'La fecha del reporte no puede ser futura.',
            'fecha.after_or_equal' => 'La fecha del reporte no puede ser menor al 13/04/1982.',
        ]);

        ReporteActivo::create([
            'id_activo' => $data['id_activo'],
            'id_usuario' => $usuarioId,
            'estado_reporte' => $data['estado_reporte'],
            'comentario' => $data['comentario'],
            'fecha' => $data['fecha'],
            'estado' => 1,
        ]);

        return redirect()->route('encargado.reportes.historial', $data['id_activo'])
            ->with('ok', 'Reporte de estado registrado correctamente.');
    }

    public function historialPorActivo(Activo $activo)
    {
        $usuarioId = auth()->user()->id_usuario;

        $puedeVer = AsignacionActivo::query()
            ->where('id_usuario', $usuarioId)
            ->where('id_activo', $activo->id_activo)
            ->exists();

        if (!$puedeVer) {
            abort(403, 'No autorizado');
        }

        $reportes = ReporteActivo::query()
            ->with('usuario')
            ->where('id_activo', $activo->id_activo)
            ->orderBy('fecha', 'desc')
            ->orderBy('id_reporte', 'desc')
            ->paginate(10);

        return view('reportes-activos.encargado-historial', compact('activo', 'reportes'));
    }
}
