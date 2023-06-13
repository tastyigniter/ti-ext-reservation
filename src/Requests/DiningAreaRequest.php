<?php

namespace Igniter\Reservation\Requests;

use Igniter\System\Classes\FormRequest;

class DiningAreaRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'table_name' => lang('igniter::admin.label_name'),
            'min_capacity' => lang('igniter.reservation::default.tables.label_min_capacity'),
            'max_capacity' => lang('igniter.reservation::default.tables.label_capacity'),
            'extra_capacity' => lang('igniter.reservation::default.tables.label_extra_capacity'),
            'priority' => lang('igniter.reservation::default.tables.label_priority'),
            'is_joinable' => lang('igniter.reservation::default.tables.label_joinable'),
            'table_status' => lang('igniter::admin.label_status'),
            'locations' => lang('igniter::admin.label_location'),
            'locations.*' => lang('igniter::admin.label_location'),
        ];
    }

    public function rules()
    {
        return [
            ['name', 'admin::lang.label_name', 'required|between:2,128'],
        ];
    }
}
