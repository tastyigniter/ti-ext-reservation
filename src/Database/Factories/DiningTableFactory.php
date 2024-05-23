<?php

namespace Igniter\Reservation\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;

class DiningTableFactory extends Factory
{
    protected $model = \Igniter\Reservation\Models\DiningTable::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(2),
            'min_capacity' => $this->faker->randomDigitNotNull(),
            'max_capacity' => $this->faker->numberBetween(10, 20),
            'priority' => $this->faker->randomDigit(),
            'is_enabled' => $this->faker->boolean(),
        ];
    }
}
