<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * Keep the subdomain->tenant resolution cache (see ResolveTenant) honest:
     * any write to a tenant — archive, restore, rename, delete — must drop the
     * cached copy so the change takes effect on the very next request instead of
     * lingering until the TTL expires (which let archived tenants stay browsable).
     */
    protected static function booted(): void
    {
        static::saved(fn (self $tenant) => $tenant->forgetResolutionCache());
        static::deleted(fn (self $tenant) => $tenant->forgetResolutionCache());
    }

    /**
     * Forget the resolution cache for this tenant's subdomain, including the
     * previous subdomain when it was just renamed.
     */
    public function forgetResolutionCache(): void
    {
        $subdomains = array_unique(array_filter([
            $this->subdomain,
            $this->getOriginal('subdomain'),
        ]));

        foreach ($subdomains as $subdomain) {
            Cache::forget("tenant:subdomain:{$subdomain}");
        }
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'locale',
        'subdomain',
        'logo_path',
        'screenshot_path',
        'status',
        'template_id',
        'theme_config',
        'tax_percent',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'theme_config' => 'array',
            'tax_percent' => 'decimal:2',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Look up a tenant by its subdomain label.
     */
    public static function findBySubdomain(string $subdomain): ?self
    {
        return static::query()->where('subdomain', $subdomain)->first();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeArchived(Builder $query): void
    {
        $query->where('status', self::STATUS_ARCHIVED);
    }

    public function subdomainUrl(): string
    {
        return 'http://'.$this->subdomain.'.'.config('tenancy.central_domain').'/';
    }

    public function screenshotUrl(): ?string
    {
        if (! $this->screenshot_path) {
            return null;
        }

        $abs = storage_path('app/public/'.$this->screenshot_path);
        $version = file_exists($abs) ? filemtime($abs) : 0;

        return asset('storage/'.$this->screenshot_path).'?v='.$version;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->using(TenantUser::class)
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Whether this tenant follows the owner's studio language rather than
     * pinning its own.
     */
    public function followsStudioLocale(): bool
    {
        return $this->locale === null;
    }

    /**
     * The locale actually applied to this tenant. A null `locale` means the
     * tenant follows the owner's studio language dynamically.
     */
    public function resolvedLocale(): string
    {
        return \App\Support\Locale::normalize($this->locale ?? $this->owner?->locale);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function layout(): string
    {
        return $this->theme_config['layout'] ?? config('branding.defaults.layout');
    }

    public function theme(): string
    {
        return $this->theme_config['theme'] ?? config('branding.defaults.theme');
    }

    public function colorPalette(): string
    {
        return $this->theme_config['color_palette'] ?? config('branding.defaults.color_palette');
    }

    public function taxPercent(): float
    {
        return (float) ($this->tax_percent ?? 0);
    }
}
