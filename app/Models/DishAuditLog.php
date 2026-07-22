<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RNF-20: entrada de la bitácora de auditoría del catálogo (crear/editar/
 * eliminar platos y cambios de precio). Append-only: sin updated_at.
 */
class DishAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'dish_id',
        'user_id',
        'action',
        'dish_name',
        'old_price',
        'new_price',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
