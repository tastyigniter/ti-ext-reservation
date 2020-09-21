<?php

namespace Igniter\Reservation\Components;

use Admin\Models\Locations_model;
use Admin\Traits\ValidatesForm;
use Auth;
use Carbon\Carbon;
use Exception;
use Igniter\Reservation\Classes\BookingManager;
use Location;
use Redirect;
use System\Classes\BaseComponent;

class Booking extends BaseComponent
{
    use ValidatesForm;
    use \Main\Traits\UsesPage;

    /**
     * @var Locations_model
     */
    public $location;

    /**
     * @var \Igniter\Reservation\Classes\BookingManager
     */
    protected $manager;

    protected $reservation;

    protected $dateFormat;

    protected $timeFormat;

    public $pickerStep;

    public function defineProperties()
    {
        return [
            'mode' => [
                'label' => 'Enable or disable booking',
                'type' => 'switch',
                'default' => TRUE,
            ],
            'minGuestSize' => [
                'label' => 'The minimum guest size',
                'type' => 'number',
                'default' => 2,
            ],
            'maxGuestSize' => [
                'label' => 'The maximum guest size',
                'type' => 'number',
                'default' => 20,
            ],
            'datePickerNoOfDays' => [
                'label' => 'The number of days to list for the date picker',
                'type' => 'number',
                'default' => 30,
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
            'bookingDateFormat' => [
                'label' => 'Date format to use for the date picker',
                'type' => 'text',
                'default' => 'MMM DD, YYYY',
            ],
            'bookingTimeFormat' => [
                'label' => 'Time format to use for the time dropdown',
                'type' => 'text',
                'default' => 'hh:mm a',
            ],
            'bookingDateTimeFormat' => [
                'label' => 'Date time format to use for displaying reservation date & time',
                'type' => 'text',
                'default' => 'dddd, MMMM D, YYYY \a\t hh:mm a',
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
                'options' => [static::class, 'getThemePageOptions'],
            ],
            'successPage' => [
                'label' => 'Page to redirect to when checkout is successful',
                'type' => 'select',
                'default' => 'reservation/success',
                'options' => [static::class, 'getThemePageOptions'],
            ],
            'defaultLocationParam' => [
                'label' => 'The default location route parameter (used internally when no location is selected)',
                'type' => 'text',
                'default' => 'local',
            ],
            'locationNotFoundPage' => [
                'label' => 'Page to redirect to when no location is found',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'home',
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
        if ($redirect = $this->checkLocationParam())
            return $redirect;
        
        $this->addJs('~/app/system/assets/ui/js/vendor/moment.min.js', 'moment-js');
        $this->addCss('~/app/admin/formwidgets/datepicker/assets/vendor/datepicker/bootstrap-datepicker.min.css', 'bootstrap-datepicker-css');
        $this->addJs('~/app/admin/formwidgets/datepicker/assets/vendor/datepicker/bootstrap-datepicker.min.js', 'bootstrap-datepicker-js');
        $this->addCss('~/app/admin/formwidgets/datepicker/assets/css/datepicker.css', 'datepicker-css');
        //$this->addJs('~/app/admin/formwidgets/datepicker/assets/js/datepicker.js', 'datepicker-js');
        $this->addCss('css/booking.css', 'booking-css');
        $this->addJs('js/booking.js', 'booking-js');

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['pickerStep'] = $this->pickerStep;
        $this->page['bookingDateFormat'] = $this->dateFormat = $this->property('bookingDateFormat');
        $this->page['bookingTimeFormat'] = $this->timeFormat = $this->property('bookingTimeFormat');
        $this->page['bookingDateTimeFormat'] = $this->property('bookingDateTimeFormat');

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

    public function getGuestSizeOptions($noOfGuests = null)
    {
        $options = [];
        $minGuestSize = $this->property('minGuestSize');
        $maxGuestSize = $this->property('maxGuestSize');
        for ($i = $minGuestSize; $i <= $maxGuestSize; $i++) {
            $options[$i] = "{$i} ".(($i > 1)
                    ? lang('igniter.reservation::default.text_people')
                    : lang('igniter.reservation::default.text_person'));
        }

        if (is_null($noOfGuests))
            return $options;

        return array_get($options, $noOfGuests);
    }
    
    public function getDisabledDaysOfWeek()
    {
        // get a 7 day schedule
        $schedule = $this->manager->getSchedule(7);

        $disabled = [];
        foreach ($schedule->getPeriods() as $index=>$day) {
	         if ($day->isEmpty())
	             $disabled[] = (int)date("w", strtotime($index));
        }
	    
        return $disabled;
    }
    
    public function getDisabledDates()
    {
        // future proofing - ability to disable specific days
        return [];	    
    }

    public function getTimeSlots()
    {
        $result = [];
        $selectedDate = $this->getSelectedDate();
        $interval = $this->location->getReservationInterval();
        $dateTimes = $this->manager->makeTimeSlots($selectedDate, $interval);
        foreach ($dateTimes as $date) {
            $dateTime = $selectedDate->copy()->setTimeFromTimeString($date->format());
            $result[] = (object)[
                'rawTime' => $dateTime->format('H:i'),
                'time' => $dateTime->isoFormat($this->property('bookingTimeFormat')),
                'fullyBooked' => $this->manager->isFullyBookedOn($dateTime, input('guest', $this->property('minGuestSize'))),
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
        $pickerStep = get('picker_step', 1);

        $this->pickerStep = 'dateselect';

        $this->page['nextOpen'] = Carbon::parse($this->manager->getSchedule()->getOpenTime());
        $this->page['timeOptions'] = $this->getTimeSlots();
        $this->page['disabledDaysOfWeek'] = $this->getDisabledDaysOfWeek();
        $this->page['disabledDates'] = $this->getDisabledDates();

        // location selection made, show date selection
        if ($pickerStep == 1) {
            return;
        }

        $dateTime = $this->getSelectedDateTime();
        $this->page['selectedDate'] = $dateTime;
        $this->page['longDateTime'] = $dateTime->isoFormat($this->property('bookingDateTimeFormat'));
        $this->page['guestSize'] = input('guest', 2);

        $data = get();

        $this->validateAfter(function ($validator) use ($dateTime) {
            $this->processValidateAfter($validator, $dateTime);
        });

        if (!$this->validatePasses($data, $this->createRules('picker')))
            return;

        $this->pickerStep = 'info';
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

    protected function checkLocationParam()
    {
        $param = $this->param('location', 'local');
        if (is_single_location() AND $param === $this->property('defaultLocationParam', 'local'))
            return;

        if ($this->location = Location::getBySlug($param))
            return;

        return \Redirect::to($this->pageUrl($this->property('locationNotFoundPage')));
    }

    protected function getLocation()
    {
        if (!is_numeric($locationId = input('location')))
            $locationId = params('default_location_id');

        if (!is_null($this->location))
            return $this->location;

        $this->location = Location::getById($locationId);
        $this->location->parseOptionsValue();

        return $this->location;
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
                ];
            case 'booking':
                return [
                    ['first_name', 'lang:igniter.reservation::default.label_first_name', 'required|between:1,48'],
                    ['last_name', 'lang:igniter.reservation::default.label_last_name', 'required|between:1,48'],
                    ['email', 'lang:igniter.reservation::default.label_email', 'required|email:filter|max:96'],
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
    }

    //
    // Helpers
    //

    /**
     * @return \Carbon\Carbon
     */
    public function getSelectedDate()
    {
        $date = strlen(input('date'))
            ? Carbon::createFromFormat('Y-m-d', input('date'))
            : Carbon::tomorrow();

        return $date;
    }

    /**
     * @return \Carbon\Carbon
     */
    protected function getSelectedDateTime()
    {
        $startDate = strlen(input('date'))
            ? Carbon::createFromFormat('Y-m-d H:i', input('date').' '.(input('time') ?? '00:01'))
            : Carbon::tomorrow();

        $dateTime = ($sdateTime = input('sdateTime'))
            ? Carbon::createFromFormat('Y-m-d H:i', $sdateTime)
            : $startDate;

        return $dateTime;
    }
}
