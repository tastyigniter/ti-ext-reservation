<?php

namespace Igniter\Reservation\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = \Igniter\Reservation\Models\Table::class;

    public function definition(): array
    {
        return [
            'table_name' => $this->faker->sentence(2),
            'min_capacity' => $this->faker->randomDigitNotNull(),
            'max_capacity' => $this->faker->numberBetween(10, 99),
            'extra_capacity' => $this->faker->numberBetween(1, 999),
            'priority' => $this->faker->randomDigit(),
            'is_joinable' => $this->faker->boolean(),
            'table_status' => $this->faker->boolean(),
        ];
    }
}
