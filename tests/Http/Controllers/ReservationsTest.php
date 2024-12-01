<?php

namespace Igniter\Reservation\Tests\Http\Controllers;

use Igniter\Reservation\Models\Reservation;

it('loads reservations page', function() {
    actingAsSuperUser()
        ->get(route('igniter.reservation.reservations'))
        ->assertOk();
});

it('loads create reservation page', function() {
    actingAsSuperUser()
        ->get(route('igniter.reservation.reservations', ['slug' => 'create']))
        ->assertOk();
});

it('loads edit reservation page', function() {
    $reservation = Reservation::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.reservation.reservations', ['slug' => 'edit/'.$reservation->getKey()]))
        ->assertOk();
});

it('loads reservation preview page', function() {
    $reservation = Reservation::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.reservation.reservations', ['slug' => 'preview/'.$reservation->getKey()]))
        ->assertOk();
});

it('creates reservation', function() {
    actingAsSuperUser()
        ->post(route('igniter.reservation.reservations', ['slug' => 'create']), [
            'Reservation' => [
                'location_id' => 1,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'telephone' => '1234567890',
                'reserve_date' => '2021-01-01',
                'reserve_time' => '12:00',
                'guest_num' => 2,
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    $this->assertDatabaseHas('reservations', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'telephone' => '1234567890',
        'reserve_date' => '2021-01-01',
        'reserve_time' => '12:00',
        'guest_num' => 2,
    ]);
});

it('updates reservation', function() {
    $reservation = Reservation::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.reservation.reservations', ['slug' => 'edit/'.$reservation->getKey()]), [
            'Reservation' => [
                'location_id' => 1,
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'telephone' => '1234567890',
                'reserve_date' => '2025-05-05',
                'reserve_time' => '16:00',
                'guest_num' => 2,
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    $this->assertDatabaseHas('reservations', [
        'first_name' => 'Jane',
        'reserve_date' => '2025-05-05',
        'reserve_time' => '16:00',
        'guest_num' => 2,
    ]);
});

it('deletes reservation', function() {
    $reservation = Reservation::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.reservation.reservations', ['slug' => 'edit/'.$reservation->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(Reservation::find($reservation->getKey()))->toBeNull();
});
