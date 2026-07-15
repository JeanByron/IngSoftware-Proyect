<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RF-03 (corrección): permitir eliminar un plato aunque tenga pedidos históricos.
 *
 * La FK dish_id se declaró sin comportamiento de borrado, así que con
 * foreign_key_constraints activas (SQLite) borrar un plato referenciado lanzaba
 * una violación de FK (error 500). Se pasa a nullable + nullOnDelete: al borrar
 * el plato, order_items.dish_id queda en NULL pero dish_name/unit_price (snapshot
 * congelado) conservan el histórico intacto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['dish_id']);
            $table->foreignId('dish_id')->nullable()->change();
            $table->foreign('dish_id')->references('id')->on('dishes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['dish_id']);
            $table->foreignId('dish_id')->nullable(false)->change();
            $table->foreign('dish_id')->references('id')->on('dishes');
        });
    }
};
