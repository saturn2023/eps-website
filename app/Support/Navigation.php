<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Collection;

/**
 * The main menu, built from published pages:
 *  - a section with one page is a plain link,
 *  - a section with several pages is a dropdown,
 *  - Parent Hub is a mega menu, its pages split into groups.
 * Add, rename, reorder or unpublish pages in the admin and the menu follows.
 */
class Navigation
{
    /** Site sections, in menu order. */
    public const SECTIONS = [
        'about' => 'About',
        'teaching-learning' => 'Teaching & Learning',
        'iec' => 'IEC',
        'student-services' => 'Student Services',
        'parent-hub' => 'Parent Hub',
        'enrolments' => 'Enrolments',
        'contact' => 'Contact Us',
    ];

    /** Groups inside the Parent Hub section, in menu order. */
    public const GROUPS = [
        'every-day' => 'Every day',
        'getting-ready' => 'Getting ready',
        'health-safety' => 'Health and safety',
        'stay-connected' => 'Stay connected',
    ];

    /** The section whose pages are shown in groups (mega menu and icon grid). */
    public const GROUPED_SECTION = 'parent-hub';

    private static ?array $menu = null;

    private static ?Collection $pages = null;

    public static function sectionLabel(?string $key): ?string
    {
        return self::SECTIONS[$key] ?? null;
    }

    public static function groupLabel(?string $key): ?string
    {
        return self::GROUPS[$key] ?? null;
    }

    public static function flush(): void
    {
        self::$menu = null;
        self::$pages = null;
    }

    /** All published pages, in order. Loaded once per request. */
    public static function pages(): Collection
    {
        return self::$pages ??= Page::published()
            ->orderBy('sort')->orderBy('title')
            ->get(['id', 'title', 'slug', 'section', 'group', 'sort', 'icon']);
    }

    /** Published pages in a group, e.g. Parent Hub > Getting ready. */
    public static function groupPages(string $group): Collection
    {
        return self::pages()->where('section', self::GROUPED_SECTION)->where('group', $group)->values();
    }

    public static function menu(): array
    {
        if (self::$menu !== null) {
            return self::$menu;
        }

        $menu = [];
        foreach (self::SECTIONS as $key => $label) {
            $pages = self::pages()->where('section', $key)->values();
            if ($pages->isEmpty()) {
                continue;
            }

            $item = ['label' => $label, 'section' => $key, 'href' => '/' . $pages->first()->slug];

            if ($key === self::GROUPED_SECTION) {
                $item['mega'] = [];
                foreach (self::GROUPS as $groupKey => $groupLabel) {
                    $links = $pages->where('group', $groupKey)->map(fn (Page $p) => [
                        'label' => $p->title,
                        'href' => '/' . $p->slug,
                        'icon' => $p->iconUrl(),
                    ])->values()->all();
                    if ($links) {
                        $item['mega'][$groupLabel] = $links;
                    }
                }
                // The hub page itself is the one without a group.
                $hub = $pages->firstWhere('group', null);
                $item['href'] = '/' . ($hub ?? $pages->first())->slug;
            } elseif ($pages->count() > 1) {
                $item['children'] = $pages->map(fn (Page $p) => ['label' => $p->title, 'href' => '/' . $p->slug])->all();
            }

            $menu[] = $item;
        }

        return self::$menu = $menu;
    }
}
