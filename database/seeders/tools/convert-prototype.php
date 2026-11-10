<?php

/**
 * One-off converter: turns the review prototype's HTML pages into page/block
 * data for the seeder. Run from the project root:
 *
 *   php database/seeders/tools/convert-prototype.php
 *
 * Reads  resources/concept/review-site/eps-review-site/*.html
 * Writes database/seeders/data/pages.json and events.json
 */

$root = dirname(__DIR__, 3);
$src = "$root/resources/concept/review-site/eps-review-site";
$outDir = "$root/database/seeders/data";

// slug => [section, group, sort]. About pages are seeded separately (AboutPagesSeeder).
$map = [
    'teaching-learning' => ['teaching-learning', null, 1],
    'teaching-learning-handbook' => ['teaching-learning', null, 2],
    'assessment-reporting' => ['teaching-learning', null, 3],
    'curriculum-overview' => ['teaching-learning', null, 4],
    'iec' => ['iec', null, 1],
    'student-services' => ['student-services', null, 1],
    'parent-hub' => ['parent-hub', null, 0],
    'record-an-absence' => ['parent-hub', 'every-day', 1],
    'school-hours' => ['parent-hub', 'every-day', 2],
    'gate-opening-times' => ['parent-hub', 'every-day', 3],
    'collecting-students' => ['parent-hub', 'every-day', 4],
    'calendar' => ['parent-hub', 'every-day', 5],
    'book-lists' => ['parent-hub', 'getting-ready', 6],
    'uniform-shop' => ['parent-hub', 'getting-ready', 7],
    'canteen' => ['parent-hub', 'getting-ready', 8],
    'belongings' => ['parent-hub', 'getting-ready', 9],
    'bikes-scooters-skateboards' => ['parent-hub', 'getting-ready', 10],
    'medical-information' => ['parent-hub', 'health-safety', 11],
    'emergency-contact' => ['parent-hub', 'health-safety', 12],
    'child-custody-access' => ['parent-hub', 'health-safety', 13],
    'lost-property' => ['parent-hub', 'health-safety', 14],
    'newsletters' => ['parent-hub', 'stay-connected', 15],
    'assemblies' => ['parent-hub', 'stay-connected', 16],
    'library' => ['parent-hub', 'stay-connected', 17],
    'p-and-c' => ['parent-hub', 'stay-connected', 18],
    'outside-school-hours' => ['parent-hub', 'stay-connected', 19],
    'enrolments' => ['enrolments', null, 1],
    'contact' => ['contact', null, 1],
];

/** Rewrite prototype links (canteen.html#x) to site links (/canteen#x). */
function fixLinks(string $html): string
{
    // Inline styles belong to the prototype layout, not the content.
    $html = preg_replace('/\s+style="[^"]*"/', '', $html);

    return preg_replace_callback('/href="([a-z0-9-]+)\.html(#[^"]*)?"/', function ($m) {
        $path = $m[1] === 'index' ? '/' : '/' . $m[1];

        return 'href="' . $path . ($m[2] ?? '') . '"';
    }, $html);
}

function inner(DOMNode $node): string
{
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }

    return trim(fixLinks($html));
}

function outer(DOMNode $node): string
{
    return trim(fixLinks($node->ownerDocument->saveHTML($node)));
}

function hasClass(DOMNode $node, string $class): bool
{
    return $node instanceof DOMElement && in_array($class, preg_split('/\s+/', $node->getAttribute('class')), true);
}

function text(?DOMNode $node): string
{
    return $node ? trim(preg_replace('/\s+/', ' ', $node->textContent)) : '';
}

function first(DOMXPath $xp, string $query, ?DOMNode $ctx = null): ?DOMNode
{
    return $xp->query($query, $ctx)->item(0);
}

function downloadsFrom(DOMXPath $xp, DOMElement $ul): array
{
    $files = [];
    foreach ($xp->query('.//a[contains(concat(" ",@class," ")," dl ")]', $ul) as $a) {
        $small = first($xp, './/small', $a);
        $label = trim(str_replace(text($small), '', text(first($xp, './/span[contains(concat(" ",@class," ")," t ")]', $a))));
        $href = $a->getAttribute('href');
        if (preg_match('/^([a-z0-9-]+)\.html(#.*)?$/', $href, $m)) {
            $href = '/' . $m[1] . ($m[2] ?? '');
        }
        $files[] = [
            'label' => $label,
            'note' => text($small) ?: null,
            'file' => null,
            'url' => ($href && $href !== '#') ? $href : null,
        ];
    }

    return $files;
}

$events = [];
$pages = [];

