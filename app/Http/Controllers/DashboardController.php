<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\AsignacionActivo;
use App\Models\BajaActivo;
use App\Models\ReporteActivo;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        $rol = $usuario->rol;

        $stats = [];

        if ($rol === 'ADMIN') {
            $stats = [
                'total_activos' => Activo::count(),
                'activos_aprobados' => Activo::where('estado', 'APROBADO')->count(),
                'asignaciones_activas' => AsignacionActivo::where('estado', 1)
                    ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION'])
                    ->count(),
                'bajas_pendientes' => BajaActivo::where('estado', 'PENDIENTE')->count(),
            ];
        } elseif ($rol === 'DECANO') {
            $usuarioId = $usuario->id_usuario;

            $activosIds = AsignacionActivo::query()
                ->where('asignado_a', $usuarioId)
                ->where('estado', 1)
                ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION'])
                ->pluck('id_activo');

            $stats = [
                'mis_activos' => $activosIds->count(),
                'reportes_totales' => ReporteActivo::whereIn('id_activo', $activosIds)->count(),
                'reportes_problema' => ReporteActivo::whereIn('id_activo', $activosIds)
                    ->whereIn('estado_reporte', ['DANIADO', 'PERDIDO'])
                    ->count(),
            ];
        }

        return view('dashboard', compact('stats'));
    }
}
