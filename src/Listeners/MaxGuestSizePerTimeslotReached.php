<?php

namespace Igniter\Reservation\Listeners;

use Carbon\Carbon;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Reservation\Models\Reservation;

class MaxGuestSizePerTimeslotReached
{
    protected static $reservationsCache = [];

    public function handle($timeslot, $guestNum)
    {
        $locationModel = LocationFacade::current();
        if (!(bool)$locationModel->getSettings('booking.limit_guests')) {
            return;
        }

        if (!$limitCount = (int)$locationModel->getSettings('booking.limit_guests_count', 20)) {
            return;
        }

        $totalGuestNumOnThisDay = $this->getGuestNum($timeslot);
        if (!$totalGuestNumOnThisDay) {
            return;
        }

        if (($totalGuestNumOnThisDay + $guestNum) > $limitCount) {
            return true;
        }
    }

    protected function getGuestNum($timeslot)
    {
        $date = Carbon::parse($timeslot)->toDateString();

        if (array_has(self::$reservationsCache, $date)) {
            return self::$reservationsCache[$date];
        }

        $startTime = Carbon::parse($timeslot)->subMinutes(2);
        $endTime = Carbon::parse($timeslot)->addMinutes(2);

        $guestNum = Reservation::where('location_id', LocationFacade::getId())
            ->where('status_id', '!=', setting('canceled_reservation_status'))
            ->whereBetweenReservationDateTime($startTime->toDateTimeString(), $endTime->toDateTimeString())
            ->sum('guest_num');

        return self::$reservationsCache[$date] = $guestNum;
    }
}
