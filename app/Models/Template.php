<?php

namespace App\Models;

use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'preview_image',
        'screenshot_path',
        'default_config',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'default_config' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * Public URL of the generated preview screenshot, cache-busted by the file's
     * mtime, or null when no screenshot has been generated yet.
     */
    public function screenshotUrl(): ?string
    {
        if (! $this->screenshot_path) {
            return null;
        }

        $abs = storage_path('app/public/'.$this->screenshot_path);
        $version = file_exists($abs) ? filemtime($abs) : 0;

        return asset('storage/'.$this->screenshot_path).'?v='.$version;
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'template_id');
    }
}
