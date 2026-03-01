<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoriaActivo>
 */
class CategoriaActivoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement([
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
            ]),
            'estado' => fake()->boolean(90),
        ];
    }
}
