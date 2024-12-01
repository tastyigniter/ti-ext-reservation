<?php

namespace Igniter\Reservation\Tests\Models;

use Carbon\Carbon;
use Igniter\Flame\Database\Traits\NestedTree;
use Igniter\Flame\Database\Traits\Sortable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\Reservation\Models\DiningArea;
use Igniter\Reservation\Models\DiningSection;
use Igniter\Reservation\Models\DiningTable;
use Igniter\Reservation\Models\Reservation;
use Mockery;

it('returns correct dining section id options when dining area exists', function() {
    DiningSection::factory()->create(['location_id' => 1, 'name' => 'Section1']);
    DiningSection::factory()->create(['location_id' => 1, 'name' => 'Section2']);

    $diningTable = Mockery::mock(DiningTable::class)->makePartial();
    $diningArea = Mockery::mock(DiningArea::class)->makePartial();

    $diningTable->exists = true;
    $diningTable->dining_area = $diningArea;
    $diningArea->shouldReceive('getAttribute')->with('location_id')->andReturn(1);

    expect($diningTable->getDiningSectionIdOptions()->toArray())->toBe([1 => 'Section1', 2 => 'Section2']);
});

it('returns empty dining section id options when dining area does not exist', function() {
    $diningTable = Mockery::mock(DiningTable::class)->makePartial();
    $diningTable->shouldReceive('exists')->andReturn(false);

    expect($diningTable->getDiningSectionIdOptions())->toBe([]);
});

it('returns correct section name attribute', function() {
    $diningSection = Mockery::mock(DiningSection::class)->makePartial();
    $diningSection->shouldReceive('getAttribute')->with('name')->andReturn('Main Section');

    $diningTable = Mockery::mock(DiningTable::class)->makePartial();
    $diningTable->shouldReceive('extendableGet')->with('dining_section')->andReturn($diningSection);

    expect($diningTable->section_name)->toBe('Main Section');
});

it('returns null section name attribute when dining section is null', function() {
    $diningTable = Mockery::mock(DiningTable::class)->makePartial();
    $diningTable->shouldReceive('getAttribute')->with('dining_section')->andReturn(null);

    expect($diningTable->section_name)->toBeNull();
});

it('returns correct floor plan array without reservation', function() {
    $diningTable = Mockery::mock(DiningTable::class)->makePartial();
    $diningTable->id = 1;
    $diningTable->name = 'Table1';
    $diningTable->min_capacity = 2;
    $diningTable->max_capacity = 4;
    $diningTable->shape = 'square';
    $diningTable->seat_layout = ['layout'];

    $expectedArray = [
        'id' => 1,
        'name' => 'Table1',
        'description' => '2-4',
        'capacity' => 4,
        'shape' => 'square',
        'seatLayout' => ['layout'],
        'tableColor' => null,
        'seatColor' => null,
        'customerName' => null,
    ];

    expect($diningTable->toFloorPlanArray())->toBe($expectedArray);
});

it('returns correct floor plan array with reservation', function() {
    $reservation = Mockery::mock(Reservation::class)->makePartial();
    $reservation->shouldReceive('getAttribute')->with('reservation_datetime')->andReturn(Carbon::parse('2023-10-10 12:00:00'));
    $reservation->shouldReceive('getAttribute')->with('reservation_end_datetime')->andReturn(Carbon::parse('2023-10-10 14:00:00'));
    $reservation->shouldReceive('getAttribute')->with('customer_name')->andReturn('John Doe');
    $reservation->status = (object)['status_color' => 'red'];

    $diningTable = Mockery::mock(DiningTable::class)->makePartial();
    $diningTable->id = 1;
    $diningTable->name = 'Table1';
    $diningTable->min_capacity = 2;
    $diningTable->max_capacity = 4;
    $diningTable->shape = 'square';
    $diningTable->seat_layout = ['layout'];

    $expectedArray = [
        'id' => 1,
        'name' => 'Table1',
        'description' => '12:00 pm - 02:00 pm',
        'capacity' => 4,
        'shape' => 'square',
        'seatLayout' => ['layout'],
        'tableColor' => null,
        'seatColor' => 'red',
        'customerName' => 'John Doe',
    ];

    expect($diningTable->toFloorPlanArray($reservation))->toBe($expectedArray);
});

it('configures dining table model correctly', function() {
    $diningTable = new DiningTable();

    expect(class_uses_recursive($diningTable))
        ->toContain(Locationable::class)
        ->toContain(NestedTree::class)
        ->toContain(Sortable::class)
        ->and(DiningTable::SORT_ORDER)->toBe('priority')
        ->and($diningTable->getTable())->toBe('dining_tables')
        ->and($diningTable->timestamps)->toBeTrue()
        ->and($diningTable->getMorphClass())->toBe('tables')
        ->and($diningTable->getCasts())->toHaveKeys([
            'min_capacity', 'max_capacity', 'extra_capacity', 'priority', 'is_combo', 'is_enabled', 'seat_layout',
        ])
        ->and($diningTable->relation)->toEqual([
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
        ]);
});
