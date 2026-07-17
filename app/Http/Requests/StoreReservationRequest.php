<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación declarativa de la reserva, compartida por crear y editar.
 * La autorización real la da el middleware 'auth' de la ruta.
 */
class StoreReservationRequest extends FormRequest
{
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
            'customer_name' => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'reserved_at'   => ['required', 'date', 'after_or_equal:now'],
            'party_size'    => ['required', 'integer', 'min:1', 'max:50'],
            'table_number'  => ['nullable', 'integer', 'min:1'],
            'status'        => ['nullable', Rule::in(Reservation::STATUSES)],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Datos normalizados para persistir: si no llega estado (creación), se
     * arranca en 'pendiente'.
     *
     * @return array<string, mixed>
     */
    public function validatedData(): array
    {
        $data = $this->validated();
        $data['status'] = $data['status'] ?? Reservation::STATUS_PENDIENTE;

        return $data;
    }
}
