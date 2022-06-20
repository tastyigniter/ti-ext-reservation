<?php

namespace Igniter\Reservation\Models;

use Igniter\Flame\Database\Traits\Validation;

class DiningSection extends \Igniter\Flame\Database\Model
{
    use Validation;

    public $table = 'dining_sections';

    public $timestamps = true;

    /**
     * @var array Relations
     */
    public $relation = [
        'belongsTo' => [
            'dining_area' => [DiningArea::class],
        ],
    ];

    public $rules = [
        'dining_area_id' => ['required', 'integer'],
        'name' => ['required', 'string'],
        'description' => ['string'],
        'color' => ['string'],
    ];

    public function getRecordEditorOptions()
    {
        return static::dropdown('name');
    }
}
