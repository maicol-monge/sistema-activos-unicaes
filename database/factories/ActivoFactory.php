<?php

namespace Database\Factories;

use App\Models\CategoriaActivo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activo>
 */
class ActivoFactory extends Factory
{
    public function definition(): array
    {
        $registradoPor = User::query()->inRandomOrder()->value('id_usuario') ?? User::factory()->create()->id_usuario;
        $aprobadoPor = User::query()->inRandomOrder()->value('id_usuario');

        return [
            'codigo' => fake()->unique()->numerify('ACT-#####'),
            'serial' => fake()->unique()->bothify('SN-??-####-#####'),
            'nombre' => fake()->words(3, true),
            'descripcion' => fake()->sentence(12),
            'tipo' => fake()->randomElement(['FIJO', 'INTANGIBLE']),
            'marca' => fake()->company(),
            'estado' => fake()->randomElement(['PENDIENTE', 'APROBADO', 'RECHAZADO', 'BAJA']),
            'condicion' => fake()->randomElement(['BUENO', 'DANIADO', 'REGULAR']),
            'fecha_adquisicion' => fake()->dateTimeBetween('-8 years', 'now')->format('Y-m-d'),
            'valor_compra' => fake()->randomFloat(2, 10, 9999),
            'id_categoria_activo' => CategoriaActivo::query()->inRandomOrder()->value('id_categoria_activo') ?? CategoriaActivo::factory()->create()->id_categoria_activo,
            'fecha_registro' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'registrado_por' => $registradoPor,
            'aprobado_por' => $aprobadoPor,
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
