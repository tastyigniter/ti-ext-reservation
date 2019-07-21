<?php

namespace Igniter\Reservation\Notifications;

use Igniter\Notify\Classes\BaseNotification;

class NewReservation extends BaseNotification
{
    public function templateDetails()
    {
        return [
            'name' => 'Reservation confirmation notification',
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
                'default' => 'Your booking for {guest_num} has been created and will be confirmed shortly.',
            ],
            'data[alert][subject]' => [
                'tab' => 'Alert (eg. slack)',
                'label' => 'Subject',
                'type' => 'text',
                'default' => 'You received a new booking!',
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
                'default' => 'You just received a new booking for {guest_num} guests at {location.location_name}.',
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