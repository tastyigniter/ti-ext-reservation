<?php

namespace SamPoyigi\Reservation\Components;

use Admin\Models\Locations_model;
use Admin\Models\Reservations_model;
use Admin\Traits\ValidatesForm;
use Auth;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Exception;
use Location;
use Redirect;
use Request;
use System\Classes\BaseComponent;

class Booking extends BaseComponent
{
    use ValidatesForm;

    public $uniqueHash;

    public $location;

    public $reservation;

    public $maxGuestSize;

    public $timePickerInterval;

    public $dateTimeFormat;

    public $dateFormat;

    public $timeFormat;

    public $pickerStep;

    public function defineProperties()
    {
        return [
            'maxGuestSize'        => [
                'label'   => 'The maximum guest size',
                'type'    => 'number',
                'default' => 20,
            ],
            'timePickerInterval'  => [
                'label'   => 'The interval to use for the time picker',
                'type'    => 'number',
                'default' => 30,
            ],
            'timeSlotsInterval'   => [
                'label'   => 'The interval to use for the time slots',
                'type'    => 'number',
                'default' => 15,
            ],
            'dateFormat'          => [
                'label'   => 'Date format to use for the date picker',
                'type'    => 'text',
                'default' => 'M d, yyyy',
            ],
            'timeFormat'          => [
                'label'   => 'Time format to use for the time dropdown',
                'type'    => 'text',
                'default' => 'h:i a',
            ],
            'dateTimeFormat'      => [
                'label'   => 'Date time format to use for the book summary',
                'type'    => 'text',
                'default' => 'l, F j, Y',
            ],
            'showLocationThumb'   => [
                'label' => 'Show Location Image Thumbnail',
                'type'  => 'switch',
            ],
            'locationThumbWidth'  => [
                'label'   => 'Height',
                'type'    => 'number',
                'default' => 95,
                'trigger' => [
                    'action'    => 'show',
                    'field'     => 'showLocationThumb',
                    'condition' => 'checked',
                ],
            ],
            'locationThumbHeight' => [
                'label'   => 'Width',
                'type'    => 'number',
                'default' => 80,
                'trigger' => [
                    'action'    => 'show',
                    'field'     => 'showLocationThumb',
                    'condition' => 'checked',
                ],
            ],
            'bookingPage'         => [
                'label'   => 'Booking Page',
                'type'    => 'text',
                'default' => 'reservation/reservation',
            ],
            'successPage'    => [
                'label'   => 'Page to redirect to when checkout is successful',
                'type'    => 'text',
                'default' => 'reservation/success',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['reservation'] = $this->getReservation();

        if (get('hash'))
            $this->processPickerForm();

        $this->loadAssets();
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->uniqueHash = uniqid();
        $this->page['bookingDateFormat'] = $this->dateFormat = $this->property('dateFormat');
        $this->page['bookingTimeFormat'] = $this->timeFormat = $this->property('timeFormat');
        $this->page['bookingDateTimeFormat'] = $this->dateTimeFormat = $this->property('dateTimeFormat');
        $this->page['maxGuestSize'] = $this->maxGuestSize = $this->property('maxGuestSize');
        $this->page['timePickerInterval'] = $this->timePickerInterval = $this->property('timePickerInterval');

        $this->page['showLocationThumb'] = $this->property('showLocationThumb');
        $this->page['locationThumbWidth'] = $this->property('locationThumbWidth');
        $this->page['locationThumbHeight'] = $this->property('locationThumbHeight');

        $this->page['bookingEventHandler'] = $this->getEventHandler('onComplete');

        $this->page['customer'] = Auth::getUser();
        $this->page['pickerStep'] = $this->pickerStep;

        $this->page['bookingLocation'] = $this->location = $this->getLocation();
        $this->page['guestSize'] = input('guest', 2);

        $startDate = (input('date'))
            ? Carbon::createFromFormat('Y-m-d H:i', input('date')." ".input('time'))
            : Carbon::now();

        $dateTime = ($sdateTime = input('sdateTime'))
            ? Carbon::createFromFormat('Y-m-d H:i', $sdateTime)
            : $startDate;

        $this->page['longDateTime'] = $dateTime->format("{$this->dateTimeFormat}");
        $this->page['selectedDate'] = $dateTime->format('Y-m-d');
        $this->page['selectedTime'] = $dateTime->format($this->property('timeFormat'));
    }

    public function getFormAction()
    {
        return $this->pageUrl($this->property('bookingPage'));
    }

    public function getLocations()
    {
        return Locations_model::isEnabled()->dropdown('location_name');
    }

    public function getGuestSizeOptions()
    {
        $options = [];
        $maxGuestSize = $this->maxGuestSize;
        for ($i = 1; $i <= $maxGuestSize; $i++) {
            $options[$i] = "{$i} ".(($i > 1)
                    ? lang('sampoyigi.reservation::default.text_people')
                    : lang('sampoyigi.reservation::default.text_person'));
        }

        return $options;
    }

    public function getTimePickerOptions()
    {
        $interval = new DateInterval("PT{$this->timePickerInterval}M");
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

        $selectedDate = Carbon::createFromFormat('Y-m-d', input('date'));
//        $openingSchedule = $location->workingScheduleInstance('opening');
//        $openingPeriod = $openingSchedule->getHoursByDate($selectedDate);
//        $openingPeriod->setWeekDate($selectedDate);
        $dateTime = $selectedDate->copy()->setTimeFromTimeString(input('time'));

        $interval = $this->location->getReservationInterval();
        $dateInterval = new DateInterval("PT".$interval."M");
        $dateTimes = new DatePeriod(
            $dateTime->copy()->subMinutes($interval * 2),
            $dateInterval,
            $dateTime->copy()->addMinutes($interval * 3)
        );

        $times = [];
        foreach ($dateTimes as $dateTime) {
            $times[] = (object)[
                'rawTime'     => $dateTime->format('Y-m-d H:i'),
                'time'        => $dateTime->format($this->timeFormat),
                'fullyBooked' => FALSE,
                'actionUrl'   => Request::fullUrl().'&sdateTime='.urlencode($dateTime->format('Y-m-d H:i')),
            ];
        }

//        dd($selectedDate, $dateTime, $dateTimes, $times, $openingPeriod->checkStatus($dateTime));

        return $times;

        return Reservations_model::findAvailableTimeSlots([
            'location' => input('location'),
            'guest'    => input('guest'),
            'date'     => input('date'),
            'time'     => input('time'),
            'interval' => $this->location->getReservationInterval(),
        ]);
    }

    /**
     * @return \Admin\Models\Reservations_model
     */
    public function getReservation()
    {
        if (!is_null($this->reservation))
            return $this->reservation;

//        $id = $this->getCurrentOrderId();
//
//        $order = Reservations_model::find($id);
//
//        $user = Auth::getUser();
//        $customerId = $user ? $user->customer_id : null;

        // Only orders without a status can be confirmed
        // Only users can view their own orders
//        if (!$order OR $order->isPlaced() OR $order->customer_id != $customerId)
        $reservation = Reservations_model::make();

//        if ($order)
//            $order->setReceiptPageName($this->property('successPage'));
//
        return $this->reservation = $reservation;
    }

    protected function processPickerForm()
    {
        try {
            $data = get();

            $this->validate($data, $this->createRules('picker'));

            $this->pickerStep = 'timeslot';
            if (array_get($data, 'sdateTime'))
                $this->pickerStep = 'info';

            // check availability

        } catch (Exception $ex) {
            flash()->warning($ex->getMessage());
        }
    }

    public function onComplete()
    {
        try {
            $this->processPickerForm();

            $data = post();

            $this->validate($data, $this->createRules('booking'));

            $this->createReservation($data);

            if (!$redirect = input('redirect'))
                $redirect = $this->property('successPage');

            return Redirect::to($this->pageUrl($redirect));
        } catch (Exception $ex) {
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

    protected function loadAssets()
    {
        $this->addCss('vendor/datepicker/bootstrap-datepicker3.min.css', 'bootstrap-datepicker3-css');
        $this->addCss('css/booking.css', 'booking-css');
        $this->addJs("vendor/datepicker/bootstrap-datepicker.min.js", 'bootstrap-datepicker-js');
        $this->addJs("js/booking.js", 'booking-js');
    }

    protected function createRules($form)
    {
        switch ($form) {
            case 'picker':
                return [
                    ['location', 'lang:sampoyigi.reservation::default.label_location', 'required|integer'],
                    ['guest', 'lang:sampoyigi.reservation::default.label_guest_num', 'required|integer'],
                    ['date', 'lang:sampoyigi.reservation::default.label_date', 'required|date_format:Y-m-d'],
                    ['time', 'lang:sampoyigi.reservation::default.label_time', 'required|date_format:H:i'],
                    ['sdateTime', 'lang:sampoyigi.reservation::default.label_time', 'sometimes|date_format:Y-m-d H:i'],
                ];
            case 'booking':
                return [
                    ['first_name', 'lang:main::default.reservation.label_first_name', 'required|min:2|max:32'],
                    ['last_name', 'lang:main::default.reservation.label_last_name', 'required|min:2|max:32'],
                    ['email', 'lang:main::default.reservation.label_email', 'required|email'],
                    ['telephone', 'lang:main::default.reservation.label_telephone', 'required|integer'],
                    ['comment', 'lang:main::default.reservation.label_comment', 'max:520'],
                ];
        }
    }

    protected function createReservation($data)
    {
    }
}