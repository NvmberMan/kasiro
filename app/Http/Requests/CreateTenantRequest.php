<?php

namespace App\Http\Requests;

use App\Rules\ValidSubdomain;
use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', new ValidSubdomain],
            'logo'      => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }
}
