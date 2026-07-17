<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Pasarela SIMULADA (RNF-08). No mueve dinero real: aprueba el cobro y
 * devuelve una referencia ficticia. Es el driver por defecto del MVP.
 *
 * IMPORTANTE para la sustentación: la estructura (interfaz + inyección) queda
 * lista para una pasarela real; esta implementación NO cumple RNF-08 al 100 %
 * (no hay cobro real), sólo demuestra el flujo y el punto de integración.
 */
class FakePaymentGateway implements PaymentGateway
{
    public function charge(Order $order, string $method): PaymentResult
    {
        // Referencia ficticia estilo comprobante de pasarela.
        $reference = 'SIM-'.mb_strtoupper(Str::random(10));

        return PaymentResult::approved($reference);
    }
}
