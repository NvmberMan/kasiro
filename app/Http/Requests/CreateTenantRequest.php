<?php

namespace App\Http\Requests;

use App\Rules\ValidSubdomain;
use App\Support\Locale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Null / absent = follow the studio language dynamically.
            'locale'    => ['nullable', Rule::in(Locale::codes())],
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
            'name.required'      => __('Nama toko wajib diisi.'),
            'name.max'           => __('Nama toko maksimal 255 karakter.'),
            'subdomain.required' => __('Nama domain wajib diisi.'),
            'logo.image'         => __('Logo harus berupa file gambar.'),
            'logo.mimes'         => __('Logo harus berformat PNG, JPG, atau WebP.'),
            'logo.max'           => __('Ukuran logo maksimal 2 MB.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'      => __('nama toko'),
            'subdomain' => __('nama domain'),
            'logo'      => __('logo'),
        ];
    }
}
