<?php

namespace Igniter\Reservation\Tests\Models;

use Carbon\Carbon;
use Igniter\Admin\Models\Concerns\GeneratesHash;
use Igniter\Admin\Models\Status;
use Igniter\Admin\Traits\LogsStatusHistory;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Reservation\Models\DiningTable;
use Igniter\Reservation\Models\Reservation;
use Igniter\System\Traits\SendsMailTemplate;
use Igniter\User\Models\Concerns\Assignable;
use Mockery;

it('returns correct customer name', function() {
    $reservation = new Reservation([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($reservation->customer_name)->toBe('John Doe');
});

it('calculates correct reservation end time with duration', function() {
    $reservation = new Reservation([
        'reserve_date' => '2023-10-10',
        'reserve_time' => '12:00:00',
    ]);
    $reservation->duration = 120;

    expect($reservation->reservation_end_datetime->toDateTimeString())->toBe('2023-10-10 14:00:00');
});

it('calculates correct reservation end time without duration', function() {
    $reservation = new Reservation([
        'reserve_date' => '2023-10-10',
        'reserve_time' => '12:00:00',
    ]);

    expect($reservation->reservation_end_datetime->toDateTimeString())->toBe('2023-10-10 23:59:59');
});

it('returns correct table name when tables are assigned', function() {
    $table1 = Mockery::mock(DiningTable::class)->makePartial();
    $table2 = Mockery::mock(DiningTable::class)->makePartial();
    $table1->name = 'Table1';
    $table2->name = 'Table2';

    $reservation = new Reservation();
    $reservation->setRelation('tables', collect([$table1, $table2]));

    expect($reservation->table_name)->toBe('Table1, Table2');
});

it('returns empty table name when no tables are assigned', function() {
    $reservation = new Reservation();
    $reservation->setRelation('tables', collect());

    expect($reservation->table_name)->toBe('');
});

it('returns correct occasion attribute', function() {
    $reservation = new Reservation();
    $reservation->occasion_id = 1;

    expect($reservation->occasion)->toBe('birthday');
});

it('returns correct reservation dates', function() {
    $reservation = Mockery::mock(Reservation::class)->makePartial();
    $reservation->shouldReceive('pluckDates')->with('reserve_date')->andReturn(['2023-10-10', '2023-10-11']);

    expect($reservation->getReservationDates())->toBe(['2023-10-10', '2023-10-11']);
});

it('adds reservation tables correctly', function() {
    $reservation = Mockery::mock(Reservation::class)->makePartial();
    $reservation->exists = true;
    $reservation->shouldReceive('tables->sync')->with([1, 2, 3])->once();

    expect($reservation->addReservationTables([1, 2, 3]))->toBeTrue();
});

it('does not add reservation tables when reservation does not exist', function() {
    $reservation = Mockery::mock(Reservation::class)->makePartial();
    $reservation->shouldReceive('exists')->andReturn(false);

    expect($reservation->addReservationTables([1, 2, 3]))->toBeFalse();
});

it('applies filters to query builder', function() {
    $query = Reservation::query()->applyFilters([
        'status' => 'confirmed',
        'location' => 1,
        'dateTimeFilter' => '2023-10-10 12:00:00',
        'search' => 'John Doe',
    ]);

    expect($query->toSql())
        ->toContain('`status_id` in (?)')
        ->toContain('`location_id` in (?)')
        ->toContain('ADDTIME(reserve_date, reserve_time) between ? and ?')
        ->toContain('lower(first_name) like ?', 'lower(last_name) like ?');
});

it('returns correct event details', function() {
    $reservation = Mockery::mock(Reservation::class)->makePartial();
    $reservation->reservation_datetime = Carbon::parse('2023-10-10 12:00:00');
    $reservation->reservation_end_datetime = Carbon::parse('2023-10-10 14:00:00');
    $reservation->guest_num = 4;
    $reservation->first_name = 'John';
    $reservation->last_name = 'Doe';
    $reservation->email = 'john.doe@example.com';
    $reservation->telephone = '1234567890';
    $reservation->reserve_date = Carbon::parse('2023-10-10');
    $reservation->reserve_time = '12:00:00';
    $reservation->duration = 120;
    $reservation->status = new Status(['status_color' => 'red']);
    $reservation->tables = collect([new DiningTable(['name' => 'Table1'])]);

    $expectedDetails = [
        'id' => null,
        'title' => 'Table1 (4)',
        'start' => '2023-10-10T12:00:00+01:00',
        'end' => '2023-10-10T14:00:00+01:00',
        'allDay' => false,
        'color' => 'red',
        'location_name' => null,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'telephone' => '1234567890',
        'guest_num' => 4,
        'reserve_date' => '2023-10-10',
        'reserve_time' => '12:00:00',
        'reserve_end_time' => '14:00:00',
        'duration' => 120,
        'status' => ['status_color' => 'red'],
        'tables' => [
            [
                'priority' => 0,
                'extra_capacity' => 0,
                'name' => 'Table1',
            ],
        ],
    ];

    expect($reservation->getEventDetails())->toBe($expectedDetails);
});

it('configures reservation model correctly', function() {
    $reservation = new Reservation();

    expect(class_uses_recursive($reservation))
        ->toContain(Assignable::class)
        ->toContain(GeneratesHash::class)
        ->toContain(Locationable::class)
        ->toContain(LogsStatusHistory::class)
        ->toContain(Purgeable::class)
        ->toContain(SendsMailTemplate::class)
        ->and($reservation->getTable())->toBe('reservations')
        ->and($reservation->getKeyName())->toBe('reservation_id')
        ->and($reservation->timestamps)->toBeTrue()
        ->and($reservation->getDateFormat())->toBe('Y-m-d')
        ->and($reservation->getTimeFormat())->toBe('H:i')
        ->and($reservation->getGuarded())->toBe(['ip_address', 'user_agent', 'hash'])
        ->and($reservation->getCasts())->toHaveKeys([
            'location_id', 'table_id', 'guest_num',
            'occasion_id', 'assignee_id', 'reserve_time',
            'reserve_date', 'notify', 'duration', 'processed',
        ])
        ->and($reservation->relation['belongsTo']['customer'])->toBe(\Igniter\User\Models\Customer::class)
        ->and($reservation->relation['belongsTo']['location'])->toBe(\Igniter\Local\Models\Location::class)
        ->and($reservation->relation['belongsToMany']['tables'])->toBe([
            \Igniter\Reservation\Models\DiningTable::class, 'table' => 'reservation_tables',
        ])
        ->and($reservation->getAppends())->toContain('customer_name', 'duration', 'table_name', 'reservation_datetime', 'reservation_end_datetime')
        ->and($reservation->getPurgeableAttributes())->toBe(['tables']);
});
