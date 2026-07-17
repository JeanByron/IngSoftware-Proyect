<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Reserva de mesa (módulo activable, RNF-10 / flag MODULE_RESERVAS).
 * Gestionada por el personal desde el panel.
 */
class Reservation extends Model
{
    use HasFactory;

    /** Estados del ciclo de vida de la reserva. */
    public const STATUS_PENDIENTE  = 'pendiente';
    public const STATUS_CONFIRMADA = 'confirmada';
    public const STATUS_CANCELADA  = 'cancelada';

    public const STATUSES = [
        self::STATUS_PENDIENTE,
        self::STATUS_CONFIRMADA,
        self::STATUS_CANCELADA,
    ];

    protected $fillable = [
        'customer_name',
        'phone',
        'reserved_at',
        'party_size',
        'table_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'reserved_at'  => 'datetime',
        'party_size'   => 'integer',
        'table_number' => 'integer',
    ];

    /** Reservas de hoy en adelante, ordenadas por fecha (para el panel). */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('reserved_at', '>=', now()->startOfDay())
            ->orderBy('reserved_at');
    }

    /** Etiqueta legible del estado para las vistas. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDIENTE  => 'Pendiente',
            self::STATUS_CONFIRMADA => 'Confirmada',
            self::STATUS_CANCELADA  => 'Cancelada',
            default                 => ucfirst($this->status),
        };
    }
}
