<?php namespace Igniter\Reservation\Components;

use Admin\Models\Reservations_model;
use Auth;

class Reservations extends \System\Classes\BaseComponent
{
    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'label' => 'Page Number',
                'type' => 'string',
            ],
            'itemsPerPage' => [
                'label' => 'Items Per Page',
                'type' => 'number',
                'default' => 20,
            ],
            'sortOrder' => [
                'label' => 'Sort order',
                'type' => 'string',
            ],
            'addReviewsPage' => [
                'label' => 'Add review page',
                'type' => 'string',
                'default' => 'account/reviews',
            ],
            'reservationsPage' => [
                'label' => 'Account Reservations Page',
                'type' => 'string',
                'default' => 'account/reservations',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['reservationsPage'] = $this->property('reservationsPage');
        $this->page['addReviewsPage'] = $this->property('addReviewsPage');
        $this->page['showReviews'] = setting('allow_reviews') == 1;
        $this->page['customerReservations'] = $this->loadReservations();

        $this->page['reservationIdParam'] = $this->param('reservationId');
        $this->page['customerReservation'] = $this->getReservation();
    }

    protected function getReservation()
    {
        if (!is_numeric($reservationIdParam = $this->param('reservationId')))
            return null;

        $customer = Auth::customer();
        $reservation = Reservations_model::find($reservationIdParam);
        if (!$customer OR $reservation->customer_id != $customer->customer_id)
            return null;

        return $reservation;
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