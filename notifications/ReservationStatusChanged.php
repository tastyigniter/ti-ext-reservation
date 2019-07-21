<?php

namespace Igniter\Reservation\Notifications;

use Igniter\Notify\Classes\BaseNotification;

class ReservationStatusChanged extends BaseNotification
{
    public function templateDetails()
    {
        return [
            'name' => 'Reservation status update notification',
            'description' => '',
        ];
    }

    public function defineFormFields()
    {
        return [
            'data[sms][content]' => [
                'tab' => 'SMS',
                'label' => 'Content',
                'type' => 'textarea',
                'default' => 'Reservation {reservation_id} status has been updated to: {status_name} ({status_comment})',
            ],
            'data[alert][subject]' => [
                'tab' => 'Alert (eg. slack)',
                'label' => 'Subject',
                'type' => 'text',
                'default' => 'Reservation status update!',
            ],
            'data[alert][title]' => [
                'tab' => 'Alert (eg. slack)',
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Reservation ID: {reservation_id}',
            ],
            'data[alert][content]' => [
                'tab' => 'Alert (eg. slack)',
                'label' => 'Content',
                'type' => 'textarea',
                'default' => 'Reservation {reservation_id} status has been updated to {status_name}.',
            ],
        ];
    }

    public function defineValidationRules()
    {
        return [
            ['data.sms.content', 'SMS Content', 'required|string|max:255'],
            ['data.alert.subject', 'Alert Subject', 'required|string|max:255'],
            ['data.alert.title', 'Alert Title', 'required|string|max:255'],
            ['data.alert.content', 'Alert Content', 'required|string|max:255'],
        ];
    }

    public function getActionUrl($notifiable)
    {
        return admin_url('reservation/edit/'.$this->parameters->get('reservation_id'));
    }
}