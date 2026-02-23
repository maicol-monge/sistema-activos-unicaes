<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\AsignacionActivo;
use App\Models\BajaActivo;
use App\Models\MovimientoActivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BajaActivoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'in:PENDIENTE,APROBADA,RECHAZADA'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        $solicitudes = BajaActivo::with(['activo', 'solicitante'])
            ->when(!empty($filtros['q']), function ($query) use ($filtros) {
                $texto = trim($filtros['q']);
                $query->where(function ($sub) use ($texto) {
                    $sub->whereHas('activo', function ($q) use ($texto) {
                        $q->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                    })
                    ->orWhereHas('solicitante', function ($q) use ($texto) {
                        $q->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('correo', 'like', "%{$texto}%");
                    })
                    ->orWhere('motivo', 'like', "%{$texto}%");
                });
            })
            ->when(!empty($filtros['estado']), fn($query) => $query->where('estado', $filtros['estado']))
            ->when(!empty($filtros['fecha_desde']), fn($query) => $query->whereDate('created_at', '>=', $filtros['fecha_desde']))
            ->when(!empty($filtros['fecha_hasta']), fn($query) => $query->whereDate('created_at', '<=', $filtros['fecha_hasta']))
            ->orderByDesc('id_baja')
            ->paginate(10)
            ->withQueryString();

        return view('bajas-activos.index', compact('solicitudes', 'filtros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $usuario = auth()->user();

        if (in_array($usuario->rol, ['ENCARGADO', 'INVENTARIADOR'])) {
            // Solo activos actualmente asignados al usuario (encargado o inventariador)
            $activos = Activo::where('estado', 'APROBADO')
                ->whereHas('asignaciones', function ($q) use ($usuario) {
                    $q->where('asignado_a', $usuario->id_usuario)
                        ->where('estado', 1)
                        ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION']);
                })
                ->orderBy('nombre')
                ->get();
        } else {
            // ADMIN u otros roles autorizados: todos los activos aprobados
            $activos = Activo::where('estado', 'APROBADO')
                ->orderBy('nombre')
                ->get();
        }

        return view('bajas-activos.create', compact('activos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_activo' => 'required|exists:activos,id_activo',
            'motivo' => 'required|string|max:255',
        ]);

        $usuario = auth()->user();

        // Si es ENCARGADO o INVENTARIADOR, validar que el activo le pertenece actualmente
        if (in_array($usuario->rol, ['ENCARGADO', 'INVENTARIADOR'])) {
            $tieneAsignacion = \App\Models\AsignacionActivo::where('id_activo', $request->id_activo)
                ->where('asignado_a', $usuario->id_usuario)
                ->where('estado', 1)
                ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION'])
                ->exists();

            if (!$tieneAsignacion) {
                return back()
                    ->with('err', 'Solo puedes solicitar baja de activos que tienes asignados.')
                    ->withInput();
            }
        }

        BajaActivo::create([
            'id_activo' => $request->id_activo,
            'id_usuario_solicitante' => $usuario->id_usuario,
            'motivo' => $request->motivo,
            'estado' => 'PENDIENTE',
        ]);

        // Redirigir al dashboard para roles que no gestionan el inventario general
        if (in_array($usuario->rol, ['ENCARGADO', 'DECANO', 'INVENTARIADOR'])) {
            return redirect()
                ->route('dashboard')
                ->with('ok', 'Solicitud de baja enviada correctamente.');
        }

        // Para ADMIN u otros roles que sí gestionan activos, mantener flujo actual
        return redirect()
            ->route('activos.index')
            ->with('ok', 'Solicitud de baja enviada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Proceder con la baja de una solicitud existente.
     */
    public function destroy(string $id)
    {
        $solicitud = BajaActivo::findOrFail($id);
        $activo = $solicitud->activo;
        $usuario = auth()->user();

        DB::transaction(function () use ($solicitud, $activo, $usuario) {
            // Cambiar el estado del activo a 'BAJA'
            $activo->estado = 'BAJA';
            $activo->save();

            // Cerrar cualquier asignación activa asociada a este activo
            $asignacionesActivas = AsignacionActivo::where('id_activo', $activo->id_activo)
                ->where('estado', 1)
                ->get();

            foreach ($asignacionesActivas as $asignacion) {
                $nuevoEstadoAsignacion = $asignacion->estado_asignacion === 'PENDIENTE'
                    ? 'RECHAZADO'
                    : 'CARGADO';

                $asignacion->estado_asignacion = $nuevoEstadoAsignacion;
                $asignacion->estado = 0;
                $asignacion->fecha_respuesta = now();
                $asignacion->save();
            }

            // Marcar la solicitud como APROBADA (no se elimina para mantener historial)
            $solicitud->estado = 'APROBADA';
            $solicitud->save();

            // Registrar movimiento de baja
            MovimientoActivo::create([
                'id_activo' => $activo->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'BAJA',
                'observaciones' => 'Baja aplicada desde solicitud. Motivo: ' . $solicitud->motivo,
                'fecha' => now()->toDateString(),
                'estado' => 1,
            ]);
        });

        return redirect()->route('bajas-activos.index')->with('ok', 'El activo ha sido dado de baja correctamente.');
    }

    /**
     * Rechazar una solicitud de baja sin afectar el estado del activo.
     */
    public function rechazar(Request $request, string $id)
    {
        $solicitud = BajaActivo::findOrFail($id);

        if ($solicitud->estado !== 'PENDIENTE') {
            return redirect()
                ->route('bajas-activos.solicitudes')
                ->with('err', 'Solo se pueden rechazar solicitudes de baja pendientes.');
        }

        $solicitud->estado = 'RECHAZADO';
        $solicitud->save();

        return redirect()
            ->route('bajas-activos.solicitudes')
            ->with('success', 'La solicitud de baja ha sido rechazada correctamente.');
    }

    public function solicitudes()
    {
        $solicitudes = BajaActivo::with(['activo', 'solicitante'])
            ->where('estado', 'PENDIENTE')
            ->get();

        return view('bajas-activos.index', compact('solicitudes'));
    }
}
