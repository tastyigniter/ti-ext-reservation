<?php namespace Igniter\Reservation;

class Extension extends \System\Classes\BaseExtension
{
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
}
