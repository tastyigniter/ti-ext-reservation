<?php

namespace Tests\Requests;

use Igniter\Reservation\Http\Requests\ReservationRequest;

it('has required rule for inputs: location_id, first_name, last_name, reserve_date, reserve_time, guest_num', function() {
    expect('required')->toBeIn(array_get((new ReservationRequest)->rules(), 'location_id'))
        ->and('required')->toBeIn(array_get((new ReservationRequest)->rules(), 'first_name'))
        ->and('required')->toBeIn(array_get((new ReservationRequest)->rules(), 'last_name'))
        ->and('required')->toBeIn(array_get((new ReservationRequest)->rules(), 'reserve_date'))
        ->and('required')->toBeIn(array_get((new ReservationRequest)->rules(), 'reserve_time'))
        ->and('required')->toBeIn(array_get((new ReservationRequest)->rules(), 'guest_num'));
});

it('has sometimes rule for inputs: location_id and telephone', function() {
    expect('sometimes')->toBeIn(array_get((new ReservationRequest)->rules(), 'location_id'))
        ->and('sometimes')->toBeIn(array_get((new ReservationRequest)->rules(), 'telephone'));
});

it('has nullable rule for inputs: tables and comment', function() {
    expect('nullable')->toBeIn(array_get((new ReservationRequest)->rules(), 'tables'))
        ->and('nullable')->toBeIn(array_get((new ReservationRequest)->rules(), 'comment'));
});

it('has min:0 rule for duration input', function() {
    expect('min:0')->toBeIn(array_get((new ReservationRequest)->rules(), 'duration'));
});

it('has array rule for tables input', function() {
    expect('array')->toBeIn(array_get((new ReservationRequest)->rules(), 'tables'));
});

it('has between:1,48 rule for inputs: first_name and last_name', function() {
    expect('between:1,48')->toBeIn(array_get((new ReservationRequest)->rules(), 'first_name'))
        ->and('between:1,48')->toBeIn(array_get((new ReservationRequest)->rules(), 'last_name'));
});

it('has email:filter rule for inputs: email', function() {
    expect('email:filter')->toBeIn(array_get((new ReservationRequest)->rules(), 'email'));
});

it('has integer rule for inputs:
    location_id, guest_num and duration',
    function() {
        $rules = (new ReservationRequest)->rules();
        $inputNames = ['location_id', 'guest_num', 'duration'];
        $testExpectation = null;

        foreach ($inputNames as $key => $inputName) {
            if ($key == 0) {
                $testExpectation = expect('integer')->toBeIn(array_get($rules, $inputName));
            }
            $testExpectation = $testExpectation->and('integer')->toBeIn(array_get($rules, $inputName));
        }

    }
);

it('has string rule for inputs:
    first_name, last_name, telephone and comment',
    function() {
        $rules = (new ReservationRequest)->rules();
        $inputNames = ['first_name', 'last_name', 'telephone', 'comment'];
        $testExpectation = null;

        foreach ($inputNames as $key => $inputName) {
            if ($key == 0) {
                $testExpectation = expect('string')->toBeIn(array_get($rules, $inputName));
            }
            $testExpectation = $testExpectation->and('string')->toBeIn(array_get($rules, $inputName));
        }

    }
);

it('has max characters rule for inputs', function() {
    expect('between:1,48')->toBeIn(array_get((new ReservationRequest)->rules(), 'first_name'))
        ->and('between:1,48')->toBeIn(array_get((new ReservationRequest)->rules(), 'last_name'))
        ->and('max:96')->toBeIn(array_get((new ReservationRequest)->rules(), 'email'));
});

it('has date_format:Y-m-d rule for reserve_date input', function() {
    expect('date_format:Y-m-d')->toBeIn(array_get((new ReservationRequest)->rules(), 'reserve_date'));
});

it('has date_format:H:i rule for reserve_time input', function() {
    expect('date_format:H:i')->toBeIn(array_get((new ReservationRequest)->rules(), 'reserve_time'));
});
