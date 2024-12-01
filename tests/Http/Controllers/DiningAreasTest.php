<?php

namespace Igniter\Reservation\Tests\Http\Controllers;

use Igniter\Reservation\Models\DiningArea;

it('loads dining areas page', function() {
    actingAsSuperUser()
        ->get(route('igniter.reservation.dining_areas'))
        ->assertOk();
});

it('loads create dining area page', function() {
    actingAsSuperUser()
        ->get(route('igniter.reservation.dining_areas', ['slug' => 'create']))
        ->assertOk();
});

it('loads edit dining area page', function() {
    $diningArea = DiningArea::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.reservation.dining_areas', ['slug' => 'edit/'.$diningArea->getKey()]))
        ->assertOk();
});

it('loads dining area preview page', function() {
    $diningArea = DiningArea::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.reservation.dining_areas', ['slug' => 'preview/'.$diningArea->getKey()]))
        ->assertOk();
});

it('creates dining area', function() {
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

it('updates dining area', function() {
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

it('deletes dining area', function() {
    $diningArea = DiningArea::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.reservation.dining_areas', ['slug' => 'edit/'.$diningArea->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(DiningArea::find($diningArea->getKey()))->toBeNull();
});
