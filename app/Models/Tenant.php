<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'subdomain',
        'logo_path',
        'status',
        'template_id',
        'theme_config',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'theme_config' => 'array',
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
}
