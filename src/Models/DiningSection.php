<?php

namespace Igniter\Reservation\Models;

use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;

/**
 * DiningSection Model Class
 *
 * @property int $id
 * @property int $location_id
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property int $priority
 * @property int $is_enabled
 * @property string|null $created_at
 * @property string|null $updated_at
 * @mixin \Igniter\Flame\Database\Model
 */
class DiningSection extends \Igniter\Flame\Database\Model
{
    use HasFactory;
    use Locationable;

    public $table = 'dining_sections';

    /**
     * @var array Relations
     */
    public $relation = [
        'belongsTo' => [
            'location' => [Location::class],
        ],
        'hasMany' => [
            'dining_areas' => [DiningArea::class, 'foreignKey' => 'location_id', 'otherKey' => 'location_id'],
        ],
    ];

    public function getRecordEditorOptions()
    {
        return self::dropdown('name');
    }

    public function getPriorityOptions()
    {
        return collect(range(0, 9))->map(function($priority) {
            return lang('igniter.reservation::default.dining_tables.text_priority_'.$priority);
        })->all();
    }

    public function scopeWhereIsReservable($query)
    {
        return $query->where('is_enabled', 1);
    }
}
