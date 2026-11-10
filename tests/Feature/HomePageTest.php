<?php

namespace Tests\Feature;

use App\Filament\Pages\EditHomePage;
use App\Filament\Pages\EditSiteSettings;
use App\Models\Enquiry;
use App\Models\PageContent;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(ContentSeeder::class);
    }

    public function test_home_page_renders_seeded_content(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Ellenbrook Primary School')
            ->assertSee('Where learning is fun.')
            ->assertSee('Built on community')
            ->assertSee('Find your way around')
            ->assertSee('Send us a message')
            ->assertSee('<video id="hero-video"', false);
    }

    public function test_hero_shows_video_when_video_is_selected(): void
    {
        $home = PageContent::for(PageContent::HOME);
        $home['hero']['media_type'] = 'video';
        $home['hero']['video'] = 'home/video/intro.mp4';
        PageContent::store(PageContent::HOME, $home);

        $this->get('/')
            ->assertOk()
            ->assertSee('<video', false)
            ->assertSee('home/video/intro.mp4')
            ->assertSee('Pause background video');
    }

    public function test_admin_pages_require_login(): void
    {
        $this->get('/admin/home-page')->assertRedirect('/admin/login');
    }

    public function test_admin_can_open_and_save_home_page(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditHomePage::class)
            ->assertOk()
            ->assertSchemaStateSet(['hero.heading' => 'Ellenbrook Primary School'], 'form')
            ->set('data.hero.heading', 'Welcome to Ellenbrook')
            ->set('data.welcome.body', '<p>New <strong>welcome</strong> text</p>')
            ->call('save')
            ->assertHasNoFormErrors();

        $home = PageContent::for(PageContent::HOME);
        $this->assertSame('Welcome to Ellenbrook', $home['hero']['heading']);
        $this->assertStringContainsString('<strong>welcome</strong>', $home['welcome']['body']);
        $this->assertSame('home/hero/poster.webp', $home['hero']['image'], 'Existing image is kept when not changed');

        $this->get('/')->assertSee('Welcome to Ellenbrook')->assertSee('<strong>welcome</strong>', false);
    }

    public function test_hero_image_that_is_too_small_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditHomePage::class)
            ->set('data.hero.image', [UploadedFile::fake()->image('small.jpg', 1200, 600)])
            ->call('save')
            ->assertHasFormErrors(['hero.image']);
    }

    public function test_video_is_required_when_video_background_is_chosen(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditHomePage::class)
            ->set('data.hero.media_type', 'video')
            ->set('data.hero.video', null)
            ->call('save')
            ->assertHasFormErrors(['hero.video' => 'required']);
    }

    public function test_admin_can_open_site_settings(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditSiteSettings::class)
            ->assertOk()
            ->set('data.phone', '(08) 9000 0000')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/')->assertSee('Call (08) 9000 0000')->assertSee('tel:+61890000000', false);
    }

    public function test_enquiry_form_saves_message(): void
    {
        $this->post('/enquiries', [
            'name' => 'Sam Parent',
            'email' => 'sam@example.com',
            'topic' => 'Enrolments',
            'message' => 'When can we visit?',
        ])->assertRedirect()->assertSessionHas('enquiry_sent');

        $this->assertDatabaseHas('enquiries', ['email' => 'sam@example.com', 'topic' => 'Enrolments']);
    }

    public function test_enquiry_honeypot_drops_bots(): void
    {
        $this->post('/enquiries', [
            'name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'spam', 'website' => 'http://spam.test',
        ])->assertRedirect();

        $this->assertSame(0, Enquiry::count());
    }

    public function test_rich_text_is_sanitised_on_output(): void
    {
        $home = PageContent::for(PageContent::HOME);
        $home['welcome']['body'] = '<p>Hi</p><script>alert(1)</script>';
        PageContent::store(PageContent::HOME, $home);

        $this->get('/')->assertDontSee('<script>alert(1)</script>', false);
    }
}
