<?php

declare(strict_types=1);

namespace Igniter\Reservation\Http\Requests;

use Override;
use Igniter\System\Classes\FormRequest;

class DiningAreaRequest extends FormRequest
{
    #[Override]
    public function attributes()
    {
        return [
            'name' => lang('igniter::admin.label_name'),
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'between:2,128'],
        ];
    }
}
