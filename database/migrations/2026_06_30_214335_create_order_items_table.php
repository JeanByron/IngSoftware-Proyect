<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Líneas de un pedido (relación N:M entre pedidos y platos con cantidad).
 *
 *  - RF-09/RF-11 platos agregados al carrito que se persisten al confirmar
 *  - RF-13 cantidad por plato
 *  - unit_price congela el precio del plato en el momento del pedido,
 *    de modo que cambiar el precio del plato no altera pedidos pasados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained();
            $table->string('dish_name');                  // nombre congelado (histórico)
            $table->decimal('unit_price', 10, 2);         // precio congelado
            $table->unsignedInteger('quantity');           // RF-13: cantidad
            $table->decimal('subtotal', 10, 2);           // unit_price * quantity
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
