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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'template_id.required' => 'Silakan pilih template kasir terlebih dahulu.',
            'template_id.integer'  => 'Template yang dipilih tidak valid.',
            'template_id.exists'   => 'Template yang dipilih tidak ditemukan.',
        ]);
    }
}
