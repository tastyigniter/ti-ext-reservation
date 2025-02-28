<?php

declare(strict_types=1);

namespace Igniter\Reservation\Tests\Classes;

use Carbon\Carbon;
use DateTime;
use Igniter\Local\Classes\WorkingSchedule;
use Igniter\Local\Models\Location;
use Igniter\Reservation\Classes\BookingManager;
use Igniter\Reservation\Models\Reservation;
use Igniter\User\Facades\Auth;
use Igniter\User\Models\Customer;
use Illuminate\Support\Facades\Event;
use Mockery;

it('loads reservation with associated customer and location', function(): void {
    $customer = Mockery::mock(Customer::class)->makePartial();
    $location = Mockery::mock(Location::class)->makePartial();
    Auth::shouldReceive('customer')->andReturn($customer);
    Auth::shouldReceive('getUser')->andReturn($customer);

    $manager = new BookingManager;
    $manager->useLocation($location);

    $reservation = $manager->loadReservation();

    expect($reservation->customer)->toBe($customer)
        ->and($reservation->location)->toBe($location);
});

it('returns reservation by hash for specific customer', function(): void {
    $customer = Mockery::mock(Customer::class)->makePartial();
    $customer->customer_id = 1;
    $reservation = Reservation::factory()->create([
        'customer_id' => $customer->customer_id,
    ]);

    expect((new BookingManager)->getReservationByHash($reservation->hash, $customer))->hash->toBe($reservation->hash);
});

it('returns empty collection if location is not set when making time slots', function(): void {
    expect((new BookingManager)->makeTimeSlots(now()))->toBeEmpty();
});

it('returns time slots with default interval and lead time', function(): void {
    $this->travelTo(Carbon::now()->startOfDay());
    $location = Mockery::mock(Location::class)->makePartial();
    $schedule = Mockery::mock(WorkingSchedule::class)->makePartial();

    $location->shouldReceive('getReservationInterval')->andReturn(30);
    $location->shouldReceive('getReservationLeadTime')->andReturn(15);
    $location->shouldReceive('getMinReservationAdvanceTime')->andReturn(2);
    $location->shouldReceive('getMaxReservationAdvanceTime')->andReturn(90);
    $location->shouldReceive('getSettings')->with('booking.include_start_time', 1)->andReturn(1);
    $schedule->shouldReceive('generateTimeslot')->andReturn(collect([Carbon::now()->addMinutes(30)]));
    $location->shouldReceive('newWorkingSchedule')->andReturn($schedule);

    $manager = new BookingManager;
    $manager->useLocation($location);

    expect($manager->makeTimeSlots(now())->count())->toBe(1);
});

it('returns time slots with custom interval and lead time', function(): void {
    $this->travelTo(Carbon::now()->startOfDay());
    $location = Mockery::mock(Location::class)->makePartial();
    $schedule = Mockery::mock(WorkingSchedule::class)->makePartial();

    $location->shouldReceive('getSettings')->with('booking.include_start_time', 1)->andReturn(1);
    $location->shouldReceive('getMinReservationAdvanceTime')->andReturn(2);
    $location->shouldReceive('getMaxReservationAdvanceTime')->andReturn(90);
    $schedule->shouldReceive('generateTimeslot')->andReturn(collect([Carbon::now()->addMinutes(45)]));
    $location->shouldReceive('newWorkingSchedule')->andReturn($schedule);

    $manager = new BookingManager;
    $manager->useLocation($location);

    $result = $manager->makeTimeSlots(Carbon::now(), 45, 10);

    expect($result->count())->toBe(1);
});

it('filters out past time slots based on lead time', function(): void {
    $this->travelTo(Carbon::now()->startOfDay());
    $location = Mockery::mock(Location::class)->makePartial();
    $schedule = Mockery::mock(WorkingSchedule::class)->makePartial();

    $location->shouldReceive('getMaxReservationAdvanceTime')->andReturn(90);
    $location->shouldReceive('getReservationInterval')->andReturn(30);
    $location->shouldReceive('getReservationLeadTime')->andReturn(15);
    $location->shouldReceive('getSettings')->with('booking.include_start_time', 1)->andReturn(1);
    $location->shouldReceive('getMinReservationAdvanceTime')->andReturn(2);
    $location->shouldReceive('getMaxReservationAdvanceTime')->andReturn(90);
    $schedule->shouldReceive('generateTimeslot')->andReturn(collect([Carbon::now()->subMinutes(10), Carbon::now()->addMinutes(30)]));
    $location->shouldReceive('newWorkingSchedule')->andReturn($schedule);

    $manager = new BookingManager;
    $manager->useLocation($location);

    $result = $manager->makeTimeSlots(Carbon::now());

    expect($result->count())->toBe(2);
});

it('saves reservation with provided data', function(): void {
    Event::fake();

    $location = Mockery::mock(Location::class)->makePartial();
    $customer = Mockery::mock(Customer::class)->makePartial();
    $reservation = Mockery::mock(Reservation::class)->makePartial();

    $customer->email = 'john@example.com';
    Auth::shouldReceive('customer')->andReturn($customer);
    $location->shouldReceive('getReservationStayTime')->andReturn(60);
    $manager = new BookingManager;
    $manager->useLocation($location);

    $data = [
        'guest' => 2,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'telephone' => '1234567890',
        'comment' => 'Test comment',
        'sdateTime' => now()->toDateTimeString(),
    ];

    $reservation->shouldReceive('save')->once();
    $reservation->shouldReceive('addStatusHistory')->once();
    $result = $manager->saveReservation($reservation, $data);

    expect($result->guest_num)->toBe(2)
        ->and($result->first_name)->toBe('John')
        ->and($result->last_name)->toBe('Doe')
        ->and($result->email)->toBe('john@example.com')
        ->and($result->telephone)->toBe('1234567890')
        ->and($result->comment)->toBe('Test comment');

    Event::assertDispatched('igniter.reservation.confirmed');
});

it('returns false if not fully booked on given date and time', function(): void {
    Event::fake();

    $dateTime = Carbon::now();
    $location = Mockery::mock(Location::class)->makePartial();
    $location->shouldReceive('getReservationStayTime')->andReturn(60);
    $manager = new BookingManager;
    $manager->useLocation($location);

    $manager->isFullyBookedOn($dateTime);

    expect($manager->isFullyBookedOn($dateTime))->toBeFalse();

    Event::assertDispatched('igniter.reservation.isFullyBookedOn');
});

it('returns next bookable table for given date and time and number of guests', function(): void {
    $location = Mockery::mock(Location::class)->makePartial();
    $manager = Mockery::mock(BookingManager::class)->makePartial();
    $reservation = Mockery::mock(Reservation::class)->makePartial();

    $location->shouldReceive('getReservationStayTime')->andReturn(60);
    $reservation->shouldReceive('getNextBookableTable')->andReturn(collect(['table1', 'table2']));
    $manager->shouldReceive('getReservation')->andReturn($reservation);

    $dateTime = new DateTime;
    $manager->useLocation($location);
    $result = $manager->getNextBookableTable($dateTime, 4);

    expect($result)->toContain('table1', 'table2');
});
