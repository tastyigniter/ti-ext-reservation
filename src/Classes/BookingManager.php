<?php

namespace Igniter\Reservation\Classes;

use Carbon\Carbon;
use DateInterval;
use Igniter\Admin\Models\Reservation;
use Igniter\Admin\Models\Status;
use Igniter\Admin\Models\Table;
use Igniter\Main\Facades\Auth;
use Illuminate\Support\Facades\Event;

class BookingManager
{
    /**
     * @var \Igniter\Main\Models\Customer
     */
    protected $customer;

    /**
     * @var \Igniter\Admin\Models\Location
     */
    protected $location;

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
        $reservation = Reservation::make($this->getRequiredAttributes());

        $reservation->customer = $this->customer;
        $reservation->location = $this->location;

        return $reservation;
    }

    public function getReservationByHash($hash, $customer = null)
    {
        $query = Reservation::whereHash($hash);

        if (!is_null($customer))
            $query->where('customer_id', $customer->getKey());

        return $query->first();
    }

    /**
     * @param \Carbon\Carbon $date
     * @param int $interval
     * @param int $lead
     * @return array|\Illuminate\Support\Collection
     * @throws \Exception
     */
    public function makeTimeSlots(Carbon $date, $interval = null, $lead = null)
    {
        if (!$this->location)
            return [];

        $interval = !is_null($interval)
            ? $interval : $this->location->getReservationInterval();

        $lead = !is_null($lead) ? $lead : $this->location->getReservationLeadTime();
        if ($this->location->getOption('reservation_include_start_time', 1))
            $lead = 0;

        $dateInterval = new DateInterval('PT'.$interval.'M');
        $leadTime = new DateInterval('PT'.$lead.'M');

        return $this->getSchedule()
            ->generateTimeslot($date, $dateInterval, $leadTime)
            ->filter(function ($dateTime, $timestamp) use ($date, $lead) {
                return $date->copy()
                    ->setTimeFromTimeString($dateTime->format('H:i'))
                    ->gte(Carbon::now()->addMinutes($lead));
            });
    }

    /**
     * @param $reservation
     * @param $data
     *
     * @return \Igniter\Admin\Models\Reservation
     */
    public function saveReservation($reservation, $data)
    {
        Event::fire('igniter.reservation.beforeSaveReservation', [$reservation, $data]);

        $reservation->customer_id = $this->customer ? $this->customer->getKey() : null;
        $reservation->location_id = $this->location ? $this->location->getKey() : null;

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

        Event::fire('igniter.reservation.confirmed', [$reservation]);

        return $reservation;
    }

    //
    //
    //

    /**
     * @param $dateTime
     * @return \Igniter\Flame\Location\WorkingSchedule
     */
    public function getSchedule($days = null)
    {
        if (is_null($days))
            $days = $this->location->getMaxReservationAdvanceTime();

        return $this->location->newWorkingSchedule('opening', $days);
    }

    public function isFullyBookedOn(\DateTime $dateTime, $noOfGuests)
    {
        $index = $dateTime->timestamp.'-'.$noOfGuests;

        if (array_key_exists($index, $this->fullyBookedCache))
            return $this->fullyBookedCache[$index];

        $isFullyBooked = Event::fire('igniter.reservation.isFullyBookedOn', [$dateTime, $noOfGuests], true);
        if (!is_bool($isFullyBooked))
            $isFullyBooked = $this->getNextBookableTable($dateTime, $noOfGuests)->isEmpty();

        return $this->fullyBookedCache[$index] = $isFullyBooked;
    }

    /**
     * @param \DateTime $dateTime
     * @param int $noOfGuests
     * @return \Illuminate\Support\Collection|null
     */
    public function getNextBookableTable(\DateTime $dateTime, $noOfGuests)
    {
        $reservation = $this->getReservation();

        $reservation->reserve_date = $dateTime->format('Y-m-d');
        $reservation->reserve_time = $dateTime->format('H:i:s');
        $reservation->guest_num = $noOfGuests;

        return $reservation->getNextBookableTable();
    }

    protected function getRequiredAttributes()
    {
        $customer = Auth::getUser();

        return [
            'customer_id' => $customer->customer_id ?? null,
            'first_name' => $customer->first_name ?? null,
            'last_name' => $customer->last_name ?? null,
            'email' => $customer->email ?? null,
            'telephone' => $customer->telephone ?? null,
        ];
    }
}
