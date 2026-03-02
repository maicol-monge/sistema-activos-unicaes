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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class MassiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $objetivoActivos = 5000;
        $objetivoAsignaciones = (int) ($objetivoActivos * 0.6);
        $objetivoMovimientos = $objetivoActivos;
        $objetivoReportes = (int) ($objetivoActivos * 0.3);
        $objetivoBajas = (int) ($objetivoActivos * 0.1);
        $objetivoEliminaciones = (int) ($objetivoActivos * 0.05);

        $mapaCategorias = $this->asegurarCategorias();
        $this->asegurarUsuarios();

        $idsRegistradores = User::query()
            ->whereIn('rol', ['ADMIN', 'INVENTARIADOR'])
            ->where('estado', 1)
            ->pluck('id_usuario')
            ->all();

        $idsDestinatarios = User::query()
            ->whereIn('rol', ['ENCARGADO', 'DECANO', 'INVENTARIADOR'])
            ->where('estado', 1)
            ->pluck('id_usuario')
            ->all();

        if (empty($idsRegistradores) || empty($idsDestinatarios)) {
            return;
        }

        $activosPorCrear = max(0, $objetivoActivos - Activo::query()->count());
        if ($activosPorCrear > 0) {
            $this->crearActivosRealistas(
                $activosPorCrear,
                $mapaCategorias,
                $idsRegistradores
            );
        }

        $asignacionesPorCrear = max(0, $objetivoAsignaciones - AsignacionActivo::query()->count());
        if ($asignacionesPorCrear > 0) {
            $this->crearAsignacionesRealistas($asignacionesPorCrear, $idsRegistradores, $idsDestinatarios);
        }

        $reportesPorCrear = max(0, $objetivoReportes - ReporteActivo::query()->count());
        if ($reportesPorCrear > 0) {
            $this->crearReportesRealistas($reportesPorCrear, $idsDestinatarios);
        }

        $bajasPorCrear = max(0, $objetivoBajas - BajaActivo::query()->count());
        if ($bajasPorCrear > 0) {
            $this->crearBajasRealistas($bajasPorCrear, $idsDestinatarios);
        }

        $eliminacionesPorCrear = max(0, $objetivoEliminaciones - EliminacionActivo::query()->count());
        if ($eliminacionesPorCrear > 0) {
            $this->crearEliminacionesRealistas($eliminacionesPorCrear, $idsRegistradores);
        }

        $movimientosPorCrear = max(0, $objetivoMovimientos - MovimientoActivo::query()->count());
        if ($movimientosPorCrear > 0) {
            $this->crearMovimientosEdicion($movimientosPorCrear, $idsRegistradores);
        }
    }

    private function asegurarCategorias(): array
    {
        $categorias = [
            'Equipo de Cómputo',
            'Mobiliario',
            'Equipo de Oficina',
            'Vehículos',
            'Infraestructura',
            'Equipo de Red',
            'Laboratorio',
            'Audio y Video',
            'Software',
            'Herramientas',
        ];

        foreach ($categorias as $nombre) {
            CategoriaActivo::query()->updateOrCreate(
                ['nombre' => $nombre],
                ['estado' => 1]
            );
        }

        return CategoriaActivo::query()->pluck('id_categoria_activo', 'nombre')->toArray();
    }

    private function asegurarUsuarios(): void
    {
        $objetivoUsuarios = 220;
        $actuales = User::query()->count();
        if ($actuales >= $objetivoUsuarios) {
            return;
        }

        $faltantes = $objetivoUsuarios - $actuales;
        $areas = [
            'Ingeniería',
            'Arquitectura',
            'Medicina',
            'Administración',
            'Biblioteca',
            'Laboratorio',
            'Registro Académico',
            'Finanzas',
        ];

        $nombres = ['Ana', 'Luis', 'María', 'José', 'Karla', 'Carlos', 'Sofía', 'Ricardo', 'Daniela', 'Miguel', 'Gabriela', 'Óscar'];
        $apellidos = ['Hernández', 'Martínez', 'López', 'Ramírez', 'Castro', 'Rivas', 'Quinteros', 'Morales', 'Pérez', 'Gómez'];

        for ($i = 0; $i < $faltantes; $i++) {
            $rol = $this->weighted([
                'ENCARGADO' => 55,
                'DECANO' => 20,
                'INVENTARIADOR' => 20,
                'ADMIN' => 5,
            ]);

            $nombre = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
            if ($rol === 'ENCARGADO' && random_int(0, 100) < 40) {
                $nombre = 'Unidad de ' . $areas[array_rand($areas)];
            }

            $slug = Str::of($nombre)
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]+/', '.')
                ->trim('.')
                ->toString();
            $correo = $slug . '.' . random_int(100, 9999) . '@unicaes.edu.sv';

            User::query()->create([
                'nombre' => $nombre,
                'correo' => $correo,
                'contrasena' => Hash::make('password'),
                'rol' => $rol,
                'tipo' => $rol === 'ADMIN' ? null : (str_starts_with($nombre, 'Unidad de ') ? 'UNIDAD' : 'PERSONA'),
                'estado' => random_int(1, 100) <= 92,
                'remember_token' => bin2hex(random_bytes(5)),
            ]);
        }
    }

    private function crearActivosRealistas(int $cantidad, array $mapaCategorias, array $idsRegistradores): void
    {
        $perfiles = [
            [
                'categoria' => 'Equipo de Cómputo',
                'tipo' => 'FIJO',
                'marcas' => ['Dell', 'HP', 'Lenovo', 'Acer'],
                'nombres' => ['Laptop', 'PC de Escritorio', 'All In One'],
                'min' => 450,
                'max' => 2200,
            ],
            [
                'categoria' => 'Equipo de Oficina',
                'tipo' => 'FIJO',
                'marcas' => ['Epson', 'Brother', 'HP', 'Canon'],
                'nombres' => ['Impresora Multifuncional', 'Escáner', 'Proyector'],
                'min' => 120,
                'max' => 1400,
            ],
            [
                'categoria' => 'Mobiliario',
                'tipo' => 'FIJO',
                'marcas' => ['OfiMuebles', 'ErgoWork', 'MobiLux'],
                'nombres' => ['Escritorio', 'Silla Ergonómica', 'Archivador'],
                'min' => 80,
                'max' => 900,
            ],
            [
                'categoria' => 'Equipo de Red',
                'tipo' => 'FIJO',
                'marcas' => ['Cisco', 'Ubiquiti', 'TP-Link', 'MikroTik'],
                'nombres' => ['Switch de Red', 'Router Empresarial', 'Access Point'],
                'min' => 150,
                'max' => 2800,
            ],
            [
                'categoria' => 'Laboratorio',
                'tipo' => 'FIJO',
                'marcas' => ['Fluke', 'Tektronix', 'Bosch'],
                'nombres' => ['Osciloscopio', 'Multímetro Digital', 'Fuente de Poder'],
                'min' => 180,
                'max' => 3600,
            ],
            [
                'categoria' => 'Audio y Video',
                'tipo' => 'FIJO',
                'marcas' => ['Sony', 'LG', 'Samsung', 'BenQ'],
                'nombres' => ['Pantalla LED', 'Cámara', 'Sistema de Audio'],
                'min' => 120,
                'max' => 2400,
            ],
            [
                'categoria' => 'Vehículos',
                'tipo' => 'FIJO',
                'marcas' => ['Toyota', 'Nissan', 'Hyundai'],
                'nombres' => ['Pick Up Institucional', 'Microbús', 'Sedán Administrativo'],
                'min' => 8000,
                'max' => 28000,
            ],
            [
                'categoria' => 'Software',
                'tipo' => 'INTANGIBLE',
                'marcas' => ['Microsoft', 'Adobe', 'JetBrains', 'Atlassian'],
                'nombres' => ['Licencia Anual', 'Suscripción Empresarial', 'Suite Académica'],
                'min' => 50,
                'max' => 1500,
            ],
            [
                'categoria' => 'Infraestructura',
                'tipo' => 'FIJO',
                'marcas' => ['Schneider', 'Tripp Lite', 'Genérico'],
                'nombres' => ['UPS', 'Aire Acondicionado', 'Sistema Eléctrico'],
                'min' => 350,
                'max' => 6000,
            ],
        ];

        $idsAprobadores = $idsRegistradores;
        $ahora = now();
        $correlativo = $this->ultimoCorrelativoCodigo() + 1;
        $lote = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $perfil = $perfiles[array_rand($perfiles)];
            $marca = $perfil['marcas'][array_rand($perfil['marcas'])];
            $nombreBase = $perfil['nombres'][array_rand($perfil['nombres'])];

            $estado = $this->weighted([
                'APROBADO' => 72,
                'PENDIENTE' => 14,
                'RECHAZADO' => 9,
                'BAJA' => 5,
            ]);

            $condicion = match ($estado) {
                'BAJA' => $this->weighted(['DANIADO' => 70, 'REGULAR' => 30]),
                'RECHAZADO' => $this->weighted(['REGULAR' => 55, 'DANIADO' => 45]),
                'PENDIENTE' => $this->weighted(['BUENO' => 70, 'REGULAR' => 30]),
                default => $this->weighted(['BUENO' => 80, 'REGULAR' => 18, 'DANIADO' => 2]),
            };

            $fechaAdquisicion = Carbon::instance(fake()->dateTimeBetween('-8 years', '-2 months'));
            $fechaRegistro = Carbon::instance(fake()->dateTimeBetween($fechaAdquisicion, $ahora));

            $codigo = 'ACT-' . str_pad((string) $correlativo, 5, '0', STR_PAD_LEFT);
            $correlativo++;

            $registradoPor = $idsRegistradores[array_rand($idsRegistradores)];
            $aprobadoPor = in_array($estado, ['APROBADO', 'RECHAZADO', 'BAJA'], true)
                ? $idsAprobadores[array_rand($idsAprobadores)]
                : null;

            $serie = strtoupper(substr($marca, 0, 3)) . '-' . $fechaAdquisicion->format('Y') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);

            $lote[] = [
                'codigo' => $codigo,
                'serial' => $perfil['tipo'] === 'INTANGIBLE' ? 'LIC-' . $serie : 'SN-' . $serie,
                'nombre' => $nombreBase . ' ' . random_int(1, 40),
                'descripcion' => 'Activo asignado a procesos institucionales de UNICAES.',
                'tipo' => $perfil['tipo'],
                'marca' => $marca,
                'estado' => $estado,
                'condicion' => $condicion,
                'fecha_adquisicion' => $fechaAdquisicion->toDateString(),
                'valor_compra' => random_int((int) ($perfil['min'] * 100), (int) ($perfil['max'] * 100)) / 100,
                'id_categoria_activo' => $mapaCategorias[$perfil['categoria']] ?? reset($mapaCategorias),
                'fecha_registro' => $fechaRegistro->toDateString(),
                'registrado_por' => $registradoPor,
                'aprobado_por' => $aprobadoPor,
                'observaciones' => $estado === 'BAJA' ? 'Activo marcado para baja institucional.' : null,
                'created_at' => $fechaRegistro,
                'updated_at' => $fechaRegistro,
            ];

            if (count($lote) >= 300) {
                Activo::query()->insert($lote);
                $lote = [];
            }
        }

        if (!empty($lote)) {
            Activo::query()->insert($lote);
        }
    }

    private function crearAsignacionesRealistas(int $cantidad, array $idsAsignadores, array $idsDestinatarios): void
    {
        $activosAprobados = Activo::query()
            ->where('estado', 'APROBADO')
            ->pluck('id_activo')
            ->all();

        if (empty($activosAprobados)) {
            return;
        }

        $activosConAsignacionActiva = AsignacionActivo::query()
            ->where('estado', 1)
            ->whereIn('estado_asignacion', ['PENDIENTE', 'ACEPTADO', 'DEVOLUCION'])
            ->pluck('id_activo')
            ->unique()
            ->all();

        $activosDisponiblesParaActivas = array_values(array_diff($activosAprobados, $activosConAsignacionActiva));
        $lote = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $estadoAsignacion = $this->weighted([
                'ACEPTADO' => 53,
                'PENDIENTE' => 18,
                'RECHAZADO' => 12,
                'DEVOLUCION' => 12,
                'CARGADO' => 5,
            ]);

            $requiereActivoLibre = in_array($estadoAsignacion, ['PENDIENTE', 'ACEPTADO', 'DEVOLUCION'], true);
            if ($requiereActivoLibre && empty($activosDisponiblesParaActivas)) {
                $estadoAsignacion = 'CARGADO';
                $requiereActivoLibre = false;
            }

            if ($requiereActivoLibre) {
                $indiceActivo = array_rand($activosDisponiblesParaActivas);
                $idActivo = $activosDisponiblesParaActivas[$indiceActivo];
                unset($activosDisponiblesParaActivas[$indiceActivo]);
                $activosDisponiblesParaActivas = array_values($activosDisponiblesParaActivas);
            } else {
                $idActivo = $activosAprobados[array_rand($activosAprobados)];
            }

            $asignadoPor = $idsAsignadores[array_rand($idsAsignadores)];
            $asignadoA = $idsDestinatarios[array_rand($idsDestinatarios)];

            if (count($idsDestinatarios) > 1) {
                while ($asignadoA === $asignadoPor) {
                    $asignadoA = $idsDestinatarios[array_rand($idsDestinatarios)];
                }
            }

            $fechaAsignacion = Carbon::instance(fake()->dateTimeBetween('-16 months', 'now'));
            $fechaRespuesta = $estadoAsignacion === 'PENDIENTE'
                ? null
                : Carbon::instance(fake()->dateTimeBetween($fechaAsignacion, 'now'));

            $lote[] = [
                'id_activo' => $idActivo,
                'asignado_a' => $asignadoA,
                'asignado_por' => $asignadoPor,
                'estado_asignacion' => $estadoAsignacion,
                'fecha_asignacion' => $fechaAsignacion,
                'fecha_respuesta' => $fechaRespuesta,
                'estado' => in_array($estadoAsignacion, ['PENDIENTE', 'ACEPTADO', 'DEVOLUCION'], true) ? 1 : 0,
                'motivo_devolucion' => $estadoAsignacion === 'DEVOLUCION'
                    ? 'Solicitud de devolución por cambio de área académica.'
                    : null,
                'created_at' => $fechaAsignacion,
                'updated_at' => $fechaRespuesta ?? $fechaAsignacion,
            ];

            if (count($lote) >= 300) {
                AsignacionActivo::query()->insert($lote);
                $lote = [];
            }
        }

        if (!empty($lote)) {
            AsignacionActivo::query()->insert($lote);
        }
    }

    private function crearReportesRealistas(int $cantidad, array $idsDestinatarios): void
    {
        $asignaciones = AsignacionActivo::query()
            ->whereIn('estado_asignacion', ['ACEPTADO', 'DEVOLUCION'])
            ->select('id_activo', 'asignado_a', 'fecha_asignacion')
            ->get();

        if ($asignaciones->isEmpty()) {
            return;
        }

        $activosCondicion = Activo::query()->pluck('condicion', 'id_activo');
        $lote = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $asignacion = $asignaciones->random();
            $condicion = $activosCondicion[$asignacion->id_activo] ?? 'BUENO';

            $estadoReporte = match ($condicion) {
                'DANIADO' => $this->weighted(['DANIADO' => 75, 'PERDIDO' => 10, 'BUENO' => 15]),
                'REGULAR' => $this->weighted(['BUENO' => 55, 'DANIADO' => 40, 'PERDIDO' => 5]),
                default => $this->weighted(['BUENO' => 84, 'DANIADO' => 13, 'PERDIDO' => 3]),
            };

            $fechaBase = Carbon::parse($asignacion->fecha_asignacion ?? now()->subMonths(3));
            $fechaReporte = Carbon::instance(fake()->dateTimeBetween($fechaBase, 'now'));

            $lote[] = [
                'id_activo' => $asignacion->id_activo,
                'id_usuario' => $asignacion->asignado_a ?? $idsDestinatarios[array_rand($idsDestinatarios)],
                'estado_reporte' => $estadoReporte,
                'comentario' => $estadoReporte === 'BUENO'
                    ? 'Activo funcionando correctamente en operaciones diarias.'
                    : ($estadoReporte === 'PERDIDO'
                        ? 'No se localiza el activo en inventario físico del área.'
                        : 'Se detecta daño en uso normal del activo.'),
                'fecha' => $fechaReporte->toDateString(),
                'estado' => 1,
                'created_at' => $fechaReporte,
                'updated_at' => $fechaReporte,
            ];

            if (count($lote) >= 300) {
                ReporteActivo::query()->insert($lote);
                $lote = [];
            }
        }

        if (!empty($lote)) {
            ReporteActivo::query()->insert($lote);
        }
    }

    private function crearBajasRealistas(int $cantidad, array $idsSolicitantes): void
    {
        $candidatos = Activo::query()
            ->whereIn('estado', ['APROBADO', 'BAJA'])
            ->pluck('estado', 'id_activo')
            ->all();

        if (empty($candidatos)) {
            return;
        }

        $activosConBaja = BajaActivo::query()->pluck('id_activo')->unique()->all();
        $idsDisponibles = array_values(array_diff(array_keys($candidatos), $activosConBaja));
        if (empty($idsDisponibles)) {
            return;
        }

        $lote = [];
        $idsAprobados = [];

        for ($i = 0; $i < $cantidad && !empty($idsDisponibles); $i++) {
            $indice = array_rand($idsDisponibles);
            $idActivo = $idsDisponibles[$indice];
            unset($idsDisponibles[$indice]);
            $idsDisponibles = array_values($idsDisponibles);

            $estado = $this->weighted([
                'PENDIENTE' => 58,
                'APROBADA' => 25,
                'RECHAZADA' => 17,
            ]);

            $fecha = Carbon::instance(fake()->dateTimeBetween('-10 months', 'now'));

            $lote[] = [
                'id_activo' => $idActivo,
                'id_usuario_solicitante' => $idsSolicitantes[array_rand($idsSolicitantes)],
                'motivo' => $this->motivoBaja(),
                'estado' => $estado,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ];

            if ($estado === 'APROBADA') {
                $idsAprobados[] = $idActivo;
            }
        }

        if (!empty($lote)) {
            BajaActivo::query()->insert($lote);
        }

        if (!empty($idsAprobados)) {
            Activo::query()
                ->whereIn('id_activo', $idsAprobados)
                ->update([
                    'estado' => 'BAJA',
                    'updated_at' => now(),
                ]);

            $asignaciones = AsignacionActivo::query()
                ->whereIn('id_activo', $idsAprobados)
                ->where('estado', 1)
                ->get();

            foreach ($asignaciones as $asignacion) {
                $asignacion->estado_asignacion = $asignacion->estado_asignacion === 'PENDIENTE'
                    ? 'RECHAZADO'
                    : 'CARGADO';
                $asignacion->estado = 0;
                $asignacion->fecha_respuesta = now();
                $asignacion->save();
            }
        }
    }

    private function crearEliminacionesRealistas(int $cantidad, array $idsRegistradores): void
    {
        $idsConBajaAprobada = BajaActivo::query()
            ->where('estado', 'APROBADA')
            ->pluck('id_activo')
            ->all();

        if (!empty($idsConBajaAprobada)) {
            Activo::query()
                ->whereIn('id_activo', $idsConBajaAprobada)
                ->update([
                    'estado' => 'BAJA',
                    'updated_at' => now(),
                ]);
        }

        $activosBaja = Activo::query()
            ->where('estado', 'BAJA')
            ->pluck('id_activo')
            ->all();

        if (empty($activosBaja)) {
            return;
        }

        $activosConEliminacion = EliminacionActivo::query()->pluck('id_activo')->unique()->all();
        $disponibles = array_values(array_diff($activosBaja, $activosConEliminacion));
        if (empty($disponibles)) {
            return;
        }

        $lote = [];
        $tope = min($cantidad, count($disponibles));

        for ($i = 0; $i < $tope; $i++) {
            $indice = array_rand($disponibles);
            $idActivo = $disponibles[$indice];
            unset($disponibles[$indice]);
            $disponibles = array_values($disponibles);

            $fecha = Carbon::instance(fake()->dateTimeBetween('-8 months', 'now'));

            $lote[] = [
                'id_activo' => $idActivo,
                'eliminado_por' => $idsRegistradores[array_rand($idsRegistradores)],
                'motivo' => 'Depuración de inventario por baja definitiva y cierre administrativo.',
                'fecha' => $fecha->toDateString(),
                'estado' => 1,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ];
        }

        if (!empty($lote)) {
            EliminacionActivo::query()->insert($lote);
        }
    }

    private function crearMovimientosEdicion(int $cantidad, array $idsRegistradores): void
    {
        $idsActivos = Activo::query()->pluck('id_activo')->all();
        if (empty($idsActivos)) {
            return;
        }

        $lote = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $fecha = Carbon::instance(fake()->dateTimeBetween('-12 months', 'now'));

            $lote[] = [
                'id_activo' => $idsActivos[array_rand($idsActivos)],
                'realizado_por' => $idsRegistradores[array_rand($idsRegistradores)],
                'tipo' => 'EDICION',
                'observaciones' => 'Ajuste de información administrativa del activo.',
                'fecha' => $fecha->toDateString(),
                'estado' => 1,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ];

            if (count($lote) >= 400) {
                MovimientoActivo::query()->insert($lote);
                $lote = [];
            }
        }

        if (!empty($lote)) {
            MovimientoActivo::query()->insert($lote);
        }
    }

    private function ultimoCorrelativoCodigo(): int
    {
        $codigos = Activo::query()->pluck('codigo');
        $max = 0;

        foreach ($codigos as $codigo) {
            if (!is_string($codigo)) {
                continue;
            }

            if (preg_match('/^(?:ACT-)(\d+)$/', $codigo, $m)) {
                $numero = (int) $m[1];
                if ($numero > $max) {
                    $max = $numero;
                }
            }
        }

        return $max;
    }

    private function weighted(array $pesos): string
    {
        $total = array_sum($pesos);
        $r = random_int(1, $total);

        foreach ($pesos as $valor => $peso) {
            $r -= $peso;
            if ($r <= 0) {
                return $valor;
            }
        }

        return array_key_first($pesos);
    }

    private function motivoBaja(): string
    {
        $motivos = [
            'Obsolescencia tecnológica del activo.',
            'Costo de reparación superior al valor de reposición.',
            'Daño físico irreversible reportado por el área usuaria.',
            'Sustitución por equipo nuevo en proyecto institucional.',
            'Fin de vida útil según política interna de activos.',
        ];

        return $motivos[array_rand($motivos)];
    }
}