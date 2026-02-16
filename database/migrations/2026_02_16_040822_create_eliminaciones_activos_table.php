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
        Schema::create('eliminaciones_activos', function (Blueprint $table) {
            $table->id('id_eliminacion');
            $table->foreignId('id_activo')->constrained(table: 'activos', column: 'id_activo');
            $table->foreignId('eliminado_por')->constrained(table: 'users', column: 'id_usuario');
            $table->text('motivo');
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
        Schema::dropIfExists('eliminaciones_activos');
    }
};
