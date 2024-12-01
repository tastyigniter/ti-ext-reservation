<?php

namespace Igniter\Reservation\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;

class DiningSectionFactory extends Factory
{
    protected $model = \Igniter\Reservation\Models\DiningSection::class;

    public function definition()
    {
        return [
            'location_id' => $this->faker->numberBetween(1, 10),
            'name' => $this->faker->sentence(2),
            'description' => $this->faker->sentence(5),
            'priority' => $this->faker->randomDigit(),
            'is_enabled' => $this->faker->boolean(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
