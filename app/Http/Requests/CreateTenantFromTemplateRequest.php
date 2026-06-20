<?php

namespace App\Http\Requests;

class CreateTenantFromTemplateRequest extends CreateTenantRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'template_id' => ['required', 'integer', 'exists:templates,id'],
        ]);
    }
}
