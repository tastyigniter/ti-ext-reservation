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
