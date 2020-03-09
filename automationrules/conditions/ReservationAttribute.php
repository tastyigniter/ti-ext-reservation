<?php namespace Igniter\Reservation\AutomationRules\Conditions;

use ApplicationException;
use Igniter\Automation\Classes\BaseModelAttributesCondition;

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
        ];
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
