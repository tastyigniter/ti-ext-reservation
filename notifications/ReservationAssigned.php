<?php

namespace Igniter\Reservation\Notifications;

use Igniter\Notify\Classes\BaseNotification;

class ReservationAssigned extends BaseNotification
{
    public function templateDetails()
    {
        return [
            'name' => 'Reservation assigned notification',
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
                'default' => 'Reservation {reservation_id} status has been assigned to: {assignee.staff_name}',
            ],
            'data[alert][subject]' => [
                'tab' => 'Alert (eg. slack)',
                'label' => 'Subject',
                'type' => 'text',
                'default' => 'Reservation assigned!',
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
                'default' => 'Reservation {reservation_id} has been assigned to {assignee.staff_name}.',
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