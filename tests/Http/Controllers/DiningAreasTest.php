<?php

declare(strict_types=1);

namespace Igniter\Reservation\Tests\Http\Controllers;

use Igniter\Reservation\Models\DiningArea;
use Igniter\Reservation\Models\DiningTable;

it('loads dining areas page', function(): void {
    actingAsSuperUser()
        ->get(route('igniter.reservation.dining_areas'))
        ->assertOk();
});

it('loads create dining area page', function(): void {
    actingAsSuperUser()
        ->get(route('igniter.reservation.dining_areas', ['slug' => 'create']))
        ->assertOk();
});

it('loads edit dining area page', function(): void {
    $diningArea = DiningArea::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.reservation.dining_areas', ['slug' => 'edit/'.$diningArea->getKey()]))
        ->assertOk();
});

it('loads dining area preview page', function(): void {
    $diningArea = DiningArea::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.reservation.dining_areas', ['slug' => 'preview/'.$diningArea->getKey()]))
        ->assertOk();
});

it('duplicates dining area', function(): void {
    $diningArea = DiningArea::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.reservation.dining_areas'), [
            'id' => (string)$diningArea->getKey(),
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDuplicate',
        ])
        ->assertOk();

    expect(DiningArea::where('name', $diningArea->name.' (copy)')->exists())->toBeTrue();
});

it('creates dining area', function(): void {
    actingAsSuperUser()
        ->post(route('igniter.reservation.dining_areas', ['slug' => 'create']), [
            'DiningArea' => [
                'name' => 'Created Dining Area',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(DiningArea::where('name', 'Created Dining Area')->exists())->toBeTrue();
});

it('creates a dining table combo', function(): void {
    $diningArea = DiningArea::factory()->create();
    $tables = $diningArea->dining_tables()->saveMany(DiningTable::factory(3)->make());

    actingAsSuperUser()
        ->post(route('igniter.reservation.dining_areas', ['slug' => 'edit/'.$diningArea->getKey()]), [
            'DiningArea' => [
                '_select_dining_tables' => $tables->pluck('id')->all(),
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onCreateCombo',
        ]);

    $this->assertDatabaseHas('dining_tables', [
        'name' => $tables->pluck('name')->join('/'),
        'is_combo' => 1,
    ]);
});

it('updates dining area', function(): void {
    $diningArea = DiningArea::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.reservation.dining_areas', ['slug' => 'edit/'.$diningArea->getKey()]), [
            'DiningArea' => [
                'name' => 'Updated Dining Area',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(DiningArea::find($diningArea->getKey()))->name->toBe('Updated Dining Area');
});

it('updates dining area fixes broken tree', function(): void {
    $diningArea = DiningArea::factory()->create();
    $diningTableMock = mock(DiningTable::class)->makePartial();
    $diningTableMock->shouldReceive('isBroken')->andReturnTrue();
    $diningTableMock->shouldReceive('fixBrokenTreeQuietly')->once();
    app()->instance(DiningTable::class, $diningTableMock);

    actingAsSuperUser()
        ->post(route('igniter.reservation.dining_areas', ['slug' => 'edit/'.$diningArea->getKey()]), [
            'DiningArea' => [
                'name' => 'Updated Dining Area',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ])
        ->assertOk();

    expect(DiningArea::find($diningArea->getKey()))->name->toBe('Updated Dining Area');
});

it('deletes dining area', function(): void {
    $diningArea = DiningArea::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.reservation.dining_areas', ['slug' => 'edit/'.$diningArea->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(DiningArea::find($diningArea->getKey()))->toBeNull();
});
