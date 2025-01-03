<?php

namespace Igniter\Reservation\Models;

use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Traits\NestedTree;
use Igniter\Flame\Database\Traits\Sortable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;

/**
 * DiningTable Model Class
 *
 * @property int $id
 * @property int $dining_area_id
 * @property int|null $dining_section_id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $shape
 * @property int $min_capacity
 * @property int $max_capacity
 * @property int $extra_capacity
 * @property bool $is_combo
 * @property bool $is_enabled
 * @property int|null $nest_left
 * @property int|null $nest_right
 * @property int $priority
 * @property array|null $seat_layout
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Kalnoy\Nestedset\Collection<int, DiningTable> $children
 * @property-read int|null $children_count
 * @property-read mixed $section_name
 * @property-read DiningTable|null $parent
 * @mixin \Igniter\Flame\Database\Model
 */
class DiningTable extends \Igniter\Flame\Database\Model
{
    use HasFactory;
    use Locationable;
    use NestedTree;
    use Sortable;

    public const SORT_ORDER = 'priority';

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
        return $this->dining_section?->name;
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
