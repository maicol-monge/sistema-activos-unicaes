<?php

namespace App\Http\Controllers;

use App\Models\AsignacionActivo;
use App\Models\Activo;
use App\Models\Encargado;
use App\Models\MovimientoActivo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AsignacionActivoController extends Controller
{

    public function index()
    {
        $asignaciones = \App\Models\AsignacionActivo::with([
            'activo',
            'encargado',
        ])
            ->orderBy('id_asignacion', 'desc')
            ->paginate(10);

        return view('asignaciones.index', compact('asignaciones'));
    }

    public function create()
    {
        $activos = \App\Models\Activo::where('estado', 'APROBADO')
            ->orderBy('nombre')
            ->get();

        $encargados = \App\Models\Encargado::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('asignaciones.create', compact('activos', 'encargados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_activo' => ['required', 'exists:activos,id_activo'],
            'id_encargado' => ['required', 'exists:encargados,id_encargado'],
        ], [
            'id_activo.required' => 'Debe seleccionar un activo.',
            'id_encargado.required' => 'Debe seleccionar un encargado.',
        ]);

        $activo = \App\Models\Activo::find($request->id_activo);

        // ✅ Criterio: Solo activos APROBADO
        if ($activo->estado !== 'APROBADO') {
            return back()->with('err', 'Solo se pueden asignar activos en estado APROBADO.')->withInput();
        }

        // ✅ Criterio: No puede tener más de una asignación activa aceptada
        $tieneAceptada = \App\Models\AsignacionActivo::where('id_activo', $request->id_activo)
            ->where('estado', 1)
            ->whereIn('estado_asignacion', ['ACEPTADO', 'CARGADO']) // aceptada/activa
            ->exists();

        if ($tieneAceptada) {
            return back()->with('err', 'Este activo ya tiene una asignación aceptada activa.')->withInput();
        }

        // ✅ Transacción: asignación + movimiento
        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {

            // 1) Crear asignación PENDIENTE
            $asignacion = \App\Models\AsignacionActivo::create([
                'id_activo' => $request->id_activo,
                'id_encargado' => $request->id_encargado,
                'asignado_por' => auth()->user()->id_usuario,  // usuario que asignó
                'estado_asignacion' => 'PENDIENTE',
                'fecha_asignacion' => now(),
                'fecha_respuesta' => null,
                'estado' => 1,
            ]);

            // 2) Crear movimiento tipo ASIGNACION
            \App\Models\MovimientoActivo::create([
                'id_activo' => $request->id_activo,
                'realizado_por' => auth()->user()->id_usuario,
                'tipo' => 'ASIGNACION',
                'observaciones' => 'Asignación creada (PENDIENTE). ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return redirect()->route('dashboard')->with('ok', 'Asignación creada correctamente (PENDIENTE).');
    }

    //^^^^^^
    public function misAsignaciones()
    {
        $encargado = Encargado::where('id_usuario', auth()->user()->id_usuario)->first();

        if (!$encargado) {
            return redirect()->route('dashboard')
                ->with('err', 'Tu usuario no está asociado a ningún encargado.');
        }

        $asignaciones = AsignacionActivo::with('activo')
            ->where('id_encargado', $encargado->id_encargado)
            ->orderBy('id_asignacion', 'desc')
            ->paginate(10);

        return view('asignaciones.mis', compact('asignaciones'));
    }

    public function aceptar(AsignacionActivo $asignacion)
    {
        $encargado = Encargado::where('id_usuario', auth()->user()->id_usuario)->first();
        if (!$encargado) {
            return back()->with('err', 'Tu usuario no está asociado a ningún encargado.');
        }

        // ✅ Solo puede responder sus asignaciones
        if ($asignacion->id_encargado != $encargado->id_encargado) {
            abort(403, 'No autorizado');
        }

        // ✅ Solo pendientes
        if ($asignacion->estado_asignacion !== 'PENDIENTE') {
            return back()->with('err', 'Solo puedes responder asignaciones PENDIENTES.');
        }

        DB::transaction(function () use ($asignacion) {

            // Aceptar asignación
            $asignacion->estado_asignacion = 'ACEPTADO';
            $asignacion->fecha_respuesta = now();
            $asignacion->save();

            // (Opcional) marcar el activo como "EN_USO / ASIGNADO" si tienes ese estado
            // Si tu enum de activo solo tiene PENDIENTE/APROBADO/RECHAZADO/BAJA, NO lo cambies aquí.
            // Si tienes un campo/estado para asignación, aquí lo actualizamos.

            // Movimiento
            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => auth()->user()->id_usuario,
                'tipo' => 'ASIGNACION', // o 'ACEPTACION' si tu enum lo permite
                'observaciones' => 'Asignación ACEPTADA por el encargado. ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return back()->with('ok', 'Asignación aceptada correctamente.');
    }

    public function rechazar(AsignacionActivo $asignacion)
    {
        $encargado = Encargado::where('id_usuario', auth()->user()->id_usuario)->first();
        if (!$encargado) {
            return back()->with('err', 'Tu usuario no está asociado a ningún encargado.');
        }

        if ($asignacion->id_encargado != $encargado->id_encargado) {
            abort(403, 'No autorizado');
        }

        if ($asignacion->estado_asignacion !== 'PENDIENTE') {
            return back()->with('err', 'Solo puedes responder asignaciones PENDIENTES.');
        }

        DB::transaction(function () use ($asignacion) {

            // Rechazar
            $asignacion->estado_asignacion = 'RECHAZADO';
            $asignacion->fecha_respuesta = now();
            $asignacion->save();

            // Movimiento
            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => auth()->user()->id_usuario,
                'tipo' => 'ASIGNACION', // o 'RECHAZO' si tu enum lo permite
                'observaciones' => 'Asignación RECHAZADA por el encargado. ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return back()->with('ok', 'Asignación rechazada. El activo queda disponible para nueva asignación.');
    }
}
