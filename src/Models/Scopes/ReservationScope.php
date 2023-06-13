<?php

namespace Igniter\Reservation\Models\Scopes;

use Carbon\Carbon;
use Igniter\Flame\Database\Scope;
use Illuminate\Database\Eloquent\Builder;

class ReservationScope extends Scope
{
    public function addApplyDateTimeFilter()
    {
        return function (Builder $builder, $range) {
            return $builder->whereBetweenReservationDateTime(
                Carbon::parse(array_get($range, 'startAt', false))->format('Y-m-d H:i:s'),
                Carbon::parse(array_get($range, 'endAt', false))->format('Y-m-d H:i:s')
            );
        };
    }

    public function addWhereBetweenReservationDateTime()
    {
        return function (Builder $builder, $start, $end) {
            return $builder->whereRaw('ADDTIME(reserve_date, reserve_time) between ? and ?', [$start, $end]);
        };
    }

    public function addWhereBetweenDate()
    {
        return function (Builder $builder, $dateTime) {
            return $this->whereBetweenStayTime($dateTime);
        };
    }

    public function addWhereBetweenStayTime()
    {
        return function (Builder $builder, $dateTime) {
            return $builder
                ->whereRaw(
                    '? between DATE_SUB(ADDTIME(reserve_date, reserve_time), INTERVAL 2 MINUTE)'.
                    ' and DATE_ADD(ADDTIME(reserve_date, reserve_time), INTERVAL duration MINUTE)',
                    [$dateTime]
                );
        };
    }

    public function addWhereNotBetweenStayTime()
    {
        return function (Builder $builder, $dateTime) {
            return $builder->whereRaw(
                '? not between DATE_SUB(ADDTIME(reserve_date, reserve_time), INTERVAL (duration - 2) MINUTE)'.
                ' and DATE_ADD(ADDTIME(reserve_date, reserve_time), INTERVAL duration MINUTE)',
                [$dateTime]
            );
        };
    }

    public function addWhereHasDiningArea()
    {
        return function (Builder $builder, $diningAreaId) {
            return $builder->whereHas('tables', function ($q) use ($diningAreaId) {
                $q->where('dining_tables.dining_area_id', $diningAreaId);
            })->orDoesntHave('tables');
        };
    }
}
