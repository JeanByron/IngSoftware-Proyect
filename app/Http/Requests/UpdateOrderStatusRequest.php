<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del cambio de estado de un pedido desde el panel (RF-20).
 * Sólo se aceptan los estados válidos definidos en el modelo Order.
 */
class UpdateOrderStatusRequest extends FormRequest
{
    /** Protegido por el middleware 'auth' de la ruta. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Order::STATUSES)],
        ];
    }
}
