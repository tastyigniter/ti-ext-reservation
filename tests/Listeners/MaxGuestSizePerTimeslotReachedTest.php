<?php

namespace Igniter\Reservation\Tests\Listeners;

use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Reservation\Listeners\MaxGuestSizePerTimeslotReached;
use Igniter\Reservation\Models\Reservation;

it('returns true when guest limit is exceeded', function() {
    $timeslot = '2023-12-01 18:00:00';
    $guestNum = 5;
    $this->travelTo($timeslot);

    Reservation::factory()->create([
        'location_id' => 1,
        'reserve_date' => '2023-12-01',
        'reserve_time' => '18:00',
        'guest_num' => 6,
    ]);

    LocationFacade::shouldReceive('current->getSettings')->with('booking.limit_guests')->andReturn(true);
    LocationFacade::shouldReceive('current->getSettings')->with('booking.limit_guests_count', 20)->andReturn(10);
    LocationFacade::shouldReceive('getId')->andReturn(1);

    expect((new MaxGuestSizePerTimeslotReached())->handle($timeslot, $guestNum))->toBeTrue();
});

it('returns null when guest limit is not exceeded', function() {
    $timeslot = '2023-12-01 18:00:00';
    $guestNum = 5;
    $this->travelTo($timeslot);

    Reservation::factory()->create([
        'location_id' => 1,
        'reserve_date' => '2023-12-01',
        'reserve_time' => '18:00',
        'guest_num' => 10,
    ]);

    LocationFacade::shouldReceive('current->getSettings')->with('booking.limit_guests')->andReturn(true);
    LocationFacade::shouldReceive('current->getSettings')->with('booking.limit_guests_count', 20)->andReturn(20);
    LocationFacade::shouldReceive('getId')->andReturn(1);

    expect((new MaxGuestSizePerTimeslotReached())->handle($timeslot, $guestNum))->toBeNull();
});

it('returns null when guest limit setting is disabled', function() {
    $timeslot = '2023-12-01 18:00:00';
    $guestNum = 5;
    $this->travelTo($timeslot);

    LocationFacade::shouldReceive('current->getSettings')->with('booking.limit_guests')->andReturn(false);

    expect((new MaxGuestSizePerTimeslotReached())->handle($timeslot, $guestNum))->toBeNull();
});

it('returns null when guest limit count is zero', function() {
    $timeslot = '2023-12-01 18:00:00';
    $guestNum = 5;
    $this->travelTo($timeslot);

    LocationFacade::shouldReceive('current->getSettings')->with('booking.limit_guests')->andReturn(true);
    LocationFacade::shouldReceive('current->getSettings')->with('booking.limit_guests_count', 20)->andReturn(0);

    expect((new MaxGuestSizePerTimeslotReached())->handle($timeslot, $guestNum))->toBeNull();
});
