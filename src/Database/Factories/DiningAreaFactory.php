<?php

namespace Igniter\Reservation\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;

class DiningAreaFactory extends Factory
{
    protected $model = \Igniter\Reservation\Models\DiningArea::class;

    public function definition()
    {
        return [
            'location_id' => 1,
            'name' => $this->faker->sentence(2),
        ];
    }
}
