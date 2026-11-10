<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * The rest of the site (Teaching & Learning, IEC, Parent Hub, Enrolments, Contact...),
 * from database/seeders/data/pages.json — generated from the review prototype by
 * database/seeders/tools/convert-prototype.php.
 *
 * Only creates pages that don't exist yet, so edits made in the admin are never overwritten.
 */
class SitePagesSeeder extends Seeder
{
    private const SRC = 'seeders/assets/prototype/';

    public function run(): void
    {
        $pages = json_decode(file_get_contents(database_path('seeders/data/pages.json')), true);

        foreach ($pages as $page) {
            if (Page::where('slug', $page['slug'])->exists()) {
                continue;
            }

            $page['icon'] = $this->image($page['icon_src'] ?? null);
            unset($page['icon_src']);

            foreach ($page['blocks'] as &$block) {
                if (array_key_exists('icon_src', $block['data'])) {
                    $block['data']['icon'] = $this->image($block['data']['icon_src']);
                    unset($block['data']['icon_src']);
                }
            }
            unset($block);

            Page::create($page + ['is_published' => true]);
        }

        if (Event::count() === 0) {
            foreach (json_decode(file_get_contents(database_path('seeders/data/events.json')), true) as $event) {
                Event::create($event);
            }
        }
    }

    /** Copy a prototype image (e.g. images/icons/canteen.png) to the public disk. */
    private function image(?string $src): ?string
    {
        if (! $src || ! is_file($file = database_path(self::SRC . $src))) {
            return null;
        }
        $path = 'pages/icons/' . basename($src);
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents($file));
        }

        return $path;
    }
}
