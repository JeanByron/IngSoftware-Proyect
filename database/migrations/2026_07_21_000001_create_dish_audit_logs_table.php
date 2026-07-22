<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RNF-20: bitácora inalterable de auditoría de los cambios en el CATÁLOGO y los
 * PRECIOS. Registra qué se hizo (crear/editar/eliminar), quién y cuándo, y el
 * cambio de precio (antes → después). Append-only: sólo created_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dish_audit_logs', function (Blueprint $table) {
            $table->id();
            // dish_id nullable: en 'deleted' el plato ya no existe (se conserva
            // el nombre); nullOnDelete evita romper el histórico.
            $table->foreignId('dish_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');                        // created | updated | deleted
            $table->string('dish_name');
            $table->decimal('old_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_audit_logs');
    }
};
