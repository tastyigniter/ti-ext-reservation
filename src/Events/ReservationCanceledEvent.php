<?php

namespace Igniter\Reservation\Events;

use Igniter\Flame\Traits\EventDispatchable;
use Igniter\Reservation\Models\Reservation;

class ReservationCanceledEvent
{
    use EventDispatchable;

    public function __construct(public Reservation $reservation)
    {
    }

    public static function eventName()
    {
        return 'admin.reservation.canceled';
    }
}
