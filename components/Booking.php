<?php

namespace Igniter\Reservation\Components;

use Admin\Models\Locations_model;
use Admin\Models\Reservations_model;
use Admin\Models\Tables_model;
use Admin\Traits\ValidatesForm;
use ApplicationException;
use Auth;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Location;
use Main\Traits\HasPageOptions;
use Redirect;
use Request;
use System\Classes\BaseComponent;

class Booking extends BaseComponent
{
    use ValidatesForm;
    use HasPageOptions;

    public $uniqueHash;

    /**
     * @var Locations_model
     */
    public $location;

    public $reservation;

    public $dateFormat;

    public $timeFormat;

    public $pickerStep;

    /**
     * @var \Igniter\Flame\Location\WorkingSchedule
     */
    protected $schedule;

    protected $availableTablesCache;

    protected $existingReservationsCache;

    public function defineProperties()
    {
        return [
            'mode' => [
                'label' => 'Enable or disable booking',
                'type' => 'switch',
                'default' => TRUE,
            ],
            'maxGuestSize' => [
                'label' => 'The maximum guest size',
                'type' => 'number',
                'default' => 20,
            ],
            'timePickerInterval' => [
                'label' => 'The interval to use for the time picker',
                'type' => 'number',
                'default' => 30,
            ],
            'timeSlotsInterval' => [
                'label' => 'The interval to use for the time slots',
                'type' => 'number',
                'default' => 15,
            ],
            'dateFormat' => [
                'label' => 'Date format to use for the date picker',
                'type' => 'text',
                'default' => 'M d, yyyy',
            ],
            'timeFormat' => [
                'label' => 'Time format to use for the time dropdown',
                'type' => 'text',
                'default' => 'h:i a',
            ],
            'dateTimeFormat' => [
                'label' => 'Date time format to use for the book summary',
                'type' => 'text',
                'default' => 'l, F j, Y \\a\\t h:i a',
            ],
            'showLocationThumb' => [
                'label' => 'Show Location Image Thumbnail',
                'type' => 'switch',
            ],
            'locationThumbWidth' => [
                'label' => 'Height',
                'type' => 'number',
                'default' => 95,
                'trigger' => [
                    'action' => 'show',
                    'field' => 'showLocationThumb',
                    'condition' => 'checked',
                ],
            ],
            'locationThumbHeight' => [
                'label' => 'Width',
                'type' => 'number',
                'default' => 80,
                'trigger' => [
                    'action' => 'show',
                    'field' => 'showLocationThumb',
                    'condition' => 'checked',
                ],
            ],
            'bookingPage' => [
                'label' => 'Booking Page',
                'type' => 'select',
                'default' => 'reservation/reservation',
                'options' => [static::class, 'getPageOptions'],
            ],
            'successPage' => [
                'label' => 'Page to redirect to when checkout is successful',
                'type' => 'select',
                'default' => 'reservation/success',
                'options' => [static::class, 'getPageOptions'],
            ],
        ];
    }

    public function initialize()
    {
        $this->location = $this->getLocation();
        $this->processPickerForm();
    }

    public function onRun()
    {
        $this->page['reservation'] = $this->getReservation();

        $this->loadAssets();
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->uniqueHash = uniqid('booking');
        $this->page['bookingDateFormat'] = $this->dateFormat = $this->property('dateFormat');
        $this->page['bookingTimeFormat'] = $this->timeFormat = $this->property('timeFormat');
        $this->page['bookingDateTimeFormat'] = $this->property('dateTimeFormat');

        $this->page['bookingLocation'] = $this->location;
        $this->page['bookingEventHandler'] = $this->getEventHandler('onComplete');

        $this->page['showLocationThumb'] = $this->property('showLocationThumb');
        $this->page['locationThumbWidth'] = $this->property('locationThumbWidth');
        $this->page['locationThumbHeight'] = $this->property('locationThumbHeight');

        $this->page['customer'] = Auth::getUser();
    }

    public function getFormAction()
    {
        return $this->controller->pageUrl($this->property('bookingPage'));
    }

    public function getLocations()
    {
        return Locations_model::isEnabled()->dropdown('location_name');
    }

    public function getGuestSizeOptions($noOfGuests = null)
    {
        $options = [];
        $maxGuestSize = $this->property('maxGuestSize');
        for ($i = 1; $i <= $maxGuestSize; $i++) {
            $options[$i] = "{$i} ".(($i > 1)
                    ? lang('igniter.reservation::default.text_people')
                    : lang('igniter.reservation::default.text_person'));
        }

        if (is_null($noOfGuests))
            return $options;

        return array_get($options, $noOfGuests);
    }

    public function getTimePickerOptions()
    {
        $timePickerInterval = $this->property('timePickerInterval');
        $interval = new DateInterval("PT{$timePickerInterval}M");
        $startTime = Carbon::createFromTime(00, 00, 00);
        $endTime = Carbon::createFromTime(23, 59, 59);

        $options = [];
        $dateTimes = new DatePeriod($startTime, $interval, $endTime);
        foreach ($dateTimes as $dateTime) {
            $options[$dateTime->format('H:i')] = $dateTime->format($this->timeFormat);
        }

        return $options;
    }