foreach ($map as $slug => [$section, $group, $sort]) {
    $html = file_get_contents("$src/$slug.html");
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_NOERROR);
    $xp = new DOMXPath($doc);

    // The page's own name: its tab in the section tabs, else the breadcrumb.
    $title = text(first($xp, '//nav[contains(@class,"sec-nav")]/a[@aria-current="page"]'))
        ?: text(first($xp, '//nav[contains(@class,"crumb")]/span[@aria-current="page"]'));
    $h1 = text(first($xp, '//h1[@id="page-h1"]'));
    $intro = text(first($xp, '//div[contains(@class,"title-row")]//h1/following-sibling::p'));
    $icon = first($xp, '//img[contains(@class,"page-icon")]')?->getAttribute('src');
    $desc = html_entity_decode(first($xp, '//meta[@name="description"]')?->getAttribute('content') ?? '', ENT_QUOTES | ENT_HTML5);
    $content = first($xp, '//section[contains(@class,"content-wrap")]');
    $hasAside = (bool) first($xp, './/aside[contains(@class,"aside-card")]', $content);
    $prose = first($xp, './/div[contains(@class,"prose")]', $content);

    $blocks = [];
    $text = null;
    $flush = function () use (&$text, &$blocks) {
        if ($text && (trim($text['body']) !== '' || $text['lead'])) {
            $blocks[] = ['type' => 'text', 'data' => $text];
        }
        $text = null;
    };
    $ensureText = function () use (&$text) {
        $text ??= ['lead' => null, 'body' => '', 'numbered_cards' => false, 'downloads' => [], 'last' => null];
    };

    if ($slug === 'parent-hub') {
        $blocks[] = ['type' => 'page_links', 'data' => []];
    } elseif ($slug === 'contact') {
        $blocks[] = ['type' => 'contact_details', 'data' => [
            'lead' => text(first($xp, './/p[contains(@class,"lead")]', $prose)),
            'hours' => null,
            'map_embed_url' => first($xp, './/iframe', $prose)?->getAttribute('src'),
        ]];
        $form = first($xp, './/form[@id="contact"]', $prose);
        $blocks[] = ['type' => 'contact_form', 'data' => [
            'heading' => text(first($xp, './/h2', $form)),
            'intro' => text(first($xp, './/p', $form)),
            'success' => 'Thanks for your message. The office will get back to you soon.',
            'topics' => array_map('text', iterator_to_array($xp->query('.//option', $form))),
        ]];
    } else {
        foreach ($prose->childNodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $tag = $node->tagName;

            if ($tag === 'p' && hasClass($node, 'lead')) {
                $flush();
                $ensureText();
                $text['lead'] = text($node);
            } elseif ($tag === 'p' && ($btn = first($xp, './a[contains(@class,"btn")]', $node)) && text($node) === text($btn)) {
                $flush();
                $href = $btn->getAttribute('href');
                $blocks[] = ['type' => 'button', 'data' => [
                    'label' => text($btn),
                    'url' => $href === '#' ? null : $href,
                    'style' => hasClass($btn, 'btn-line') ? 'line' : 'teal',
                    'note' => null,
                ]];
            } elseif ($tag === 'p' && hasClass($node, 'note-pending')) {
                $last = array_key_last($blocks);
                if ($last !== null && $blocks[$last]['type'] === 'button') {
                    $blocks[$last]['data']['note'] = text($node);
                }
            } elseif (hasClass($node, 'callout')) {
                $flush();
                $blocks[] = ['type' => 'callout', 'data' => ['body' => inner($node)]];
            } elseif (hasClass($node, 'dl-list')) {
                // An h2 right before a download list becomes the list's heading.
                $heading = null;
                if ($text && $text['last'] && str_starts_with($text['last'], '<h2')) {
                    $heading = trim(strip_tags($text['last']));
                    $text['body'] = substr($text['body'], 0, -strlen($text['last']));
                }
                $flush();
                $blocks[] = ['type' => 'downloads', 'data' => [
                    'heading' => $heading,
                    'intro' => null,
                    'two_columns' => hasClass($node, 'dl-grid'),
                    'files' => downloadsFrom($xp, $node),
                ]];
            } elseif (hasClass($node, 'tbl-wrap')) {
                $flush();
                $rows = [];
                foreach ($xp->query('.//tbody/tr', $node) as $tr) {
                    $rows[] = ['cells' => array_map('text', iterator_to_array($xp->query('./th|./td', $tr)))];
                }
                $blocks[] = ['type' => 'table', 'data' => [
                    'caption' => text(first($xp, './/caption', $node)),
                    'headers' => array_map('text', iterator_to_array($xp->query('.//thead//th', $node))),
                    'rows' => $rows,
                ]];
            } elseif ($tag === 'ol' && hasClass($node, 'points')) {
                $ensureText();
                $node->removeAttribute('class');
                $chunk = outer($node);
                $text['body'] .= $chunk;
                $text['last'] = $chunk;
                $text['numbered_cards'] = true;
            } elseif (hasClass($node, 'chips-list')) {
                $flush();
                $blocks[] = ['type' => 'chips', 'data' => ['chips' => array_map('text', iterator_to_array($xp->query('./li', $node)))]];
            } elseif (hasClass($node, 'acc')) {
                $flush();
                $items = [];
                foreach ($xp->query('./details', $node) as $d) {
                    $summary = first($xp, './summary', $d);
                    foreach ($xp->query('.//span', $summary) as $span) {
                        $span->parentNode->removeChild($span);
                    }
                    $items[] = ['title' => text($summary), 'body' => inner(first($xp, './/div[contains(@class,"acc-body")]', $d))];
                }
                $blocks[] = ['type' => 'accordion', 'data' => ['heading' => null, 'items' => $items]];
            } elseif (hasClass($node, 'feature-row')) {
                $flush();
                $h2 = first($xp, './/h2', $node);
                $blocks[] = ['type' => 'feature', 'data' => [
                    'icon_src' => first($xp, './/img', $node)?->getAttribute('src'),
                    'heading' => text($h2),
                    'body' => outer(first($xp, './/p', $node)),
                ]];
            } elseif (hasClass($node, 'soon')) {
                $flush();
                $p = first($xp, './/p', $node);
                $strong = first($xp, './/strong', $p);
                $heading = text($strong);
                $strong->parentNode->removeChild($strong);
                $blocks[] = ['type' => 'coming_soon', 'data' => ['heading' => $heading, 'body' => '<p>' . inner($p) . '</p>', 'icon' => null]];
            } elseif ($tag === 'form' && hasClass($node, 'upload')) {
                $flush();
                $blocks[] = ['type' => 'upload_form', 'data' => [
                    'form' => first($xp, './/input[@name="form"]', $node)->getAttribute('value'),
                    'heading' => text(first($xp, './/h3', $node)),
                    'hint' => text(first($xp, './/p[contains(@class,"hint")]', $node)),
                    'year_levels' => array_values(array_filter(array_map('text', iterator_to_array($xp->query('.//select/option', $node))), fn ($o) => $o !== 'Select')),
                    'consent' => trim(rtrim(text(first($xp, './/label[contains(@class,"check")]/span', $node)), '*')),
                    'success' => 'Thank you. Your documents have been sent to the school office.',
                ]];
            } elseif ($tag === 'script') {
                // Calendar events
                if (preg_match_all('/\{start:"([^"]+)"(?:, end:"([^"]+)")?(?:, time:"([^"]+)")?, title:"([^"]+)"(?:, type:"([^"]+)")?\}/', $node->textContent, $m, PREG_SET_ORDER)) {
                    foreach ($m as $e) {
                        $events[] = [
                            'title' => $e[4],
                            'starts_on' => $e[1],
                            'ends_on' => $e[2] ?: null,
                            'time' => $e[3] ?: null,
                            'type' => ['hol' => 'holiday', 'sd' => 'development_day'][$e[5] ?? ''] ?? 'event',
                        ];
                    }
                }
            } elseif (hasClass($node, 'cal-head') || hasClass($node, 'cal') || hasClass($node, 'cal-legend') || hasClass($node, 'cal-list')) {
                if (hasClass($node, 'cal-head')) {
                    $flush();
                    $blocks[] = ['type' => 'calendar', 'data' => ['list_heading' => 'Events this month']];
                }
            } elseif ($slug === 'calendar' && $tag === 'h2') {
                // "Events this month" heading is part of the calendar block.
            } else {
                $ensureText();
                $chunk = outer($node);
                $text['body'] .= $chunk;
                $text['last'] = $chunk;
            }
        }
        $flush();
    }

    // Drop the converter's bookkeeping key.
    foreach ($blocks as &$b) {
        unset($b['data']['last']);
    }
    unset($b);

    $pages[] = [
        'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5),
        'slug' => $slug,
        'section' => $section,
        'group' => $group,
        'sort' => $sort,
        'heading' => ($h1 && $h1 !== $title) ? $h1 : null,
        'intro' => $intro ?: null,
        'icon_src' => $icon,
        'show_contact_card' => $hasAside,
        'seo_description' => $desc ?: null,
        'blocks' => $blocks,
    ];
}

@mkdir($outDir, 0777, true);
file_put_contents("$outDir/pages.json", json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
file_put_contents("$outDir/events.json", json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo count($pages) . " pages, " . count($events) . " events\n";
foreach ($pages as $p) {
    echo str_pad($p['slug'], 28) . ' ' . implode(', ', array_map(fn ($b) => $b['type'], $p['blocks'])) . "\n";
}
