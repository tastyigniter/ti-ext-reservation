<?php

namespace Igniter\Reservation\Tests\BulkActionWidget;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Classes\ToolbarButton;
use Igniter\Reservation\BulkActionWidgets\AssignTable;
use Igniter\Reservation\Models\Reservation;
use Mockery;

it('assigns tables to reservations without tables', function() {
    $controller = new class extends AdminController
    {
    };
    $reservation1 = Mockery::mock(Reservation::class)->makePartial();
    $reservation2 = Mockery::mock(Reservation::class)->makePartial();
    $reservation1->shouldReceive('assignTable')->andReturn(true);
    $reservation2->shouldReceive('assignTable')->andReturn(true);
    $reservation1->tables = collect();
    $reservation2->tables = collect();
    $reservation1->reservation_id = 1;
    $reservation2->reservation_id = 2;
    $reservation1->reservation_datetime = now();
    $reservation2->reservation_datetime = now()->addHour();

    $records = collect([$reservation1, $reservation2]);

    (new AssignTable($controller, new ToolbarButton('assign_table')))->handleAction([], $records);

    expect(flash()->messages()->first())->message->not->toBeNull()->level->toBe('success');
});

it('does not assign tables to reservations with existing tables', function() {
    $controller = new class extends AdminController
    {
    };
    $records = collect([$reservation = Mockery::mock(Reservation::class)->makePartial()]);
    $reservation->tables = collect([1]);
    $reservation->reservation_id = 1;
    $reservation->reservation_datetime = now();
    $reservation->shouldNotReceive('assignTable');

    (new AssignTable($controller, new ToolbarButton('assign_table')))->handleAction([], $records);

    expect(flash()->messages()->isEmpty())->toBeTrue();
});

it('shows warning if no tables can be assigned', function() {
    $controller = new class extends AdminController
    {
    };
    $records = collect([$reservation = Mockery::mock(Reservation::class)->makePartial()]);
    $reservation->tables = collect();
    $reservation->reservation_id = 1;
    $reservation->reservation_datetime = now();
    $reservation->shouldReceive('assignTable')->andReturnFalse();

    (new AssignTable($controller, new ToolbarButton('assign_table')))->handleAction([], $records);

    expect(flash()->messages()->first())
        ->message->not->toBeNull()
        ->level->toBe('warning');
});

it('assigns tables to reservations in correct order', function() {
    $controller = new class extends AdminController
    {
    };
    $reservation1 = Mockery::mock(Reservation::class)->makePartial();
    $reservation2 = Mockery::mock(Reservation::class)->makePartial();
    $reservation1->shouldReceive('assignTable')->andReturn(true);
    $reservation2->shouldReceive('assignTable')->andReturn(true);
    $reservation1->tables = collect();
    $reservation2->tables = collect();
    $reservation1->reservation_id = 2;
    $reservation2->reservation_id = 1;
    $reservation1->reservation_datetime = now()->addHour();
    $reservation2->reservation_datetime = now();
    $records = collect([$reservation1, $reservation2]);

    (new AssignTable($controller, new ToolbarButton('assign_table')))->handleAction([], $records);

    expect(flash()->messages()->first())->message->not->toBeNull()->level->toBe('success');
});
