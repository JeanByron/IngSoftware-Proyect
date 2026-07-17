<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'customer_name' => $this->faker->name(),
            'phone'         => $this->faker->numerify('3########'),
            'reserved_at'   => $this->faker->dateTimeBetween('now', '+2 weeks'),
            'party_size'    => $this->faker->numberBetween(1, 8),
            'table_number'  => $this->faker->optional()->numberBetween(1, 20),
            'status'        => Reservation::STATUS_PENDIENTE,
            'notes'         => $this->faker->optional()->sentence(),
        ];
    }
}
