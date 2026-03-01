<?php

namespace Database\Factories;

use App\Models\Activo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BajaActivo>
 */
class BajaActivoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_activo' => Activo::query()->inRandomOrder()->value('id_activo') ?? Activo::factory()->create()->id_activo,
            'id_usuario_solicitante' => User::query()->inRandomOrder()->value('id_usuario') ?? User::factory()->create()->id_usuario,
            'motivo' => fake()->sentence(12),
            'estado' => fake()->randomElement(['PENDIENTE', 'APROBADA', 'RECHAZADA']),
        ];
    }
}
