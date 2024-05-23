<?php

namespace Igniter\Reservation\Requests;

use Igniter\System\Classes\FormRequest;

class DiningAreaRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'name' => lang('igniter::admin.label_name'),
        ];
    }

    public function rules()
    {
        return [
            'name' => ['required', 'between:2,128'],
        ];
    }
}
