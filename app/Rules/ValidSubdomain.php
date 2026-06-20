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
            $fail('The :attribute must be a string.');

            return;
        }

        $min = (int) config('tenancy.subdomain.min', 3);
        $max = (int) config('tenancy.subdomain.max', 63);

        // Lowercase alphanumeric and hyphens; must start/end alphanumeric.
        if (! preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', $value)) {
            $fail('The :attribute may only contain lowercase letters, numbers, and hyphens, and must start and end with a letter or number.');

            return;
        }

        if (strlen($value) < $min || strlen($value) > $max) {
            $fail("The :attribute must be between {$min} and {$max} characters.");

            return;
        }

        $reserved = array_map('strtolower', (array) config('tenancy.reserved_subdomains', []));

        if (in_array($value, $reserved, true)) {
            $fail('The :attribute "'.$value.'" is reserved and cannot be used.');

            return;
        }

        $exists = Tenant::query()
            ->where('subdomain', $value)
            ->when($this->ignoreTenantId, fn ($q) => $q->where('id', '!=', $this->ignoreTenantId))
            ->exists();

        if ($exists) {
            $fail('The :attribute "'.$value.'" is already taken.');
        }
    }
}
