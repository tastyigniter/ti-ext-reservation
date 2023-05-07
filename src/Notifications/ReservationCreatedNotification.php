<?php

namespace Igniter\Reservation\Notifications;

use Igniter\Flame\Auth\Models\User;
use Igniter\System\Classes\Notification;

class ReservationCreatedNotification extends Notification
{
    public function getRecipients(): array
    {
        return User::isEnabled()
            ->whereHasLocation($this->subject->location->getKey())
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