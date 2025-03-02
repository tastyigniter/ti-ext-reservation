<?php

namespace Igniter\Reservation\AutomationRules\Events;

use Igniter\Admin\Models\Reservation;
use Igniter\Automation\Classes\BaseEvent;

class NewReservation extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'New Reservation Event',
            'description' => 'When a new reservation is created',
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

        return $params;
    }
}
