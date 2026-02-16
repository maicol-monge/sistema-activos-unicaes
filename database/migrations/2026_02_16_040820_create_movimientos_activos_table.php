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
        Schema::create('movimientos_activos', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->foreignId('id_activo')->constrained(table: 'activos', column: 'id_activo');
            $table->foreignId('realizado_por')->constrained(table: 'users', column: 'id_usuario');
            $table->enum('tipo', ['CREACION', 'EDICION', 'ASIGNACION', 'DEVOLUCION', 'BAJA', 'ELIMINACION']);
            $table->text('observaciones')->nullable();
            $table->date('fecha');
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_activos');
    }
};
