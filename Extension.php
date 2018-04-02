<?php namespace SamPoyigi\Reservation;

class Extension extends \System\Classes\BaseExtension
{
    public function registerComponents()
    {
        return [
            'SamPoyigi\Reservation\Components\Booking' => [
                'code'        => 'booking',
                'name'        => 'lang:sampoyigi.reservation::default.text_component_title',
                'description' => 'lang:sampoyigi.reservation::default.text_component_desc',
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'sampoyigi.reservation::mail.reservation' => 'Reservation confirmation email to customer',
            'sampoyigi.reservation::mail.reservation_alert' => 'New reservation alert email to admin',
        ];
    }
}
