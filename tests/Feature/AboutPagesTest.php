<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\AboutPagesSeeder;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AboutPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed([ContentSeeder::class, AboutPagesSeeder::class]);
    }

    public function test_all_four_about_pages_render(): void
    {
        $this->get('/our-school')->assertOk()
            ->assertSee('Ellenbrook Primary School History')
            ->assertSee('Our journey so far')
            ->assertSee('Preparing our students for life-long learning.')
            ->assertSee('The pursuit of excellence');

        $this->get('/principals-welcome')->assertOk()
            ->assertSee('Our operational style')
            ->assertSee('class="ol-cards"', false)
            ->assertSee('Business Plan 2023 to 2027')
            ->assertSee('Photo of Mr Tony Shields to be supplied');

        $this->get('/vision-values')->assertOk()
            ->assertSee('Our school values')
            ->assertSee('<span style="color:#3A9A48">A</span>CTIVE <span style="color:#3A9A48">L</span>EARNER', false)
            ->assertSee('Quality, Timely Feedback');

        $this->get('/school-board')->assertOk()
            ->assertSee('School Board members')
            ->assertSee('>AS<', false)  // initials from "Anthony (Tony) Shields"
            ->assertSee('Liz Dolliver');
    }

    public function test_section_tabs_breadcrumb_and_previous_next(): void
    {
        $this->get('/principals-welcome')
            ->assertSee('class="sec-nav"', false)
            ->assertSeeInOrder(['Our School', "Principal&#039;s Welcome", 'Our Vision and Values', 'School Board'], false)
            ->assertSee('‹ Our School')
            ->assertSee('Our Vision and Values ›')
            ->assertSee('<a class="nl on"', false);
    }

    public function test_unpublished_and_unknown_pages_404(): void
    {
        Page::where('slug', 'school-board')->update(['is_published' => false]);

        $this->get('/school-board')->assertNotFound();
        $this->get('/no-such-page')->assertNotFound();
        $this->get('/vision-values')->assertDontSee('School Board ›');
    }

    public function test_admin_route_is_not_swallowed_by_pages(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_list_and_edit_a_page(): void
    {
        $this->actingAs(User::factory()->create());
        $page = Page::where('slug', 'school-board')->first();

        Livewire::test(ListPages::class)->assertOk()->assertCanSeeTableRecords([$page]);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertOk()
            ->fillForm(['heading' => 'Our School Board', 'intro' => 'Who helps steer the school.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/school-board')->assertSee('Our School Board')->assertSee('Who helps steer the school.');
    }

    public function test_admin_can_create_a_page_with_blocks(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Library',
                'slug' => 'library',
                'section' => 'parent-hub',
                'sort' => 5,
                'is_published' => true,
                'blocks' => [
                    ['type' => 'text', 'data' => ['lead' => 'Our library is open every day.', 'body' => '<p>Borrow up to three books.</p>']],
                    ['type' => 'phrase', 'data' => ['phrase' => 'Reading opens doors.']],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/library')->assertOk()
            ->assertSee('Our library is open every day.')
            ->assertSee('Borrow up to three books.')
            ->assertSee('Reading opens doors.');
    }

    public function test_reserved_and_badly_formed_slugs_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Admin', 'slug' => 'admin', 'section' => 'about'])
            ->call('create')
            ->assertHasFormErrors(['slug']);

        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Bad', 'slug' => 'Bad Slug!', 'section' => 'about'])
            ->call('create')
            ->assertHasFormErrors(['slug']);

        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Dup', 'slug' => 'our-school', 'section' => 'about'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_seeder_does_not_overwrite_edits(): void
    {
        Page::where('slug', 'our-school')->update(['heading' => 'Edited heading']);

        $this->seed(AboutPagesSeeder::class);

        $this->assertSame('Edited heading', Page::where('slug', 'our-school')->value('heading'));
    }
}
