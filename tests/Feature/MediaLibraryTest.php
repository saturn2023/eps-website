<?php

namespace Tests\Feature;

use App\Filament\Pages\EditHomePage;
use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\Media;
use App\Models\PageContent;
use App\Models\User;
use Database\Seeders\AboutPagesSeeder;
use Database\Seeders\ContentSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed([ContentSeeder::class, AboutPagesSeeder::class]);
        $this->actingAs($this->user = User::factory()->create(['name' => 'Office Admin']));
    }

    private User $user;

    public function test_library_lists_existing_site_files_with_their_details(): void
    {
        $this->assertSame(0, Media::count());

        Livewire::test(ManageMedia::class)->assertOk();     // opening the library scans the disk

        $photo = Media::firstWhere('path', 'home/welcome/students.webp');
        $this->assertNotNull($photo);
        $this->assertSame('image/webp', $photo->mime);
        $this->assertGreaterThan(0, $photo->width);
        $this->assertSame(['Home page'], $photo->usages());
        $this->assertGreaterThan(0, Media::ofKind('video')->count());

        Livewire::test(ManageMedia::class)
            ->assertCanSeeTableRecords([$photo])
            ->filterTable('kind', 'video')
            ->assertCanNotSeeTableRecords([$photo]);
    }

    public function test_clicking_a_file_edits_alt_text_that_the_website_then_uses(): void
    {
        Media::sync();
        $photo = Media::firstWhere('path', 'home/welcome/students.webp');

        // The home page field has its own alt text; clear it so the library's is used.
        $home = PageContent::for(PageContent::HOME);
        $home['welcome']['photo_alt'] = null;
        PageContent::store(PageContent::HOME, $home);

        // The details panel's info block (rendered on its own, as the modal does).
        $info = view('filament.media.info', ['getRecord' => fn () => $photo])->render();
        $this->assertStringContainsString('Website setup', $info);   // added by the seeders
        $this->assertStringContainsString('Home page', $info);       // "Used on"
        $this->assertStringContainsString("{$photo->width} × {$photo->height} pixels", $info);

        Livewire::test(ManageMedia::class)
            ->callAction(TestAction::make('edit')->table($photo), [
                'alt' => 'Students smiling in leavers shirts',
                'title' => 'Year 6 leavers',
                'caption' => 'Class of 2026',
                'description' => 'Taken at the leavers assembly.',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame('Year 6 leavers', $photo->fresh()->title);
        $this->get('/')->assertSee('alt="Students smiling in leavers shirts"', false);
    }

    public function test_files_can_be_uploaded_to_the_library(): void
    {
        Livewire::test(ManageMedia::class)
            ->callAction('upload', ['files' => [
                UploadedFile::fake()->image('School Concert.jpg', 1600, 900),
                UploadedFile::fake()->create('Term 4 newsletter.pdf', 300, 'application/pdf'),
            ]])
            ->assertHasNoFormErrors()
            ->assertNotified('2 files uploaded');

        $image = Media::firstWhere('name', 'School Concert.jpg');
        $this->assertSame(1600, $image->width);
        $this->assertSame('School Concert', $image->title);
        $this->assertSame($this->user->id, $image->uploaded_by);
        $this->assertStringStartsWith('library/' . now()->format('Y/m') . '/', $image->path);
        $this->assertSame(['document'], [Media::firstWhere('name', 'Term 4 newsletter.pdf')->kind()]);
    }

    public function test_replacing_a_file_updates_every_page_that_uses_it(): void
    {
        Media::sync();
        $photo = Media::firstWhere('path', 'home/welcome/students.webp');
        $photo->update(['alt' => 'Students in leavers shirts', 'title' => 'Leavers']);

        Livewire::test(ManageMedia::class)
            ->callAction([
                TestAction::make('edit')->table($photo),
                TestAction::make('replace'),
            ], ['file' => [UploadedFile::fake()->image('new-students.jpg', 1500, 1000)]])
            ->assertHasNoErrors()
            ->assertNotified('File replaced');

        $photo->refresh();
        $this->assertNotSame('home/welcome/students.webp', $photo->path);
        $this->assertStringStartsWith('home/welcome/', $photo->path);
        $this->assertSame('new-students.jpg', $photo->name);
        $this->assertSame([1500, 1000], [$photo->width, $photo->height]);
        $this->assertSame(['Students in leavers shirts', 'Leavers'], [$photo->alt, $photo->title]);
        $this->assertSame(1, Media::where('path', $photo->path)->count());

        Storage::disk('public')->assertMissing('home/welcome/students.webp');
        $this->assertSame($photo->path, PageContent::for(PageContent::HOME)['welcome']['photo']);
        $this->get('/')->assertSee($photo->path)->assertDontSee('home/welcome/students.webp');
    }

    public function test_deleting_a_file_removes_it_from_disk(): void
    {
        Storage::disk('public')->put('library/old.png', UploadedFile::fake()->image('old.png')->getContent());
        $media = Media::register('library/old.png');

        Livewire::test(ManageMedia::class)->callAction(TestAction::make('delete')->table($media));

        $this->assertModelMissing($media);
        Storage::disk('public')->assertMissing('library/old.png');
    }

    public function test_image_field_can_choose_a_file_from_the_library(): void
    {
        Storage::disk('public')->put('library/new-hero.jpg', UploadedFile::fake()->image('new-hero.jpg', 2400, 1200)->getContent());
        $media = Media::register('library/new-hero.jpg');

        Livewire::test(EditHomePage::class)
            ->assertSee('Choose from library')
            ->callAction(TestAction::make('chooseFromLibrary')->schemaComponent('hero.image'), ['media_id' => $media->id])
            ->assertHasNoErrors()
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('library/new-hero.jpg', PageContent::for(PageContent::HOME)['hero']['image']);
        $this->assertSame(['Home page'], $media->usages());
    }

    public function test_files_uploaded_through_page_fields_join_the_library(): void
    {
        $before = PageContent::for(PageContent::HOME)['belief']['icon'];

        // Remove the current artwork, then upload a new file, as an editor would.
        Livewire::test(EditHomePage::class)
            ->set('data.belief.icon', null)
            ->set('data.belief.icon', [UploadedFile::fake()->image('bird-icon.png', 300, 300)])
            ->call('save')
            ->assertHasNoFormErrors();

        $path = PageContent::for(PageContent::HOME)['belief']['icon'];
        $this->assertNotSame($before, $path);
        $media = Media::firstWhere('path', $path);
        $this->assertNotNull($media, "Uploaded file {$path} is not in the library");
        $this->assertSame('bird-icon.png', $media->name);
        $this->assertSame(300, $media->width);
        $this->assertSame($this->user->id, $media->uploaded_by);
    }
}
