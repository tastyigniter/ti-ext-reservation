<?php

namespace Tests\Requests;

use Igniter\Reservation\Http\Requests\DiningTableRequest;

it('has required rule for inputs', function() {
    expect('required')->toBeIn(array_get((new DiningTableRequest)->rules(), 'name'))
        ->and('required')->toBeIn(array_get((new DiningTableRequest)->rules(), 'min_capacity'))
        ->and('required')->toBeIn(array_get((new DiningTableRequest)->rules(), 'max_capacity'))
        ->and('required')->toBeIn(array_get((new DiningTableRequest)->rules(), 'extra_capacity'))
        ->and('required')->toBeIn(array_get((new DiningTableRequest)->rules(), 'priority'))
        ->and('required')->toBeIn(array_get((new DiningTableRequest)->rules(), 'is_enabled'))
        ->and('required')->toBeIn(array_get((new DiningTableRequest)->rules(), 'locations'));
});

it('has rules for table_name input', function() {
    expect('between:2,255')->toBeIn(array_get((new DiningTableRequest)->rules(), 'table_name'));
});

it('has min character rule for min_capacity and max_capacity input', function() {
    expect('min:1')->toBeIn(array_get((new DiningTableRequest)->rules(), 'min_capacity'))
        ->and('min:1')->toBeIn(array_get((new DiningTableRequest)->rules(), 'max_capacity'));
});

it('has rules for max_capacity input', function() {
    expect('lte:max_capacity')->toBeIn(array_get((new DiningTableRequest)->rules(), 'min_capacity'))
        ->and('gte:min_capacity')->toBeIn(array_get((new DiningTableRequest)->rules(), 'max_capacity'));
});
