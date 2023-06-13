<?php

namespace Igniter\Reservation\Models\Concerns;

use Igniter\Flame\Traits\ExtensionTrait;
use Igniter\Local\Models\Location;
use Igniter\Reservation\Models\DiningArea;
use Igniter\Reservation\Models\DiningTable;

class ExtendsLocation
{
    use ExtensionTrait;

    public function __construct(protected Location $model)
    {
        $this->model->relation['hasMany']['dining_areas'] = [DiningArea::class, 'delete' => true];
        $this->model->relation['morphedByMany']['tables'] = [DiningTable::class, 'name' => 'locationable'];
    }
}
