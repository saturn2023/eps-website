<?php

namespace App\Models;

use App\Support\Navigation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /** Slugs that clash with app routes and so can't be used for pages. */
    public const RESERVED_SLUGS = ['admin', 'enquiries', 'documents', 'staff', 'storage', 'livewire', 'css', 'js', 'images', 'up', 'filament'];

    protected $fillable = [
        'title', 'slug', 'section', 'group', 'sort', 'heading', 'intro', 'icon', 'show_contact_card',
        'blocks', 'seo_title', 'seo_description', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_published' => 'boolean',
            'show_contact_card' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // The menu is built from pages, so refresh it whenever a page changes.
        static::saved(fn () => Navigation::flush());
        static::deleted(fn () => Navigation::flush());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function url(): string
    {
        return url($this->slug);
    }

    public function sectionLabel(): ?string
    {
        return Navigation::sectionLabel($this->section);
    }

    /** Published pages in the same section, in menu order. */
    public function siblings(): Collection
    {
        return static::published()
            ->where('section', $this->section)
            ->orderBy('sort')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'sort', 'group', 'icon']);
    }

    public function iconUrl(): ?string
    {
        return PageContent::url($this->icon);
    }
}
