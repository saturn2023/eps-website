<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Fills the home page and site settings with the content from
 * the approved concept and review prototype (assets in database/seeders/assets), so the admin starts
 * populated. Safe to re-run: it only seeds pages that are still empty.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        if (! PageContent::where('key', PageContent::SITE)->exists()) {
            PageContent::store(PageContent::SITE, $this->site());
        } else {
            // Add settings introduced later without touching anything already edited.
            $site = PageContent::for(PageContent::SITE);
            $missing = array_diff_key($this->laterSiteSettings(), $site);
            if ($missing) {
                PageContent::store(PageContent::SITE, $site = $site + $missing);
            }
            // The first build pointed these at "#…" placeholders; now the real pages exist.
            $changed = false;
            if (($site['absence_url'] ?? null) === '#absence') {
                $site['absence_url'] = '/record-an-absence';
                $changed = true;
            }
            if (collect($site['footer_links'] ?? [])->every(fn ($l) => str_starts_with($l['url'] ?? '', '#'))) {
                $site['footer_links'] = $this->footerLinks();
                $changed = true;
            }
            if ($changed) {
                PageContent::store(PageContent::SITE, $site);
            }
        }

        $home = PageContent::firstWhere('key', PageContent::HOME);
        if (! $home) {
            PageContent::store(PageContent::HOME, $this->home());
        } elseif ($home->updated_at->equalTo($home->created_at) && ! isset($home->content['hero']['video_mobile'])) {
            // Still the first concept version and never edited in the admin:
            // move it to the review prototype version (video hero, real page links).
            $home->forceFill(['content' => $this->home()])->save();
        }
    }

    /** Copy a concept image into the public disk and return its stored path. */
    private function image(string $file, string $folder): string
    {
        $path = "{$folder}/{$file}";
        Storage::disk('public')->put($path, file_get_contents(database_path("seeders/assets/concept/{$file}")));

        return $path;
    }

    /** Copy a review-prototype file (images/…, media/…) into the public disk. */
    private function proto(string $file, string $folder): string
    {
        $path = $folder . '/' . basename($file);
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(database_path("seeders/assets/prototype/{$file}")));
        }

        return $path;
    }

    private function site(): array
    {
        return [
            'school_name' => 'Ellenbrook Primary School',
            'motto' => 'a pathway to life-long learning',
            'logo' => $this->image('logo.png', 'site'),
            'phone' => '(08) 9297 7300',
            'email' => 'ellenbrook.ps@education.wa.edu.au',
            'hours_line' => 'School hours Monday to Friday, 8.45am to 2.50pm',
            'absence_url' => '/record-an-absence',
            'address' => "65 Fortescue Pl,\nEllenbrook WA 6069",
            'directions_url' => 'https://maps.google.com/?q=65+Fortescue+Pl+Ellenbrook+WA+6069',
            'footer_hours' => '<p>Monday to Friday<br><strong>8.45am to 2.50pm</strong></p><p>Classroom doors open 8.30am</p><p>Gates open 8.15am</p>',
            'footer_links' => $this->footerLinks(),
            'acknowledgement' => '<p>Ellenbrook Primary School acknowledges the Whadjuk Noongar people as the Traditional Custodians of the land on which we learn and play, and pays respect to Elders past and present. [Confirm wording with the school. Artwork by [artist name], used with permission.]</p>',
            'footer_artwork' => $this->image('footer-artwork.webp', 'site'),
            'footer_logo' => $this->image('footer-logo.webp', 'site'),
        ] + $this->laterSiteSettings();
    }

    private function footerLinks(): array
    {
        return [
            ['label' => 'Enrolments', 'url' => '/enrolments'],
            ['label' => 'Record an absence', 'url' => '/record-an-absence'],
            ['label' => 'Newsletters', 'url' => '/newsletters'],
            ['label' => 'Calendar', 'url' => '/calendar'],
            ['label' => 'Contact Us', 'url' => '/contact'],
        ];
    }

    /** Site settings added with the inner pages. */
    private function laterSiteSettings(): array
    {
        Storage::disk('public')->put('site/roo-navy.webp', file_get_contents(public_path('images/roo-navy.webp')));

        return [
            'page_header_art' => 'site/roo-navy.webp',
            'cta_heading' => 'Have a question about our school?',
            'cta_text' => 'Our office team is happy to help. Call during school hours or send us a message.',
            'cta_message_url' => '/contact#contact',
            'card_heading' => 'Need help?',
            'card_text' => 'The office is open on school days. We are happy to answer your questions.',
            'translate_languages' => \App\Support\Translate::DEFAULT_LANGUAGES,
            'translate_note' => 'Translated automatically by Google. Some words may not be exact.',
        ];
    }

    /** Home page content, from the review prototype's index.html. */
    private function home(): array
    {
        $link = fn (string $label, string $slug) => ['label' => $label, 'url' => '/' . $slug];

        return [
            'hero' => [
                'media_type' => 'video',
                'image' => $this->proto('media/poster.webp', 'home/hero'),
                'video' => $this->proto('media/hero-desktop.mp4', 'home/video'),
                'video_mobile' => $this->proto('media/hero-mobile.mp4', 'home/video'),
                'focus' => 'center',
                'tagline' => 'a Pathway to life-long learning',
                'heading' => 'Ellenbrook Primary School',
                'intro' => 'An Independent Public School in Ellenbrook, offering Kindergarten to Year 6 and an Intensive English Centre.',
            ],
            'quick_links' => [
                'heading' => 'Quick links for families',
                'items' => [
                    ['label' => 'Record an absence', 'url' => '/record-an-absence', 'icon' => 'record-an-absence'],
                    ['label' => 'Newsletters', 'url' => '/newsletters', 'icon' => 'newsletters'],
                    ['label' => 'Canteen', 'url' => '/canteen', 'icon' => 'canteen'],
                    ['label' => 'Uniform shop', 'url' => '/uniform-shop', 'icon' => 'uniform-shop'],
                    ['label' => 'School hours', 'url' => '/school-hours', 'icon' => 'school-hours'],
                    ['label' => 'Book lists', 'url' => '/book-lists', 'icon' => 'book-lists'],
                ],
            ],
            'welcome' => [
                'heading' => 'Where learning is fun.',
                'body' => '<p>Our goal is to see every child reach their potential in a caring and supportive learning environment. Our teachers are hand picked and form a strong team with a focus on your child\'s development.</p><p>Providing children with a safe and stimulating environment enables your child to build strong learning foundations that will help them develop educationally and socially.</p><p>We believe that working together we can provide a great start for our students.</p>',
                'photo' => $this->proto('images/students.webp', 'home/welcome'),
                'photo_alt' => 'Four smiling Year 6 students in Ellenbrook Primary School leavers shirts',
                'art' => $this->proto('images/mandala-navy2.webp', 'home/welcome'),
                'buttons' => [
                    ['label' => 'Enrolments at EPS', 'url' => '/enrolments', 'style' => 'teal'],
                    ['label' => 'View school curriculum', 'url' => '/curriculum-overview', 'style' => 'line'],
                ],
            ],
            'belief' => [
                'icon' => $this->proto('images/bird-white.webp', 'home/belief'),
                'phrase' => 'Working together, we can provide a great start for our students.',
            ],
            'community' => [
                'heading' => 'Built on community',
                'intro' => '<p>The principle of community is what the Ellenbrook area was developed on. We support this principle in how we interact with our students, their parents and the staff at Ellenbrook Primary.</p>',
                'button_label' => 'Read our school history',
                'button_url' => '/our-school',
                'milestones' => [
                    ['year' => '1996', 'text' => 'Opened as the first school in houses in Western Australia, with around 50 students.', 'highlight' => false],
                    ['year' => '1997', 'text' => 'Moved to our current home on Fortescue Place.', 'highlight' => false],
                    ['year' => '2011', 'text' => 'Became an Independent Public School, giving our community a stronger voice in decisions.', 'highlight' => false],
                    ['year' => 'Today', 'text' => '473 students, with some of our early students now returning as parents to enrol their own children.', 'highlight' => true],
                ],
            ],
            'explore' => [
                'heading' => 'Find your way around',
                'columns' => [
                    ['title' => 'About', 'summary' => null, 'full_width' => false, 'links' => [
                        $link('Our School', 'our-school'), $link("Principal's Welcome", 'principals-welcome'),
                        $link('Our Vision and Values', 'vision-values'), $link('School Board', 'school-board'),
                    ]],
                    ['title' => 'Teaching & Learning', 'summary' => null, 'full_width' => false, 'links' => [
                        $link('Our Approach', 'teaching-learning'), $link('Teaching and Learning Handbook', 'teaching-learning-handbook'),
                        $link('Assessment and Reporting', 'assessment-reporting'), $link('Curriculum Overview', 'curriculum-overview'),
                    ]],
                    ['title' => 'IEC', 'summary' => null, 'full_width' => false, 'links' => [
                        $link('About the Intensive English Centre', 'iec'), $link('IEC enrolments', 'enrolments#iec-enrolments'),
                    ]],
                    ['title' => 'Enrolments', 'summary' => null, 'full_width' => false, 'links' => [
                        $link('School Enrolments', 'enrolments'), $link('Contact Us', 'contact'), $link('Student Services', 'student-services'),
                    ]],
                    ['title' => 'Parent Hub', 'summary' => null, 'full_width' => true, 'links' => [
                        $link('Record an Absence', 'record-an-absence'), $link('School Hours', 'school-hours'),
                        $link('Gate Opening Times', 'gate-opening-times'), $link('Collecting Students', 'collecting-students'),
                        $link('Calendar', 'calendar'), $link('Book Lists', 'book-lists'), $link('Uniform Shop', 'uniform-shop'),
                        $link('Canteen', 'canteen'), $link('Belongings', 'belongings'),
                        $link('Bikes, Scooters and Skateboards', 'bikes-scooters-skateboards'),
                        $link('Medical Information', 'medical-information'), $link('Emergency Contact', 'emergency-contact'),
                        $link('Child Custody and Access', 'child-custody-access'), $link('Lost Property', 'lost-property'),
                        $link('Newsletters', 'newsletters'), $link('Assemblies', 'assemblies'), $link('Library', 'library'),
                        $link('P&C', 'p-and-c'), $link('Outside School Hours', 'outside-school-hours'),
                    ]],
                ],
            ],
            'enrol' => [
                'heading' => 'Start your child\'s journey at Ellenbrook Primary',
                'body' => '<p>Enrolments are open for Kindergarten to Year 6. Our office team can walk you through the process and answer any questions.</p>',
                'primary_label' => 'Start an enrolment',
                'primary_url' => '/enrolments',
                'show_call_button' => true,
                'art' => $this->proto('images/roo-navy.webp', 'home/enrol'),
                'form_heading' => 'Send us a message',
                'form_intro' => 'Questions about enrolments or school life? Send us a message and the office will get back to you.',
                'form_success' => 'Thanks for your message. The office will get back to you soon.',
                'topics' => ['Enrolments', 'Intensive English Centre (IEC)', 'Parent Hub question', 'Something else'],
            ],
            'seo' => [
                'title' => 'Ellenbrook Primary School | Kindergarten to Year 6 in Ellenbrook, WA',
                'description' => 'An Independent Public School in Ellenbrook, offering Kindergarten to Year 6 and an Intensive English Centre.',
            ],
        ];
    }
}