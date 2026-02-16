<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CategoriaActivo;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'nombre' => 'Administrador',
            'correo' => 'admin@unicaes.edu.sv',
            'contrasena' => Hash::make('admin123'), // Cambia 'admin123' por una contraseña segura
            'rol' => 'ADMIN',
            'estado' => true,
        ]);

        // Crear categorías de activos por defecto
        $categorias = [
            ['nombre' => 'Equipo de Cómputo'],
            ['nombre' => 'Mobiliario'],
            ['nombre' => 'Equipo de Oficina'],
            ['nombre' => 'Vehículos'],
            ['nombre' => 'Infraestructura'],
        ];

        foreach ($categorias as $categoria) {
            CategoriaActivo::create($categoria);
        }
    }
}
