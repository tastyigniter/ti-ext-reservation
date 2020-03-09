<?php

namespace Igniter\Reservation\ActivityTypes;

use Admin\Models\Reservations_model;
use Admin\Models\Staffs_model;
use Igniter\Flame\ActivityLog\Contracts\ActivityInterface;
use Igniter\Flame\ActivityLog\Models\Activity;

class ReservationCreated implements ActivityInterface
{
    public $reservation;

    public function __construct(Reservations_model $reservation)
    {
        $this->reservation = $reservation;
    }

    public static function log($model)
    {
        $recipients = Staffs_model::isEnabled()->get()->map(function ($model) {
            return $model->user;
        })->all();

        activity()->pushLog(new static($model), $recipients);
    }

    /**
     * {@inheritdoc}
     */
    public function getCauser()
    {
        return $this->reservation->customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->reservation;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return [
            'reservation_id' => $this->reservation->reservation_id,
            'full_name' => $this->reservation->customer_name,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return 'reservationCreated';
    }

    public static function getUrl(Activity $activity)
    {
        $url = 'reservations';
        if ($activity->subject)
            $url .= '/edit/'.$activity->subject->reservation_id;

        return admin_url($url);
    }

    public static function getMessage(Activity $activity)
    {
        return lang('igniter.reservation::default.activity_reservation_created');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubjectModel()
    {
        return Reservations_model::class;
    }
}