    public function getTimeSlots()
    {
        $result = [];

        if (!$location = $this->location)
            return $result;

        return $this->createTimeSlots();
    }

    /**
     * @return \Admin\Models\Reservations_model
     */
    public function getReservation()
    {
        if (!is_null($this->reservation))
            return $this->reservation;

        $reservation = null;
        if ($hash = $this->param('hash'))
            $reservation = Reservations_model::where('hash', $hash)->first();

        if (!$reservation)
            $reservation = Reservations_model::make($this->getDefaultAttributes());

        return $this->reservation = $reservation;
    }

    public function processPickerForm()
    {
        $this->page['guestSize'] = input('guest', 2);

        $startDate = strlen(input('date'))
            ? Carbon::createFromFormat('Y-m-d H:i', input('date').' '.input('time'))
            : Carbon::tomorrow();

        $dateTime = ($sdateTime = input('sdateTime'))
            ? Carbon::createFromFormat('Y-m-d H:i', $sdateTime)
            : $startDate;

        $dateTimeFormat = $this->property('dateTimeFormat');
        $this->page['longDateTime'] = $dateTime->format($dateTimeFormat);
        $this->page['selectedDate'] = $dateTime;

        if (!get('hash'))
            return;

        $this->schedule = $this->getSchedule($dateTime);

        $data = get();
        $this->validateAfter(function ($validator) use ($dateTime) {
            $this->processValidateAfter($validator, $dateTime);
        });

        if (!$this->validatePasses($data, $this->createRules('picker')))
            return;

        $this->pickerStep = 'timeslot';
        if (array_get($data, 'sdateTime')) {
            $this->pickerStep = 'info';
        }

        $this->page['pickerStep'] = $this->pickerStep;
    }

    public function onComplete()
    {
        $data = input();

        if (!$this->validatePasses($data, $this->createRules('booking')))
            return Redirect::back()->withInput();

        try {
            $table = $this->findAvailableTable();

            $reservation = $this->createReservation($table, $data);

            $this->sendConfirmationMail($reservation);

            if (!$redirect = input('redirect'))
                $redirect = $this->property('successPage');

            return Redirect::to($this->controller->pageUrl($redirect, ['hash' => $reservation->hash]));
        }
        catch (ApplicationException $ex) {
            flash()->warning($ex->getMessage());

            return Redirect::back()->withInput();
        }
    }

    protected function getLocation()
    {
        if (!is_numeric($locationId = input('location')))
            return null;

        return Location::getById($locationId);
    }

    protected function getSchedule($dateTime)
    {
        $interval = $this->location->getReservationInterval();
        return $this->location->newWorkingSchedule('opening', null, $interval)->setNow($dateTime);
    }

    protected function createTimeSlots()
    {
        $selectedDate = Carbon::createFromFormat('Y-m-d H:i', input('date').' '.input('time'));
        $interval = $this->location->getReservationInterval();
        $start = $selectedDate->copy()->subMinutes($interval * 2);
        $end = $selectedDate->copy()->addMinutes($interval * 3);

        $dateInterval = new DateInterval("PT".$interval."M");
        $dateTimes = new DatePeriod($start, $dateInterval, $end);

        $timeSlot = [];
        foreach ($dateTimes as $date) {
            $timeSlot[] = (object)[
                'rawTime' => $date->format('Y-m-d H:i'),
                'time' => $date->format($this->timeFormat),
                'actionUrl' => Request::fullUrl().'&sdateTime='.urlencode($date->format('Y-m-d H:i')),
            ];
        }

        return $timeSlot;
    }

    protected function createRules($form)
    {
        switch ($form) {
            case 'picker':
                return [
                    ['location', 'lang:igniter.reservation::default.label_location', 'required|integer'],
                    ['guest', 'lang:igniter.reservation::default.label_guest_num', 'required|integer'],
                    ['date', 'lang:igniter.reservation::default.label_date', 'required|date_format:Y-m-d'],
                    ['time', 'lang:igniter.reservation::default.label_time', 'required|date_format:H:i'],
                    ['sdateTime', 'lang:igniter.reservation::default.label_time', 'sometimes|date_format:Y-m-d H:i'],
                ];
            case 'booking':
                return [
                    ['first_name', 'lang:igniter.reservation::default.label_first_name', 'required|min:2|max:32'],
                    ['last_name', 'lang:igniter.reservation::default.label_last_name', 'required|min:2|max:32'],
                    ['email', 'lang:igniter.reservation::default.label_email', 'required|email'],
                    ['telephone', 'lang:igniter.reservation::default.label_telephone', 'required'],
                    ['comment', 'lang:igniter.reservation::default.label_comment', 'max:520'],
                ];
        }
    }

