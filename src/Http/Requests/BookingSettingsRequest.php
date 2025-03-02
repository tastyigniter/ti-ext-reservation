<?php

namespace Igniter\Reservation\Http\Requests;

use Igniter\System\Classes\FormRequest;

class BookingSettingsRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'is_enabled' => lang('igniter.reservation::default.offer_reservation'),
            'limit_guests' => lang('igniter.reservation::default.label_limit_guests'),
            'limit_guests_count' => lang('igniter.reservation::default.label_limit_guests_count'),
            'time_interval' => lang('igniter.reservation::default.label_reservation_time_interval'),
            'stay_time' => lang('igniter.reservation::default.reservation_stay_time'),
            'auto_allocate_table' => lang('igniter.reservation::default.label_auto_allocate_table'),
            'min_advance_time' => lang('igniter.reservation::default.label_min_reservation_advance_time'),
            'max_advance_time' => lang('igniter.reservation::default.label_max_reservation_advance_time'),
            'cancellation_timeout' => lang('igniter.reservation::default.label_reservation_cancellation_timeout'),
        ];
    }

    public function rules()
    {
        return [
            'is_enabled' => ['boolean'],
            'limit_guests' => ['boolean'],
            'limit_guests_count' => ['integer', 'min:1', 'max:999'],
            'time_interval' => ['min:5', 'integer'],
            'stay_time' => ['min:5', 'integer'],
            'auto_allocate_table' => ['integer'],
            'min_advance_time' => ['integer', 'min:0', 'max:999'],
            'max_advance_time' => ['integer', 'min:0', 'max:999'],
            'cancellation_timeout' => ['integer', 'min:0', 'max:999'],
        ];
    }
}
