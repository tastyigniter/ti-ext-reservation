<?php

namespace Igniter\Reservation\EventRules\Events;

use Admin\Models\Reservations_model;
use Igniter\EventRules\Classes\BaseEvent;

class ReservationAssigned extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'Reservation Assigned Event',
            'description' => 'When an reservation is assigned to a staff',
            'group' => 'reservation',
        ];
    }

    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        $params = [];
        $reservation = array_get($args, 0);
        if ($reservation instanceof Reservations_model)
            $params = $reservation->mailGetData();

        $params['reservation'] = $reservation;
        $params['assignee'] = $reservation->assignee;

        return $params;
    }
}