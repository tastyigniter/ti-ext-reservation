<?php

namespace Igniter\Reservation\Models;

use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Exception\FlashException;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Illuminate\Support\Collection;

/**
 * DiningArea Model Class
 *
 * @property int $id
 * @property int $location_id
 * @property string $name
 * @property string|null $description
 * @property array|null $floor_plan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $dining_table_count
 * @mixin \Igniter\Flame\Database\Model
 */
class DiningArea extends \Igniter\Flame\Database\Model
{
    use HasFactory;
    use Locationable;

    public $table = 'dining_areas';

    public $timestamps = true;

    protected $casts = [
        'floor_plan' => 'array',
    ];

    /**
     * @var array Relations
     */
    public $relation = [
        'hasMany' => [
            'dining_sections' => [DiningSection::class, 'foreignKey' => 'location_id', 'otherKey' => 'location_id'],
            'dining_tables' => [DiningTable::class, 'delete' => true],
            'dining_table_solos' => [DiningTable::class, 'scope' => 'whereIsNotCombo'],
            'dining_table_combos' => [DiningTable::class, 'scope' => 'whereIsCombo'],
            'available_tables' => [DiningTable::class, 'scope' => 'whereIsRoot'],
        ],
        'belongsTo' => [
            'location' => [Location::class],
        ],
    ];

    public static function getDropdownOptions()
    {
        return static::dropdown('name');
    }

    public function getTablesForFloorPlan()
    {
        return $this->available_tables->map(function($diningTable) {
            return $diningTable->toFloorPlanArray();
        });
    }

    public function getDiningTablesWithReservation($reservations)
    {
        return $this->available_tables
            ->map(function($diningTable) use ($reservations) {
                $reservation = $reservations->first(function($reservation) use ($diningTable) {
                    return $reservation->tables->where('id', $diningTable->id)->count() > 0;
                });

                return $diningTable->toFloorPlanArray($reservation);
            });
    }

    //
    // Events
    //

    //
    // Accessors & Mutators
    //

    public function getDiningTableCountAttribute($value)
    {
        return $this->available_tables->count();
    }

    public function scopeWhereIsActive($query)
    {
        return $query->whereIsRoot()->where('is_active', 1);
    }

    //
    // Helpers
    //

    public function duplicate()
    {
        $newDiningArea = $this->replicate();
        $newDiningArea->name .= ' (copy)';
        $newDiningArea->save();

        $this->dining_tables
            ->filter(function($table) {
                return !$table->is_combo;
            })
            ->each(function($table) use ($newDiningArea) {
                $newTable = $table->replicate();
                $newTable->dining_area_id = $newDiningArea->getKey();
                $newTable->save();
            });

        return $newDiningArea;
    }

    public function createCombo(Collection $tables)
    {
        $firstTable = $tables->first();
        $tableNames = $tables->pluck('name')->join('/');

        if ($tables->filter(function($table) {
            return $table->parent !== null;
        })->isNotEmpty()) {
            throw new FlashException(lang('igniter.reservation::default.dining_areas.alert_table_already_combined'));
        }

        if ($tables->pluck('dining_section_id')->unique()->count() > 1) {
            throw new FlashException(lang('igniter.reservation::default.dining_areas.alert_table_combo_section_mismatch'));
        }

        $comboTable = $this->dining_tables()->create([
            'name' => $tableNames,
            'shape' => $firstTable->shape,
            'dining_area_id' => $firstTable->dining_area_id,
            'dining_section_id' => $firstTable->dining_section_id,
            'min_capacity' => $tables->sum('min_capacity'),
            'max_capacity' => $tables->sum('max_capacity'),
            'is_combo' => true,
            'is_enabled' => true,
        ]);

        $tables->each(function($table) use ($comboTable) {
            $table->parent()->associate($comboTable)->saveQuietly();
        });

        $comboTable::fixBrokenTreeQuietly();

        return $comboTable;
    }
}
