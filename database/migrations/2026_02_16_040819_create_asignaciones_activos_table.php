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
        Schema::create('asignaciones_activos', function (Blueprint $table) {
            $table->id('id_asignacion');
            $table->foreignId('id_activo')->constrained(table: 'activos', column: 'id_activo');
            $table->foreignId('id_encargado')->constrained(table: 'encargados', column: 'id_encargado');
            $table->foreignId('asignado_por')->constrained(table: 'users', column: 'id_usuario');
            $table->enum('estado_asignacion', ['PENDIENTE', 'ACEPTADO', 'RECHAZADO', 'CARGADO']);
            $table->dateTime('fecha_asignacion');
            $table->dateTime('fecha_respuesta')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_activos');
    }
};
