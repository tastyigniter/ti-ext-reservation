<?php

namespace Igniter\Reservation\Classes;

use Admin\Models\Reservations_model;
use Admin\Models\Statuses_model;
use Admin\Models\Tables_model;
use Auth;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Event;
use Igniter\Flame\Traits\Singleton;

class BookingManager
{
    use Singleton;

    /**
     * @var \Admin\Models\Customers_model
     */
    protected $customer;

    /**
     * @var \Admin\Models\Locations_model
     */
    protected $location;

    protected $availableTables;

    public function initialize()
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
        return Reservations_model::make($this->getRequiredAttributes());
    }

    public function getReservationByHash($hash, $customer = null)
    {
        $query = Reservations_model::whereHash($hash);

        if (!is_null($customer))
            $query->where('customer_id', $customer->getKey());

        return $query->first();
    }

    /**
     * @param \Carbon\Carbon $date
     * @param $interval
     * @return array|\DatePeriod|\DateTime[]
     * @throws \Exception
     */
    public function makeTimeSlots(Carbon $date, $interval)
    {
        if (!$this->location)
            return [];

        $start = $date->copy()->subMinutes($interval * 2);
        $end = $date->copy()->addMinutes($interval * 3);

        $dateInterval = new DateInterval('PT'.$interval.'M');
        $dateTimes = new DatePeriod($start, $dateInterval, $end);

        return $dateTimes;
    }

    /**
     * @param $reservation
     * @param $data
     */
    public function saveReservation($reservation, $data)
    {
        Event::fire('igniter.reservation.beforeSaveReservation', [$reservation, $data]);

        $reservation->customer_id = $this->customer ? $this->customer->getKey() : null;;
        $reservation->location_id = $this->location ? $this->location->getKey() : null;

        $reservation->guest_num = array_get($data, 'guest');
        $reservation->first_name = array_get($data, 'first_name', $reservation->first_name);
        $reservation->last_name = array_get($data, 'last_name', $reservation->last_name);
        $reservation->email = array_get($data, 'email', $reservation->email);
        $reservation->telephone = array_get($data, 'telephone', $reservation->telephone);
        $reservation->comment = array_get($data, 'comment');

        $dateTime = Carbon::createFromFormat('Y-m-d H:i', array_get($data, 'sdateTime'));
        $reservation->reserve_date = $dateTime->format('Y-m-d');
        $reservation->reserve_time = $dateTime->format('H:i:s');
        $reservation->duration = $this->location->getReservationStayTime();
        $reservation->save();

        $tables = $this->getBookableTables($dateTime, $reservation->guest_num);
        $reservation->addReservationTables($tables->pluck('table_id')->all());

        $status = Statuses_model::find(setting('default_reservation_status'));
        $reservation->addStatusHistory($status, ['notify' => FALSE]);

        Event::fire('igniter.reservation.confirmed', [$reservation]);
    }

    //
    //
    //

    /**
     * @param $dateTime
     * @return \Igniter\Flame\Location\WorkingSchedule
     */
    public function getSchedule()
    {
        return $this->location->newWorkingSchedule('opening');
    }

    public function hasAvailableTables($noOfGuests)
    {
        return $this->getAvailableTables($noOfGuests)->isNotEmpty();
    }

    public function isFullyBookedOn(\DateTime $dateTime, $noOfGuests)
    {
        return $this->getBookableTables($dateTime, $noOfGuests)->isEmpty();
    }

    /**
     * @param \DateTime $dateTime
     * @param int $noOfGuests
     * @return \Illuminate\Support\Collection|null
     */
    public function getBookableTables(\DateTime $dateTime, $noOfGuests)
    {
        $tables = $this->getAvailableTables($noOfGuests);

        $reserved = Reservations_model::findReservedTables(
            $this->location, $dateTime
        );

        $tables = $tables->diff($reserved)->sortBy('max_capacity');

        return $this->filterBookableTables($tables, $noOfGuests);
    }

    /**
     * @param int $noOfGuests
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getAvailableTables($noOfGuests)
    {
        if (!is_null($this->availableTables))
            return $this->availableTables;

        if ($this->location->has('tables')) {
            $query = $this->location->tables();
            $query->isEnabled()->whereBetweenCapacity($noOfGuests);
            $tables = $query->get();
        }
        else {
            $query = Tables_model::isEnabled();
            $query->whereBetweenCapacity($noOfGuests);
            $tables = $query->get();
        }

        return $this->availableTables = $tables;
    }

    protected function filterBookableTables($tables, int $noOfGuests)
    {
        $result = collect();
        $previousCapacity = 0;
        foreach ($tables as $table) {
            $previousCapacity += $table->max_capacity;
            $result->push($table);
            if ($previousCapacity >= $noOfGuests)
                break;
        }

        return $result;
    }

    protected function getRequiredAttributes()
    {
        $customer = Auth::getUser();

        return [
            'first_name' => $customer ? $customer->first_name : null,
            'last_name' => $customer ? $customer->last_name : null,
            'email' => $customer ? $customer->email : null,
            'telephone' => $customer ? $customer->telephone : null,
        ];
    }
}