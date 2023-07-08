<?php

namespace Igniter\Reservation\Requests;

use Igniter\System\Classes\FormRequest;

class ReservationRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'location_id' => lang('igniter.reservation::default.text_restaurant'),
            'first_name' => lang('igniter.reservation::default.label_first_name'),
            'last_name' => lang('igniter.reservation::default.label_last_name'),
            'email' => lang('igniter::admin.label_email'),
            'telephone' => lang('igniter.reservation::default.label_customer_telephone'),
            'reserve_date' => lang('igniter.reservation::default.label_reservation_date'),
            'reserve_time' => lang('igniter.reservation::default.label_reservation_time'),
            'guest_num' => lang('igniter.reservation::default.label_guest'),
        ];
    }

    public function rules()
    {
        return [
            'location_id' => ['sometimes', 'required', 'integer'],
            'first_name' => ['required', 'string', 'between:1,48'],
            'last_name' => ['required', 'string', 'between:1,48'],
            'email' => ['email:filter', 'max:96'],
            'telephone' => ['sometimes', 'string'],
            'reserve_date' => ['required', 'valid_date'],
            'reserve_time' => ['required', 'valid_time'],
            'guest_num' => ['required', 'integer'],
            'duration' => ['integer', 'min:1'],
            'tables' => ['nullable', 'array'],
        ];
    }
}