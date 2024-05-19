<?php

namespace Igniter\Reservation\Models\Scopes;

use Igniter\Flame\Database\Scope;
use Illuminate\Database\Eloquent\Builder;

class DiningTableScope extends Scope
{
    public function addReservable()
    {
        return function(Builder $builder, $options) {
            $builder->whereIsReservable();

            if ($dateTime = array_get($options, 'dateTime')) {
                $builder->whereIsAvailableOn($dateTime, array_get($options, 'duration', 15));
            }

            if ($date = array_get($options, 'date')) {
                $builder->whereIsAvailableForDate($date);
            }

            if ($locationId = array_get($options, 'locationId')) {
                $builder->whereIsAvailableAt($locationId);
            }

            if ($guestNum = array_get($options, 'guestNum')) {
                $builder->whereCanAccommodate($guestNum);
            }

            $builder
                ->orderBy('dining_sections.priority', 'desc')
                ->orderBy('dining_tables.priority', 'desc');

            $builder->getModel()->fireEvent('model.extendDiningTableReservableQuery', [$builder]);

            return $builder;
        };
    }

    public function addWhereIsReservable()
    {
        return function(Builder $builder) {
            return $builder
                ->whereIsRoot()
                ->where('dining_tables.is_enabled', 1)
                ->leftJoin('dining_sections', function($join) {
                    $join->on('dining_sections.id', '=', 'dining_tables.dining_section_id')
                        ->where('dining_sections.is_enabled', 1);
                });
        };
    }

    public function addWhereIsCombo()
    {
        return function(Builder $builder) {
            return $builder->where('is_combo', 1);
        };
    }

    public function addWhereIsNotCombo()
    {
        return function(Builder $builder) {
            return $builder->where('is_combo', '!=', 1);
        };
    }

    public function addWhereIsAvailableAt()
    {
        return function(Builder $builder, $locationId) {
            return $builder->join('dining_areas', function($join) use ($locationId) {
                $join->on('dining_areas.id', '=', 'dining_tables.dining_area_id')
                    ->where('dining_areas.location_id', $locationId);
            });
        };
    }

    public function addWhereIsAvailableForDate()
    {
        return function(Builder $builder, $date) {
            return $builder->whereDoesntHave('reservations', function($builder) use ($date) {
                $builder->where('reserve_date', $date)
                    ->whereNotIn('status_id', [0, setting('canceled_reservation_status')]);
            });
        };
    }

    public function addWhereIsAvailableOn()
    {
        return function(Builder $builder, $dateTime, $duration = 15) {
            if (is_string($dateTime)) {
                $dateTime = make_carbon($dateTime);
            }

            return $builder->whereDoesntHave('reservations', function($builder) use ($dateTime, $duration) {
                $builder
                    ->where(function($builder) use ($dateTime) {
                        $builder->whereBetweenStayTime($dateTime->clone()->addMinute());
                    })
                    ->orWhere(function($builder) use ($dateTime, $duration) {
                        $builder->whereBetweenStayTime($dateTime->clone()->addMinutes($duration - 1));
                    })
                    ->whereNotIn('status_id', [0, setting('canceled_reservation_status')]);
            });
        };
    }

    public function addWhereCanAccommodate()
    {
        return function(Builder $builder, $guestNumber) {
            return $builder
                ->where('min_capacity', '<=', $guestNumber)
                ->where('max_capacity', '>=', $guestNumber);
        };
    }

    public function addWhereHasReservationLocation()
    {
        return function(Builder $builder, $model) {
            return $builder->whereHasLocation($model->location_id);
        };
    }
}
