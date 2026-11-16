<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

/**
 * One file in the Media Library (a file on the public disk), with the
 * WordPress-style details: alt text, title, caption and description.
 */
class Media extends Model
{
    public const DISK = 'public';

    /** Types the library and the "Choose from library" picker can filter by. */
    public const KINDS = [
        'image' => 'Images',
        'video' => 'Videos',
        'document' => 'Documents',
    ];

    protected $table = 'media';

    protected $fillable = ['path', 'name', 'mime', 'size', 'width', 'height', 'alt', 'title', 'caption', 'description', 'uploaded_by'];

    /** @var array<string, string>|null path => alt, loaded once per request */
    private static ?array $alts = null;

    protected static function booted(): void
    {
        static::saved(fn () => static::$alts = null);
        static::deleted(function (Media $media) {
            static::$alts = null;
            Storage::disk(self::DISK)->delete($media->path);
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return Storage::disk(self::DISK)->url($this->path);
    }

    public function kind(): string
    {
        return match (true) {
            str_starts_with((string) $this->mime, 'image/') => 'image',
            str_starts_with((string) $this->mime, 'video/') => 'video',
            default => 'document',
        };
    }

    public function isImage(): bool
    {
        return $this->kind() === 'image';
    }

    public function extension(): string
    {
        return strtoupper(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }

    public function dimensions(): ?string
    {
        return $this->width ? "{$this->width} × {$this->height} pixels" : null;
    }

    public function scopeOfKind(Builder $query, ?string $kind): Builder
    {
        return match ($kind) {
            'image' => $query->where('mime', 'like', 'image/%'),
            'video' => $query->where('mime', 'like', 'video/%'),
            'document' => $query->where(fn ($q) => $q->whereNull('mime')
                ->orWhere(fn ($q) => $q->where('mime', 'not like', 'image/%')->where('mime', 'not like', 'video/%'))),
            default => $query,
        };
    }

    /**
     * Add a file that is already on the public disk to the library
     * (or return the existing entry). Reads its type, size and pixel size.
     */
    public static function register(string $path, ?string $originalName = null, ?int $userId = null): ?self
    {
        $disk = Storage::disk(self::DISK);
        if (! $disk->exists($path)) {
            return null;
        }

        if ($existing = static::firstWhere('path', $path)) {
            return $existing;
        }

        $mime = $disk->mimeType($path) ?: null;
        [$width, $height] = [null, null];
        if (str_starts_with((string) $mime, 'image/') && ($info = @getimagesize($disk->path($path)))) {
            [$width, $height] = $info;
        }

        $name = $originalName ?: basename($path);

        return static::create([
            'path' => $path,
            'name' => $name,
            'mime' => $mime,
            'size' => $disk->size($path),
            'width' => $width,
            'height' => $height,
            'title' => Str::of(pathinfo($name, PATHINFO_FILENAME))->replace(['-', '_'], ' ')->squish()->limit(250, '')->toString(),
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * Bring the library in line with the files on the public disk: add new
     * files (e.g. from the seeders) and drop entries whose file has gone.
     *
     * @return array{added: int, removed: int}
     */
    public static function sync(): array
    {
        $files = collect(Storage::disk(self::DISK)->allFiles())
            ->reject(fn (string $path) => str_starts_with(basename($path), '.') || str_starts_with($path, 'livewire-tmp/'));

        $known = static::pluck('path');

        $added = $files->diff($known)->filter(fn ($path) => static::register($path))->count();

        $missing = $known->diff($files);
        static::whereIn('path', $missing)->get()->each(fn (Media $m) => $m->deleteQuietly());

        return ['added' => $added, 'removed' => $missing->count()];
    }

    /**
     * Swap in a newly uploaded file (already stored at $newPath): every page and
     * setting that used the old file now uses the new one, the alt text, title,
     * caption and description stay, and the old file is deleted.
     */
    public function replaceWith(string $newPath, ?string $originalName = null): void
    {
        $oldPath = $this->path;
        if ($newPath === $oldPath) {
            return;
        }

        DB::transaction(function () use ($oldPath, $newPath, $originalName) {
            // Point every use of the old file at the new one (JSON may escape "/" as "\/").
            $swap = fn (?string $raw) => $raw === null ? null : str_replace(
                [$oldPath, str_replace('/', '\/', $oldPath)],
                [$newPath, str_replace('/', '\/', $newPath)],
                $raw,
            );

            foreach (PageContent::all() as $content) {
                $raw = $content->getRawOriginal('content');
                if ($raw !== ($new = $swap($raw))) {
                    PageContent::whereKey($content->getKey())->update(['content' => $new]);
                }
            }

            foreach (Page::all() as $page) {
                $changes = array_filter([
                    'blocks' => ($raw = $page->getRawOriginal('blocks')) !== ($new = $swap($raw)) ? $new : null,
                    'icon' => $page->icon === $oldPath ? $newPath : null,
                ]);
                if ($changes) {
                    Page::whereKey($page->getKey())->update($changes);
                }
            }

            $disk = Storage::disk(self::DISK);
            $mime = $disk->mimeType($newPath) ?: null;
            $info = str_starts_with((string) $mime, 'image/') ? @getimagesize($disk->path($newPath)) : false;

            // If the upload was already listed as its own entry, this one takes over (paths are unique).
            static::where('path', $newPath)->whereKeyNot($this->getKey())->get()->each->deleteQuietly();

            $this->update([
                'path' => $newPath,
                'name' => $originalName ?: basename($newPath),
                'mime' => $mime,
                'size' => $disk->size($newPath),
                'width' => $info[0] ?? null,
                'height' => $info[1] ?? null,
            ]);
        });

        Storage::disk(self::DISK)->delete($oldPath);
        \App\Support\Navigation::flush();
    }

    /** Alt text set in the library for a stored path ('' when none). */
    public static function altFor(?string $path): string
    {
        if (! $path) {
            return '';
        }

        static::$alts ??= static::whereNotNull('alt')->where('alt', '!=', '')->pluck('alt', 'path')->all();

        return static::$alts[$path] ?? '';
    }

    /**
     * Where this file is used on the site (home page, settings, pages).
     *
     * @return list<string>
     */
    public function usages(): array
    {
        // JSON columns may store "a/b.webp" as "a\/b.webp". The site has a few dozen
        // content rows, so checking the raw values is simple and works on any database.
        $needles = [$this->path, str_replace('/', '\/', $this->path)];
        $contains = fn (?string $raw) => $raw !== null && Str::contains($raw, $needles);

        $labels = [PageContent::HOME => 'Home page', PageContent::SITE => 'Site settings'];
        $used = PageContent::all()
            ->filter(fn (PageContent $c) => $contains($c->getRawOriginal('content')))
            ->map(fn (PageContent $c) => $labels[$c->key] ?? $c->key);

        $pages = Page::orderBy('title')->get()
            ->filter(fn (Page $p) => $p->icon === $this->path || $contains($p->getRawOriginal('blocks')))
            ->pluck('title');

        return $used->merge($pages)->values()->all();
    }
}
