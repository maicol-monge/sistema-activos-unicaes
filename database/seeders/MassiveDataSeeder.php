<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\AsignacionActivo;
use App\Models\BajaActivo;
use App\Models\CategoriaActivo;
use App\Models\EliminacionActivo;
use App\Models\MovimientoActivo;
use App\Models\ReporteActivo;
use App\Models\User;
use Illuminate\Database\Seeder;

class MassiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $objetivoActivos = 5000;

        $categoriasExistentes = CategoriaActivo::query()->count();
        if ($categoriasExistentes < 10) {
            CategoriaActivo::factory()->count(10 - $categoriasExistentes)->create();
        }

        $usuariosExistentes = User::query()->count();
        if ($usuariosExistentes < 100) {
            User::factory()->count(100 - $usuariosExistentes)->create();
        }

        $activosPorCrear = max(0, $objetivoActivos - Activo::query()->count());
        if ($activosPorCrear > 0) {
            Activo::factory()->count($activosPorCrear)->create();
        }

        $objetivoAsignaciones = (int) ($objetivoActivos * 0.6);
        $objetivoMovimientos = $objetivoActivos;
        $objetivoReportes = (int) ($objetivoActivos * 0.3);
        $objetivoBajas = (int) ($objetivoActivos * 0.1);
        $objetivoEliminaciones = (int) ($objetivoActivos * 0.05);

        $asignacionesPorCrear = max(0, $objetivoAsignaciones - AsignacionActivo::query()->count());
        if ($asignacionesPorCrear > 0) {
            AsignacionActivo::factory()->count($asignacionesPorCrear)->create();
        }

        $movimientosPorCrear = max(0, $objetivoMovimientos - MovimientoActivo::query()->count());
        if ($movimientosPorCrear > 0) {
            MovimientoActivo::factory()->count($movimientosPorCrear)->create();
        }

        $reportesPorCrear = max(0, $objetivoReportes - ReporteActivo::query()->count());
        if ($reportesPorCrear > 0) {
            ReporteActivo::factory()->count($reportesPorCrear)->create();
        }

        $bajasPorCrear = max(0, $objetivoBajas - BajaActivo::query()->count());
        if ($bajasPorCrear > 0) {
            BajaActivo::factory()->count($bajasPorCrear)->create();
        }

        $eliminacionesPorCrear = max(0, $objetivoEliminaciones - EliminacionActivo::query()->count());
        if ($eliminacionesPorCrear > 0) {
            EliminacionActivo::factory()->count($eliminacionesPorCrear)->create();
        }
    }
}
