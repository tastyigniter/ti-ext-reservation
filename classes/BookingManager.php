<?php

namespace Igniter\Reservation\Classes;

use Admin\Models\Reservations_model;
use Admin\Models\Statuses_model;
use Admin\Models\Tables_model;
use Carbon\Carbon;
use DateInterval;
use Igniter\Flame\Traits\Singleton;
use Illuminate\Support\Facades\Event;
use Main\Facades\Auth;

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

    protected $fullyBookedCache = [];

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

        $lead = !is_null($lead)
            ? $lead : $this->location->getReservationLeadTime();

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
     * @return \Admin\Models\Reservations_model
     */
    public function saveReservation($reservation, $data)
    {
        Event::fire('igniter.reservation.beforeSaveReservation', [$reservation, $data]);

        $reservation->customer_id = $this->customer ? $this->customer->getKey() : null;
        $reservation->location_id = $this->location ? $this->location->getKey() : null;

        $reservation->guest_num = (int)array_get($data, 'guest', 1);
        $reservation->first_name = array_get($data, 'first_name', $reservation->first_name);
        $reservation->last_name = array_get($data, 'last_name', $reservation->last_name);
        $reservation->email = array_get($data, 'email', $reservation->email);
        $reservation->telephone = array_get($data, 'telephone', $reservation->telephone);
        $reservation->comment = array_get($data, 'comment');

        $dateTime = Carbon::createFromFormat('Y-m-d H:i', array_get($data, 'date').' '.array_get($data, 'time'));
        $reservation->reserve_date = $dateTime->format('Y-m-d');
        $reservation->reserve_time = $dateTime->format('H:i:s');
        $reservation->duration = $this->location->getReservationStayTime();

        if ((bool)$this->location->getOption('auto_allocate_table', 1)) {
            $tables = $this->getNextBookableTable($dateTime, $reservation->guest_num);
            $reservation->tables = $tables->pluck('table_id')->all();
        }

        $reservation->save();

        $status = Statuses_model::find(setting('default_reservation_status'));
        $reservation->addStatusHistory($status, ['notify' => FALSE]);

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

        $isFullyBooked = Event::fire('igniter.reservation.isFullyBookedOn', [$dateTime, $noOfGuests], TRUE);
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
        $tables = $this->getAvailableTables();

        $reserved = Reservations_model::findReservedTables(
            $this->location, $dateTime
        );

        $tables = $tables->diff($reserved)->sortBy('priority');

        return $this->filterNextBookableTable($tables, $noOfGuests);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getAvailableTables()
    {
        if (!is_null($this->availableTables))
            return $this->availableTables;

        $query = Tables_model::isEnabled()
            ->whereHasLocation($this->location->getKey());

        $tables = $query->get();

        return $this->availableTables = $tables;
    }

    protected function filterNextBookableTable($tables, int $noOfGuests)
    {
        $result = collect();
        $unseatedGuests = $noOfGuests;
        foreach ($tables as $table) {
            if ($table->min_capacity <= $noOfGuests && $table->max_capacity >= $noOfGuests)
                return collect([$table]);

            if ($table->is_joinable && $unseatedGuests >= $table->min_capacity) {
                $result->push($table);
                $unseatedGuests -= $table->max_capacity;
                if ($unseatedGuests <= 0)
                    break;
            }
        }

        return $unseatedGuests > 0 ? collect() : $result;
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
