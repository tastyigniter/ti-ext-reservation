<?php

namespace Igniter\Reservation\Classes;

use Carbon\Carbon;
use DateInterval;
use Igniter\Admin\Models\Status;
use Igniter\Local\Models\Location;
use Igniter\Reservation\Models\Concerns\LocationAction;
use Igniter\Reservation\Models\Reservation;
use Igniter\User\Facades\Auth;
use Igniter\User\Models\Customer;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Event;

class BookingManager
{
    protected Customer|Authenticatable|null $customer;

    protected Location|LocationAction|null $location;

    protected $availableTables;

    protected $fullyBookedCache = [];

    public function __construct()
    {
        $this->customer = Auth::customer();
    }

    public function useLocation($location)
    {
        $this->location = $location;
    }

    public function getReservation()
    {
        return $this->loadReservation();
    }

    public function loadReservation()
    {
        /** @var Reservation $reservation */
        $reservation = Reservation::make($this->getRequiredAttributes());

        $reservation->customer()->associate($this->customer);
        $reservation->location()->associate($this->location);

        return $reservation;
    }

    public function getReservationByHash($hash, $customer = null)
    {
        $query = Reservation::query()->whereHash($hash);

        if (!is_null($customer)) {
            $query->where('customer_id', $customer->getKey());
        }

        return $query->first();
    }

    /**
     * @param int $interval
     * @param int $lead
     * @return array|\Illuminate\Support\Collection
     * @throws \Exception
     */
    public function makeTimeSlots(Carbon $date, $interval = null, $lead = null)
    {
        if (!$this->location) {
            return [];
        }

        $interval = !is_null($interval)
            ? $interval : $this->location->getReservationInterval();

        $lead = !is_null($lead) ? $lead : $this->location->getReservationLeadTime();
        if ($this->location->getSettings('booking.include_start_time', 1)) {
            $lead = 0;
        }

        $dateInterval = new DateInterval('PT'.$interval.'M');
        $leadTime = new DateInterval('PT'.$lead.'M');

        return $this->getSchedule()
            ->generateTimeslot($date, $dateInterval, $leadTime)
            ->filter(function($dateTime, $timestamp) use ($date, $lead) {
                return $date->copy()
                    ->setTimeFromTimeString($dateTime->format('H:i'))
                    ->gte(Carbon::now()->addMinutes($lead));
            });
    }

    /**
     * @return \Igniter\Reservation\Models\Reservation
     */
    public function saveReservation($reservation, $data)
    {
        Event::dispatch('igniter.reservation.beforeSaveReservation', [$reservation, $data]);

        $reservation->guest_num = (int)array_get($data, 'guest', 1);
        $reservation->first_name = array_get($data, 'first_name', $reservation->first_name);
        $reservation->last_name = array_get($data, 'last_name', $reservation->last_name);
        $reservation->email = $this->customer->email ?? array_get($data, 'email', $reservation->email);
        $reservation->telephone = array_get($data, 'telephone', $reservation->telephone);
        $reservation->comment = array_get($data, 'comment');

        $dateTime = make_carbon(array_get($data, 'sdateTime'));
        $reservation->reserve_date = $dateTime->format('Y-m-d');
        $reservation->reserve_time = $dateTime->format('H:i:s');
        $reservation->duration = $this->location->getReservationStayTime();

        $reservation->save();

        $status = Status::find(setting('default_reservation_status'));
        $reservation->addStatusHistory($status, ['notify' => false]);

        Event::dispatch('igniter.reservation.confirmed', [$reservation]);

        return $reservation;
    }

    //
    //
    //

    /**
     * @param $dateTime
     * @return \Igniter\Local\Classes\WorkingSchedule
     */
    public function getSchedule($days = null)
    {
        if (is_null($days)) {
            $days = [
                $this->location->getMinReservationAdvanceTime(),
                $this->location->getMaxReservationAdvanceTime(),
            ];
        }

        return $this->location->newWorkingSchedule('opening', $days);
    }

    public function isFullyBookedOn(\DateTime $dateTime, $noOfGuests = null)
    {
        /** @var Carbon $dateTime */
        $index = $dateTime->timestamp.'-'.$noOfGuests;

        if (array_key_exists($index, $this->fullyBookedCache)) {
            return $this->fullyBookedCache[$index];
        }

        /** @var string|array|bool|null $isFullyBooked */
        $isFullyBooked = Event::dispatch('igniter.reservation.isFullyBookedOn', [$dateTime, $noOfGuests], true);
        if (!is_bool($isFullyBooked)) {
            $isFullyBooked = $this->getNextBookableTable($dateTime, $noOfGuests)->isEmpty();
        }

        return $this->fullyBookedCache[$index] = $isFullyBooked;
    }

    /**
     * @param int $noOfGuests
     * @return \Illuminate\Support\Collection|null
     */
    public function getNextBookableTable(\DateTime $dateTime, $noOfGuests)
    {
        $reservation = $this->getReservation();

        $reservation->reserve_date = $dateTime->format('Y-m-d');
        $reservation->reserve_time = $dateTime->format('H:i:s');
        $reservation->guest_num = $noOfGuests;
        $reservation->duration = $this->location->getReservationStayTime();

        return $reservation->getNextBookableTable();
    }

    protected function getRequiredAttributes()
    {
        return [
            'customer_id' => $this->customer->customer_id ?? null,
            'first_name' => $this->customer->first_name ?? null,
            'last_name' => $this->customer->last_name ?? null,
            'email' => $this->customer->email ?? null,
            'telephone' => $this->customer->telephone ?? null,
        ];
    }
}
