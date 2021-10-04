<?php

namespace Igniter\Reservation\Listeners;

use Admin\Models\Reservations_model;
use Carbon\Carbon;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Location\Models\AbstractLocation;
use Igniter\Local\Facades\Location as LocationFacade;
use Illuminate\Contracts\Events\Dispatcher;

class MaxGuestSizePerTimeslotReached
{
    protected static $reservationsCache = [];

    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('igniter.workingSchedule.timeslotValid', __CLASS__.'@timeslotValid');

        $dispatcher->listen('igniter.reservation.beforeSaveReservation', __CLASS__.'@beforeSaveReservation');
    }

    public function timeslotValid($workingSchedule, $timeslot)
    {
        // Skip if the working schedule is not for opening
        if ($workingSchedule->getType() != AbstractLocation::OPENING)
            return;

        if ($this->execute($timeslot))
            return FALSE;
    }

    public function beforeSaveReservation($reservation, $data)
    {
        $dateTime = Carbon::createFromFormat('Y-m-d H:i', array_get($data, 'date').' '.array_get($data, 'time'));
        if ($this->execute($dateTime, array_get($data, 'guest')))
            throw new ApplicationException(lang('igniter.reservation::default.alert_fully_booked'));
    }

    protected function execute($timeslot, $guestNum = 0)
    {
        $locationModel = LocationFacade::current();
        if (!(bool)$locationModel->getOption('limit_reservations'))
            return;

        $totalGuestNumOnThisDay = $this->getGuestNum($timeslot);
        if (!$totalGuestNumOnThisDay)
            return;

        return ($totalGuestNumOnThisDay + $guestNum) >= (int)$locationModel->getOption('limit_guests_count', 20);
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
