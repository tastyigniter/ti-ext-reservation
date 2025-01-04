<?php

namespace Igniter\Reservation\Listeners;

use Carbon\Carbon;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Models\Location;
use Igniter\Reservation\Models\Reservation;

class MaxGuestSizePerTimeslotReached
{
    protected static $reservationsCache = [];

    public function handle(\DateTimeInterface $timeslot, int|string $guestNum)
    {
        /** @var Location $locationModel */
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

        return ($totalGuestNumOnThisDay + $guestNum) > $limitCount || $totalGuestNumOnThisDay >= $limitCount;
    }

    protected function getGuestNum($timeslot)
    {
        $dateTime = Carbon::parse($timeslot)->toDateTimeString();

        if (array_has(self::$reservationsCache, $dateTime)) {
            return self::$reservationsCache[$dateTime];
        }

        $startTime = Carbon::parse($timeslot)->subMinutes(2);
        $endTime = Carbon::parse($timeslot)->addMinutes(2);

        $guestNum = Reservation::query()
            ->where('location_id', LocationFacade::getId())
            ->where('status_id', '!=', setting('canceled_reservation_status'))
            ->whereBetweenReservationDateTime($startTime->toDateTimeString(), $endTime->toDateTimeString())
            ->sum('guest_num');

        return self::$reservationsCache[$dateTime] = (int)$guestNum;
    }

    public function clearInternalCache()
    {
        self::$reservationsCache = [];
    }
}
