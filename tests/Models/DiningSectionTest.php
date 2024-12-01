<?php

namespace Igniter\Reservation\Tests\Models;

use Igniter\Flame\Database\Builder;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Reservation\Models\DiningSection;
use Mockery;

it('returns correct record editor options', function() {
    $diningSection = Mockery::mock(DiningSection::class)->makePartial();
    $diningSection->shouldReceive('dropdown')->with('name')->andReturn(['Option1', 'Option2']);

    expect($diningSection->getRecordEditorOptions())->toBe(['Option1', 'Option2']);
});

it('returns correct priority options', function() {
    $diningSection = new DiningSection();
    $expectedOptions = [
        lang('igniter.reservation::default.dining_tables.text_priority_0'),
        lang('igniter.reservation::default.dining_tables.text_priority_1'),
        lang('igniter.reservation::default.dining_tables.text_priority_2'),
        lang('igniter.reservation::default.dining_tables.text_priority_3'),
        lang('igniter.reservation::default.dining_tables.text_priority_4'),
        lang('igniter.reservation::default.dining_tables.text_priority_5'),
        lang('igniter.reservation::default.dining_tables.text_priority_6'),
        lang('igniter.reservation::default.dining_tables.text_priority_7'),
        lang('igniter.reservation::default.dining_tables.text_priority_8'),
        lang('igniter.reservation::default.dining_tables.text_priority_9'),
    ];

    expect($diningSection->getPriorityOptions())->toBe($expectedOptions);
});

it('applies where is reservable scope', function() {
    $builder = Mockery::mock(Builder::class);
    $builder->shouldReceive('where')->with('is_enabled', 1)->once()->andReturnSelf();

    $diningSection = new DiningSection();
    $diningSection->scopeWhereIsReservable($builder);
});

it('configures dining section model correctly', function() {
    $diningSection = new DiningSection();

    expect(class_uses_recursive($diningSection))
        ->toContain(Locationable::class)
        ->and($diningSection->getTable())->toBe('dining_sections')
        ->and($diningSection->timestamps)->toBeFalse()
        ->and($diningSection->relation)->toEqual([
            'belongsTo' => [
                'location' => ['Igniter\Local\Models\Location'],
            ],
            'hasMany' => [
                'dining_areas' => ['Igniter\Reservation\Models\DiningArea', 'foreignKey' => 'location_id', 'otherKey' => 'location_id'],
            ],
        ]);
});
