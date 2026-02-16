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
        Schema::create('activos', function (Blueprint $table) {
            $table->id('id_activo');
            $table->string('codigo', 100)->unique();
            $table->string('serial')->nullable();
            $table->string('nombre', 50);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['FIJO', 'INTANGIBLE']);
            $table->string('marca')->nullable();
            $table->enum('estado', ['PENDIENTE', 'APROBADO', 'RECHAZADO', 'BAJA']);
            $table->enum('condicion', ['BUENO', 'DANIADO', 'REGULAR']);
            $table->date('fecha_adquisicion');
            $table->decimal('valor_compra', 8, 2);
            $table->foreignId('id_categoria_activo')->constrained(table: 'categorias_activos', column: 'id_categoria_activo');
            $table->date('fecha_registro');
            $table->foreignId('registrado_por')->constrained(table: 'users', column: 'id_usuario');
            $table->foreignId('aprobado_por')->nullable()->constrained(table: 'users', column: 'id_usuario');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};
