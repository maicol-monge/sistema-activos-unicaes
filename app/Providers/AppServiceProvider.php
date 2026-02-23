<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Asegurar que la zona horaria de PHP use la definida en config/app.php
        date_default_timezone_set(config('app.timezone'));

        // Forzar zona horaria de la conexión MySQL/MariaDB a UTC-6 (El Salvador)
        try {
            $default = config('database.default');

            if (in_array($default, ['mysql', 'mariadb'])) {
                DB::statement("SET time_zone = '-06:00'");
            }
        } catch (\Throwable $e) {
            // En comandos/artisan sin BD disponible, ignorar el error silenciosamente
        }
    }
}
