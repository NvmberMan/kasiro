<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CreateTenantCustomRequest extends CreateTenantRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'layout'        => ['required', 'string', Rule::in(array_keys(config('branding.layouts')))],
            'theme'         => ['required', 'string', Rule::in(array_keys(config('branding.themes')))],
            'color_palette' => ['required', 'string', Rule::in(array_keys(config('branding.palettes')))],
        ]);
    }
}
