<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class PageContent extends Model
{
    public const HOME = 'home';

    public const SITE = 'site';

    protected $fillable = ['key', 'content'];

    protected function casts(): array
    {
        return ['content' => 'array'];
    }

    /** Get the stored content array for a page key. */
    public static function for(string $key): array
    {
        return static::firstWhere('key', $key)?->content ?? [];
    }

    /** Save the content array for a page key. */
    public static function store(string $key, array $content): void
    {
        static::updateOrCreate(['key' => $key], ['content' => $content]);
    }

    /** Public URL for a file stored on the public disk, or null. */
    public static function url(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