    protected function createReservation($table, $data)
    {
        $customerId = ($user = Auth::getUser()) ? $user->getKey() : null;

        $reservation = $this->getReservation();
        $reservation->customer_id = $customerId;
        $reservation->location_id = $this->location->getKey();
        $reservation->table_id = $table->getKey();
        $reservation->guest_num = array_get($data, 'guest');
        $reservation->first_name = array_get($data, 'first_name');
        $reservation->last_name = array_get($data, 'last_name');
        $reservation->email = array_get($data, 'email');
        $reservation->telephone = array_get($data, 'telephone');
        $reservation->comment = array_get($data, 'comment');

        $dateTime = Carbon::createFromFormat('Y-m-d H:i', array_get($data, 'sdateTime'));
        $reservation->reserve_date = $dateTime->format('Y-m-d');
        $reservation->reserve_time = $dateTime->format('H:i:s');
        $reservation->duration = $this->location->getReservationStayTime();
        $reservation->status_id = setting('default_reservation_status', 0);
        $reservation->save();

        return $reservation;
    }

    protected function processValidateAfter($validator, $dateTime)
    {
        if (!(bool)$this->property('mode', TRUE)) {
            $validator->errors()->add('location', lang('igniter.reservation::default.alert_reservation_disabled'));

            return;
        }

        if ($dateTime->lt(Carbon::now()))
            $validator->errors()->add('date', lang('igniter.reservation::default.error_invalid_date'));

        if (!$this->schedule->isOpen())
            $validator->errors()->add('time', lang('igniter.reservation::default.error_invalid_time'));

        $tables = $this->getAvailableTables();
        if (!count($tables))
            $validator->errors()->add('guest', lang('igniter.reservation::default.alert_no_table_available'));
    }

    protected function loadAssets()
    {
        $this->addCss('vendor/datepicker/bootstrap-datepicker3.min.css', 'bootstrap-datepicker3-css');
        $this->addCss('css/booking.css', 'booking-css');
        $this->addJs('vendor/datepicker/bootstrap-datepicker.min.js', 'bootstrap-datepicker-js');
        $this->addJs('js/booking.js', 'booking-js');
    }

    protected function getDefaultAttributes()
    {
        $customer = Auth::getUser();

        return [
            'first_name' => $customer ? $customer->first_name : null,
            'last_name' => $customer ? $customer->last_name : null,
            'email' => $customer ? $customer->email : null,
            'telephone' => $customer ? $customer->telephone : null,
        ];
    }

    protected function findAvailableTable()
    {
        $selectedDate = Carbon::createFromFormat('Y-m-d H:i', input('sdateTime'));
        $interval = $this->location->getReservationInterval();
        $start = $selectedDate->copy()->subMinutes($interval * 2);
        $end = $selectedDate->copy()->addMinutes($interval * 3);

        $tables = $this->getAvailableTables();
        if (!count($tables))
            throw new ApplicationException(lang('igniter.reservation::default.alert_no_table_available'));

        $reservedTables = $this->filterReservedTables($this->getExistingReservations($start, $end), $selectedDate);

        $availableTables = $tables->diff($reservedTables);
        if (!count($availableTables))
            throw new ApplicationException(lang('igniter.reservation::default.alert_fully_booked'));

        $result = $availableTables->sortBy('max_capacity')->first();

        return $result ?: $tables->first();
    }

    protected function getAvailableTables()
    {
        if (count($this->availableTablesCache))
            return $this->availableTablesCache;

        $availableTables = Tables_model::isEnabled()
                                       ->whereHasLocation(input('location'))
                                       ->whereBetweenCapacity(input('guest'))
                                       ->get();

        return $this->availableTablesCache = $availableTables;
    }

    protected function getExistingReservations($start, $end)
    {
        if (count($this->existingReservationsCache))
            return $this->existingReservationsCache;

        $existing = Reservations_model::whereLocationId(input('location'))
                                      ->whereBetweenPeriod(
                                          $start->format('Y-m-d H:i:s'),
                                          $end->format('Y-m-d H:i:s')
                                      )->get();

        return $this->existingReservationsCache = $existing;
    }

    protected function filterReservedTables($reservations, $dateTime = null)
    {
        $filtered = $reservations;
        if ($dateTime) {
            $filtered = $reservations->filter(function ($reservation) use ($dateTime) {
                return ($reservation->reservation_datetime->lte($dateTime)
                    AND $reservation->reservation_end_datetime->gte($dateTime));
            });
        }

        return $filtered->map(function ($reservation) {
            return $reservation->related_table;
        });
    }

    protected function sendConfirmationMail($reservation)
    {
        $reservation->mailSend('igniter.reservation::mail.reservation', 'customer');
        $reservation->mailSend('igniter.reservation::mail.reservation_alert', 'location');
        $reservation->mailSend('igniter.reservation::mail.reservation_alert', 'admin');
    }
}