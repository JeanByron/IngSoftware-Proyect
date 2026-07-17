<?php

namespace App\Services\Payments;

/**
 * Resultado de un intento de cobro. Objeto de valor inmutable devuelto por
 * cualquier PaymentGateway (RNF-08).
 */
final class PaymentResult
{
    public function __construct(
        public readonly bool $successful,
        public readonly ?string $reference = null,
        public readonly ?string $message = null,
    ) {
    }

    public static function approved(string $reference): self
    {
        return new self(true, $reference, 'Pago aprobado.');
    }

    public static function declined(string $message): self
    {
        return new self(false, null, $message);
    }
}
