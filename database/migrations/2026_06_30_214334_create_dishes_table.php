<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de platos del menú.
 *
 * Soporta el Módulo de Gestión de Menú:
 *  - RF-01 crear plato (nombre, descripción, precio)
 *  - RF-04 marcar disponible / no disponible (is_available)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // RF-01: nombre
            $table->text('description')->nullable();          // RF-01: descripción
            $table->decimal('price', 10, 2);                 // RF-01: precio
            $table->boolean('is_available')->default(true);   // RF-04: disponibilidad
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
