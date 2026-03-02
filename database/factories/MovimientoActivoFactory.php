<?php

namespace Database\Factories;

use App\Models\Activo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MovimientoActivo>
 */
class MovimientoActivoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_activo' => Activo::query()->inRandomOrder()->value('id_activo') ?? Activo::factory()->create()->id_activo,
            'realizado_por' => User::query()->inRandomOrder()->value('id_usuario') ?? User::factory()->create()->id_usuario,
            'tipo' => fake()->randomElement(['CREACION', 'EDICION', 'ASIGNACION', 'DEVOLUCION', 'BAJA', 'ELIMINACION']),
            'observaciones' => fake()->optional()->sentence(),
            'fecha' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'estado' => fake()->boolean(95),
        ];
    }
}
