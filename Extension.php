<?php namespace Igniter\Reservation;

use Event;

class Extension extends \System\Classes\BaseExtension
{
    public function boot()
    {
        $this->bindReservationEvents();
    }

    public function registerComponents()
    {
        return [
            'Igniter\Reservation\Components\Booking' => [
                'code' => 'booking',
                'name' => 'lang:igniter.reservation::default.text_component_title',
                'description' => 'lang:igniter.reservation::default.text_component_desc',
            ],
            'Igniter\Reservation\Components\Reservations' => [
                'code' => 'accountReservations',
                'name' => 'lang:igniter.reservation::default.reservations.component_title',
                'description' => 'lang:igniter.reservation::default.reservations.component_desc',
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'igniter.reservation::mail.reservation' => 'Reservation confirmation email to customer',
            'igniter.reservation::mail.reservation_alert' => 'New reservation alert email to admin',
        ];
    }

    public function registerActivityTypes()
    {
        return [
            ActivityTypes\ReservationCreated::class,
        ];
    }

    protected function bindReservationEvents()
    {
        Event::listen('igniter.reservation.completed', function ($model) {
            ActivityTypes\ReservationCreated::pushActivityLog($model);

            $model->mailSend('igniter.reservation::mail.reservation', 'customer');
            $model->mailSend('igniter.reservation::mail.reservation_alert', 'location');
            $model->mailSend('igniter.reservation::mail.reservation_alert', 'admin');
        });
    }
}
