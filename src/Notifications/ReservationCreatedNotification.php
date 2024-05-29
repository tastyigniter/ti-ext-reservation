<?php

namespace Igniter\Reservation\Notifications;

use Igniter\User\Classes\Notification;
use Igniter\User\Models\User;

class ReservationCreatedNotification extends Notification
{
    public function getRecipients(): array
    {
        return User::query()->isEnabled()
            ->whereHasOrDoesntHaveLocation($this->subject->location->getKey())
            ->get()->all();
    }

    public function getTitle(): string
    {
        return lang('igniter.reservation::default.notify_reservation_created_title');
    }

    public function getUrl(): string
    {
        $url = 'reservations';
        if ($this->subject) {
            $url .= '/edit/'.$this->subject->getKey();
        }

        return admin_url($url);
    }

    public function getMessage(): string
    {
        return sprintf(lang('igniter.reservation::default.notify_reservation_created'), $this->subject->customer_name);
    }

    public function getIcon(): ?string
    {
        return 'fa-chair';
    }
}
