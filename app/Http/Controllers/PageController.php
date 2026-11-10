<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageContent;
use App\Support\Navigation;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();
        $siblings = $page->siblings();

        $secNav = collect();
        $previous = $next = null;

        if ($page->section === Navigation::GROUPED_SECTION) {
            // Parent Hub: the hub page is the one without a group. Grouped pages
            // show "All Parent Hub" plus the other pages in their group.
            $sectionHome = $siblings->firstWhere('group', null) ?? $siblings->first();
            if ($page->group) {
                $secNav = collect([['label' => 'All ' . $page->sectionLabel(), 'url' => $sectionHome->url(), 'current' => false]])
                    ->concat($siblings->where('group', $page->group)->map(fn (Page $p) => [
                        'label' => $p->title, 'url' => $p->url(), 'current' => $p->id === $page->id,
                    ]));
            }
        } else {
            $sectionHome = $siblings->first();
            if ($siblings->count() > 1) {
                $secNav = $siblings->map(fn (Page $p) => ['label' => $p->title, 'url' => $p->url(), 'current' => $p->id === $page->id]);
                $index = $siblings->search(fn (Page $p) => $p->id === $page->id);
                $previous = $index > 0 ? $siblings[$index - 1] : null;
                $next = $siblings[$index + 1] ?? null;
            }
        }

        return view('page', [
            'page' => $page,
            'site' => PageContent::for(PageContent::SITE),
            'secNav' => $secNav->values(),
            'previous' => $previous,
            'next' => $next,
            'sectionHome' => $sectionHome,
            'currentSection' => $page->section,
        ]);
    }
}
