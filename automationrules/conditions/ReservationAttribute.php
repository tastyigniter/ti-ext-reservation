<?php

namespace Igniter\Reservation\AutomationRules\Conditions;

use Carbon\Carbon;
use Igniter\Automation\Classes\BaseModelAttributesCondition;
use Igniter\Flame\Exception\ApplicationException;

class ReservationAttribute extends BaseModelAttributesCondition
{
    protected $modelClass = \Admin\Models\Reservations_model::class;

    protected $modelAttributes;

    public function conditionDetails()
    {
        return [
            'name' => 'Reservation attribute',
            'description' => 'Reservation attributes',
        ];
    }

    public function defineModelAttributes()
    {
        return [
            'first_name' => [
                'label' => 'First Name',
            ],
            'last_name' => [
                'label' => 'Last Name',
            ],
            'email' => [
                'label' => 'Email address',
            ],
            'location_id' => [
                'label' => 'Location ID',
            ],
            'guest_num' => [
                'label' => 'Number of guests',
            ],
            'hours_since' => [
                'label' => 'Hours since reservation time',
            ],
            'hours_until' => [
                'label' => 'Hours until reservation time',
            ],
        ];
    }

    public function getHoursSinceAttribute($value, $reservation)
    {
        $currentDateTime = Carbon::now();
        $reservationDateTime = $reservation->reservation_datetime;

        return $currentDateTime->isAfter($reservationDateTime)
            ? $reservationDateTime->diffInRealHours($currentDateTime)
            : 0;
    }

    public function getHoursUntilAttribute($value, $reservation)
    {
        $currentDateTime = Carbon::now();
        $reservationDateTime = $reservation->reservation_datetime;

        return $currentDateTime->isBefore($reservationDateTime)
            ? $currentDateTime->diffInRealHours($reservationDateTime)
            : 0;
    }

    /**
     * Checks whether the condition is TRUE for specified parameters
     * @param array $params Specifies a list of parameters as an associative array.
     * @return bool
     */
    public function isTrue(&$params)
    {
        if (!$reservation = array_get($params, 'reservation')) {
            throw new ApplicationException('Error evaluating the reservation attribute condition: the reservation object is not found in the condition parameters.');
        }

        return $this->evalIsTrue($reservation);
    }
}
