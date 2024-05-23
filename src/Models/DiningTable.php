<?php

namespace Igniter\Reservation\Models;

use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Traits\NestedTree;
use Igniter\Flame\Database\Traits\Sortable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;

class DiningTable extends \Igniter\Flame\Database\Model
{
    use HasFactory;
    use Locationable;
    use NestedTree;
    use Sortable;

    const SORT_ORDER = 'priority';

    public $table = 'dining_tables';

    public $timestamps = true;

    protected $casts = [
        'min_capacity' => 'integer',
        'max_capacity' => 'integer',
        'extra_capacity' => 'integer',
        'priority' => 'integer',
        'is_combo' => 'boolean',
        'is_enabled' => 'boolean',
        'seat_layout' => 'array',
    ];

    /**
     * @var array Relations
     */
    public $relation = [
        'belongsTo' => [
            'dining_area' => [DiningArea::class],
            'dining_section' => [DiningSection::class],
        ],
        'belongsToMany' => [
            'reservations' => [Reservation::class, 'table' => 'reservation_tables', 'otherKey' => 'reservation_id'],
        ],
        'hasOneThrough' => [
            'location' => [
                Location::class,
                'through' => DiningArea::class,
                'foreignKey' => 'id',
                'throughKey' => 'location_id',
                'otherKey' => 'dining_area_id',
                'secondOtherKey' => 'location_id',
            ],
        ],
    ];

    public $attributes = [
        'priority' => 0,
        'extra_capacity' => 0,
    ];

    public function getDiningSectionIdOptions()
    {
        return $this->exists ? DiningSection::where('location_id', $this->dining_area->location_id)->dropdown('name') : [];
    }

    public function getPriorityOptions()
    {
        return collect(range(0, 9))->map(function($priority) {
            return lang('igniter.reservation::default.dining_tables.text_priority_'.$priority);
        })->all();
    }

    //
    // Accessors & Mutators
    //

    public function getSectionNameAttribute()
    {
        return optional($this->dining_section)->name;
    }

    //
    // Helpers
    //

    public function sortWhenCreating()
    {
        return false;
    }

    public function toFloorPlanArray($reservation = null)
    {
        $defaults = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->min_capacity.'-'.$this->max_capacity,
            'capacity' => $this->max_capacity,
            'shape' => $this->shape,
            'seatLayout' => $this->seat_layout,
            'tableColor' => null,
            'seatColor' => null,
            'customerName' => null,
        ];

        if (!is_null($reservation)) {
            $defaults['description'] = $reservation->reservation_datetime->isoFormat(lang('system::lang.moment.time_format'))
                .' - '.$reservation->reservation_end_datetime->isoFormat(lang('system::lang.moment.time_format'));

            $defaults['seatColor'] = $reservation->status->status_color ?? null;
            $defaults['customerName'] = $reservation->customer_name;
        }

        return $defaults;
    }
}
