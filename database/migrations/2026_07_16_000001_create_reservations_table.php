<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo Reservas (RNF-10, gobernado por el flag MODULE_RESERVAS).
 * La tabla existe siempre; el flag decide si el módulo se usa. Así encender
 * el módulo en un comercio es cambiar el .env, sin migraciones extra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('phone')->nullable();
            $table->dateTime('reserved_at');           // fecha y hora de la reserva
            $table->unsignedSmallInteger('party_size'); // número de comensales
            $table->unsignedSmallInteger('table_number')->nullable();
            $table->string('status')->default('pendiente');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
