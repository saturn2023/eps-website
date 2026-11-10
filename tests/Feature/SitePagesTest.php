<?php

namespace Tests\Feature;

use App\Filament\Resources\DocumentSubmissions\Pages\ListDocumentSubmissions;
use App\Filament\Resources\DocumentSubmissions\Pages\ViewDocumentSubmission;
use App\Filament\Resources\Events\Pages\ManageEvents;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\DocumentSubmission;
use App\Models\Event;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\AboutPagesSeeder;
use Database\Seeders\ContentSeeder;
use Database\Seeders\SitePagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->seed([ContentSeeder::class, AboutPagesSeeder::class, SitePagesSeeder::class]);
    }

    public function test_every_prototype_page_exists_and_renders(): void
    {
        $slugs = collect(json_decode(file_get_contents(database_path('seeders/data/pages.json')), true))->pluck('slug')
            ->merge(['our-school', 'principals-welcome', 'vision-values', 'school-board']);

        $this->assertCount(32, $slugs);
        foreach ($slugs as $slug) {
            $this->get('/' . $slug)->assertOk();
        }
    }

    public function test_content_page_with_office_card_callout_and_table(): void
    {
        $this->get('/school-hours')
            ->assertSee('Classroom doors open at 8.30am')
            ->assertSee('<caption>Years 1 to 6 breaks</caption>', false)
            ->assertSee('<th scope="col">Break</th>', false)
            ->assertSee('class="callout"', false)
            ->assertSee('class="aside-card"', false)
            ->assertSee('Need help?');
    }

    public function test_parent_hub_group_tabs_and_breadcrumb(): void
    {
        $response = $this->get('/canteen')
            ->assertSee('<a href="' . url('/parent-hub') . '">Parent Hub</a>', false)
            ->assertDontSee('class="next"', false);                  // no Previous/Next on Parent Hub pages

        // Section tabs: "All Parent Hub" plus only the "Getting ready" group.
        preg_match('#<nav aria-label="In the Parent Hub section" class="sec-nav">(.*?)</nav>#s', $response->getContent(), $m);
        $tabs = strip_tags(str_replace('</a>', '|', $m[1] ?? ''));
        $this->assertSame('All Parent Hub|Book Lists|Uniform Shop|Canteen|Belongings|Bikes, Scooters and Skateboards|', html_entity_decode($tabs));
    }

    public function test_parent_hub_icon_grid_and_mega_menu_are_built_from_pages(): void
    {
        $this->get('/parent-hub')
            ->assertSee('class="hub-grid"', false)
            ->assertSeeInOrder(['Every day', 'Getting ready', 'Health and safety', 'Stay connected']);

        // Unpublish a page: it leaves the grid and the menu.
        Page::where('slug', 'belongings')->first()->update(['is_published' => false]);
        $this->get('/parent-hub')->assertDontSee('/belongings"', false);
    }

    public function test_teaching_learning_has_previous_next(): void
    {
        $this->get('/teaching-learning-handbook')
            ->assertSee('‹ Our Approach')
            ->assertSee('Assessment and Reporting ›');
    }

    public function test_iec_accordion_and_downloads(): void
    {
        $this->get('/iec')
            ->assertSee('class="acc"', false)
            ->assertSee('Ellenbrook IEC bus transport')
            ->assertSee('IEC Enrolment Documents')
            ->assertSee('id="iec-upload"', false);
    }

    public function test_book_lists_link_to_pdfs(): void
    {
        $this->get('/book-lists')
            ->assertSee('Kindergarten book list')
            ->assertSee('Ellenbrook_PS_2026_final_K.pdf')
            ->assertSee('dl-grid', false);
    }

    public function test_calendar_uses_events_from_the_admin(): void
    {
        $this->assertSame(5, Event::count());
        Event::create(['title' => 'Book Week parade', 'starts_on' => '2026-11-20', 'type' => 'event']);

        $this->get('/calendar')
            ->assertSee('window.EPS_EVENTS', false)
            ->assertSee('Book Week parade')
            ->assertSee('"type":"hol"', false);
    }

    public function test_contact_page_shows_details_map_and_form(): void
    {
        $this->get('/contact')
            ->assertSee('class="contact-list"', false)
            ->assertSee('google.com/maps/embed', false)
            ->assertSee('action="' . route('enquiries.store') . '"', false);

        $this->from('/contact')->post('/enquiries', ['name' => 'Ann', 'email' => 'ann@example.com', 'message' => 'Hello'])
            ->assertRedirect(url('/contact') . '#contact');
    }

    public function test_enrolment_documents_can_be_uploaded_and_downloaded_by_staff_only(): void
    {
        $this->from('/enrolments')->post('/documents', [
            'form' => 'enrolment',
            'parent_name' => 'Sam Parent',
            'email' => 'sam@example.com',
            'phone' => '0400 000 000',
            'student_name' => 'Alex Parent',
            'year_level' => 'Year 2',
            'consent' => '1',
            'documents' => [UploadedFile::fake()->create('birth-certificate.pdf', 200, 'application/pdf')],
        ])->assertRedirect(url('/enrolments') . '#enrolment-upload')->assertSessionHas('upload_sent', 'enrolment');

        $submission = DocumentSubmission::sole();
        $this->assertSame('birth-certificate.pdf', $submission->files[0]['name']);
        Storage::disk('local')->assertExists($submission->files[0]['path']);

        // Guests are sent to the login; staff can download.
        $this->get(route('documents.download', [$submission, 0]))->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())
            ->get(route('documents.download', [$submission, 0]))->assertOk()->assertDownload('birth-certificate.pdf');

        // Opening it in the admin marks it read; deleting removes the files.
        Livewire::test(ListDocumentSubmissions::class)->assertCanSeeTableRecords([$submission]);
        Livewire::test(ViewDocumentSubmission::class, ['record' => $submission->getRouteKey()])->assertOk()->assertSee('birth-certificate.pdf');
        $this->assertNotNull($submission->fresh()->read_at);
        $path = $submission->files[0]['path'];
        $submission->delete();
        Storage::disk('local')->assertMissing($path);
    }

    public function test_upload_form_rejects_wrong_file_types_and_missing_consent(): void
    {
        $this->from('/enrolments')->post('/documents', [
            'form' => 'enrolment',
            'parent_name' => 'Sam', 'email' => 'sam@example.com', 'phone' => '1', 'student_name' => 'Alex',
            'documents' => [UploadedFile::fake()->create('virus.exe', 10)],
        ])->assertSessionHasErrors(['documents.0', 'consent']);

        $this->assertSame(0, DocumentSubmission::count());
    }

    public function test_admin_can_manage_events_and_edit_content_pages(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageEvents::class)->assertOk();

        $page = Page::where('slug', 'canteen')->first();
        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertOk()
            ->fillForm(['group' => 'stay-connected'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/canteen')->assertSeeInOrder(['All Parent Hub', 'Newsletters']);
    }

    public function test_home_page_uses_prototype_content(): void
    {
        $this->get('/')
            ->assertSee('<video id="hero-video"', false)
            ->assertSee('media="(max-width: 760px)"', false)
            ->assertSee('class="ql-icon"', false)
            ->assertSee('href="/our-school"', false)
            ->assertSee('href="/enrolments"', false);
    }

    public function test_translate_panel_lists_languages_without_loading_google_up_front(): void
    {
        $this->get('/canteen')
            ->assertSee('<option value="ar">', false)
            ->assertSee('<option value="fa-AF">', false)
            ->assertSee('class="tr-wrap notranslate"', false)
            ->assertDontSee('[Translate widget renders here]')
            ->assertDontSee('translate.google.com/translate_a/element.js', false);  // only loaded by site.js after a choice
    }

    public function test_seeders_never_overwrite_admin_edits(): void
    {
        Page::where('slug', 'canteen')->update(['heading' => 'Edited']);
        Event::query()->delete();
        Event::create(['title' => 'Mine', 'starts_on' => '2026-12-01', 'type' => 'event']);

        $this->seed(SitePagesSeeder::class);

        $this->assertSame('Edited', Page::where('slug', 'canteen')->value('heading'));
        $this->assertSame(['Mine'], Event::pluck('title')->all());
    }
}
