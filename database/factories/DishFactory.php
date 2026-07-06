<?php

namespace Database\Factories;

use App\Models\Dish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fábrica de platos para pruebas (Módulo de Gestión de Menú).
 *
 * @extends Factory<Dish>
 */
class DishFactory extends Factory
{
    protected $model = Dish::class;

    /**
     * Estado por defecto: un plato disponible con precio realista.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'         => fake()->unique()->words(2, true),
            'description'  => fake()->sentence(),
            'price'        => fake()->numberBetween(5000, 40000),
            'is_available' => true,
        ];
    }

    /** Estado: plato no disponible (RF-04 / RF-05). */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
