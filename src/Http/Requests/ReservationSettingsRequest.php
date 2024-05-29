<?php

namespace Igniter\Reservation\Http\Requests;

use Igniter\System\Classes\FormRequest;

class ReservationSettingsRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'default_reservation_status' => lang('igniter.reservation::default.label_default_reservation_status'),
            'confirmed_reservation_status' => lang('igniter.reservation::default.label_confirmed_reservation_status'),
            'canceled_reservation_status' => lang('igniter.reservation::default.label_canceled_reservation_status'),
        ];
    }

    public function rules()
    {
        return [
            'reservation_email.*' => ['required', 'alpha'],
            'default_reservation_status' => ['required', 'integer'],
            'confirmed_reservation_status' => ['required', 'integer'],
            'canceled_reservation_status' => ['required', 'integer'],
        ];
    }
}
