<?php

namespace Igniter\Reservation\AutomationRules\Conditions;

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
            'status_id' => [
                'label' => 'Last reservation status ID',
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
            'days_since' => [
                'label' => 'Days since reservation time',
            ],
            'days_until' => [
                'label' => 'Days until reservation time',
            ],
            'history_status_id' => [
                'label' => 'Recent reservation status IDs (eg. 1,2,3)',
            ],
        ];
    }

    public function getHoursSinceAttribute($value, $reservation)
    {
        $currentDateTime = now();

        return $currentDateTime->isAfter($reservation->reservation_datetime)
            ? $reservation->reservation_datetime->diffInRealHours($currentDateTime)
            : 0;
    }

    public function getHoursUntilAttribute($value, $reservation)
    {
        $currentDateTime = now();

        return $currentDateTime->isBefore($reservation->reservation_datetime)
            ? $currentDateTime->diffInRealHours($reservation->reservation_datetime)
            : 0;
    }

    public function getDaysSinceAttribute($value, $reservation)
    {
        $currentDateTime = now();

        return $currentDateTime->isAfter($reservation->reservation_datetime)
            ? $reservation->reservation_datetime->diffInDays($currentDateTime)
            : 0;
    }

    public function getDaysUntilAttribute($value, $reservation)
    {
        $currentDateTime = now();

        return $currentDateTime->isBefore($reservation->reservation_datetime)
            ? $currentDateTime->diffInDays($reservation->reservation_datetime)
            : 0;
    }

    public function getHistoryStatusIdAttribute($value, $reservation)
    {
        return $reservation->status_history()->pluck('status_id')->implode(',');
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
