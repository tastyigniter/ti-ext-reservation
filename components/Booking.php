<?php

namespace Igniter\Reservation\Components;

use Admin\Models\Locations_model;
use Admin\Traits\ValidatesForm;
use Carbon\Carbon;
use Exception;
use Igniter\Local\Facades\Location;
use Igniter\Reservation\Classes\BookingManager;
use Illuminate\Support\Facades\Redirect;
use Main\Facades\Auth;
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

    public $startDate;

    public $endDate;

    public function defineProperties()
    {
        return [
            'useCalendarView' => [
                'label' => 'Enable to display a calendar view for date selection',
                'type' => 'switch',
                'default' => true,
            ],
            'weekStartOn' => [
                'label' => 'Day of the week start the calendar. 0 (Sunday) to 6 (Saturday).',
                'type' => 'number',
                'default' => 0,
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
                'validationRule' => 'required|integer',
            ],
            'timeSlotsInterval' => [
                'label' => 'The interval to use for the time slots',
                'type' => 'number',
                'default' => 15,
                'validationRule' => 'required|integer',
            ],
            'showLocationThumb' => [
                'label' => 'Show Location Image Thumbnail',
                'type' => 'switch',
                'validationRule' => 'required|boolean',
            ],
            'telephoneIsRequired' => [
                'label' => 'Whether the telephone field should be required',
                'type' => 'switch',
                'default' => true,
                'validationRule' => 'required|boolean',
            ],
            'locationThumbWidth' => [
                'label' => 'Height',
                'type' => 'number',
                'default' => 95,
                'validationRule' => 'integer',
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
                'validationRule' => 'integer',
                'trigger' => [
                    'action' => 'show',
                    'field' => 'showLocationThumb',
                    'condition' => 'checked',
                ],
            ],
            'bookingPage' => [
                'label' => 'Booking Page',
                'type' => 'select',
                'default' => 'reservation'.DIRECTORY_SEPARATOR.'reservation',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'defaultLocationParam' => [
                'label' => 'The default location route parameter',
                'type' => 'text',
                'default' => 'local',
                'validationRule' => 'string',
            ],
            'successPage' => [
                'label' => 'Page to redirect to when checkout is successful',
                'type' => 'select',
                'default' => 'reservation'.DIRECTORY_SEPARATOR.'success',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'localNotFoundPage' => [
                'label' => 'Page to redirect to when location does not exist',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'home',
                'validationRule' => 'regex:/^[a-z0-9\-_\/]+$/i',
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

        $this->addJs('~/app/admin/assets/src/js/vendor/moment.min.js', 'moment-js');
        $this->addCss('~/app/admin/formwidgets/datepicker/assets/vendor/datepicker/bootstrap-datepicker.min.css', 'bootstrap-datepicker-css');
        $this->addJs('~/app/admin/formwidgets/datepicker/assets/vendor/datepicker/bootstrap-datepicker.min.js', 'bootstrap-datepicker-js');
        if (setting('default_language') != 'en')
            $this->addJs('~/app/admin/formwidgets/datepicker/assets/vendor/datepicker/locales/bootstrap-datepicker.'.strtolower(str_replace('_', '-', setting('default_language'))).'.min.js', 'bootstrap-datepicker-js');

        $this->addCss('~/app/admin/formwidgets/datepicker/assets/css/datepicker.css', 'datepicker-css');
        $this->addCss('css/booking.css', 'booking-css');
        $this->addJs('js/booking.js', 'booking-js');

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['pickerStep'] = $this->pickerStep;
        $this->page['bookingDateFormat'] = $this->dateFormat = lang('system::lang.moment.date_format');
        $this->page['bookingTimeFormat'] = $this->timeFormat = lang('system::lang.moment.time_format');
        $this->page['bookingDateTimeFormat'] = lang('system::lang.moment.date_time_format_long');
        $this->page['useCalendarView'] = (bool)$this->property('useCalendarView', false);

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
        return $this->controller->pageUrl($this->property('bookingPage'), [
            'location' => $this->getLocation()->permalink_slug,
        ]);
    }

    public function getLocations()
    {
        return Locations_model::isEnabled()
            ->get()
            ->filter(function ($location) {
                return $location->getOption('offer_reservation', 1) == 1;
            })
            ->pluck('location_name', 'permalink_slug');
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

    public function getDatePickerOptions()
    {
        $start = $this->getStartDate()->copy();
        $end = $this->getEndDate()->copy();

        $options = [];
        $schedule = $this->manager->getSchedule();
        for ($date = $start; $date->lte($end); $date->addDay()) {
            if (count($schedule->forDate($date)))
                $options[] = $date->copy();
        }

        return $options;
    }

    public function getDisabledDaysOfWeek()
    {
        return [];
    }

    public function getDisabledDates()
    {
        $result = [];
        $startDate = $this->getStartDate()->copy();
        $endDate = $this->getEndDate()->copy();
        $schedule = $this->manager->getSchedule();
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!count($schedule->forDate($date)))
                $result[] = $date->toDateString();
        }

        return $result;
    }

    public function getTimeSlots()
    {
        $result = [];
        $selectedDate = $this->getSelectedDate();
        $selectedTime = $this->getSelectedDateTime();
        $autoAllocateTable = (bool)$this->location->getOption('auto_allocate_table', 1);
        $guestSize = input('guest', $this->property('minGuestSize'));

        $index = 0;
        $dateTimes = $this->manager->makeTimeSlots($selectedDate);
        foreach ($dateTimes as $dateTime) {
            $selectedDateTime = $selectedDate->copy()->setTimeFromTimeString($dateTime->format('H:i'));
            $result[] = (object)[
                'isSelected' => $dateTime->format('H:i') == $selectedTime->format('H:i'),
                'rawTime' => $dateTime->format('H:i'),
                'time' => Carbon::instance($dateTime)->isoFormat(lang('system::lang.moment.time_format')),
                'fullyBooked' => $autoAllocateTable
                    ? $this->manager->isFullyBookedOn($selectedDateTime, $guestSize) : false,
            ];
        }

        return collect($result);
    }

    public function getReducedTimeSlots()
    {
        $timeslots = $this->getTimeslots()->filter(function ($slot) {
            return !$slot->fullyBooked;
        })->values();

        $selectedIndex = $timeslots->search(function ($slot, $key) {
            return $slot->isSelected;
        });

        $noOfSlots = 6;

        if (($from = ($selectedIndex ?: 0) - ((int)($noOfSlots / 2) - 1)) < 0)
            $from = 0;

        return $timeslots->slice($from, $noOfSlots - 1);
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
        } else {
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
        $this->page['weekStartOn'] = $this->property('weekStartOn', 0);

        // location selection made, show date selection
        if ($pickerStep == 1) {
            return;
        }

        $dateTime = $this->getSelectedDateTime();
        $this->page['selectedDate'] = $dateTime;
        $this->page['longDateTime'] = $dateTime->isoFormat(lang('system::lang.moment.date_time_format_long'));
        $this->page['guestSize'] = input('guest', 2);

        $data = get();

        $this->validateAfter(function ($validator) {
            $this->processValidateAfter($validator);
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
        } catch (Exception $ex) {
            flash()->warning($ex->getMessage());

            return Redirect::back()->withInput();
        }
    }

    //
    //
    //

    protected function getLocation()
    {
        if (!is_null($this->location))
            return $this->location;

        $this->location = Location::current();

        if (!$this->location->location_status)
            $this->location = null;

        return $this->location;
    }

    protected function createRules($form)
    {
        switch ($form) {
            case 'picker':
                return [
                    ['guest', 'lang:igniter.reservation::default.label_guest_num', 'required|integer'],
                    ['date', 'lang:igniter.reservation::default.label_date', 'required|date_format:Y-m-d'],
                    ['time', 'lang:igniter.reservation::default.label_time', 'required|date_format:H:i'],
                ];
            case 'booking':
                $telephoneRule = 'regex:/^([0-9\s\-\+\(\)]*)$/i';
                if ($this->property('telephoneIsRequired', true))
                    $telephoneRule = 'required|'.$telephoneRule;

                return [
                    ['first_name', 'lang:igniter.reservation::default.label_first_name', 'required|between:1,48'],
                    ['last_name', 'lang:igniter.reservation::default.label_last_name', 'required|between:1,48'],
                    ['email', 'lang:igniter.reservation::default.label_email', 'sometimes|required|email:filter|max:96'],
                    ['telephone', 'lang:igniter.reservation::default.label_telephone', $telephoneRule],
                    ['comment', 'lang:igniter.reservation::default.label_comment', 'max:520'],
                ];
        }
    }

    protected function processValidateAfter($validator)
    {
        if (!$location = $this->getLocation())
            return $validator->errors()->add('date', lang('igniter.reservation::default.error_invalid_location'));

        if (!(bool)$location->getOption('offer_reservation', 1)) {
            return $validator->errors()->add('location', lang('igniter.reservation::default.alert_reservation_disabled'));
        }

        $dateTime = $this->getSelectedDateTime();
        if ($dateTime->lt(Carbon::now()))
            return $validator->errors()->add('date', lang('igniter.reservation::default.error_invalid_date'));

        if (!$dateTime->isBetween($this->getStartDate(), $this->getEndDate()))
            return $validator->errors()->add('date', lang('igniter.reservation::default.error_invalid_datetime'));

        if ($this->getTimeSlots()->where('rawTime', $dateTime->format('H:i'))->isEmpty())
            return $validator->errors()->add('time', lang('igniter.reservation::default.error_invalid_time'));

        $autoAllocateTable = (bool)$this->location->getOption('auto_allocate_table', 1);
        if ($autoAllocateTable && $this->manager->isFullyBookedOn($dateTime, (int)input('guest', 1)))
            return $validator->errors()->add('guest', lang('igniter.reservation::default.alert_no_table_available'));
    }

    //
    // Helpers
    //

    public function getStartDate()
    {
        if (!is_null($this->startDate))
            return $this->startDate;

        return $this->startDate = now()->addDays(
            $this->getLocation()->getMinReservationAdvanceTime()
        )->startOfDay();
    }

    public function getEndDate()
    {
        if (!is_null($this->endDate))
            return $this->endDate;

        return $this->endDate = now()->addDays(
            $this->getLocation()->getMaxReservationAdvanceTime()
        )->endOfDay();
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getSelectedDate()
    {
        return strlen(input('date'))
            ? Carbon::createFromFormat('Y-m-d', input('date'))
            : $this->getStartDate()->copy();
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

    protected function checkLocationParam()
    {
        $param = $this->param('location', $this->property('defaultLocationParam', 'local'));
        if (!empty($param) && Locations_model::whereSlug($param)->exists()) {
            return;
        }

        return Redirect::to($this->controller->pageUrl($this->property('localNotFoundPage')));
    }
}