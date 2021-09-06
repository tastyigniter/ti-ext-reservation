<?php

namespace Igniter\Reservation;

use Admin\Models\Reservations_model;
use Admin\Models\Status_history_model;
use Igniter\Reservation\Listeners\MaxGuestSizePerTimeslotReached;
use Illuminate\Support\Facades\Event;

class Extension extends \System\Classes\BaseExtension
{
    public function boot()
    {
        $this->bindReservationEvent();
    }

    public function registerComponents()
    {
        return [
            'Igniter\Reservation\Components\Booking' => [
                'code' => 'booking',
                'name' => 'lang:igniter.reservation::default.text_component_title',
                'description' => 'lang:igniter.reservation::default.text_component_desc',
            ],
            'Igniter\Reservation\Components\Reservations' => [
                'code' => 'accountReservations',
                'name' => 'lang:igniter.reservation::default.reservations.component_title',
                'description' => 'lang:igniter.reservation::default.reservations.component_desc',
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'igniter.reservation::mail.reservation' => 'Reservation confirmation email to customer',
            'igniter.reservation::mail.reservation_alert' => 'New reservation alert email to admin',
        ];
    }

    public function registerActivityTypes()
    {
        return [
            ActivityTypes\ReservationCreated::class => 'reservationCreated',
        ];
    }

    public function registerAutomationRules()
    {
        return [
            'events' => [
                'igniter.reservation.confirmed' => \Igniter\Reservation\AutomationRules\Events\NewReservation::class,
                'igniter.reservation.statusAdded' => \Igniter\Reservation\AutomationRules\Events\NewReservationStatus::class,
                'igniter.reservation.assigned' => \Igniter\Reservation\AutomationRules\Events\ReservationAssigned::class,
            ],
            'actions' => [],
            'conditions' => [
                \Igniter\Reservation\AutomationRules\Conditions\ReservationAttribute::class,
                \Igniter\Reservation\AutomationRules\Conditions\ReservationStatusAttribute::class,
            ],
        ];
    }

    protected function bindReservationEvent()
    {
        Event::subscribe(MaxGuestSizePerTimeslotReached::class);

        Event::listen('igniter.reservation.confirmed', function (Reservations_model $model) {
            ActivityTypes\ReservationCreated::log($model);

            $model->mailSend('igniter.reservation::mail.reservation', 'customer');
            $model->mailSend('igniter.reservation::mail.reservation_alert', 'location');
            $model->mailSend('igniter.reservation::mail.reservation_alert', 'admin');
        });

        Event::listen('admin.statusHistory.beforeAddStatus', function ($model, $object, $statusId, $previousStatus) {
            if (!$object instanceof Reservations_model)
                return;

            if (Status_history_model::alreadyExists($object, $statusId))
                return;

            Event::fire('igniter.reservation.beforeAddStatus', [$model, $object, $statusId, $previousStatus], TRUE);
        });

        Event::listen('admin.statusHistory.added', function ($model, $statusHistory) {
            if (!$model instanceof Reservations_model)
                return;

            Event::fire('igniter.reservation.statusAdded', [$model, $statusHistory], TRUE);
        });

        Event::listen('admin.assignable.assigned', function ($model, $assignableLog) {
            if (!$model instanceof Reservations_model)
                return;

            Event::fire('igniter.reservation.assigned', [$model, $assignableLog], TRUE);
        });
    }
}
