<?php

namespace Igniter\Reservation\Http\Controllers;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Models\Status;
use Igniter\Flame\Exception\FlashException;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Reservation\Models\DiningArea;
use Igniter\Reservation\Models\Reservation;
use Igniter\User\Models\Customer;

class Reservations extends \Igniter\Admin\Classes\AdminController
{
    public array $implement = [
        \Igniter\Admin\Http\Actions\ListController::class,
        \Igniter\Admin\Http\Actions\CalendarController::class,
        \Igniter\Admin\Http\Actions\FormController::class,
        \Igniter\User\Http\Actions\AssigneeController::class,
        \Igniter\Local\Http\Actions\LocationAwareController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => \Igniter\Reservation\Models\Reservation::class,
            'title' => 'lang:igniter.reservation::default.text_title',
            'emptyMessage' => 'lang:igniter.reservation::default.text_empty',
            'defaultSort' => ['reservation_id', 'DESC'],
            'configFile' => 'reservation',
        ],
        'floor_plan' => [
            'model' => \Igniter\Reservation\Models\Reservation::class,
            'title' => 'lang:igniter.reservation::default.text_title',
            'emptyMessage' => 'lang:igniter.reservation::default.text_empty',
            'defaultSort' => ['reservation_id', 'DESC'],
            'showCheckboxes' => false,
            'showSetup' => false,
            'configFile' => 'floor_plan',
        ],
    ];

    public $calendarConfig = [
        'calender' => [
            'title' => 'lang:igniter.reservation::default.text_title',
            'emptyMessage' => 'lang:igniter.reservation::default.text_no_booking',
            'popoverPartial' => 'reservations/form/calendar_popover',
            'configFile' => 'reservation',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:igniter.reservation::default.text_form_name',
        'model' => \Igniter\Reservation\Models\Reservation::class,
        'request' => \Igniter\Reservation\Http\Requests\ReservationRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'reservations/edit/{reservation_id}',
            'redirectClose' => 'reservations',
            'redirectNew' => 'reservations/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'reservations/edit/{reservation_id}',
            'redirectClose' => 'reservations',
            'redirectNew' => 'reservations/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'reservations',
        ],
        'delete' => [
            'redirect' => 'reservations',
        ],
        'configFile' => 'reservation',
    ];

    protected null|string|array $requiredPermissions = [
        'Admin.Reservations',
        'Admin.AssignReservations',
        'Admin.DeleteReservations',
    ];

    public static function getSlug()
    {
        return 'reservations';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('reservations', 'sales');
    }

    public function index()
    {
        $this->asExtension('ListController')->index();

        $this->vars['statusesOptions'] = \Igniter\Admin\Models\Status::getDropdownOptionsForReservation();
    }

    public function floor_plan()
    {
        $this->addJs('https://unpkg.com/konva@8.3.12/konva.min.js', 'konva-js');
        $this->addCss('igniter.reservation::/css/floorplanner.css', 'floorplanner-css');
        $this->addJs('igniter.reservation::/js/floorplanner.js', 'floorplanner-js');

        $this->asExtension('ListController')->index();

        $this->vars['statusesOptions'] = Status::getDropdownOptionsForReservation();
    }

    public function index_onDelete()
    {
        throw_unless($this->authorize('Admin.DeleteReservations'),
            new FlashException(lang('igniter::admin.alert_user_restricted'))
        );

        return $this->asExtension(\Igniter\Admin\Http\Actions\ListController::class)->index_onDelete();
    }

    public function onUpdateStatus()
    {
        $model = Reservation::find((int)post('recordId'));
        $status = Status::find((int)post('statusId'));
        if (!$model || !$status) {
            return;
        }

        $model->addStatusHistory($status);

        flash()->success(sprintf(lang('igniter::admin.alert_success'), lang('igniter::admin.statuses.text_form_name').' updated'))->now();

        return $this->redirectBack();
    }

    public function edit_onDelete()
    {
        throw_unless($this->authorize('Admin.DeleteReservations'),
            new FlashException(lang('igniter::admin.alert_user_restricted'))
        );

        return $this->asExtension(\Igniter\Admin\Http\Actions\FormController::class)->edit_onDelete();
    }

    public function calendarGenerateEvents($startAt, $endAt)
    {
        return Reservation::listCalendarEvents(
            $startAt, $endAt, LocationFacade::getId()
        );
    }

    public function calendarUpdateEvent($eventId, $startAt, $endAt)
    {
        throw_unless($reservation = Reservation::find($eventId),
            new FlashException(lang('igniter.reservation::default.alert_no_reservation_found'))
        );

        $startAt = make_carbon($startAt);
        $endAt = make_carbon($endAt);

        $reservation->duration = (int)floor($startAt->diffInMinutes($endAt));
        $reservation->reserve_date = $startAt->toDateString();
        $reservation->reserve_time = $startAt->toTimeString();

        $reservation->save();
    }

    public function listExtendQuery($query, $alias)
    {
        $query->with(['tables', 'status']);
    }

    public function formExtendQuery($query)
    {
        $query->with([
            'status_history' => function($q) {
                $q->orderBy('created_at', 'desc');
            },
            'status_history.user',
            'status_history.status',
        ]);
    }

    public function listFilterExtendScopesBefore($filter)
    {
        if ($filter->alias !== 'floor_plan_filter') {
            return;
        }

        $filter->scopes['reserve_date']['default'] = now()->toDateString();
    }

    public function listFilterExtendScopes($filter)
    {
        if ($filter->alias !== 'floor_plan_filter') {
            return;
        }

        if ($diningAreaId = $filter->getScopeValue('dining_area')) {
            $this->vars['diningArea'] = DiningArea::find($diningAreaId);
        }

        $reserveDateScope = $filter->getScope('reserve_date');
        $reserveTimeScope = $filter->getScope('reserve_time');

        $selectedDate = $filter->getScopeValue('reserve_date', $reserveDateScope->config['default']);

        $reserveTimeScope->options = $this->getReserveTimeOptions($selectedDate) ?: ['No times available'];
    }

    protected function getReserveTimeOptions($date = null)
    {
        $items = [];

        $date = make_carbon($date ?? now());
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();
        $interval = new DateInterval('PT15M');

        $datePeriod = new DatePeriod($start, $interval, $end);
        foreach ($datePeriod as $dateTime) {
            $dateTime = new Carbon($dateTime);
            $items[$dateTime->toDateTimeString()] = $dateTime->isoFormat(lang('system::lang.moment.time_format'));
        }

        return $items;
    }

    public function formBeforeSave($model)
    {
        $registeredCustomerId = post('Reservation.registered_customer_id');
        if ($registeredCustomerId) {
            $registeredCustomer = Customer::findOrFail($registeredCustomerId);
            $model->first_name = $registeredCustomer->first_name;
            $model->last_name = $registeredCustomer->last_name;
            $model->email = $registeredCustomer->email;
            $model->telephone = $registeredCustomer->telephone;
        }
    }
}
