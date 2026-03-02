<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\AsignacionActivo;
use App\Models\BajaActivo;
use App\Models\MovimientoActivo;
use App\Models\ReporteActivo;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\ReportesAiService;

class ReporteActivoController extends Controller
{
    /**
     * Listado y filtros de reportes para ADMIN.
     */
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'estado_reporte' => ['nullable', Rule::in(['BUENO', 'DANIADO', 'PERDIDO'])],
        ]);

        $query = ReporteActivo::query()
            ->with(['activo', 'usuario'])
            ->where('estado', true);

        if (!empty($filtros['q'])) {
            $texto = trim($filtros['q']);
            $query->where(function ($sub) use ($texto) {
                $sub->whereHas('activo', function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('codigo', 'like', "%{$texto}%");
                })->orWhereHas('usuario', function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%");
                });
            });
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['estado_reporte'])) {
            $query->where('estado_reporte', $filtros['estado_reporte']);
        }

        $reportes = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id_reporte')
            ->paginate(15)
            ->withQueryString();

        $charts = $this->buildGlobalChartsData($filtros);

        return view('reportes-activos.admin-index', compact('reportes', 'filtros', 'charts'));
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
            ->where('asignado_a', $usuarioId)
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
            ->where('asignado_a', $usuarioId)
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
            ->where('asignado_a', $usuarioId)
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
            ->where('asignado_a', $usuarioId)
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

    /**
     * Descargar PDF de reportes filtrados para ADMIN.
     */
    public function adminPdf(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'estado_reporte' => ['nullable', Rule::in(['BUENO', 'DANIADO', 'PERDIDO'])],
        ]);

        $query = ReporteActivo::query()
            ->with(['activo', 'usuario'])
            ->where('estado', true);

        if (!empty($filtros['q'])) {
            $texto = trim($filtros['q']);
            $query->where(function ($sub) use ($texto) {
                $sub->whereHas('activo', function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('codigo', 'like', "%{$texto}%");
                })->orWhereHas('usuario', function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%");
                });
            });
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['estado_reporte'])) {
            $query->where('estado_reporte', $filtros['estado_reporte']);
        }

        $reportes = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id_reporte')
            ->get();

        $pdf = Pdf::loadView('reportes-activos.admin-pdf', compact('reportes', 'filtros'))
            ->setPaper('letter', 'portrait');

        $fileName = 'reporte_estado_activos_admin_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Vista de reportes y estadísticas para DECANO.
     */
    public function decanoIndex(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'estado_reporte' => ['nullable', Rule::in(['BUENO', 'DANIADO', 'PERDIDO'])],
            'tipo' => ['nullable', Rule::in(['reportes', 'activos', 'asignaciones', 'movimientos', 'bajas', 'usuarios'])],
        ]);

        $tipo = $filtros['tipo'] ?? 'reportes';

        $reportes = $activos = $asignaciones = $movimientos = $bajas = $usuarios = null;
        $resumen = null;

        if ($tipo === 'reportes') {
            $query = ReporteActivo::query()
                ->with(['activo', 'usuario'])
                ->where('estado', true);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->whereHas('activo', function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('codigo', 'like', "%{$texto}%");
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
            }

            if (!empty($filtros['estado_reporte'])) {
                $query->where('estado_reporte', $filtros['estado_reporte']);
            }

            $reportes = $query
                ->orderByDesc('fecha')
                ->orderByDesc('id_reporte')
                ->paginate(15)
                ->withQueryString();

            $totalReportes = (clone $query)->count('id_reporte');
            $reportesBuenos = (clone $query)->where('estado_reporte', 'BUENO')->count('id_reporte');
            $reportesDaniados = (clone $query)->where('estado_reporte', 'DANIADO')->count('id_reporte');
            $reportesPerdidos = (clone $query)->where('estado_reporte', 'PERDIDO')->count('id_reporte');

            $resumen = [
                'total' => $totalReportes,
                'buenos' => $reportesBuenos,
                'daniados' => $reportesDaniados,
                'perdidos' => $reportesPerdidos,
            ];
        } elseif ($tipo === 'activos') {
            $query = Activo::query()->with(['categoria', 'registrador', 'aprobador']);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('codigo', 'like', "%{$texto}%")
                        ->orWhere('serial', 'like', "%{$texto}%")
                        ->orWhere('marca', 'like', "%{$texto}%");
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha_adquisicion', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha_adquisicion', '<=', $filtros['fecha_hasta']);
            }

            $activos = $query
                ->orderBy('nombre')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tipo === 'asignaciones') {
            $query = AsignacionActivo::query()
                ->with(['activo', 'usuarioAsignado', 'usuarioAsignador']);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->whereHas('activo', function ($qa) use ($texto) {
                        $qa->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                    })->orWhereHas('usuarioAsignado', function ($qu) use ($texto) {
                        $qu->where('nombre', 'like', "%{$texto}%");
                    })->orWhereHas('usuarioAsignador', function ($qu) use ($texto) {
                        $qu->where('nombre', 'like', "%{$texto}%");
                    });
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha_asignacion', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha_asignacion', '<=', $filtros['fecha_hasta']);
            }

            $asignaciones = $query
                ->orderByDesc('fecha_asignacion')
                ->orderByDesc('id_asignacion')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tipo === 'movimientos') {
            $query = MovimientoActivo::query()
                ->with(['activo', 'usuario']);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->whereHas('activo', function ($qa) use ($texto) {
                        $qa->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                    })->orWhereHas('usuario', function ($qu) use ($texto) {
                        $qu->where('nombre', 'like', "%{$texto}%");
                    })->orWhere('tipo', 'like', "%{$texto}%");
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
            }

            $movimientos = $query
                ->orderByDesc('fecha')
                ->orderByDesc('id_movimiento')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tipo === 'bajas') {
            $query = BajaActivo::query()
                ->with(['activo', 'solicitante']);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->whereHas('activo', function ($qa) use ($texto) {
                        $qa->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                    })->orWhereHas('solicitante', function ($qu) use ($texto) {
                        $qu->where('nombre', 'like', "%{$texto}%");
                    })->orWhere('estado', 'like', "%{$texto}%");
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('created_at', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('created_at', '<=', $filtros['fecha_hasta']);
            }

            $bajas = $query
                ->orderByDesc('created_at')
                ->orderByDesc('id_baja')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tipo === 'usuarios') {
            $query = User::query();

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('correo', 'like', "%{$texto}%")
                        ->orWhere('rol', 'like', "%{$texto}%");
                });
            }

            $usuarios = $query
                ->orderBy('nombre')
                ->paginate(15)
                ->withQueryString();
        }

        $charts = $this->buildGlobalChartsData($filtros);

        return view('reportes-activos.decano-index', compact(
            'tipo', 'filtros', 'reportes', 'activos', 'asignaciones', 'movimientos', 'bajas', 'usuarios', 'resumen', 'charts'
        ));
    }

    /**
     * Descargar PDF de la consulta del DECANO (según tipo seleccionado).
     */
    public function decanoPdf(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'estado_reporte' => ['nullable', Rule::in(['BUENO', 'DANIADO', 'PERDIDO'])],
            'tipo' => ['nullable', Rule::in(['reportes', 'activos', 'asignaciones', 'movimientos', 'bajas', 'usuarios'])],
        ]);

        $tipo = $filtros['tipo'] ?? 'reportes';

        // Inicializamos todas las colecciones como null
        $reportes = $activos = $asignaciones = $movimientos = $bajas = $usuarios = null;

        if ($tipo === 'reportes') {
            $query = ReporteActivo::query()
                ->with(['activo', 'usuario'])
                ->where('estado', true);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->whereHas('activo', function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('codigo', 'like', "%{$texto}%");
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
            }

            if (!empty($filtros['estado_reporte'])) {
                $query->where('estado_reporte', $filtros['estado_reporte']);
            }

            $reportes = $query
                ->orderByDesc('fecha')
                ->orderByDesc('id_reporte')
                ->get();

            $pdf = Pdf::loadView('reportes-activos.decano-pdf', compact('reportes', 'filtros'))
                ->setPaper('letter', 'portrait');

            $fileName = 'reporte_estado_activos_decano_' . now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } elseif ($tipo === 'activos') {
            $query = Activo::query()->with(['categoria', 'registrador', 'aprobador']);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('codigo', 'like', "%{$texto}%")
                        ->orWhere('serial', 'like', "%{$texto}%")
                        ->orWhere('marca', 'like', "%{$texto}%");
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha_adquisicion', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha_adquisicion', '<=', $filtros['fecha_hasta']);
            }

            $activos = $query
                ->orderBy('nombre')
                ->get();
        } elseif ($tipo === 'asignaciones') {
            $query = AsignacionActivo::query()
                ->with(['activo', 'usuarioAsignado', 'usuarioAsignador']);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->whereHas('activo', function ($qa) use ($texto) {
                        $qa->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                    })->orWhereHas('usuarioAsignado', function ($qu) use ($texto) {
                        $qu->where('nombre', 'like', "%{$texto}%");
                    })->orWhereHas('usuarioAsignador', function ($qu) use ($texto) {
                        $qu->where('nombre', 'like', "%{$texto}%");
                    });
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha_asignacion', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha_asignacion', '<=', $filtros['fecha_hasta']);
            }

            $asignaciones = $query
                ->orderByDesc('fecha_asignacion')
                ->orderByDesc('id_asignacion')
                ->get();
        } elseif ($tipo === 'movimientos') {
            $query = MovimientoActivo::query()
                ->with(['activo', 'usuario']);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->whereHas('activo', function ($qa) use ($texto) {
                        $qa->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                    })->orWhereHas('usuario', function ($qu) use ($texto) {
                        $qu->where('nombre', 'like', "%{$texto}%");
                    })->orWhere('tipo', 'like', "%{$texto}%");
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
            }

            $movimientos = $query
                ->orderByDesc('fecha')
                ->orderByDesc('id_movimiento')
                ->get();
        } elseif ($tipo === 'bajas') {
            $query = BajaActivo::query()
                ->with(['activo', 'solicitante']);

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->whereHas('activo', function ($qa) use ($texto) {
                        $qa->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                    })->orWhereHas('solicitante', function ($qu) use ($texto) {
                        $qu->where('nombre', 'like', "%{$texto}%");
                    })->orWhere('estado', 'like', "%{$texto}%");
                });
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('created_at', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('created_at', '<=', $filtros['fecha_hasta']);
            }

            $bajas = $query
                ->orderByDesc('created_at')
                ->orderByDesc('id_baja')
                ->get();
        } elseif ($tipo === 'usuarios') {
            $query = User::query();

            if (!empty($filtros['q'])) {
                $texto = trim($filtros['q']);
                $query->where(function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('correo', 'like', "%{$texto}%")
                        ->orWhere('rol', 'like', "%{$texto}%");
                });
            }

            $usuarios = $query
                ->orderBy('nombre')
                ->get();
        }

        // Para tipos distintos de "reportes" usamos una vista genérica de tabla
        $pdf = Pdf::loadView('reportes-activos.decano-consulta-pdf', compact(
            'tipo', 'filtros', 'reportes', 'activos', 'asignaciones', 'movimientos', 'bajas', 'usuarios'
        ))->setPaper('letter', 'landscape');

        $fileName = 'consulta_decano_' . $tipo . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Exportar consulta del DECANO (según tipo) a CSV.
     */
    public function decanoCsv(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'estado_reporte' => ['nullable', Rule::in(['BUENO', 'DANIADO', 'PERDIDO'])],
            'tipo' => ['nullable', Rule::in(['reportes', 'activos', 'asignaciones', 'movimientos', 'bajas', 'usuarios'])],
        ]);

        $tipo = $filtros['tipo'] ?? 'reportes';

        $fileName = 'consulta_decano_' . $tipo . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($tipo, $filtros) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para que Excel reconozca correctamente acentos y eñes
            fwrite($handle, "\xEF\xBB\xBF");

            if ($tipo === 'reportes') {
                $query = ReporteActivo::query()
                    ->with(['activo', 'usuario'])
                    ->where('estado', true);

                if (!empty($filtros['q'])) {
                    $texto = trim($filtros['q']);
                    $query->whereHas('activo', function ($q) use ($texto) {
                        $q->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                    });
                }

                if (!empty($filtros['fecha_desde'])) {
                    $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
                }

                if (!empty($filtros['fecha_hasta'])) {
                    $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
                }

                if (!empty($filtros['estado_reporte'])) {
                    $query->where('estado_reporte', $filtros['estado_reporte']);
                }

                fputcsv($handle, ['Fecha', 'Activo', 'Código', 'Estado reporte', 'Usuario', 'Comentario']);

                $query->orderByDesc('fecha')->orderByDesc('id_reporte')->chunk(500, function ($chunk) use ($handle) {
                    foreach ($chunk as $reporte) {
                        fputcsv($handle, [
                            optional($reporte->fecha)->format('Y-m-d') ?? $reporte->fecha,
                            $reporte->activo?->nombre ?? '',
                            $reporte->activo?->codigo ?? '',
                            $reporte->estado_reporte,
                            $reporte->usuario?->nombre ?? '',
                            $reporte->comentario,
                        ]);
                    }
                });
            } elseif ($tipo === 'activos') {
                $query = Activo::query()->with(['categoria']);

                if (!empty($filtros['q'])) {
                    $texto = trim($filtros['q']);
                    $query->where(function ($q) use ($texto) {
                        $q->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%")
                            ->orWhere('serial', 'like', "%{$texto}%")
                            ->orWhere('marca', 'like', "%{$texto}%");
                    });
                }

                if (!empty($filtros['fecha_desde'])) {
                    $query->whereDate('fecha_adquisicion', '>=', $filtros['fecha_desde']);
                }

                if (!empty($filtros['fecha_hasta'])) {
                    $query->whereDate('fecha_adquisicion', '<=', $filtros['fecha_hasta']);
                }

                fputcsv($handle, ['Código', 'Nombre', 'Tipo', 'Condición', 'Estado', 'Categoría', 'Valor compra', 'Fecha adquisición']);

                $query->orderBy('nombre')->chunk(500, function ($chunk) use ($handle) {
                    foreach ($chunk as $activo) {
                        fputcsv($handle, [
                            $activo->codigo,
                            $activo->nombre,
                            $activo->tipo,
                            $activo->condicion,
                            $activo->estado,
                            $activo->categoria?->nombre ?? '',
                            $activo->valor_compra,
                            $activo->fecha_adquisicion,
                        ]);
                    }
                });
            } elseif ($tipo === 'asignaciones') {
                $query = AsignacionActivo::query()
                    ->with(['activo', 'usuarioAsignado', 'usuarioAsignador']);

                if (!empty($filtros['q'])) {
                    $texto = trim($filtros['q']);
                    $query->where(function ($q) use ($texto) {
                        $q->whereHas('activo', function ($qa) use ($texto) {
                            $qa->where('nombre', 'like', "%{$texto}%")
                                ->orWhere('codigo', 'like', "%{$texto}%");
                        })->orWhereHas('usuarioAsignado', function ($qu) use ($texto) {
                            $qu->where('nombre', 'like', "%{$texto}%");
                        })->orWhereHas('usuarioAsignador', function ($qu) use ($texto) {
                            $qu->where('nombre', 'like', "%{$texto}%");
                        });
                    });
                }

                if (!empty($filtros['fecha_desde'])) {
                    $query->whereDate('fecha_asignacion', '>=', $filtros['fecha_desde']);
                }

                if (!empty($filtros['fecha_hasta'])) {
                    $query->whereDate('fecha_asignacion', '<=', $filtros['fecha_hasta']);
                }

                fputcsv($handle, ['Fecha asignación', 'Activo', 'Código', 'Asignado a', 'Asignado por', 'Estado asignación']);

                $query->orderByDesc('fecha_asignacion')->orderByDesc('id_asignacion')->chunk(500, function ($chunk) use ($handle) {
                    foreach ($chunk as $asignacion) {
                        fputcsv($handle, [
                            $asignacion->fecha_asignacion,
                            $asignacion->activo?->nombre ?? '',
                            $asignacion->activo?->codigo ?? '',
                            $asignacion->usuarioAsignado?->nombre ?? '',
                            $asignacion->usuarioAsignador?->nombre ?? '',
                            $asignacion->estado_asignacion,
                        ]);
                    }
                });
            } elseif ($tipo === 'movimientos') {
                $query = MovimientoActivo::query()
                    ->with(['activo', 'usuario']);

                if (!empty($filtros['q'])) {
                    $texto = trim($filtros['q']);
                    $query->where(function ($q) use ($texto) {
                        $q->whereHas('activo', function ($qa) use ($texto) {
                            $qa->where('nombre', 'like', "%{$texto}%")
                                ->orWhere('codigo', 'like', "%{$texto}%");
                        })->orWhereHas('usuario', function ($qu) use ($texto) {
                            $qu->where('nombre', 'like', "%{$texto}%");
                        })->orWhere('tipo', 'like', "%{$texto}%");
                    });
                }

                if (!empty($filtros['fecha_desde'])) {
                    $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
                }

                if (!empty($filtros['fecha_hasta'])) {
                    $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
                }

                fputcsv($handle, ['Fecha', 'Tipo', 'Activo', 'Código', 'Usuario', 'Observaciones']);

                $query->orderByDesc('fecha')->orderByDesc('id_movimiento')->chunk(500, function ($chunk) use ($handle) {
                    foreach ($chunk as $movimiento) {
                        fputcsv($handle, [
                            $movimiento->fecha,
                            $movimiento->tipo,
                            $movimiento->activo?->nombre ?? '',
                            $movimiento->activo?->codigo ?? '',
                            $movimiento->usuario?->nombre ?? '',
                            $movimiento->observaciones,
                        ]);
                    }
                });
            } elseif ($tipo === 'bajas') {
                $query = BajaActivo::query()
                    ->with(['activo', 'solicitante']);

                if (!empty($filtros['q'])) {
                    $texto = trim($filtros['q']);
                    $query->where(function ($q) use ($texto) {
                        $q->whereHas('activo', function ($qa) use ($texto) {
                            $qa->where('nombre', 'like', "%{$texto}%")
                                ->orWhere('codigo', 'like', "%{$texto}%");
                        })->orWhereHas('solicitante', function ($qu) use ($texto) {
                            $qu->where('nombre', 'like', "%{$texto}%");
                        })->orWhere('estado', 'like', "%{$texto}%");
                    });
                }

                if (!empty($filtros['fecha_desde'])) {
                    $query->whereDate('created_at', '>=', $filtros['fecha_desde']);
                }

                if (!empty($filtros['fecha_hasta'])) {
                    $query->whereDate('created_at', '<=', $filtros['fecha_hasta']);
                }

                fputcsv($handle, ['Fecha solicitud', 'Activo', 'Código', 'Solicitante', 'Estado', 'Motivo']);

                $query->orderByDesc('created_at')->orderByDesc('id_baja')->chunk(500, function ($chunk) use ($handle) {
                    foreach ($chunk as $baja) {
                        fputcsv($handle, [
                            $baja->created_at,
                            $baja->activo?->nombre ?? '',
                            $baja->activo?->codigo ?? '',
                            $baja->solicitante?->nombre ?? '',
                            $baja->estado,
                            $baja->motivo,
                        ]);
                    }
                });
            } elseif ($tipo === 'usuarios') {
                $query = User::query();

                if (!empty($filtros['q'])) {
                    $texto = trim($filtros['q']);
                    $query->where(function ($q) use ($texto) {
                        $q->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('correo', 'like', "%{$texto}%")
                            ->orWhere('rol', 'like', "%{$texto}%");
                    });
                }

                fputcsv($handle, ['Nombre', 'Correo', 'Rol', 'Tipo', 'Estado']);

                $query->orderBy('nombre')->chunk(500, function ($chunk) use ($handle) {
                    foreach ($chunk as $usuario) {
                        fputcsv($handle, [
                            $usuario->nombre,
                            $usuario->correo,
                            $usuario->rol,
                            $usuario->tipo,
                            $usuario->estado,
                        ]);
                    }
                });
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Exportar reportes del DECANO (globales) a "Excel" (CSV con MIME de Excel).
     */
    public function decanoExcel(Request $request)
    {
        $filtros = $request->all();
        $request->merge($filtros);

        $response = $this->decanoCsv($request);

        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $disposition = $response->headers->get('Content-Disposition');
        $disposition = str_replace('.csv"', '.xls"', $disposition ?? '');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Endpoint de IA para responder consultas en lenguaje natural
     * usando los datos agregados del sistema (ADMIN/DECANO).
     */
    public function decanoIaConsulta(Request $request, ReportesAiService $aiService)
    {
        $data = $request->validate([
            'pregunta' => ['required', 'string', 'max:400'],
            'tipo' => ['nullable', Rule::in(['reportes', 'activos', 'asignaciones', 'movimientos', 'bajas', 'usuarios'])],
            'fecha_desde' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:1982-04-13', 'before_or_equal:today'],
        ]);

        $tipo = $data['tipo'] ?? 'reportes';

        // Solo usamos fecha_desde / fecha_hasta para acotar los datos agregados
        $filtros = [
            'fecha_desde' => $data['fecha_desde'] ?? null,
            'fecha_hasta' => $data['fecha_hasta'] ?? null,
        ];

        $charts = $this->buildGlobalChartsData($filtros);

        $contexto = [
            'tipo_actual' => $tipo,
            'filtros' => $filtros,
            'charts' => $charts,
        ];

        try {
            $respuesta = $aiService->responderConsulta($data['pregunta'], $contexto);
        } catch (\Throwable $e) {
            report($e);
            $respuesta = '';
        }

        if (trim($respuesta) === '') {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo obtener una respuesta de la IA en este momento. Intenta nuevamente más tarde.',
            ], 200);
        }

        return response()->json([
            'ok' => true,
            'respuesta' => $respuesta,
        ]);
    }

    /**
     * Construye datos agregados globales para gráficas (ADMIN y DECANO).
     */
    private function buildGlobalChartsData(array $filtros): array
    {
        $fechaDesde = $filtros['fecha_desde'] ?? null;
        $fechaHasta = $filtros['fecha_hasta'] ?? null;

        // -------- Activos: por categoría --------
        $activosPorCategoriaQuery = Activo::query()
            ->select('categorias_activos.nombre as categoria', DB::raw('COUNT(activos.id_activo) as total'))
            ->leftJoin('categorias_activos', 'categorias_activos.id_categoria_activo', '=', 'activos.id_categoria_activo');

        if ($fechaDesde) {
            $activosPorCategoriaQuery->whereDate('fecha_adquisicion', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $activosPorCategoriaQuery->whereDate('fecha_adquisicion', '<=', $fechaHasta);
        }

        $activosPorCategoriaRows = $activosPorCategoriaQuery
            ->groupBy('categorias_activos.nombre')
            ->orderBy('categorias_activos.nombre')
            ->get();

        $activosPorCategoria = [
            'labels' => $activosPorCategoriaRows->pluck('categoria')->map(fn($v) => $v ?? 'Sin categoría')->all(),
            'data' => $activosPorCategoriaRows->pluck('total')->map(fn($v) => (int) $v)->all(),
        ];

        // -------- Activos: por estado --------
        $activosPorEstadoQuery = Activo::query()
            ->select('estado', DB::raw('COUNT(*) as total'));

        if ($fechaDesde) {
            $activosPorEstadoQuery->whereDate('fecha_adquisicion', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $activosPorEstadoQuery->whereDate('fecha_adquisicion', '<=', $fechaHasta);
        }

        $activosPorEstadoRows = $activosPorEstadoQuery
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        $activosPorEstado = [
            'labels' => $activosPorEstadoRows->pluck('estado')->all(),
            'data' => $activosPorEstadoRows->pluck('total')->map(fn($v) => (int) $v)->all(),
        ];

        // -------- Activos: por condición --------
        $activosPorCondicionQuery = Activo::query()
            ->select('condicion', DB::raw('COUNT(*) as total'));

        if ($fechaDesde) {
            $activosPorCondicionQuery->whereDate('fecha_adquisicion', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $activosPorCondicionQuery->whereDate('fecha_adquisicion', '<=', $fechaHasta);
        }

        $activosPorCondicionRows = $activosPorCondicionQuery
            ->groupBy('condicion')
            ->orderBy('condicion')
            ->get();

        $activosPorCondicion = [
            'labels' => $activosPorCondicionRows->pluck('condicion')->all(),
            'data' => $activosPorCondicionRows->pluck('total')->map(fn($v) => (int) $v)->all(),
        ];

        // -------- Reportes: por estado --------
        $reportesBase = ReporteActivo::query()->where('estado', true);
        if ($fechaDesde) {
            $reportesBase->whereDate('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $reportesBase->whereDate('fecha', '<=', $fechaHasta);
        }

        $reportesPorEstadoRows = (clone $reportesBase)
            ->select('estado_reporte', DB::raw('COUNT(*) as total'))
            ->groupBy('estado_reporte')
            ->orderBy('estado_reporte')
            ->get();

        $reportesPorEstado = [
            'labels' => $reportesPorEstadoRows->pluck('estado_reporte')->all(),
            'data' => $reportesPorEstadoRows->pluck('total')->map(fn($v) => (int) $v)->all(),
        ];

        // -------- Reportes: por mes (últimos 12 o rango) --------
        $reportesPorMesQuery = ReporteActivo::query()->where('estado', true);
        if ($fechaDesde) {
            $reportesPorMesQuery->whereDate('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $reportesPorMesQuery->whereDate('fecha', '<=', $fechaHasta);
        }

        $reportesPorMesRows = $reportesPorMesQuery
            ->select(DB::raw("DATE_FORMAT(fecha, '%Y-%m') as periodo"), DB::raw('COUNT(*) as total'))
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $reportesPorMesLabels = [];
        $reportesPorMesData = [];
        foreach ($reportesPorMesRows as $row) {
            $periodo = $row->periodo;
            if (!$periodo) {
                continue;
            }
            $carbon = Carbon::createFromFormat('Y-m', $periodo);
            $reportesPorMesLabels[] = $carbon->format('m/Y');
            $reportesPorMesData[] = (int) $row->total;
        }

        $reportesPorMes = [
            'labels' => $reportesPorMesLabels,
            'data' => $reportesPorMesData,
        ];

        // -------- Bajas: por estado --------
        $bajasPorEstadoQuery = BajaActivo::query()
            ->select('estado', DB::raw('COUNT(*) as total'));

        if ($fechaDesde) {
            $bajasPorEstadoQuery->whereDate('created_at', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $bajasPorEstadoQuery->whereDate('created_at', '<=', $fechaHasta);
        }

        $bajasPorEstadoRows = $bajasPorEstadoQuery
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        $bajasPorEstado = [
            'labels' => $bajasPorEstadoRows->pluck('estado')->all(),
            'data' => $bajasPorEstadoRows->pluck('total')->map(fn($v) => (int) $v)->all(),
        ];

        // -------- Bajas: por categoría y año (para IA) --------
        $bajasPorCategoriaAnioQuery = BajaActivo::query()
            ->join('activos', 'activos.id_activo', '=', 'bajas_activos.id_activo')
            ->leftJoin('categorias_activos', 'categorias_activos.id_categoria_activo', '=', 'activos.id_categoria_activo')
            ->select(
                DB::raw("YEAR(bajas_activos.created_at) as anio"),
                DB::raw('COALESCE(categorias_activos.nombre, "Sin categoría") as categoria'),
                DB::raw('COUNT(*) as total')
            );

        if ($fechaDesde) {
            $bajasPorCategoriaAnioQuery->whereDate('bajas_activos.created_at', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $bajasPorCategoriaAnioQuery->whereDate('bajas_activos.created_at', '<=', $fechaHasta);
        }

        $bajasPorCategoriaAnioRows = $bajasPorCategoriaAnioQuery
            ->groupBy('anio', 'categoria')
            ->orderBy('anio')
            ->orderBy('categoria')
            ->get();

        $bajasPorCategoriaAnio = $bajasPorCategoriaAnioRows->map(function ($row) {
            return [
                'anio' => (int) $row->anio,
                'categoria' => $row->categoria,
                'total' => (int) $row->total,
            ];
        })->all();

        // -------- Asignaciones: por estado --------
        $asignacionesPorEstadoQuery = AsignacionActivo::query()
            ->select('estado_asignacion', DB::raw('COUNT(*) as total'));

        if ($fechaDesde) {
            $asignacionesPorEstadoQuery->whereDate('fecha_asignacion', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $asignacionesPorEstadoQuery->whereDate('fecha_asignacion', '<=', $fechaHasta);
        }

        $asignacionesPorEstadoRows = $asignacionesPorEstadoQuery
            ->groupBy('estado_asignacion')
            ->orderBy('estado_asignacion')
            ->get();

        $asignacionesPorEstado = [
            'labels' => $asignacionesPorEstadoRows->pluck('estado_asignacion')->all(),
            'data' => $asignacionesPorEstadoRows->pluck('total')->map(fn($v) => (int) $v)->all(),
        ];

        // -------- Valor total de activos por categoría --------
        $valorPorCategoriaQuery = Activo::query()
            ->select('categorias_activos.nombre as categoria', DB::raw('SUM(activos.valor_compra) as total'))
            ->leftJoin('categorias_activos', 'categorias_activos.id_categoria_activo', '=', 'activos.id_categoria_activo');

        if ($fechaDesde) {
            $valorPorCategoriaQuery->whereDate('fecha_adquisicion', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $valorPorCategoriaQuery->whereDate('fecha_adquisicion', '<=', $fechaHasta);
        }

        $valorPorCategoriaRows = $valorPorCategoriaQuery
            ->groupBy('categorias_activos.nombre')
            ->orderBy('categorias_activos.nombre')
            ->get();

        $valorPorCategoria = [
            'labels' => $valorPorCategoriaRows->pluck('categoria')->map(fn($v) => $v ?? 'Sin categoría')->all(),
            'data' => $valorPorCategoriaRows->pluck('total')->map(fn($v) => (float) $v)->all(),
        ];

        // -------- Histograma de valor de compra de activos --------
        $valoresQuery = Activo::query()->select('valor_compra');
        if ($fechaDesde) {
            $valoresQuery->whereDate('fecha_adquisicion', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $valoresQuery->whereDate('fecha_adquisicion', '<=', $fechaHasta);
        }
        $valores = $valoresQuery->pluck('valor_compra')->map(fn($v) => (float) $v)->all();

        $bins = [0, 500, 1000, 2000, 5000, 10000, 20000, 50000];
        $labelsHist = [];
        $dataHist = [];
        for ($i = 0; $i < count($bins); $i++) {
            $min = $bins[$i];
            $max = $bins[$i + 1] ?? null;
            if ($max === null) {
                $labelsHist[] = "$min+";
            } else {
                $labelsHist[] = "$min - $max";
            }
            $dataHist[] = 0;
        }
        foreach ($valores as $valor) {
            for ($i = 0; $i < count($bins); $i++) {
                $min = $bins[$i];
                $max = $bins[$i + 1] ?? null;
                $inBin = $max === null
                    ? $valor >= $min
                    : ($valor >= $min && $valor < $max);
                if ($inBin) {
                    $dataHist[$i]++;
                    break;
                }
            }
        }
        $histogramaValorActivos = [
            'labels' => $labelsHist,
            'data' => $dataHist,
        ];

        // -------- Dispersión: valor de activo vs cantidad de reportes --------
        $activosScatter = Activo::query()
            ->withCount('reportes')
            ->select('id_activo', 'valor_compra')
            ->take(200)
            ->get();

        $scatterValorVsReportes = [
            'data' => $activosScatter->map(function ($a) {
                return [
                    'x' => (float) $a->valor_compra,
                    'y' => (int) ($a->reportes_count ?? 0),
                ];
            })->all(),
        ];

        // -------- Asignaciones: resumen por usuario (top 200) --------
        $asignacionesPorUsuarioQuery = AsignacionActivo::query()
            ->join('users', 'users.id_usuario', '=', 'asignaciones_activos.asignado_a')
            ->select(
                'users.id_usuario',
                'users.nombre as usuario',
                DB::raw('COUNT(*) as total_asignaciones'),
                DB::raw("SUM(CASE WHEN estado_asignacion = 'ACEPTADO' THEN 1 ELSE 0 END) as aceptadas"),
                DB::raw("SUM(CASE WHEN estado_asignacion = 'DEVOLUCION' THEN 1 ELSE 0 END) as devolucion"),
                DB::raw("SUM(CASE WHEN estado_asignacion = 'DEVUELTO' THEN 1 ELSE 0 END) as devueltos")
            );

        if ($fechaDesde) {
            $asignacionesPorUsuarioQuery->whereDate('fecha_asignacion', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $asignacionesPorUsuarioQuery->whereDate('fecha_asignacion', '<=', $fechaHasta);
        }

        $asignacionesPorUsuarioRows = $asignacionesPorUsuarioQuery
            ->groupBy('users.id_usuario', 'users.nombre')
            ->orderByDesc('total_asignaciones')
            ->limit(200)
            ->get();

        $asignacionesPorUsuario = $asignacionesPorUsuarioRows->map(function ($row) {
            return [
                'usuario' => $row->usuario,
                'total_asignaciones' => (int) $row->total_asignaciones,
                'aceptadas' => (int) ($row->aceptadas ?? 0),
                'devolucion' => (int) ($row->devolucion ?? 0),
                'devueltos' => (int) ($row->devueltos ?? 0),
            ];
        })->all();

        // -------- Asignaciones: por usuario y año (para IA, top global) --------
        $asignacionesPorUsuarioAnioQuery = AsignacionActivo::query()
            ->join('users', 'users.id_usuario', '=', 'asignaciones_activos.asignado_a')
            ->select(
                DB::raw('YEAR(fecha_asignacion) as anio'),
                'users.id_usuario',
                'users.nombre as usuario',
                DB::raw('COUNT(*) as total_asignaciones')
            )
            ->whereNotNull('fecha_asignacion');

        if ($fechaDesde) {
            $asignacionesPorUsuarioAnioQuery->whereDate('fecha_asignacion', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $asignacionesPorUsuarioAnioQuery->whereDate('fecha_asignacion', '<=', $fechaHasta);
        }

        // Para acotar tamaño, solo consideramos años desde 2000 en adelante
        $asignacionesPorUsuarioAnioQuery->whereYear('fecha_asignacion', '>=', 2000);

        $asignacionesPorUsuarioAnioRows = $asignacionesPorUsuarioAnioQuery
            ->groupBy('anio', 'users.id_usuario', 'users.nombre')
            ->orderBy('anio')
            ->orderByDesc('total_asignaciones')
            ->limit(1000)
            ->get();

        $asignacionesPorUsuarioAnio = $asignacionesPorUsuarioAnioRows->map(function ($row) {
            return [
                'anio' => (int) $row->anio,
                'usuario' => $row->usuario,
                'total_asignaciones' => (int) $row->total_asignaciones,
            ];
        })->all();

        return [
            'activosPorCategoria' => $activosPorCategoria,
            'activosPorEstado' => $activosPorEstado,
            'activosPorCondicion' => $activosPorCondicion,
            'reportesPorEstado' => $reportesPorEstado,
            'reportesPorMes' => $reportesPorMes,
            'bajasPorEstado' => $bajasPorEstado,
            'bajasPorCategoriaAnio' => $bajasPorCategoriaAnio,
            'asignacionesPorEstado' => $asignacionesPorEstado,
            'valorPorCategoria' => $valorPorCategoria,
            'histogramaValorActivos' => $histogramaValorActivos,
            'scatterValorVsReportes' => $scatterValorVsReportes,
            'asignacionesPorUsuario' => $asignacionesPorUsuario,
            'asignacionesPorUsuarioAnio' => $asignacionesPorUsuarioAnio,
        ];
    }
}
