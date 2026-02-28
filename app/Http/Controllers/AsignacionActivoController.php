<?php

namespace App\Http\Controllers;

use App\Models\AsignacionActivo;
use App\Models\User;
use App\Models\Activo;
use App\Models\MovimientoActivo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AsignacionActivoController extends Controller
{

    /**
     * Marca como RECHAZADAS las asignaciones pendientes cuyo plazo ya venció.
     *
     * - Asignaciones creadas de lunes a jueves: 24 horas para aceptar.
     * - Asignaciones creadas en viernes: 96 horas (4 días) para aceptar.
     */
    protected function rechazarAsignacionesVencidas(): void
    {
        $ahora = now();

        $pendientes = AsignacionActivo::where('estado_asignacion', 'PENDIENTE')
            ->where('estado', 1)
            ->get();

        foreach ($pendientes as $asignacion) {
            $creada = Carbon::parse($asignacion->fecha_asignacion);

            // Viernes (5) según isoWeekday; resto de días, 24 horas
            $esViernes = $creada->isoWeekday() === 5;
            $horasLimite = $esViernes ? 96 : 24;

            if ($creada->copy()->addHours($horasLimite)->lte($ahora)) {
                DB::transaction(function () use ($asignacion, $ahora) {
                    $asignacion->estado_asignacion = 'RECHAZADO';
                    $asignacion->estado = 0;
                    $asignacion->fecha_respuesta = $ahora;
                    $asignacion->save();

                    MovimientoActivo::create([
                        'id_activo' => $asignacion->id_activo,
                        // Se registra como movimiento automático usando el asignador original
                        'realizado_por' => $asignacion->asignado_por,
                        'tipo' => 'ASIGNACION',
                        'observaciones' => 'Asignación RECHAZADA automáticamente por vencimiento de plazo.',
                        'fecha' => $ahora,
                        'estado' => 1,
                    ]);
                });
            }
        }
    }

    public function misActivos()
    {
        $request = request();

        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'id_categoria_activo' => ['nullable', 'exists:categorias_activos,id_categoria_activo'],
            'tipo' => ['nullable', 'in:FIJO,INTANGIBLE'],
        ]);

        $asignaciones = AsignacionActivo::with(['activo.categoria', 'usuarioAsignador'])
            ->where('asignado_a', auth()->user()->id_usuario)
            ->where('estado', 1)
            ->where('estado_asignacion', 'ACEPTADO')
            ->when(!empty($filtros['q']), function ($query) use ($filtros) {
                $texto = trim($filtros['q']);
                $query->whereHas('activo', function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('codigo', 'like', "%{$texto}%")
                        ->orWhere('serial', 'like', "%{$texto}%");
                });
            })
            ->when(!empty($filtros['id_categoria_activo']), function ($query) use ($filtros) {
                $query->whereHas('activo', function ($q) use ($filtros) {
                    $q->where('id_categoria_activo', $filtros['id_categoria_activo']);
                });
            })
            ->when(!empty($filtros['tipo']), function ($query) use ($filtros) {
                $query->whereHas('activo', function ($q) use ($filtros) {
                    $q->where('tipo', $filtros['tipo']);
                });
            })
            ->orderBy('id_asignacion', 'desc')
            ->paginate(10)
            ->withQueryString();

        $categorias = \App\Models\CategoriaActivo::where('estado', 1)->orderBy('nombre')->get();

        return view('activos.mis', compact('asignaciones', 'filtros', 'categorias'));
    }

    public function index()
    {
        // Actualizar estados de asignaciones vencidas antes de listar
        $this->rechazarAsignacionesVencidas();

        $request = request();

        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'estado_asignacion' => ['nullable', 'in:PENDIENTE,ACEPTADO,RECHAZADO,DEVOLUCION,CARGADO'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        $asignaciones = AsignacionActivo::with([
            'activo',
            'usuarioAsignado',
            'usuarioAsignador'
        ])
            ->when(!empty($filtros['q']), function ($query) use ($filtros) {
                $texto = trim($filtros['q']);
                $query->where(function ($sub) use ($texto) {
                    $sub->whereHas('activo', function ($q) use ($texto) {
                        $q->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('codigo', 'like', "%{$texto}%");
                    })
                        ->orWhereHas('usuarioAsignado', function ($q) use ($texto) {
                            $q->where('nombre', 'like', "%{$texto}%")
                                ->orWhere('correo', 'like', "%{$texto}%");
                        })
                        ->orWhereHas('usuarioAsignador', function ($q) use ($texto) {
                            $q->where('nombre', 'like', "%{$texto}%");
                        });
                });
            })
            ->when(!empty($filtros['estado_asignacion']), fn($query) => $query->where('estado_asignacion', $filtros['estado_asignacion']))
            ->when(!empty($filtros['fecha_desde']), fn($query) => $query->whereDate('fecha_asignacion', '>=', $filtros['fecha_desde']))
            ->when(!empty($filtros['fecha_hasta']), fn($query) => $query->whereDate('fecha_asignacion', '<=', $filtros['fecha_hasta']))
            ->orderBy('id_asignacion', 'desc')
            ->get();

        return view('asignaciones.index', compact('asignaciones', 'filtros'));
    }

    public function detalleAdmin(AsignacionActivo $asignacion)
    {
        $usuario = auth()->user();

        if (!$usuario || $usuario->rol !== 'ADMIN') {
            abort(403, 'No autorizado');
        }

        $asignacion->load([
            'activo.categoria',
            'activo.registrador',
            'activo.aprobador',
            'usuarioAsignador',
            'usuarioAsignado',
        ]);

        return view('asignaciones.detalle-admin', compact('asignacion'));
    }

    public function create()
    {
        $usuario = auth()->user();

        if ($usuario->rol === 'ENCARGADO') {
            // Activos actualmente asignados al encargado (ACEPTADO y activos)
            $activos = Activo::whereHas('asignaciones', function ($q) use ($usuario) {
                $q->where('asignado_a', $usuario->id_usuario)
                    ->where('estado', 1)
                    ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION']);
            })
                ->orderBy('nombre')
                ->get();
        } else {
            // ADMIN o INVENTARIADOR: activos aprobados sin asignación aceptada activa
            $activos = Activo::where('estado', 'APROBADO')
                ->whereDoesntHave('asignaciones', function ($query) {
                    $query->where('estado', 1)
                        ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION']);
                })
                ->orderBy('nombre')
                ->get();
        }

        // Destinatarios permitidos: ENCARGADO, DECANO, INVENTARIADOR, ADMIN (activos)
        $destinatarios = User::whereIn('rol', ['ENCARGADO', 'DECANO', 'INVENTARIADOR', 'ADMIN'])
            ->where('estado', 1)
            ->when($usuario->rol === 'ENCARGADO', fn($q) => $q->where('id_usuario', '!=', $usuario->id_usuario))
            ->orderBy('nombre')
            ->get();

        return view('asignaciones.create', [
            'activos' => $activos,
            'destinatarios' => $destinatarios,
            'esEncargado' => $usuario->rol === 'ENCARGADO',
        ]);
    }

    public function store(Request $request)
    {
        $usuario = auth()->user();

        $request->validate([
            'id_activo' => ['required', 'exists:activos,id_activo'],
            'asignado_a' => ['required', 'exists:users,id_usuario'],
        ], [
            'id_activo.required' => 'Debe seleccionar un activo.',
            'asignado_a.required' => 'Debe seleccionar un usuario destino.',
            'id_activo.exists' => 'El activo seleccionado no existe.',
            'asignado_a.exists' => 'El usuario seleccionado no existe.',
        ]);

        $u = User::find($request->asignado_a);

        if (!$u || !in_array($u->rol, ['ENCARGADO', 'DECANO', 'INVENTARIADOR', 'ADMIN']) || (int)$u->estado !== 1) {
            return back()
                ->with('err', 'Debe seleccionar un usuario activo con rol ENCARGADO, DECANO, INVENTARIADOR o ADMIN.')
                ->withInput();
        }

        if ($u->id_usuario === $usuario->id_usuario) {
            return back()
                ->with('err', 'No puedes asignarte un activo a ti mismo.')
                ->withInput();
        }

        $activo = Activo::find($request->id_activo);

        if (!$activo || $activo->estado !== 'APROBADO') {
            return back()
                ->with('err', 'Solo se pueden asignar activos en estado APROBADO.')
                ->withInput();
        }

        // Si es ENCARGADO, solo puede asignar activos que tiene actualmente asignados
        if ($usuario->rol === 'ENCARGADO') {
            $loTieneAsignado = AsignacionActivo::where('id_activo', $request->id_activo)
                ->where('asignado_a', $usuario->id_usuario)
                ->where('estado', 1)
                ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION'])
                ->exists();

            if (!$loTieneAsignado) {
                return back()
                    ->with('err', 'Solo puedes generar asignaciones sobre activos que tienes asignados.')
                    ->withInput();
            }
        }

        // No permitir más de una asignación ACEPTADA activa a un usuario distinto del que asigna
        $tieneAceptadaOtro = AsignacionActivo::where('id_activo', $request->id_activo)
            ->where('estado', 1)
            ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION'])
            ->where('asignado_a', '!=', $usuario->id_usuario)
            ->exists();

        if ($tieneAceptadaOtro) {
            return back()
                ->with('err', 'Este activo ya tiene una asignación aceptada activa a otro usuario.')
                ->withInput();
        }

        // ✅ Transacción: crear asignación + movimiento
        DB::transaction(function () use ($request, $usuario) {

            // 1️⃣ Crear asignación PENDIENTE
            $asignacion = AsignacionActivo::create([
                'id_activo' => $request->id_activo,
                'asignado_a' => $request->asignado_a,
                'asignado_por' => $usuario->id_usuario,
                'estado_asignacion' => 'PENDIENTE',
                'fecha_asignacion' => now(),
                'fecha_respuesta' => null,
                'estado' => 1,
            ]);

            // 2️⃣ Movimiento tipo ASIGNACION
            MovimientoActivo::create([
                'id_activo' => $request->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'ASIGNACION',
                'observaciones' => 'Asignación creada (PENDIENTE). ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        // Redirección según rol
        if ($usuario->rol === 'ADMIN') {
            // ADMIN: listado global de asignaciones
            return redirect()
                ->route('asignaciones.index')
                ->with('ok', 'Asignación creada correctamente (PENDIENTE).');
        }

        if ($usuario->rol === 'ENCARGADO') {
            // ENCARGADO: sus propias asignaciones
            return redirect()
                ->route('asignaciones.mis')
                ->with('ok', 'Asignación creada correctamente (PENDIENTE).');
        }

        if ($usuario->rol === 'INVENTARIADOR') {
            // INVENTARIADOR: vuelve al listado de activos
            return redirect()
                ->route('dashboard')
                ->with('ok', 'Asignación creada correctamente (PENDIENTE).');
        }

        // Cualquier otro rol (por seguridad) vuelve al dashboard
        return redirect()
            ->route('dashboard')
            ->with('ok', 'Asignación creada correctamente (PENDIENTE).');
    }

    //^^^^^^
    public function misAsignaciones()
    {
        // Actualizar estados de asignaciones vencidas antes de mostrar al encargado
        $this->rechazarAsignacionesVencidas();

        $request = request();

        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'estado_asignacion' => ['nullable', 'in:PENDIENTE,ACEPTADO,RECHAZADO,DEVOLUCION,BAJA'],
        ]);

        // Por defecto mostrar solo asignaciones PENDIENTES si no se envía filtro
        if (!isset($filtros['estado_asignacion']) || $filtros['estado_asignacion'] === null) {
            $filtros['estado_asignacion'] = 'PENDIENTE';
        }

        $asignaciones = AsignacionActivo::with('activo')
            ->where('asignado_a', auth()->user()->id_usuario)
            ->when(!empty($filtros['q']), function ($query) use ($filtros) {
                $texto = trim($filtros['q']);
                $query->whereHas('activo', function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('codigo', 'like', "%{$texto}%");
                });
            })
            ->when(!empty($filtros['estado_asignacion']), fn($query) => $query->where('estado_asignacion', $filtros['estado_asignacion']))
            ->orderBy('id_asignacion', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('asignaciones.mis', compact('asignaciones', 'filtros'));
    }

    public function detalle(AsignacionActivo $asignacion)
    {
        // Actualizar estados de asignaciones vencidas antes de mostrar el detalle
        $this->rechazarAsignacionesVencidas();

        // ✅ Solo el usuario asignado puede ver el detalle de su asignación
        if ($asignacion->asignado_a != auth()->user()->id_usuario) {
            abort(403, 'No autorizado');
        }

        $asignacion->load([
            'activo.categoria',
            'activo.registrador',
            'activo.aprobador',
            'usuarioAsignador',
            'usuarioAsignado',
        ]);

        return view('asignaciones.detalle', compact('asignacion'));
    }

    public function comprobante(AsignacionActivo $asignacion)
    {
        // ✅ Solo el usuario asignado puede ver el comprobante
        if ($asignacion->asignado_a != auth()->user()->id_usuario) {
            abort(403, 'No autorizado');
        }

        $asignacion->load([
            'activo.categoria',
            'usuarioAsignador',
            'usuarioAsignado',
        ]);

        // La fecha de aceptación real se toma del movimiento. Nota: `movimientos_activos.fecha`
        // es tipo DATE (sin hora), por eso usamos `created_at` para mostrar fecha+hora.
        $id = (int) $asignacion->id_asignacion;
        $fechaAceptacion = MovimientoActivo::where('id_activo', $asignacion->id_activo)
            ->where('tipo', 'ASIGNACION')
            ->where('observaciones', 'like', "%ID asignación: {$id}%")
            ->where('observaciones', 'like', '%ACEPTADA%')
            ->orderByDesc('created_at')
            ->value('created_at');

        $numero = 'COMP-' . str_pad((string) $asignacion->id_asignacion, 6, '0', STR_PAD_LEFT);
        $fileName = "{$numero}.pdf";

        $pdf = Pdf::loadView('asignaciones.comprobante-pdf', [
            'asignacion' => $asignacion,
            'fechaAceptacion' => $fechaAceptacion,
            'numero' => $numero,
        ])->setPaper('letter');

        return $pdf->download($fileName);
    }

    public function comprobantePreview(AsignacionActivo $asignacion)
    {
        // ✅ Solo el usuario asignado puede ver el comprobante
        if ($asignacion->asignado_a != auth()->user()->id_usuario) {
            abort(403, 'No autorizado');
        }

        $asignacion->load([
            'activo.categoria',
            'usuarioAsignador',
            'usuarioAsignado',
        ]);

        $id = (int) $asignacion->id_asignacion;
        $fechaAceptacion = MovimientoActivo::where('id_activo', $asignacion->id_activo)
            ->where('tipo', 'ASIGNACION')
            ->where('observaciones', 'like', "%ID asignación: {$id}%")
            ->where('observaciones', 'like', '%ACEPTADA%')
            ->orderByDesc('created_at')
            ->value('created_at');

        $numero = 'COMP-' . str_pad((string) $asignacion->id_asignacion, 6, '0', STR_PAD_LEFT);
        $fileName = "{$numero}.pdf";

        $pdf = Pdf::loadView('asignaciones.comprobante-pdf', [
            'asignacion' => $asignacion,
            'fechaAceptacion' => $fechaAceptacion,
            'numero' => $numero,
        ])->setPaper('letter');

        // `stream` sirve el PDF en el navegador (Content-Disposition: inline), ideal para iframe/modal.
        return $pdf->stream($fileName);
    }

    public function aceptar(AsignacionActivo $asignacion)
    {
        // Actualizar posibles vencimientos antes de procesar la respuesta
        $this->rechazarAsignacionesVencidas();

        // ✅ Solo el usuario asignado puede aceptar
        if ($asignacion->asignado_a != auth()->user()->id_usuario) {
            abort(403, 'No autorizado');
        }

        // ✅ Solo se pueden responder asignaciones pendientes
        if ($asignacion->estado_asignacion !== 'PENDIENTE') {
            return back()->with('err', 'Solo puedes responder asignaciones PENDIENTES.');
        }

        DB::transaction(function () use ($asignacion) {

            // Cerrar otras asignaciones aceptadas activas del mismo activo
            AsignacionActivo::where('id_activo', $asignacion->id_activo)
                ->where('id_asignacion', '!=', $asignacion->id_asignacion)
                ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION'])
                ->where('estado', 1)
                ->update([
                    'estado_asignacion' => 'CARGADO',
                    'estado' => 0,
                    'fecha_respuesta' => now(),
                ]);

            // 1️⃣ Cambiar estado a ACEPTADO
            $asignacion->estado_asignacion = 'ACEPTADO';
            $asignacion->fecha_respuesta = now();
            $asignacion->save();

            // 2️⃣ Registrar movimiento (ASIGNACION recomendado)
            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => auth()->user()->id_usuario,
                'tipo' => 'ASIGNACION',
                'observaciones' => 'Asignación ACEPTADA por el usuario destino. ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return redirect()
            ->route('activos.mis')
            ->with('ok', 'Asignación aceptada correctamente.');
    }

    public function rechazar(AsignacionActivo $asignacion)
    {
        // Actualizar posibles vencimientos antes de procesar la respuesta
        $this->rechazarAsignacionesVencidas();

        // ✅ Solo el usuario asignado puede rechazar
        if ($asignacion->asignado_a != auth()->user()->id_usuario) {
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

        return redirect()
            ->route('asignaciones.mis')
            ->with('ok', 'Asignación rechazada. El activo queda disponible para nueva asignación.');
    }

    /**
     * Aceptar la devolución de un activo (ADMIN).
     */
    public function aceptarDevolucion(AsignacionActivo $asignacion)
    {
        $usuario = auth()->user();

        if ($usuario->rol !== 'ADMIN') {
            abort(403, 'No autorizado');
        }

        if ($asignacion->estado_asignacion !== 'DEVOLUCION' || (int) $asignacion->estado !== 1) {
            return back()->with('err', 'Solo se pueden aceptar devoluciones pendientes.');
        }

        DB::transaction(function () use ($asignacion, $usuario) {
            $asignacion->estado_asignacion = 'CARGADO';
            $asignacion->estado = 0;
            $asignacion->fecha_respuesta = now();
            $asignacion->save();

            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'DEVOLUCION',
                'observaciones' => 'Devolución aceptada por administrador. ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return back()->with('ok', 'Devolución aceptada correctamente. La asignación ha sido cerrada.');
    }

    /**
     * Rechazar la devolución de un activo (ADMIN).
     */
    public function rechazarDevolucion(AsignacionActivo $asignacion)
    {
        $usuario = auth()->user();

        if ($usuario->rol !== 'ADMIN') {
            abort(403, 'No autorizado');
        }

        if ($asignacion->estado_asignacion !== 'DEVOLUCION' || (int) $asignacion->estado !== 1) {
            return back()->with('err', 'Solo se pueden rechazar devoluciones pendientes.');
        }

        DB::transaction(function () use ($asignacion, $usuario) {
            // La devolución es rechazada: el activo continúa asignado al encargado
            $asignacion->estado_asignacion = 'ACEPTADO';
            $asignacion->save();

            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'DEVOLUCION',
                'observaciones' => 'Devolución rechazada por administrador. El activo continúa asignado. ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return back()->with('ok', 'Devolución rechazada. El activo continúa asignado al usuario.');
    }

    public function devolver(Request $request, AsignacionActivo $asignacion)
    {
        $request->validate([
            'motivo_devolucion' => 'required|string|min:10|max:500',
        ]);
        if ($asignacion->asignado_a != auth()->user()->id_usuario) {
            abort(403, 'No autorizado');
        }

        if ($asignacion->estado_asignacion !== 'ACEPTADO' || (int) $asignacion->estado !== 1) {
            return back()->with('err', 'Solo pueden devolverse asignaciones aceptadas activas.');
        }

        DB::transaction(function () use ($request, $asignacion) {
            // Marcar la asignación como DEVOLUCION (pendiente de revisión por ADMIN)
            $asignacion->estado_asignacion = 'DEVOLUCION';
            $asignacion->motivo_devolucion = $request->motivo_devolucion;
            // Se mantiene estado = 1 hasta que el ADMIN acepte o rechace la devolución
            $asignacion->save();

            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => auth()->user()->id_usuario,
                'tipo' => 'DEVOLUCION',
                'observaciones' => 'Devolución solicitada por el encargado. ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return back()->with('ok', 'Solicitud de devolución registrada. Queda pendiente de aprobación por el administrador.');
    }

    /**
     * Forzar la devolución / retiro de una asignación por parte del ADMIN.
     *
     * - Si la asignación está PENDIENTE y activa: se marca como RECHAZADO y se cierra.
     * - Si está ACEPTADO o en DEVOLUCION y activa: se marca como CARGADO (devuelto) y se cierra.
     */
    public function forzarDevolucion(AsignacionActivo $asignacion)
    {
        $usuario = auth()->user();

        if ($usuario->rol !== 'ADMIN') {
            abort(403, 'No autorizado');
        }

        if ((int) $asignacion->estado !== 1) {
            return back()->with('err', 'Solo se pueden retirar asignaciones activas.');
        }

        DB::transaction(function () use ($asignacion, $usuario) {
            if ($asignacion->estado_asignacion === 'PENDIENTE') {
                $asignacion->estado_asignacion = 'RECHAZADO';
            } else {
                // ACEPTADO, DEVOLUCION u otro estado activo: se cierra como devuelto
                $asignacion->estado_asignacion = 'CARGADO';
            }

            $asignacion->estado = 0;
            $asignacion->fecha_respuesta = now();
            $asignacion->save();

            MovimientoActivo::create([
                'id_activo' => $asignacion->id_activo,
                'realizado_por' => $usuario->id_usuario,
                'tipo' => 'DEVOLUCION',
                'observaciones' => 'Asignación cerrada por el administrador (retiro/auto devolución forzada). ID asignación: ' . $asignacion->id_asignacion,
                'fecha' => now(),
                'estado' => 1,
            ]);
        });

        return back()->with('ok', 'La asignación ha sido retirada/cerrada correctamente por el administrador.');
    }
}
