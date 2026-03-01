<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rol = fake()->randomElement(['ADMIN', 'INVENTARIADOR', 'DECANO', 'ENCARGADO']);

        return [
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'contrasena' => static::$password ??= Hash::make('password'),
            'rol' => $rol,
            'tipo' => $rol === 'ADMIN' ? null : fake()->randomElement(['PERSONA', 'UNIDAD']),
            'estado' => fake()->boolean(90),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this;
    }
}
