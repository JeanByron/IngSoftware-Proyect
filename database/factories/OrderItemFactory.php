<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fábrica de líneas de pedido para pruebas.
 * Congela dish_name/unit_price al momento del pedido (snapshot histórico).
 *
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity  = fake()->numberBetween(1, 5);
        $unitPrice = fake()->numberBetween(5000, 40000);

        return [
            'order_id'   => Order::factory(),
            'dish_id'    => Dish::factory(),
            'dish_name'  => fake()->words(2, true),
            'unit_price' => $unitPrice,
            'quantity'   => $quantity,
            'subtotal'   => $unitPrice * $quantity,
        ];
    }
}
