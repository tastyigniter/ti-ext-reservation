<?php

namespace Igniter\Reservation\Components;

use Admin\Models\Reservations_model;
use Admin\Models\Statuses_model;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Reservation\Classes\BookingManager;
use Main\Facades\Auth;
use Main\Traits\UsesPage;

class Reservations extends \System\Classes\BaseComponent
{
    use UsesPage;

    public function defineProperties()
    {
        return [
            'itemsPerPage' => [
                'label' => 'Items Per Page',
                'type' => 'number',
                'default' => 20,
                'validationRule' => 'required|integer',
            ],
            'sortOrder' => [
                'label' => 'Sort order',
                'type' => 'text',
                'default' => 'created_at desc',
                'validationRule' => 'required|string',
            ],
            'reservationsPage' => [
                'label' => 'Account Reservations Page',
                'type' => 'select',
                'default' => 'account'.DIRECTORY_SEPARATOR.'reservations',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'hashParamName' => [
                'label' => 'The parameter name used for the reservation hash code',
                'type' => 'text',
                'default' => 'hash',
                'validationRule' => 'required|string',
            ],
        ];
    }

    public function showCancelButton($reservation = null)
    {
        if (is_null($reservation) && !$reservation = $this->getReservation())
            return FALSE;

        if ($reservation->hasStatus(setting('canceled_reservation_status')))
            return FALSE;

        if (!$timeout = $reservation->location->getReservationCancellationTimeout())
            return FALSE;

        if (!$reservation->reservation_datetime->isFuture())
            return FALSE;

        return $reservation->reservation_datetime->diffInRealMinutes() > $timeout;
    }

    public function onRun()
    {
        $this->page['reservationsPage'] = $this->property('reservationsPage');
        $this->page['customerReservations'] = $this->loadReservations();
        $this->page['reservationDateTimeFormat'] = lang('system::lang.moment.date_time_format_short');

        $this->page['customerReservation'] = $this->getReservation();
    }

    public function onCancel()
    {
        if (!is_numeric($reservationId = input('reservationId')))
            return;

        if (!$reservation = Reservations_model::find($reservationId))
            return;

        if (!$this->showCancelButton($reservation))
            throw new ApplicationException(lang('igniter.reservation::default.reservations.alert_cancel_failed'));

        if (!$reservation->addStatusHistory(Statuses_model::find(setting('canceled_reservation_status'))))
            throw new ApplicationException(lang('igniter.reservation::default.reservations.alert_cancel_failed'));

        flash()->success(lang('igniter.reservation::default.reservations.alert_cancel_success'));

        return redirect()->back();
    }

    protected function getReservation()
    {
        $hashParam = $this->param($this->property('hashParamName'));
        if (!is_string($hashParam))
            return null;

        return BookingManager::instance()->getReservationByHash($hashParam, Auth::customer());
    }

    protected function loadReservations()
    {
        if (!$customer = Auth::customer())
            return [];

        return Reservations_model::with(['location', 'status', 'related_table'])->listFrontEnd([
            'page' => $this->param('page'),
            'pageLimit' => $this->property('itemsPerPage'),
            'sort' => $this->property('sortOrder', 'created_at desc'),
            'customer' => $customer,
        ]);
    }
}
