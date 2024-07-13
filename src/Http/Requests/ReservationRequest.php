<?php

namespace Igniter\Reservation\Http\Requests;

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
            'comment' => lang('igniter.reservation::default.column_comment'),
        ];
    }

    public function rules()
    {
        return [
            'location_id' => ['sometimes', 'required', 'integer'],
            'registered_customer_id' => ['nullable', 'integer'],
            'first_name' => ['required_without:registered_customer_id', 'string', 'between:1,48'],
            'last_name' => ['required_without:registered_customer_id', 'string', 'between:1,48'],
            'email' => ['email:filter', 'max:96'],
            'telephone' => ['sometimes', 'string'],
            'reserve_date' => ['required', 'date_format:Y-m-d'],
            'reserve_time' => ['required', 'date_format:H:i'],
            'guest_num' => ['required', 'integer'],
            'duration' => ['integer', 'min:1'],
            'tables' => ['nullable', 'array'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
