<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The school's illustrated icons (public/images/icons/*.png), used for the
 * home page quick links. Editors pick one by name in the admin.
 */
class QuickLinkIcons
{
    /** Older icon names from the first home page build, mapped to the PNG set. */
    private const LEGACY = [
        'absence' => 'record-an-absence',
        'newsletter' => 'newsletters',
        'uniform' => 'uniform-shop',
        'clock' => 'school-hours',
        'book' => 'book-lists',
        'phone' => 'contact',
        'mail' => 'contact',
        'globe' => 'iec',
    ];

    /** Options for an admin select: name => label. */
    public static function options(): array
    {
        return collect(glob(public_path('images/icons/*.png')))
            ->map(fn ($file) => pathinfo($file, PATHINFO_FILENAME))
            ->mapWithKeys(fn ($name) => [$name => Str::of($name)->replace('-', ' ')->ucfirst()->replace('P and c', 'P&C')->toString()])
            ->sort()
            ->all();
    }

    public static function url(?string $name): string
    {
        $name = self::LEGACY[$name] ?? $name;

        return asset('images/icons/' . (is_file(public_path("images/icons/{$name}.png")) ? $name : 'school') . '.png');
    }
}
