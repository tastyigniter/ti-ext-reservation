<?php

namespace Igniter\Reservation\Components;

use Admin\Models\Locations_model;
use Admin\Traits\ValidatesForm;
use Auth;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Exception;
use Igniter\Reservation\Classes\BookingManager;
use Location;
use Main\Traits\HasPageOptions;
use Redirect;
use Request;
use System\Classes\BaseComponent;

class Booking extends BaseComponent
{
    use ValidatesForm;
    use HasPageOptions;

    /**
     * @var Locations_model
     */
    public $location;

    /**
     * @var \Igniter\Reservation\Classes\BookingManager
     */
    protected $manager;

    public $reservation;

    public $dateFormat;

    public $timeFormat;

    public $pickerStep;

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
                'label' => 'Date time format to use for displaying reservation date & time',
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
        $this->manager = BookingManager::instance();
        $this->manager->useLocation($this->getLocation());

        $this->processPickerForm();
    }

    public function onRun()
    {
        $this->loadAssets();
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['pickerStep'] = $this->pickerStep;
        $this->page['bookingDateFormat'] = $this->dateFormat = $this->property('dateFormat');
        $this->page['bookingTimeFormat'] = $this->timeFormat = $this->property('timeFormat');
        $this->page['bookingDateTimeFormat'] = $this->property('dateTimeFormat');

        $this->page['reservation'] = $this->getReservation();
        $this->page['bookingLocation'] = $this->getLocation();
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
        $options = [];
        $startTime = Carbon::createFromTime(00, 00, 00);
        $endTime = Carbon::createFromTime(23, 59, 59);
        $interval = new DateInterval("PT{$this->property('timePickerInterval')}M");
        $dateTimes = new DatePeriod($startTime, $interval, $endTime);
        foreach ($dateTimes as $dateTime) {
            $options[$dateTime->format('H:i')] = $dateTime->format($this->timeFormat);
        }

        return $options;
    }

    public function getTimeSlots()
    {
        $result = [];
        $selectedDate = Carbon::createFromFormat('Y-m-d H:i', input('date').' '.input('time'));
        $interval = $this->property('timeSlotsInterval', $this->location->getReservationInterval());
        $dateTimes = $this->manager->makeTimeSlots($selectedDate, $interval);
        $query = Request::query();
        foreach ($dateTimes as $date) {
            $query['sdateTime'] = $date->format('Y-m-d H:i');
            $result[] = (object)[
                'rawTime' => $date->format('Y-m-d H:i'),
                'time' => $date->format($this->timeFormat),
                'fullyBooked' => $this->manager->isFullyBookedOn($date, input('guest')),
                'actionUrl' => Request::url().'?'.http_build_query($query),
            ];
        }

        return $result;
    }

    /**
     * @return \Admin\Models\Reservations_model
     */
    public function getReservation()
    {
        if (!is_null($this->reservation))
            return $this->reservation;

        if (strlen($hash = $this->param('hash'))) {
            $reservation = $this->manager->getReservationByHash($hash);
        }
        else {
            $reservation = $this->manager->loadReservation();
        }

        return $this->reservation = $reservation;
    }

    public function processPickerForm()
    {
        $dateTime = $this->getSelectedDateTime();
        $this->page['selectedDate'] = $dateTime;
        $this->page['longDateTime'] = $dateTime->format($this->property('dateTimeFormat'));
        $this->page['guestSize'] = input('guest', 2);

        if (!get('picker_form'))
            return;

        $data = get();

        $this->validateAfter(function ($validator) use ($dateTime) {
            $this->processValidateAfter($validator, $dateTime);
        });

        if (!$this->validatePasses($data, $this->createRules('picker')))
            return;

        $this->pickerStep = array_get($data, 'sdateTime') ? 'info' : 'timeslot';
    }

    public function onComplete()
    {
        $data = input();

        if (!$this->validatePasses($data, $this->createRules('booking')))
            return Redirect::back()->withInput();

        try {
            $reservation = $this->getReservation();

            $this->manager->saveReservation($reservation, $data);

            if (!$redirect = input('redirect'))
                $redirect = $this->property('successPage');

            return Redirect::to($this->controller->pageUrl($redirect, ['hash' => $reservation->hash]));
        }
        catch (Exception $ex) {
            flash()->warning($ex->getMessage());

            return Redirect::back()->withInput();
        }
    }

    //
    //
    //

    protected function getLocation()
    {
        if (!is_numeric($locationId = input('location')))
            return null;

        if (!is_null($this->location))
            return $this->location;

        return $this->location = Location::getById($locationId);
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

    protected function processValidateAfter($validator, $dateTime)
    {
        if (!(bool)$this->property('mode', TRUE)) {
            return $validator->errors()->add('location', lang('igniter.reservation::default.alert_reservation_disabled'));
        }

        if (!$this->getLocation())
            return $validator->errors()->add('date', lang('igniter.reservation::default.error_invalid_location'));

        if ($dateTime->lt(Carbon::now()))
            return $validator->errors()->add('date', lang('igniter.reservation::default.error_invalid_date'));

        if (!$this->manager->getSchedule()->isOpenAt($dateTime))
            return $validator->errors()->add('time', lang('igniter.reservation::default.error_invalid_time'));

        if (!$this->manager->hasAvailableTables(input('guest')))
            return $validator->errors()->add('guest', lang('igniter.reservation::default.alert_no_table_available'));

        $this->pickerStep = 'timeslot';

        if (strlen(input('sdateTime')) AND $this->manager->isFullyBookedOn($dateTime, input('guest')))
            return $validator->errors()->add('sdateTime', lang('igniter.reservation::default.alert_fully_booked'));

        $this->pickerStep = 'info';
    }

    //
    // Helpers
    //

    protected function loadAssets()
    {
        $this->addCss('vendor/datepicker/bootstrap-datepicker3.min.css', 'bootstrap-datepicker3-css');
        $this->addCss('css/booking.css', 'booking-css');
        $this->addJs('vendor/datepicker/bootstrap-datepicker.min.js', 'bootstrap-datepicker-js');
        $this->addJs('js/booking.js', 'booking-js');
    }

    /**
     * @return \Carbon\Carbon
     */
    protected function getSelectedDateTime()
    {
        $startDate = strlen(input('date'))
            ? Carbon::createFromFormat('Y-m-d H:i', input('date').' '.input('time'))
            : Carbon::tomorrow();

        $dateTime = ($sdateTime = input('sdateTime'))
            ? Carbon::createFromFormat('Y-m-d H:i', $sdateTime)
            : $startDate;

        return $dateTime;
    }
}