<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RNF-20: entrada de la bitácora de cambios de estado de un pedido.
 * Append-only: no tiene `updated_at`.
 */
class OrderStatusLog extends Model
{
    /** Registro de sólo-inserción: no se actualiza. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'user_id',
        'from_status',
        'to_status',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Usuario que hizo el cambio (puede ser null si se borró la cuenta). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
