<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fábrica de pedidos para pruebas (Módulo de Flujo de Cliente / Panel).
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Estado por defecto: pedido presencial recién recibido (RF-17).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type'         => Order::TYPE_PRESENCIAL,
            'table_number' => fake()->numberBetween(1, 30),
            'address'      => null,
            'total'        => 0,
            'status'       => Order::STATUS_RECIBIDO,
        ];
    }

    /** Estado: pedido a domicilio con dirección (RF-10 / RF-12). */
    public function domicilio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'         => Order::TYPE_DOMICILIO,
            'table_number' => null,
            'address'      => fake()->address(),
        ]);
    }

    /** Estado: pedido presencial asociado a una mesa (RF-06 / RF-08). */
    public function presencial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'         => Order::TYPE_PRESENCIAL,
            'table_number' => fake()->numberBetween(1, 30),
            'address'      => null,
        ]);
    }

    /** Fija el estado del pedido (RF-20). */
    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
