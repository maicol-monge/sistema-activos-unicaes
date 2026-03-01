<?php

namespace Database\Factories;

use App\Models\Activo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReporteActivo>
 */
class ReporteActivoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_activo' => Activo::query()->inRandomOrder()->value('id_activo') ?? Activo::factory()->create()->id_activo,
            'id_usuario' => User::query()->inRandomOrder()->value('id_usuario') ?? User::factory()->create()->id_usuario,
            'estado_reporte' => fake()->randomElement(['BUENO', 'DANIADO', 'PERDIDO']),
            'comentario' => fake()->optional()->sentence(),
            'fecha' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'estado' => fake()->boolean(90),
        ];
    }
}
