<?php namespace SamPoyigi\Reservation;

class Extension extends \System\Classes\BaseExtension
{
    public function registerComponents()
    {
        return [
            'SamPoyigi\Reservation\components\SeatBooker' => [
                'code'        => 'sampoyigi.reservation',
                'name'        => 'lang:sampoyigi.reservation::default.text_component_title',
                'description' => 'lang:sampoyigi.reservation::default.text_component_desc',
            ],
        ];
    }
}
