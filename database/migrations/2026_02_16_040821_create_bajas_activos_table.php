<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bajas_activos', function (Blueprint $table) {
            $table->id('id_baja');
            $table->foreignId('id_activo')->constrained(table: 'activos', column: 'id_activo');
            $table->foreignId('id_usuario_solicitante')->constrained(table: 'users', column: 'id_usuario');
            $table->text('motivo');

            // Estado de la solicitud: PENDIENTE, APROBADA, RECHAZADA, etc.
            $table->string('estado', 20)->default('PENDIENTE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bajas_activos');
    }
};
