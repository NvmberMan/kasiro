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

    /**
     * Indonesian validation messages — the app has no lang files and ships an
     * Indonesian UI, so framework English defaults are overridden here.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'      => 'Nama toko wajib diisi.',
            'name.max'           => 'Nama toko maksimal 255 karakter.',
            'subdomain.required' => 'Nama domain wajib diisi.',
            'logo.image'         => 'Logo harus berupa file gambar.',
            'logo.mimes'         => 'Logo harus berformat PNG, JPG, atau WebP.',
            'logo.max'           => 'Ukuran logo maksimal 2 MB.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'      => 'nama toko',
            'subdomain' => 'nama domain',
            'logo'      => 'logo',
        ];
    }
}
