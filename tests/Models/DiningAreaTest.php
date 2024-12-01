<?php

namespace Igniter\Reservation\Tests\Models;

use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\Reservation\Models\DiningArea;
use Igniter\Reservation\Models\DiningSection;
use Igniter\Reservation\Models\DiningTable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mockery;

it('returns correct dining table count', function() {
    $diningArea = Mockery::mock(DiningArea::class)->makePartial();
    $diningArea->shouldReceive('getAttribute')->with('available_tables')->andReturn(collect([1, 2, 3]));

    expect($diningArea->dining_table_count)->toBe(3);
});

it('duplicates dining area with tables', function() {
    $diningArea = Mockery::mock(DiningArea::class)->makePartial();
    $diningArea->name = 'Original';
    $diningArea->shouldReceive('replicate')->andReturnSelf();
    $diningArea->shouldReceive('save')->once();
    $diningArea->shouldReceive('getKey')->andReturn(2);

    $table = Mockery::mock(DiningTable::class)->makePartial();
    $table->is_combo = false;
    $table->shouldReceive('replicate')->andReturnSelf();
    $table->shouldReceive('save')->once();
    $table->shouldReceive('setAttribute')->with('dining_area_id', 2)->once();

    $diningArea->shouldReceive('getAttribute')->with('dining_tables')->andReturn(collect([$table]));

    $newDiningArea = $diningArea->duplicate();

    expect($newDiningArea->name)->toBe('Original (copy)');
});

it('creates combo dining table', function() {
    $diningArea = Mockery::mock(DiningArea::class)->makePartial();
    $comboTable = Mockery::mock(DiningTable::class)->makePartial();
    $diningArea->shouldReceive('dining_tables->create')->andReturnUsing(function($attributes) use ($comboTable) {
        $comboTable->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $comboTable->shouldReceive('fixBrokenTreeQuietly')->once();
        return $comboTable;
    });

    $table1 = Mockery::mock(DiningTable::class)->makePartial();
    $table1->shouldReceive('extendableGet')->with('parent')->andReturnNull();
    $table1->shouldReceive('getAttribute')->with('name')->andReturn('Table1');
    $table1->shouldReceive('getAttribute')->with('shape')->andReturn('square');
    $table1->shouldReceive('getAttribute')->with('dining_area_id')->andReturn(1);
    $table1->shouldReceive('getAttribute')->with('dining_section_id')->andReturn(1);
    $table1->shouldReceive('getAttribute')->with('min_capacity')->andReturn(2);
    $table1->shouldReceive('getAttribute')->with('max_capacity')->andReturn(4);
    $table1->shouldReceive('parent')->andReturn($parent1Relation = Mockery::mock(Relation::class));
    $parent1Relation->shouldReceive('getResults')->andReturnSelf();
    $parent1Relation->shouldReceive('associate')->with($comboTable)->andReturnSelf();
    $parent1Relation->shouldReceive('saveQuietly')->once();

    $table2 = Mockery::mock(DiningTable::class)->makePartial();
    $table2->shouldReceive('extendableGet')->with('parent')->andReturnNull();
    $table2->shouldReceive('getAttribute')->with('name')->andReturn('Table2');
    $table2->shouldReceive('getAttribute')->with('shape')->andReturn('square');
    $table2->shouldReceive('getAttribute')->with('dining_area_id')->andReturn(1);
    $table2->shouldReceive('getAttribute')->with('dining_section_id')->andReturn(1);
    $table2->shouldReceive('getAttribute')->with('min_capacity')->andReturn(2);
    $table2->shouldReceive('getAttribute')->with('max_capacity')->andReturn(4);
    $table2->shouldReceive('parent')->andReturn($parent2Relation = Mockery::mock(Relation::class));
    $parent2Relation->shouldReceive('getResults')->andReturnSelf();
    $parent2Relation->shouldReceive('associate')->with($comboTable)->andReturnSelf();
    $parent2Relation->shouldReceive('saveQuietly')->once();

    $tables = collect([$table1, $table2]);

    $diningArea->createCombo($tables);
});

it('configures dining area model correctly', function() {
    $diningArea = new DiningArea();

    expect(class_uses_recursive($diningArea))
        ->toContain(Locationable::class)
        ->and($diningArea->getTable())->toBe('dining_areas')
        ->and($diningArea->timestamps)->toBeTrue()
        ->and($diningArea->getCasts()['floor_plan'])->toBe('array')
        ->and($diningArea->getMorphClass())->toBe('dining_areas')
        ->and($diningArea->relation['hasMany']['dining_sections'])->toBe([DiningSection::class, 'foreignKey' => 'location_id', 'otherKey' => 'location_id'])
        ->and($diningArea->relation['hasMany']['dining_tables'])->toBe([DiningTable::class, 'delete' => true])
        ->and($diningArea->relation['hasMany']['dining_table_solos'])->toBe([DiningTable::class, 'scope' => 'whereIsNotCombo'])
        ->and($diningArea->relation['hasMany']['dining_table_combos'])->toBe([DiningTable::class, 'scope' => 'whereIsCombo'])
        ->and($diningArea->relation['hasMany']['available_tables'])->toBe([DiningTable::class, 'scope' => 'whereIsRoot'])
        ->and($diningArea->relation['belongsTo']['location'])->toBe([Location::class]);
});
