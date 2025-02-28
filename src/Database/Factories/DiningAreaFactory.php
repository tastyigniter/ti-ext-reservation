<?php

declare(strict_types=1);

namespace Igniter\Reservation\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;
use Igniter\Reservation\Models\DiningArea;
use Override;

class DiningAreaFactory extends Factory
{
    protected $model = DiningArea::class;

    #[Override]
    public function definition()
    {
        return [
            'location_id' => 1,
            'name' => $this->faker->sentence(2),
        ];
    }
}
