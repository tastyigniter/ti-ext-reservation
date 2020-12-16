<?php

namespace Igniter\Reservation\ActivityTypes;

use Admin\Models\Reservations_model;
use Admin\Models\Staffs_model;
use Igniter\Flame\ActivityLog\Contracts\ActivityInterface;
use Igniter\Flame\ActivityLog\Models\Activity;

class ReservationCreated implements ActivityInterface
{
    public $type;

    public $subject;

    public function __construct(string $type, Reservations_model $subject)
    {
        $this->type = $type;
        $this->subject = $subject;
    }

    public static function log($reservation)
    {
        $recipients = Staffs_model::isEnabled()
            ->whereHasLocation($reservation->location->getKey())
            ->get()
            ->map(function ($staff) {
                return $staff->user;
            })->all();

        activity()->pushLog(new static('reservationCreated', $reservation), $recipients);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getCauser()
    {
        return $this->subject->customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return [
            'reservation_id' => $this->subject->reservation_id,
            'full_name' => $this->subject->customer_name,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubjectModel()
    {
        return Reservations_model::class;
    }

    public static function getTitle(Activity $activity)
    {
        return lang('igniter.reservation::default.activity_reservation_created_title');
    }

    public static function getUrl(Activity $activity)
    {
        $url = 'reservations';
        if ($activity->subject)
            $url .= '/edit/'.$activity->subject->getKey();

        return admin_url($url);
    }

    public static function getMessage(Activity $activity)
    {
        return lang('igniter.reservation::default.activity_reservation_created');
    }
}
