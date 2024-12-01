<?php

namespace Igniter\Local\Tests\AutomationRules\Conditions;

use Igniter\Admin\Models\Status;
use Igniter\Reservation\AutomationRules\Conditions\ReservationStatusAttribute;
use Mockery;

it('returns true if status attribute condition is met', function() {
    $status = Mockery::mock(Status::class);
    $condition = Mockery::mock(ReservationStatusAttribute::class)->makePartial();
    $condition->shouldReceive('evalIsTrue')->with($status)->andReturn(true);
    $params = ['status' => $status];

    $result = $condition->isTrue($params);
    expect($result)->toBeTrue();
});

it('throws exception if status object is not found in parameters', function() {
    $params = [];

    $this->expectException(\Igniter\Automation\AutomationException::class);
    $this->expectExceptionMessage('Error evaluating the status attribute condition: the status object is not found in the condition parameters.');

    (new ReservationStatusAttribute())->isTrue($params);
});

it('returns false if status attribute condition is not met', function() {
    $status = Mockery::mock(Status::class);
    $condition = Mockery::mock(ReservationStatusAttribute::class)->makePartial();
    $condition->shouldReceive('evalIsTrue')->with($status)->andReturn(false);
    $params = ['status' => $status];

    expect((new ReservationStatusAttribute())->isTrue($params))->toBeFalse();
});
