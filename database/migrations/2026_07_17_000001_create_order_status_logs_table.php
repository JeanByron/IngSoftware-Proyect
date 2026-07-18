<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RNF-20: bitácora de auditoría de los cambios de estado del pedido.
 * Append-only: cada cambio deja un registro (quién, cuándo, de qué estado a
 * cuál). No se edita ni se borra (sólo `created_at`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Quién hizo el cambio; nullable + nullOnDelete para conservar el
            // histórico aunque se borre el usuario.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status')->nullable();   // null = estado inicial
            $table->string('to_status');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
    }
};
