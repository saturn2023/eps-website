<?php

namespace App\Filament\Pages;

use App\Models\PageContent;
use App\Support\QuickLinkIcons;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class EditHomePage extends ContentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Home page';

    protected static ?string $title = 'Home page';

    protected static ?string $slug = 'home-page';

    protected static string|\UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 1;

    protected static function contentKey(): string
    {
        return PageContent::HOME;
    }

    protected function fields(): array
    {
        return [
            Tabs::make('Home page')
                ->vertical()
                ->persistTabInQueryString()
                ->tabs([
                    $this->heroTab(),
                    $this->quickLinksTab(),
                    $this->welcomeTab(),
                    $this->beliefTab(),
                    $this->communityTab(),
                    $this->exploreTab(),
                    $this->enrolTab(),
                    $this->seoTab(),
                ]),
        ];
    }

    private function heroTab(): Tab
    {
        return Tab::make('1. Hero')
            ->schema([
                Radio::make('hero.media_type')
                    ->label('Background')
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video (with the image as its poster frame)',
                    ])
                    ->default('image')
                    ->inline()
                    ->live()
                    ->required(),

                static::image(
                    'hero.image',
                    'Hero image',
                    'Landscape, at least 1800px wide (2400px is ideal), JPG or WebP. The left side sits behind the text, '
                    . 'so keep faces and key detail to the right. When a video is used, this image shows while the video loads '
                    . 'and for visitors who have turned off motion.',
                    'home/hero',
                )
                    ->required()
                    ->rules(['dimensions:min_width=1800']),

                static::withMediaLibrary(FileUpload::make('hero.video'), 'video')
                    ->label('Hero video')
                    ->helperText('MP4 (H.264), 1920 × 1080, 10–30 seconds, under 20 MB. It plays muted on a loop, so it '
                        . 'should not need sound. Visitors get a pause button. Leave text out of the video, because the '
                        . 'headline sits on top of it.')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('home/video')
                    ->acceptedFileTypes(['video/mp4'])
                    ->maxSize(20480)
                    ->validationMessages(['max' => 'This video is over 20 MB. Please compress it and upload again.'])
                    ->visible(fn (Get $get): bool => $get('hero.media_type') === 'video')
                    ->required(fn (Get $get): bool => $get('hero.media_type') === 'video'),

                static::withMediaLibrary(FileUpload::make('hero.video_mobile'), 'video')
                    ->label('Hero video for phones (optional)')
                    ->helperText('Portrait or square MP4, about 720 × 1280, under 10 MB. Phones play this smaller file instead. '
                        . 'Leave empty to use the main video everywhere.')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('home/video')
                    ->acceptedFileTypes(['video/mp4'])
                    ->maxSize(10240)
                    ->validationMessages(['max' => 'This video is over 10 MB. Please compress it and upload again.'])
                    ->visible(fn (Get $get): bool => $get('hero.media_type') === 'video'),

                Select::make('hero.focus')
                    ->label('Keep in view on small screens')
                    ->helperText('On phones the background is cropped. Choose which part of it to keep.')
                    ->options(['left' => 'Left side', 'center' => 'Centre', 'right' => 'Right side'])
                    ->default('left')
                    ->selectablePlaceholder(false),

                TextInput::make('hero.tagline')
                    ->label('Tagline')
                    ->helperText('Short line in italic script above the heading.')
                    ->maxLength(60),

                TextInput::make('hero.heading')
                    ->label('Heading (this is the page H1)')
                    ->helperText('The main heading search engines read. Keep the school name in it.')
                    ->required()
                    ->maxLength(60),

                Textarea::make('hero.intro')
                    ->label('Intro')
                    ->helperText('One or two sentences. Up to 200 characters.')
                    ->rows(3)
                    ->maxLength(200),
            ]);
    }

    private function quickLinksTab(): Tab
    {
        return Tab::make('2. Quick links')
            ->schema([
                TextInput::make('quick_links.heading')
                    ->label('Small heading')
                    ->default('Quick links for families')
                    ->maxLength(60),

                Repeater::make('quick_links.items')
                    ->label('Links')
                    ->helperText('Up to 6. They sit in one row on desktop, in the white card that overlaps the hero.')
                    ->schema([
                        TextInput::make('label')->label('Label')->required()->maxLength(30),
                        static::link('url')->required(),
                        Select::make('icon')
                            ->label('Icon')
                            ->options(QuickLinkIcons::options())
                            ->required(),
                    ])
                    ->columns(3)
                    ->maxItems(6)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->addActionLabel('Add quick link'),
            ]);
    }

    private function welcomeTab(): Tab
    {
        return Tab::make('3. Welcome')
            ->schema([
                TextInput::make('welcome.heading')
                    ->label('Heading')
                    ->required()
                    ->maxLength(60),

                static::richText('welcome.body', 'Text'),

                Grid::make(2)->schema([
                    static::image(
                        'welcome.photo',
                        'Photo',
                        'Landscape 3:2, at least 900 × 600px. Shown with rounded corners and a white border.',
                        'home/welcome',
                    )
                        ->rules(['dimensions:min_width=900,min_height=600'])
                        ->imageEditorAspectRatioOptions(['3:2']),

                    static::image(
                        'welcome.art',
                        'Background artwork',
                        'Decorative artwork behind the photo. PNG or WebP with a transparent background, about 750px wide.',
                        'home/welcome',
                    ),
                ]),

                TextInput::make('welcome.photo_alt')
                    ->label('Photo description (alt text)')
                    ->helperText('Describe the photo for people using screen readers, e.g. "Four smiling Year 6 students in leavers shirts".')
                    ->maxLength(150)
                    ->required(fn (Get $get): bool => filled($get('welcome.photo'))),

                Repeater::make('welcome.buttons')
                    ->label('Buttons')
                    ->schema([
                        TextInput::make('label')->label('Button text')->required()->maxLength(40),
                        static::link('url')->required(),
                        Select::make('style')
                            ->label('Style')
                            ->options(['teal' => 'Teal (filled)', 'line' => 'Outline'])
                            ->default('teal')
                            ->selectablePlaceholder(false),
                    ])
                    ->columns(3)
                    ->maxItems(2)
                    ->reorderable()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->addActionLabel('Add button'),
            ]);
    }

    private function beliefTab(): Tab
    {
        return Tab::make('4. Stand-out phrase')
            ->schema([
                Textarea::make('belief.phrase')
                    ->label('Phrase')
                    ->helperText('Shown large in italic script on the navy band. Keep it to one sentence. Leave empty to hide this band.')
                    ->rows(2)
                    ->maxLength(140),

                static::image(
                    'belief.icon',
                    'Artwork',
                    'Decorative artwork beside the phrase. Transparent PNG or WebP, about 300px wide.',
                    'home/belief',
                ),
            ]);
    }

    private function communityTab(): Tab
    {
        return Tab::make('5. Community & history')
            ->schema([
                TextInput::make('community.heading')->label('Heading')->required()->maxLength(60),

                static::richText('community.intro', 'Intro'),

                Grid::make(2)->schema([
                    TextInput::make('community.button_label')
                        ->label('Button text')
                        ->helperText('Leave empty to hide the button.')
                        ->maxLength(40),
                    static::link('community.button_url', 'Button link'),
                ]),

                Repeater::make('community.milestones')
                    ->label('Timeline')
                    ->helperText('Up to 4 points, shown left to right.')
                    ->schema([
                        TextInput::make('year')->label('Year')->helperText('e.g. 1996 or "Today"')->required()->maxLength(12),
                        Textarea::make('text')->label('Text')->rows(2)->required()->maxLength(140),
                        Toggle::make('highlight')->label('Highlight this point in teal'),
                    ])
                    ->maxItems(4)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['year'] ?? null)
                    ->addActionLabel('Add timeline point'),
            ]);
    }

    private function exploreTab(): Tab
    {
        return Tab::make('6. Find your way around')
            ->schema([
                TextInput::make('explore.heading')->label('Heading')->required()->maxLength(60),

                Repeater::make('explore.columns')
                    ->label('Columns')
                    ->helperText('Shown four across on desktop. Turn on "Full width" for a row of its own, like Parent Hub.')
                    ->schema([
                        TextInput::make('title')->label('Title')->required()->maxLength(40),
                        Textarea::make('summary')->label('Short summary (optional)')->rows(2)->maxLength(200),
                        Toggle::make('full_width')->label('Full width (teal line, links in 5 columns)'),
                        Repeater::make('links')
                            ->label('Links')
                            ->schema([
                                TextInput::make('label')->label('Text')->required()->maxLength(60),
                                static::link('url')->required(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->defaultItems(0)
                            ->addActionLabel('Add link'),
                    ])
                    ->reorderable()
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->addActionLabel('Add column'),
            ]);
    }

    private function enrolTab(): Tab
    {
        return Tab::make('7. Enrolments & enquiry')
            ->schema([
                Section::make('Enrolments panel (teal)')
                    ->schema([
                        TextInput::make('enrol.heading')->label('Heading')->required()->maxLength(80),
                        static::richText('enrol.body', 'Text'),
                        Grid::make(2)->schema([
                            TextInput::make('enrol.primary_label')->label('Button text')->maxLength(40),
                            static::link('enrol.primary_url', 'Button link'),
                        ]),
                        Toggle::make('enrol.show_call_button')
                            ->label('Show a "Call" button')
                            ->helperText('Uses the phone number from Site settings.'),
                        static::image(
                            'enrol.art',
                            'Artwork',
                            'Decorative artwork in the bottom corner. Transparent PNG or WebP, about 450px wide.',
                            'home/enrol',
                        ),
                    ]),

                Section::make('Enquiry form')
                    ->description('Messages sent with this form appear under Enquiries in the menu.')
                    ->schema([
                        TextInput::make('enrol.form_heading')->label('Heading')->maxLength(60),
                        Textarea::make('enrol.form_intro')->label('Intro')->rows(2)->maxLength(200),
                        TextInput::make('enrol.form_success')
                            ->label('Thank-you message')
                            ->helperText('Shown after someone sends the form.')
                            ->maxLength(200),
                        TagsInput::make('enrol.topics')
                            ->label('"Enquiry about" options')
                            ->helperText('Type an option and press Enter. Drag to reorder.')
                            ->reorderable(),
                    ]),
            ]);
    }

    private function seoTab(): Tab
    {
        return Tab::make('SEO')
            ->schema([
                TextInput::make('seo.title')
                    ->label('Page title')
                    ->helperText('Shown in Google results and the browser tab. Aim for 50–60 characters.')
                    ->maxLength(70),
                Textarea::make('seo.description')
                    ->label('Meta description')
                    ->helperText('The summary under the title in Google results. Aim for 120–155 characters.')
                    ->rows(3)
                    ->maxLength(160),
            ]);
    }
}
