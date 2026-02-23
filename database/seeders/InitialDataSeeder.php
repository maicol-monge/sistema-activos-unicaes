<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\CategoriaActivo;
use App\Models\Activo;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN (updateOrCreate correcto: 1er parámetro es condición, 2do es data)
        $admin = User::updateOrCreate(
            ['correo' => 'admin@unicaes.edu.sv'],
            [
                'nombre' => 'Administrador',
                'contrasena' => Hash::make('admin123'),
                'rol' => 'ADMIN',
                'tipo' => null,
                'estado' => 1,
            ]
        );

        $inventariador = User::updateOrCreate(
            ['correo' => 'inventariador@unicaes.edu.sv'],
            [
                'nombre' => 'Luis Hernández',
                'contrasena' => Hash::make('admin123'),
                'rol' => 'INVENTARIADOR',
                'tipo' => 'PERSONA',
                'estado' => 1,
            ]
        );

        $decano = User::updateOrCreate(
            ['correo' => 'decano@unicaes.edu.sv'],
            [
                'nombre' => 'Marta López',
                'contrasena' => Hash::make('admin123'),
                'rol' => 'DECANO',
                'tipo' => 'PERSONA',
                'estado' => 1,
            ]
        );

        $encargado = User::updateOrCreate(
            ['correo' => 'encargado@unicaes.edu.sv'],
            [
                'nombre' => 'Carlos Ramírez',
                'contrasena' => Hash::make('admin123'),
                'rol' => 'ENCARGADO',
                'tipo' => 'UNIDAD',
                'estado' => 1,
            ]
        );

        // Categorías (usar updateOrCreate para NO duplicar)
        $nombresCategorias = [
            'Equipo de Cómputo',
            'Mobiliario',
            'Equipo de Oficina',
            'Vehículos',
            'Infraestructura',
        ];

        foreach ($nombresCategorias as $nombre) {
            CategoriaActivo::updateOrCreate(['nombre' => $nombre]);
        }

        // Categoria por defecto
        $cat = CategoriaActivo::where('nombre', 'Equipo de Cómputo')->first();

        // ACT-001
        Activo::updateOrCreate(
            ['codigo' => 'ACT-001'],
            [
                'serial' => 'SN-LEN-2024-001',
                'nombre' => 'Laptop Lenovo ThinkPad',
                'descripcion' => 'Laptop asignada al departamento académico',
                'tipo' => 'FIJO',
                'marca' => 'Lenovo',
                'estado' => 'PENDIENTE',
                'condicion' => 'BUENO',
                'fecha_adquisicion' => '2024-02-15',
                'valor_compra' => 950.00,
                'id_categoria_activo' => $cat?->id_categoria_activo ?? 1,
                'fecha_registro' => now(),
                'registrado_por' => $admin->id_usuario,
                'aprobado_por' => $inventariador->id_usuario,
                'observaciones' => 'Equipo en perfecto estado',
            ]
        );

        // ACT-002
        Activo::updateOrCreate(
            ['codigo' => 'ACT-002'],
            [
                'serial' => 'LIC-MIC-365-2024',
                'nombre' => 'Licencia Microsoft 365',
                'descripcion' => 'Licencia anual para personal administrativo',
                'tipo' => 'INTANGIBLE',
                'marca' => 'Microsoft',
                'estado' => 'APROBADO',
                'condicion' => 'BUENO',
                'fecha_adquisicion' => '2024-01-10',
                'valor_compra' => 120.00,
                'id_categoria_activo' => $cat?->id_categoria_activo ?? 1,
                'fecha_registro' => now(),
                'registrado_por' => $admin->id_usuario,
                'aprobado_por' => $inventariador->id_usuario,
                'observaciones' => 'Licencia activa por 1 año',
            ]
        );
    }
}
