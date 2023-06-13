<?php

namespace Igniter\Reservation\Models\Observers;

use Igniter\Reservation\Models\Reservation;

class ReservationObserver
{
    public function creating(Reservation $reservation)
    {
        $reservation->forceFill([
            'hash' => $reservation->generateHash(),
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function saved(Reservation $reservation)
    {
        $reservation->restorePurgedValues();

        if (array_key_exists('tables', $attributes = $reservation->getAttributes())) {
            $reservation->addReservationTables((array)array_get($attributes, 'tables', []));
        }

        if ($reservation->location->getOption('auto_allocate_table', 1) && !$reservation->tables()->count()) {
            $reservation->assignTable();
        }
    }
}
