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

        // Verificar que el activo no tenga asignaciones activas (debe estar devuelto o sin asignaciones)
        $tieneAsignacionesActivas = AsignacionActivo::where('id_activo', $activo->id_activo)
            ->where('estado', 1)
            ->exists();

        if ($tieneAsignacionesActivas) {
            return redirect()
                ->route('bajas-activos.index')
                ->with('err', 'No se puede dar de baja un activo que aún tiene asignaciones activas o pendientes de devolución.');
        }

        DB::transaction(function () use ($solicitud, $activo, $usuario) {
            // Cambiar el estado del activo a 'BAJA'
            $activo->estado = 'BAJA';
            $activo->save();

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
