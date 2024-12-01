<?php

namespace Igniter\Reservation\Tests;

use Igniter\Reservation\AutomationRules\Conditions\ReservationAttribute;
use Igniter\Reservation\AutomationRules\Conditions\ReservationStatusAttribute;
use Igniter\Reservation\BulkActionWidgets\AssignTable;
use Igniter\Reservation\Extension;
use Igniter\Reservation\FormWidgets\FloorPlanner;
use Igniter\Reservation\Http\Requests\BookingSettingsRequest;
use Igniter\Reservation\Models\DiningSection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Mockery;

beforeEach(function() {
    $this->extension = new Extension(app());
});

it('returns correct dining section class', function() {
    $this->extension->boot();

    $morphMap = Relation::morphMap();

    expect($morphMap['dining_sections'])->toBe(DiningSection::class);
});

it('binds reservation event correctly', function() {
    Event::shouldReceive('listen')->with('admin.statusHistory.beforeAddStatus', Mockery::any())->once();
    Event::shouldReceive('listen')->with('admin.statusHistory.added', Mockery::any())->once();
    Event::shouldReceive('listen')->with('admin.assignable.assigned', Mockery::any())->once();
    Event::shouldReceive('listen')->with('admin.form.extendFields', Mockery::any())->once();
    Event::shouldReceive('listen')->with('igniter.reservation.statusAdded', Mockery::any())->once();

    $this->extension->boot();
});

it('registers correct mail templates', function() {
    $mailTemplates = $this->extension->registerMailTemplates();

    expect($mailTemplates)->toHaveKey('igniter.reservation::mail.reservation')
        ->and($mailTemplates)->toHaveKey('igniter.reservation::mail.reservation_alert')
        ->and($mailTemplates)->toHaveKey('igniter.reservation::mail.reservation_update');
});

it('registers correct automation rules', function() {
    $automationRules = $this->extension->registerAutomationRules();

    expect($automationRules['events'])->toHaveKey('igniter.reservation.confirmed')
        ->and($automationRules['events'])->toHaveKey('igniter.reservation.statusAdded')
        ->and($automationRules['events'])->toHaveKey('igniter.reservation.assigned')
        ->and($automationRules['conditions'])->toContain(ReservationAttribute::class)
        ->and($automationRules['conditions'])->toContain(ReservationStatusAttribute::class);
});

it('registers correct permissions', function() {
    $permissions = $this->extension->registerPermissions();

    expect($permissions)->toHaveKey('Admin.Tables')
        ->and($permissions)->toHaveKey('Admin.Reservations')
        ->and($permissions)->toHaveKey('Admin.DeleteReservations')
        ->and($permissions)->toHaveKey('Admin.AssignReservations')
        ->and($permissions)->toHaveKey('Admin.AssignReservationTables');
});

it('registers correct navigation items', function() {
    $navigation = $this->extension->registerNavigation();

    expect($navigation)->toHaveKey('reservations')
        ->and($navigation['reservations']['href'])->toBe(admin_url('reservations'))
        ->and($navigation['restaurant']['child'])->toHaveKey('dining_areas')
        ->and($navigation['restaurant']['child']['dining_areas']['href'])->toBe(admin_url('dining_areas'));
});

it('registers correct form widgets', function() {
    $formWidgets = $this->extension->registerFormWidgets();

    expect($formWidgets)->toHaveKey(FloorPlanner::class)
        ->and($formWidgets[FloorPlanner::class]['code'])->toBe('floorplanner');
});

it('registers correct list action widgets', function() {
    $listActionWidgets = $this->extension->registerListActionWidgets();

    expect($listActionWidgets)->toHaveKey(AssignTable::class)
        ->and($listActionWidgets[AssignTable::class]['code'])->toBe('assign_table');
});

it('registers correct location settings', function() {
    $locationSettings = $this->extension->registerLocationSettings();

    expect($locationSettings)->toHaveKey('booking')
        ->and($locationSettings['booking']['form'])->toBe('igniter.reservation::/models/bookingsettings')
        ->and($locationSettings['booking']['request'])->toBe(BookingSettingsRequest::class);
});
