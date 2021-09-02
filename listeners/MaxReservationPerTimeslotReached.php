<?php

namespace Igniter\Reservation\Listeners;

use Admin\Models\Reservations_model;
use Carbon\Carbon;
use Igniter\Flame\Location\Models\AbstractLocation;
use Igniter\Flame\Traits\EventEmitter;
use Igniter\Local\Facades\Location as LocationFacade;
use Illuminate\Contracts\Events\Dispatcher;

class MaxReservationPerTimeslotReached
{
    use EventEmitter;

    protected static $reservationsCache = [];

    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('igniter.workingSchedule.timeslotValid', __CLASS__.'@timeslotValid');
    }

    public function timeslotValid($workingSchedule, $timeslot)
    {
        $locationModel = LocationFacade::current();

        if (!(bool)$locationModel->getOption('limit_reservations'))
            return;

        // Skip if the working schedule is not for opening
        if ($workingSchedule->getType() != AbstractLocation::OPENING)
            return;

        $reservationsOnThisDay = $this->getReservations($timeslot);
        if ($reservationsOnThisDay->isEmpty())
            return;

        $startTime = Carbon::parse($timeslot);
        $endTime = Carbon::parse($timeslot)->addMinutes($locationModel->getReservationInterval())->subMinute();

        $reservationCount = $reservationsOnThisDay->filter(function ($time) use ($startTime, $endTime) {
            $reservationTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTime->format('Y-m-d').' '.$time);

            return $reservationTime->between($startTime, $endTime);
        })->count();

        if ($reservationCount >= (int)$locationModel->getOption('limit_reservations_count', 50))
            return FALSE;
    }

    protected function getReservations($timeslot)
    {
        $date = Carbon::parse($timeslot)->toDateString();

        if (array_has(self::$reservationsCache, $date))
            return self::$reservationsCache[$date];

        $result = Reservations_model::where('reserve_date', $date)
            ->where('location_id', LocationFacade::getId())
            ->whereIn('status_id', [setting('default_reservation_status', -1), setting('confirmed_reservation_status', -1)])
            ->select('reserve_time')
            ->pluck('reserve_time');

        return self::$reservationsCache[$date] = $result;
    }
}
