<?php

namespace App\Services\Payments;

use App\Models\Order;

/**
 * Contrato de una pasarela de pago (RNF-08).
 *
 * El resto de la app depende de esta interfaz, no de una pasarela concreta.
 * Hoy se inyecta FakePaymentGateway (cobro simulado); integrar una pasarela
 * real (Wompi, MercadoPago, Stripe...) es escribir otra implementación y
 * cambiar el binding en AppServiceProvider — sin tocar el flujo del pedido.
 */
interface PaymentGateway
{
    /**
     * Intenta cobrar el pedido con el método indicado.
     */
    public function charge(Order $order, string $method): PaymentResult;
}
