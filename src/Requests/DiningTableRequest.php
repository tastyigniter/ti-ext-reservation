<?php

namespace Igniter\Reservation\Requests;

use Igniter\System\Classes\FormRequest;

class DiningTableRequest extends FormRequest
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
            'name' => ['required', 'string', 'between:2,255'],
            'shape' => ['required', 'in:rectangle,round'],
            'min_capacity' => ['required', 'integer', 'min:1', 'lte:max_capacity'],
            'max_capacity' => ['required', 'integer', 'min:1', 'gte:min_capacity'],
            'extra_capacity' => ['required', 'integer'],
            'priority' => ['required', 'integer'],
            'is_enabled' => ['required', 'boolean'],
            'dining_area_id' => ['required', 'integer'],
            'dining_section_id' => ['nullable', 'integer'],
        ];
    }
}
