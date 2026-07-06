<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pedido del cliente (presencial vía QR o a domicilio).
 */
class Order extends Model
{
    use HasFactory;

    /** Tipos de pedido. */
    public const TYPE_PRESENCIAL = 'presencial';
    public const TYPE_DOMICILIO  = 'domicilio';

    /** Estados del ciclo de vida del pedido (RF-17, RF-20). */
    public const STATUS_RECIBIDO       = 'recibido';
    public const STATUS_EN_PREPARACION = 'en_preparacion';
    public const STATUS_LISTO          = 'listo';
    public const STATUS_ENTREGADO      = 'entregado';

    /** Estados válidos a los que el panel puede transicionar (RF-20). */
    public const STATUSES = [
        self::STATUS_RECIBIDO,
        self::STATUS_EN_PREPARACION,
        self::STATUS_LISTO,
        self::STATUS_ENTREGADO,
    ];

    protected $fillable = [
        'type',
        'table_number',
        'address',
        'total',
        'status',
    ];

    protected $casts = [
        'total'        => 'decimal:2',
        'table_number' => 'integer',
    ];

    /** Líneas del pedido. */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isPresencial(): bool
    {
        return $this->type === self::TYPE_PRESENCIAL;
    }

    /** Etiqueta legible del estado para las vistas. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_RECIBIDO       => 'Recibido',
            self::STATUS_EN_PREPARACION => 'En preparación',
            self::STATUS_LISTO          => 'Listo',
            self::STATUS_ENTREGADO      => 'Entregado',
            default                     => ucfirst($this->status),
        };
    }
}
