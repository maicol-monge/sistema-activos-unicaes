<?php

namespace App\Http\Controllers;

use App\Models\AsignacionActivo;
use App\Models\User;
use App\Models\Activo;
use App\Models\MovimientoActivo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AsignacionActivoController extends Controller
{

    public function misActivos()
    {
        $asignaciones = AsignacionActivo::with(['activo', 'usuarioAsignador'])
            ->where('id_usuario', auth()->user()->id_usuario)
            ->where('estado', 1)
            ->where('estado_asignacion', 'ACEPTADO')
            ->orderBy('id_asignacion', 'desc')
            ->paginate(10);

        return view('activos.mis', compact('asignaciones'));
    }

    public function index()
    {
        $asignaciones = AsignacionActivo::with([
            'activo',
            'encargadoUsuario',
            'usuarioAsignador'
        ])
            ->orderBy('id_asignacion', 'desc')
            ->paginate(10);

        return view('asignaciones.index', compact('asignaciones'));
    }

    public function create()
    {
        // Solo activos aprobados
        $activos = Activo::where('estado', 'APROBADO')
            ->whereDoesntHave('asignaciones', function ($query) {
                $query->where('estado', 1)
                    ->where('estado_asignacion', 'ACEPTADO');
            })
            ->orderBy('nombre')
            ->get();

        // Encargados ahora son usuarios con rol ENCARGADO y estado activo
        $encargados = User::where('rol', 'ENCARGADO')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('asignaciones.create', compact('activos', 'encargados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_activo' => ['required', 'exists:activos,id_activo'],
            'id_usuario' => ['required', 'exists:users,id_usuario'],
        ], [
            'id_activo.required' => 'Debe seleccionar un activo.',
            'id_usuario.required' => 'Debe seleccionar un encargado.',
            'id_activo.exists' => 'El activo seleccionado no existe.',
            'id_usuario.exists' => 'El encargado seleccionado no existe.',
        ]);

        // 🔎 Verificar que el usuario seleccionado sea ENCARGADO y esté activo
        $u = User::find($request->id_usuario);

        if (!$u || $u->rol !== 'ENCARGADO') {
            return back()
                ->with('err', 'Debe seleccionar un usuario con rol ENCARGADO.')
                ->withInput();
        }

        if ((int)$u->estado !== 1) {
            return back()
                ->with('err', 'El usuario seleccionado no está activo.')
                ->withInput();
        }

        // 🔎 Verificar activo
        $activo = Activo::find($request->id_activo);

        // ✅ Solo activos en estado APROBADO
        if (!$activo || $activo->estado !== 'APROBADO') {
            return back()
                ->with('err', 'Solo se pueden asignar activos en estado APROBADO.')
                ->withInput();
        }

        // ✅ Un activo no puede tener más de una asignación activa ACEPTADA
        $tieneAceptada = AsignacionActivo::where('id_activo', $request->id_activo)
            ->where('estado', 1)
            ->where('estado_asignacion', 'ACEPTADO')
            ->exists();

        if ($tieneAceptada) {
            return back()
                ->with('err', 'Este activo ya tiene una asignación aceptada activa.')
                ->withInput();
        }

        // ✅ Transacción: crear asignación + movimiento
        DB::transaction(function () use ($request) {

            // 1️⃣ Crear asignación PENDIENTE
            $asignacion = AsignacionActivo::create([
                'id_activo' => $request->id_activo,
                'id_usuario' => $request->id_usuario, // encargado (user)
                'asignado_por' => auth()->user()->id_usuario, // inventariador/admin que asigna
                'estado_asignacion' => 'PENDIENTE',
                'fecha_asignacion' => now(),
                'fecha_respuesta' => null,
                'estado' => 1,
            ]);

            // 2️⃣ Movimiento tipo ASIGNACION
            MovimientoActivo::create([
                'id_activo' => $request->id_activo,
                'realizado_por' => auth()->user()->id_usuario,
                'tipo' => 'ASIGNACION',
                'observaciones' => 'Asignación creada (PENDIENTE). ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return redirect()
            ->route('asignaciones.index')
            ->with('ok', 'Asignación creada correctamente (PENDIENTE).');
    }

    //^^^^^^
    public function misAsignaciones()
    {
        $asignaciones = AsignacionActivo::with('activo')
            ->where('id_usuario', auth()->user()->id_usuario)
            ->orderBy('id_asignacion', 'desc')
            ->paginate(10);

        return view('asignaciones.mis', compact('asignaciones'));
    }

    public function aceptar(AsignacionActivo $asignacion)
    {
        // ✅ Solo el usuario asignado puede aceptar
        if ($asignacion->id_usuario != auth()->user()->id_usuario) {
            abort(403, 'No autorizado');
        }

        // ✅ Solo se pueden responder asignaciones pendientes
        if ($asignacion->estado_asignacion !== 'PENDIENTE') {
            return back()->with('err', 'Solo puedes responder asignaciones PENDIENTES.');
        }

        DB::transaction(function () use ($asignacion) {

            // 1️⃣ Cambiar estado a ACEPTADO
            $asignacion->estado_asignacion = 'ACEPTADO';
            $asignacion->fecha_respuesta = now();
            $asignacion->save();

            // 2️⃣ Registrar movimiento (ASIGNACION recomendado)
            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => auth()->user()->id_usuario,
                'tipo' => 'ASIGNACION', // si tu enum no lo permite, cambia a 'ASIGNACION'
                'observaciones' => 'Asignación ACEPTADA por el encargado. ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return back()->with('ok', 'Asignación aceptada correctamente.');
    }

    public function rechazar(AsignacionActivo $asignacion)
    {
        // ✅ Solo el usuario asignado puede rechazar
        if ($asignacion->id_usuario != auth()->user()->id_usuario) {
            abort(403, 'No autorizado');
        }

        // ✅ Solo permitir rechazar pendientes
        if ($asignacion->estado_asignacion !== 'PENDIENTE') {
            return back()->with('err', 'Solo puedes responder asignaciones PENDIENTES.');
        }

        DB::transaction(function () use ($asignacion) {

            // ❌ Marcar como rechazado
            $asignacion->estado_asignacion = 'RECHAZADO';
            $asignacion->estado = 0;
            $asignacion->fecha_respuesta = now();
            $asignacion->save();

            // 📝 Registrar movimiento
            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => auth()->user()->id_usuario,
                'tipo' => 'ASIGNACION', // si tu enum tiene 'RECHAZO', puedes cambiarlo a 'RECHAZO'
                'observaciones' => 'Asignación RECHAZADA por el encargado. ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return back()->with('ok', 'Asignación rechazada. El activo queda disponible para nueva asignación.');
    }

    public function devolver(AsignacionActivo $asignacion)
    {
        if ($asignacion->id_usuario != auth()->user()->id_usuario) {
            abort(403, 'No autorizado');
        }

        if ($asignacion->estado_asignacion !== 'ACEPTADO' || (int) $asignacion->estado !== 1) {
            return back()->with('err', 'Solo pueden devolverse asignaciones aceptadas activas.');
        }

        DB::transaction(function () use ($asignacion) {
            $asignacion->estado_asignacion = 'CARGADO';
            $asignacion->estado = 0;
            $asignacion->fecha_respuesta = now();
            $asignacion->save();

            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => auth()->user()->id_usuario,
                'tipo' => 'DEVOLUCION',
                'observaciones' => 'Devolución de activo por encargado. ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return back()->with('ok', 'Activo devuelto correctamente. La asignación fue cerrada.');
    }
}
