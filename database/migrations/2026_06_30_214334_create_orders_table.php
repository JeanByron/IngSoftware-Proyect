<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de pedidos.
 *
 * Soporta los flujos de cliente y el panel:
 *  - RF-08 asociar número de mesa cuando el acceso viene del QR (type = presencial)
 *  - RF-10/RF-12 flujo domicilio con dirección (type = domicilio)
 *  - RF-14 total del pedido
 *  - RF-17 estado inicial "recibido"
 *  - RF-20 actualización de estado desde el panel
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // 'presencial' (con mesa, vía QR) | 'domicilio' (sin QR)
            $table->string('type');
            // RF-08: número de mesa, sólo para pedidos presenciales
            $table->unsignedInteger('table_number')->nullable();
            // RF-12: dirección de entrega, sólo para domicilios
            $table->string('address')->nullable();
            // RF-14: total calculado del pedido
            $table->decimal('total', 10, 2)->default(0);
            // RF-17: estado del ciclo de vida del pedido
            //   recibido -> en_preparacion -> listo -> entregado
            $table->string('status')->default('recibido');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
