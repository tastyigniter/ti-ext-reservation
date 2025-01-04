<?php

namespace Igniter\Reservation\Tests\Models;

use Carbon\Carbon;
use Igniter\Admin\Models\Concerns\GeneratesHash;
use Igniter\Admin\Models\Status;
use Igniter\Admin\Traits\LogsStatusHistory;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\Reservation\Events\ReservationCanceledEvent;
use Igniter\Reservation\Models\DiningArea;
use Igniter\Reservation\Models\DiningSection;
use Igniter\Reservation\Models\DiningTable;
use Igniter\Reservation\Models\Reservation;
use Igniter\System\Traits\SendsMailTemplate;
use Igniter\User\Models\Concerns\Assignable;
use Igniter\User\Models\User;
use Igniter\User\Models\UserGroup;
use Illuminate\Support\Facades\Event;
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

it('returns null reservation end time when missing date time', function() {
    $reservation = new Reservation([
        'reserve_date' => null,
        'reserve_time' => null,
    ]);

    expect($reservation->reservation_end_datetime)->toBeNull();
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

it('sets location stay time as default duration when null', function() {
    $location = Location::factory()->create();
    $location->settings()->create([
        'item' => 'booking',
        'data' => ['stay_time' => 120],
    ]);
    $reservation = Reservation::factory()->create([
        'location_id' => $location->getKey(),
    ]);

    $reservation->duration = null;

    expect($reservation->duration)->toBe(120);
});

it('returns correct occasion attribute', function() {
    $reservation = new Reservation();
    $reservation->occasion_id = 1;

    expect($reservation->occasion)->toBe('birthday');
});

it('returns true if reservation is completed', function() {
    $reservation = Reservation::factory()->create();
    $reservation->addStatusHistory(setting('confirmed_reservation_status'));

    $result = $reservation->isCompleted();

    expect($result)->toBeTrue();
});

it('returns true if reservation is canceled', function() {
    $reservation = Reservation::factory()->create();
    $reservation->addStatusHistory(setting('canceled_reservation_status'));

    $result = $reservation->isCanceled();

    expect($result)->toBeTrue();
});

it('returns true if reservation is cancelable', function() {
    $location = Location::factory()->create();
    $location->settings()->create([
        'item' => 'booking',
        'data' => ['cancellation_timeout' => 60],
    ]);
    $reservation = Reservation::factory()->create([
        'location_id' => $location->getKey(),
        'reserve_date' => now()->addDay(),
        'reserve_time' => now()->addDay()->toTimeString(),
    ]);

    $result = $reservation->isCancelable();

    expect($result)->toBeTrue();
});

it('returns false if reservation is not cancelable due to timeout', function() {
    $location = Location::factory()->create();
    $location->settings()->create([
        'item' => 'booking',
        'data' => ['cancellation_timeout' => 0],
    ]);
    $reservation = Reservation::factory()->create([
        'location_id' => $location->getKey(),
        'reserve_date' => now()->addMinutes(30),
        'reserve_time' => now()->addMinutes(30)->toTimeString(),
    ]);

    $result = $reservation->isCancelable();

    expect($result)->toBeFalse();
});

it('returns false if reservation is not cancelable due to past date', function() {
    $location = Location::factory()->create();
    $location->settings()->create([
        'item' => 'booking',
        'data' => ['cancellation_timeout' => 60],
    ]);
    $reservation = Reservation::factory()->create([
        'location_id' => $location->getKey(),
        'reserve_date' => now()->subDay(),
        'reserve_time' => now()->subDay()->toTimeString(),
    ]);

    $result = $reservation->isCancelable();

    expect($result)->toBeFalse();
});

it('marks reservation as canceled and dispatches event', function() {
    Event::fake();
    $reservation = Reservation::factory()->create(['status_id' => 1]);

    $result = $reservation->markAsCanceled();

    expect($result)->toBeTrue()
        ->and($reservation->fresh()->status_id)->toBe((int)setting('canceled_reservation_status'));

    Event::assertDispatched(ReservationCanceledEvent::class);
});

it('finds reserved tables for a given location and datetime', function() {
    $location = Location::factory()->create();
    $diningArea = DiningArea::factory()->create(['location_id' => $location->getKey()]);
    $table = DiningTable::factory()->for($diningArea, 'dining_area')->create();
    $reservation = Reservation::factory()->create([
        'location_id' => $location->getKey(),
        'reserve_date' => now()->toDateString(),
        'reserve_time' => now()->toTimeString(),
        'duration' => 120,
        'status_id' => setting('confirmed_reservation_status'),
    ]);
    $reservation->tables()->attach($table);

    $result = Reservation::findReservedTables($location->getKey(), now()->toDateTimeString());

    expect($result->keys()->all())->toContain($table->getKey());
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

it('returns false when no available table to assign', function() {
    $location = Location::factory()->create();
    $location->settings()->create([
        'item' => 'booking',
        'data' => ['auto_allocate_table' => 0],
    ]);
    $reservation = Reservation::factory()->create([
        'location_id' => $location->getKey(),
        'reserve_date' => now()->toDateString(),
        'reserve_time' => now()->toTimeString(),
        'guest_num' => 50,
        'duration' => 120,
        'status_id' => 1,
    ]);

    $result = $reservation->assignTable();

    expect($result)->toBeFalse();
});

it('returns the next bookable table when available', function() {
    $location = Location::factory()->create();
    $location->settings()->create([
        'item' => 'booking',
        'data' => ['auto_allocate_table' => 1],
    ]);
    $diningArea = DiningArea::factory()->for($location, 'location')->create();
    $attributes = [
        'min_capacity' => 40,
        'max_capacity' => 60,
        'priority' => 1,
    ];
    $diningTable1 = DiningTable::factory()->for($diningArea, 'dining_area')->create($attributes);
    $diningTable2 = DiningTable::factory()->for($diningArea, 'dining_area')->create($attributes);
    $reservation = Reservation::factory()->create([
        'location_id' => $location->getKey(),
        'reserve_date' => now()->toDateString(),
        'reserve_time' => now()->toTimeString(),
        'guest_num' => 50,
        'duration' => 120,
        'status_id' => 1,
    ]);

    $result = $reservation->getNextBookableTable();

    expect($result->first())->toBeInstanceOf(DiningTable::class)
        ->and(collect([$diningTable1, $diningTable2])->pluck('id')->all())->toContain($result->first()->getKey());
});

it('returns the next bookable sectioned table when available', function() {
    $location = Location::factory()->create();
    $location->settings()->create([
        'item' => 'booking',
        'data' => ['auto_allocate_table' => 1],
    ]);
    $diningArea = DiningArea::factory()->for($location, 'location')->create();
    $diningSection = DiningSection::factory()->for($location, 'location')->create();
    $attributes = [
        'min_capacity' => 40,
        'max_capacity' => 60,
        'priority' => 1,
    ];
    $diningTable1 = DiningTable::factory()->for($diningArea, 'dining_area')->create($attributes);
    $diningTable2 = DiningTable::factory()->for($diningArea, 'dining_area')->create($attributes);
    $diningTable1->dining_section()->associate($diningSection)->save();
    $diningTable2->dining_section()->associate($diningSection)->save();
    $reservation = Reservation::factory()->create([
        'location_id' => $location->getKey(),
        'reserve_date' => now()->toDateString(),
        'reserve_time' => now()->toTimeString(),
        'guest_num' => 50,
        'duration' => 120,
        'status_id' => 1,
    ]);

    $result = $reservation->getNextBookableTable();

    expect($result->first())->toBeInstanceOf(DiningTable::class)
        ->and(collect([$diningTable1, $diningTable2])->pluck('id')->all())->toContain($result->first()->getKey());
});

it('returns empty dining tables if location is not set', function() {
    $reservation = Reservation::factory()->create(['location_id' => '']);

    $result = $reservation->getDiningTableOptions();

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('returns dining table options for the given location', function() {
    $location = Location::factory()->create();
    $location = Location::factory()->create();
    $diningArea = DiningArea::factory()->create(['location_id' => $location->getKey()]);
    $diningTables = DiningTable::factory()->count(2)->for($diningArea, 'dining_area')->create();
    $reservation = Reservation::factory()->for($location, 'location')->create();

    $result = $reservation->getDiningTableOptions()->all();

    expect($result)->toBeArray()
        ->and($result)->toContain(...$diningTables->pluck('name')->all());
});

it('gets mail recipients correctly for customer type', function() {
    $reservation = Reservation::factory()->create([
        'email' => 'customer@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    setting()->set(['reservation_email' => ['customer']]);

    $recipients = $reservation->mailGetRecipients('customer');

    expect($recipients)->toBe([[$reservation->email, $reservation->customer_name]]);
});

it('gets mail recipients correctly for location type', function() {
    $location = Location::factory()->create();
    $reservation = Reservation::factory()->for($location)->create();

    setting()->set(['reservation_email' => ['location']]);

    $recipients = $reservation->mailGetRecipients('location');

    expect($recipients)->toBe([[$location->location_email, $location->location_name]]);
});

it('gets mail recipients correctly for admin type', function() {
    $reservation = Reservation::factory()->create();

    setting()->set(['reservation_email' => ['admin']]);

    $recipients = $reservation->mailGetRecipients('admin');

    expect($recipients)->toBe([[setting('site_email'), setting('site_name')]]);
});

it('returns empty array when type is not in reservation email settings', function() {
    $reservation = Reservation::factory()->create();

    setting()->set(['reservation_email' => ['admin']]);

    $recipients = $reservation->mailGetReplyTo('admin');

    expect($recipients)->toBeArray()
        ->and($recipients)->toContain($reservation->email)
        ->and($recipients)->toContain($reservation->customer_name);
});

it('returns correct mail data for reservation', function() {
    $location = Location::factory()->create();
    $reservation = Reservation::factory()->create([
        'location_id' => $location->getKey(),
        'reserve_date' => now()->toDateString(),
        'reserve_time' => now()->toTimeString(),
        'guest_num' => 4,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'telephone' => '1234567890',
        'comment' => 'Test comment',
    ]);

    $data = $reservation->mailGetData();

    expect($data['reservation_number'])->toBe($reservation->reservation_id)
        ->and($data['reservation_time'])->toBe(Carbon::createFromTimeString($reservation->reserve_time)->isoFormat(lang('system::lang.moment.time_format')))
        ->and($data['reservation_date'])->toBe($reservation->reserve_date->isoFormat(lang('system::lang.moment.date_format_long')))
        ->and($data['reservation_guest_no'])->toBe($reservation->guest_num)
        ->and($data['first_name'])->toBe($reservation->first_name)
        ->and($data['last_name'])->toBe($reservation->last_name)
        ->and($data['email'])->toBe($reservation->email)
        ->and($data['telephone'])->toBe($reservation->telephone)
        ->and($data['reservation_comment'])->toBe($reservation->comment)
        ->and($data['location_name'])->toBe($location->location_name)
        ->and($data['location_email'])->toBe($location->location_email)
        ->and($data['location_telephone'])->toBe($location->location_telephone);
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
    $location = Location::factory()->create();
    $reservation = Reservation::factory()->for($location, 'location')->create([
        'guest_num' => 4,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'telephone' => '1234567890',
        'reserve_date' => '2023-10-10',
        'reserve_time' => '12:00:00',
        'duration' => 120,
    ]);

    $status = Status::factory()->create(['status_color' => 'red']);
    $reservation->status()->associate($status)->save();
    $reservation->tables()->saveMany([
        $diningTable = DiningTable::factory()->create(['name' => 'Table1']),
    ]);

    $expectedDetails = [
        'id' => $reservation->getKey(),
        'title' => 'Table1 (4)',
        'start' => '2023-10-10T12:00:00+01:00',
        'end' => '2023-10-10T14:00:00+01:00',
        'allDay' => false,
        'color' => 'red',
        'location_name' => $location->location_name,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'telephone' => '1234567890',
        'guest_num' => 4,
        'reserve_date' => '2023-10-10',
        'reserve_time' => '12:00:00',
        'reserve_end_time' => '14:00:00',
        'duration' => 120,
        'status' => [
            'status_name' => $status->status_name,
            'status_for' => $status->status_for,
            'status_color' => 'red',
            'status_comment' => $status->status_comment,
            'notify_customer' => $status->notify_customer,
            'updated_at' => $status->updated_at->toJson(),
            'created_at' => $status->created_at->toJson(),
            'status_id' => $status->getKey(),
        ],
        'tables' => [
            [
                'id' => $diningTable->getKey(),
                'dining_area_id' => 0,
                'dining_section_id' => null,
                'parent_id' => null,
                'name' => 'Table1',
                'shape' => null,
                'min_capacity' => $diningTable->min_capacity,
                'max_capacity' => $diningTable->max_capacity,
                'extra_capacity' => 0,
                'is_combo' => false,
                'is_enabled' => $diningTable->is_enabled,
                'nest_left' => 29,
                'nest_right' => 30,
                'priority' => $diningTable->priority,
                'seat_layout' => null,
                'created_at' => $diningTable->created_at->toJson(),
                'updated_at' => $diningTable->updated_at->toJson(),
                'pivot' => [
                    'reservation_id' => $reservation->getKey(),
                    'dining_table_id' => $diningTable->getKey(),
                ],
            ],
        ],
    ];

    expect($reservation->getEventDetails())->toBe($expectedDetails);
});

it('assigns staff to reservation', function() {
    Event::fake(['igniter.reservation.assigned']);
    $assigneeGroup = UserGroup::factory()->create();
    $assignee = User::factory()->create();
    $user = User::factory()->create();
    $reservation = Reservation::factory()->create();
    $reservation->assignee_group()->associate($assigneeGroup)->save();

    $assignableLog = $reservation->assignTo($assignee, $user);

    expect($assignableLog->assignable_id)->toBe($reservation->getKey())
        ->and($assignableLog->assignee_id)->toBe($assignee->getKey())
        ->and($assignableLog->user_id)->toBe($user->getKey());

    Event::assertDispatched('igniter.reservation.assigned');
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
