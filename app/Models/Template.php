<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    /** @use HasFactory<\Database\Factories\TemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'preview_image',
        'default_config',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'default_config' => 'array',
            'is_published'   => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'template_id');
    }
}
