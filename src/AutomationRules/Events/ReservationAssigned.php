<?php

namespace Igniter\Reservation\AutomationRules\Events;

use Igniter\Automation\Classes\BaseEvent;
use Igniter\Reservation\Models\Reservation;

class ReservationAssigned extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'Reservation Assigned Event',
            'description' => 'When a reservation is assigned to a staff',
            'group' => 'reservation',
        ];
    }

    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        $params = [];
        $reservation = array_get($args, 0);
        if ($reservation instanceof Reservation) {
            $params = $reservation->mailGetData();
        }

        $params['status'] = $reservation->status;
        $params['assignee'] = $reservation->assignee;

        return $params;
    }
}
