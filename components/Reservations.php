<?php

namespace Igniter\Reservation\Components;

use Admin\Models\Reservations_model;
use Auth;
use Igniter\Reservation\Classes\BookingManager;
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
                'default' => 'date_added desc',
                'validationRule' => 'required|string',
            ],
            'reservationsPage' => [
                'label' => 'Account Reservations Page',
                'type' => 'select',
                'default' => 'account/reservations',
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

    public function onRun()
    {
        $this->page['reservationsPage'] = $this->property('reservationsPage');
        $this->page['customerReservations'] = $this->loadReservations();
        $this->page['reservationDateTimeFormat'] = lang('system::lang.moment.date_time_format_short');

        $this->page['customerReservation'] = $this->getReservation();
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
            'sort' => $this->property('sortOrder', 'date_added desc'),
            'customer' => $customer,
        ]);
    }
}
