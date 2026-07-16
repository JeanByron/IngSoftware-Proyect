<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Reglas base del pedido del cliente (RF-15/16). Las reglas condicionales por
 * tipo (dirección en domicilio, mesa en presencial) y la revalidación de
 * disponibilidad se resuelven en el controlador, que necesita esa lógica.
 */
class StoreOrderRequest extends FormRequest
{
    /** Ruta pública: cualquiera puede registrar un pedido. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'type'             => ['required', 'in:presencial,domicilio'],
            'table_number'     => ['nullable', 'integer', 'min:1'],
            'address'          => ['nullable', 'string', 'max:255'],
            'items'            => ['required', 'array', 'min:1'], // RF-16: al menos un ítem
            'items.*.dish_id'  => ['required', 'integer', 'exists:dishes,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
