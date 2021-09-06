<?php

namespace Igniter\Reservation\Listeners;

use Admin\Models\Reservations_model;
use Carbon\Carbon;
use Igniter\Flame\Location\Models\AbstractLocation;
use Igniter\Flame\Traits\EventEmitter;
use Igniter\Local\Facades\Location as LocationFacade;
use Illuminate\Contracts\Events\Dispatcher;

class MaxGuestSizePerTimeslotReached
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

        $totalGuestNumOnThisDay = $this->getGuestNum($timeslot);
        if (!$totalGuestNumOnThisDay)
            return;

        if ($totalGuestNumOnThisDay >= (int)$locationModel->getOption('limit_guests_count', 20))
            return FALSE;
    }

    protected function getGuestNum($timeslot)
    {
        $date = Carbon::parse($timeslot)->toDateString();

        if (array_has(self::$reservationsCache, $date))
            return self::$reservationsCache[$date];

        $locationModel = LocationFacade::current();
        $startTime = Carbon::parse($timeslot);
        $endTime = Carbon::parse($timeslot)->addMinutes($locationModel->getReservationInterval())->subMinute();

        $guestNum = Reservations_model::where('location_id', LocationFacade::getId())
            ->whereBetweenReservationDateTime($startTime->toDateTimeString(), $endTime->toDateTimeString())
            ->sum('guest_num');

        return self::$reservationsCache[$date] = $guestNum;
    }
}
