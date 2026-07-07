<?php

namespace App\Rules;

use App\Models\Tenant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a tenant subdomain: format, reserved-list, and global uniqueness.
 *
 * Used at tenant creation (Milestone 3) and reused as the single source of
 * truth for what constitutes an acceptable subdomain. Resolution-time guarding
 * of reserved labels is handled in ResolveTenant for performance, but the
 * reserved list itself lives in config/tenancy.php so both agree.
 */
class ValidSubdomain implements ValidationRule
{
    /**
     * @param  int|null  $ignoreTenantId  Existing tenant id to exclude from the
     *                                     uniqueness check (e.g. when editing).
     */
    public function __construct(protected ?int $ignoreTenantId = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Nama domain harus berupa teks.');

            return;
        }

        $min = (int) config('tenancy.subdomain.min', 3);
        $max = (int) config('tenancy.subdomain.max', 63);

        // Lowercase alphanumeric and hyphens; must start/end alphanumeric.
        if (! preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', $value)) {
            $fail('Nama domain hanya boleh berisi huruf kecil, angka, dan tanda hubung, serta harus diawali dan diakhiri dengan huruf atau angka.');

            return;
        }

        if (strlen($value) < $min || strlen($value) > $max) {
            $fail("Nama domain harus terdiri dari {$min} sampai {$max} karakter.");

            return;
        }

        $reserved = array_map('strtolower', (array) config('tenancy.reserved_subdomains', []));

        if (in_array($value, $reserved, true)) {
            $fail('Nama domain "'.$value.'" sudah dipesan sistem dan tidak dapat digunakan. Silakan pilih yang lain.');

            return;
        }

        $exists = Tenant::query()
            ->where('subdomain', $value)
            ->when($this->ignoreTenantId, fn ($q) => $q->where('id', '!=', $this->ignoreTenantId))
            ->exists();

        if ($exists) {
            $fail('Nama domain "'.$value.'" sudah dipakai toko lain. Silakan pilih nama domain yang lain.');
        }
    }
}
