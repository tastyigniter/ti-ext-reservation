<?php

namespace Igniter\Reservation\Listeners;

use Igniter\Reservation\Models\Reservation;
use Igniter\Reservation\Notifications\ReservationCreatedNotification;

class SendReservationConfirmation
{
    public function handle(Reservation $model)
    {
        ReservationCreatedNotification::make()->subject($model)->broadcast();

        $model->mailSend('igniter.reservation::mail.reservation', 'customer');
        $model->mailSend('igniter.reservation::mail.reservation_alert', 'location');
        $model->mailSend('igniter.reservation::mail.reservation_alert', 'admin');
    }
}
