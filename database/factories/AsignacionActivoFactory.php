<?php

namespace Database\Factories;

use App\Models\Activo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AsignacionActivo>
 */
class AsignacionActivoFactory extends Factory
{
    public function definition(): array
    {
        $estadoAsignacion = fake()->randomElement(['PENDIENTE', 'ACEPTADO', 'RECHAZADO', 'DEVOLUCION', 'CARGADO']);
        $fechaAsignacion = fake()->dateTimeBetween('-1 years', 'now');

        return [
            'id_activo' => Activo::query()->inRandomOrder()->value('id_activo') ?? Activo::factory()->create()->id_activo,
            'asignado_a' => User::query()->inRandomOrder()->value('id_usuario') ?? User::factory()->create()->id_usuario,
            'asignado_por' => User::query()->inRandomOrder()->value('id_usuario') ?? User::factory()->create()->id_usuario,
            'estado_asignacion' => $estadoAsignacion,
            'fecha_asignacion' => $fechaAsignacion,
            'fecha_respuesta' => $estadoAsignacion === 'PENDIENTE' ? null : fake()->dateTimeBetween($fechaAsignacion, 'now'),
            'estado' => fake()->boolean(90),
            'motivo_devolucion' => $estadoAsignacion === 'DEVOLUCION' ? fake()->sentence() : null,
        ];
    }
}
