<?php

namespace App\Filament\Blocks;

use App\Filament\Concerns\ContentFields;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

/**
 * Content blocks staff can stack on a page (like ACF Flexible Content).
 * Each block's front-end template is resources/views/blocks/{type}.blade.php
 * (underscores in the type become dashes).
 */
class PageBlocks
{
    use ContentFields;

    /**
     * Full-width bands. All other blocks sit together in the content column,
     * next to the optional "Need help?" office card.
     */
    public const WIDE = ['text_timeline', 'phrase', 'statement_chips', 'vision', 'value_cards', 'numbered_cards'];

    public static function all(): array
    {
        return [
            // Content column
            static::text(),
            static::callout(),
            static::table(),
            static::button(),
            static::downloads(),
            static::accordion(),
            static::chips(),
            static::feature(),
            static::members(),
            static::comingSoon(),
            // Forms and automatic content
            static::uploadForm(),
            static::contactDetails(),
            static::contactForm(),
            static::pageLinks(),
            static::calendar(),
            // Full-width bands
            static::textTimeline(),
            static::phrase(),
            static::statementChips(),
            static::vision(),
            static::valueCards(),
            static::numberedCards(),
        ];
    }

    private static function callout(): Block
    {
        return Block::make('callout')
            ->label('Callout (highlighted note)')
            ->icon(Heroicon::OutlinedExclamationCircle)
            ->schema([
                static::richText('body', 'Text', 'Shown on a light panel with a teal bar. Keep it short.'),
            ]);
    }

    private static function table(): Block
    {
        return Block::make('table')
            ->label('Table (times, hours)')
            ->icon(Heroicon::OutlinedTableCells)
            ->schema([
                TextInput::make('caption')->label('Table title')->placeholder('e.g. Opening hours')->maxLength(80),
                TagsInput::make('headers')
                    ->label('Column headings (optional)')
                    ->helperText('Type each heading and press Enter, e.g. Gate, Morning, Afternoon. Leave empty for a simple two-column list.')
                    ->reorderable(),
                Repeater::make('rows')
                    ->label('Rows')
                    ->helperText('The first cell is the row label (shown in bold).')
                    ->schema([
                        TagsInput::make('cells')
                            ->hiddenLabel()
                            ->placeholder('Type a cell and press Enter')
                            ->reorderable()
                            ->required(),
                    ])
                    ->reorderable()
                    ->itemLabel(fn (array $state): ?string => $state['cells'][0] ?? null)
                    ->addActionLabel('Add row'),
            ]);
    }

    private static function button(): Block
    {
        return Block::make('button')
            ->label('Button')
            ->icon(Heroicon::OutlinedCursorArrowRays)
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('label')->label('Button text')->required()->maxLength(40),
                    static::link('url', 'Link')->helperText('Leave empty to show the button greyed out until the link is ready.'),
                    Select::make('style')
                        ->label('Style')
                        ->options(['teal' => 'Teal (filled)', 'line' => 'Outline'])
                        ->default('teal')
                        ->selectablePlaceholder(false),
                ]),
                TextInput::make('note')
                    ->label('Small note under the button (optional)')
                    ->helperText('Shown in an amber label, e.g. "Link to be confirmed by the school".')
                    ->maxLength(150),
            ]);
    }

    private static function accordion(): Block
    {
        return Block::make('accordion')
            ->label('Accordion (click to open)')
            ->icon(Heroicon::OutlinedBars3BottomLeft)
            ->schema([
                TextInput::make('heading')->label('Heading (optional)')->maxLength(80),
                Repeater::make('items')
                    ->label('Sections')
                    ->schema([
                        TextInput::make('title')->label('Title')->required()->maxLength(120),
                        static::richText('body', 'Text', headings: true),
                    ])
                    ->reorderable()
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->addActionLabel('Add section'),
            ]);
    }

    private static function chips(): Block
    {
        return Block::make('chips')
            ->label('Tags (row of rounded labels)')
            ->icon(Heroicon::OutlinedTag)
            ->schema([
                TagsInput::make('chips')->label('Tags')->helperText('Type a word or short phrase and press Enter.')->reorderable()->required(),
            ]);
    }

    private static function feature(): Block
    {
        return Block::make('feature')
            ->label('Feature row (icon with text)')
            ->icon(Heroicon::OutlinedStar)
            ->schema([
                static::image('icon', 'Icon', 'Square PNG with a transparent background, at least 192 × 192px.', 'pages/icons'),
                TextInput::make('heading')->label('Heading')->maxLength(80),
                static::richText('body', 'Text'),
            ]);
    }

    private static function comingSoon(): Block
    {
        return Block::make('coming_soon')
            ->label('Coming soon (placeholder)')
            ->icon(Heroicon::OutlinedWrenchScrewdriver)
            ->schema([
                TextInput::make('heading')->label('Heading')->default('Content coming soon')->maxLength(80),
                static::richText('body', 'Text'),
                static::image('icon', 'Icon (optional)', 'Square PNG, at least 168 × 168px. Leave empty to use the school icon.', 'pages/icons'),
            ]);
    }

    private static function uploadForm(): Block
    {
        return Block::make('upload_form')
            ->label('Document upload form')
            ->icon(Heroicon::OutlinedCloudArrowUp)
            ->schema([
                Select::make('form')
                    ->label('Which form')
                    ->helperText('Sent documents appear under Document uploads in the menu, labelled with this form.')
                    ->options(\App\Models\DocumentSubmission::FORMS)
                    ->required(),
                TextInput::make('heading')->label('Heading')->default('Upload your documents')->maxLength(80),
                Textarea::make('hint')->label('Instructions')->rows(2)->maxLength(300)
                    ->default('Send your completed form and supporting documents straight to the school. PDF, JPG or PNG files, up to 10 MB each and 10 files in total. Fields marked * are required.'),
                TagsInput::make('year_levels')->label('Year level options')->reorderable()
                    ->default(['Kindergarten', 'Pre-primary', 'Year 1', 'Year 2', 'Year 3', 'Year 4', 'Year 5', 'Year 6']),
                Textarea::make('consent')->label('Consent wording')->rows(2)->maxLength(300)
                    ->default('I am the parent or legal guardian of this child, and I agree to these documents being sent to Ellenbrook Primary School for enrolment purposes.'),
                TextInput::make('success')->label('Thank-you message')->maxLength(200)
                    ->default('Thank you. Your documents have been sent to the school office.'),
            ]);
    }

    private static function contactDetails(): Block
    {
        return Block::make('contact_details')
            ->label('Contact details and map')
            ->icon(Heroicon::OutlinedMapPin)
            ->schema([
                Textarea::make('lead')->label('Intro')->rows(2)->maxLength(250),
                TextInput::make('hours')->label('School hours line')->helperText('Leave empty to use the hours from Site settings.')->maxLength(100),
                TextInput::make('map_embed_url')
                    ->label('Google Maps embed link')
                    ->helperText('In Google Maps: Share → Embed a map → copy only the address inside src="…".')
                    ->url()
                    ->startsWith(['https://www.google.com/maps/embed'])
                    ->maxLength(1000),
            ])
            ->columns(1);
    }

    private static function contactForm(): Block
    {
        return Block::make('contact_form')
            ->label('Enquiry form')
            ->icon(Heroicon::OutlinedEnvelope)
            ->schema([
                TextInput::make('heading')->label('Heading')->default('Send us a message')->maxLength(60),
                Textarea::make('intro')->label('Intro')->rows(2)->maxLength(200),
                TextInput::make('success')->label('Thank-you message')->maxLength(200),
                TagsInput::make('topics')->label('"Enquiry about" options')->reorderable()
                    ->default(['Enrolments', 'Intensive English Centre (IEC)', 'Parent Hub question', 'Something else']),
            ]);
    }

    private static function pageLinks(): Block
    {
        return Block::make('page_links')
            ->label('Parent Hub icon grid (automatic)')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->schema([
                \Filament\Forms\Components\Placeholder::make('info')
                    ->hiddenLabel()
                    ->content('Shows an icon tile for every published Parent Hub page, in its group. Add or reorder pages under Site pages and this grid updates itself.'),
            ]);
    }

    private static function calendar(): Block
    {
        return Block::make('calendar')
            ->label('Calendar (automatic)')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->schema([
                TextInput::make('list_heading')->label('List heading')->default('Events this month')->maxLength(60)
                    ->helperText('Events are managed under Calendar events in the menu.'),
            ]);
    }

    private static function downloadItems(string $name): Repeater
    {
        return Repeater::make($name)
            ->label('Files')
            ->schema([
                TextInput::make('label')->label('Link text')->required()->maxLength(120),
                TextInput::make('note')->label('Small note (optional)')->helperText('e.g. "Updated Term 1, 2026". Shown under the link text.')->maxLength(120),
                static::document('file', 'File', 'pages/files'),
                static::link('url', 'Or a web link')->helperText('Use this instead of a file to link to another website.'),
            ])
            ->columns(2)
            ->reorderable()
            ->collapsible()
            ->defaultItems(0)
            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
            ->addActionLabel('Add file');
    }

    private static function text(): Block
    {
        return Block::make('text')
            ->label('Text')
            ->icon(Heroicon::OutlinedDocumentText)
            ->schema([
                Textarea::make('lead')
                    ->label('Lead paragraph (optional)')
                    ->helperText('Shown larger, above the main text. One or two sentences.')
                    ->rows(2),
                static::richText('body', 'Text', 'Use Heading 2 for section headings.', headings: true),
                Toggle::make('numbered_cards')
                    ->label('Show numbered lists as cards')
                    ->helperText('Numbered lists get the navy number badges, like the Principal\'s Welcome page.'),
                Fieldset::make('Downloads (optional)')->schema([static::downloadItems('downloads')])->columns(1),
                Grid::make(2)->schema([
                    TextInput::make('signature_name')->label('Signature name (optional)')->maxLength(100),
                    TextInput::make('signature_role')->label('Signature role')->placeholder('e.g. BEd., Principal')->maxLength(100),
                ]),
                Fieldset::make('Side photo (optional)')->schema([
                    static::image('photo', 'Photo', 'Portrait 4:5, at least 600 × 750px. Shown in a column on the right.', 'pages/photos')
                        ->rules(['dimensions:min_width=600,min_height=750'])
                        ->imageEditorAspectRatioOptions(['4:5']),
                    TextInput::make('photo_alt')->label('Photo description (alt text)')->maxLength(150),
                    TextInput::make('photo_caption')->label('Caption (optional)')->maxLength(150),
                    TextInput::make('photo_placeholder')
                        ->label('Placeholder text while there is no photo')
                        ->helperText('e.g. "Photo of the Principal to be supplied". Leave empty for no side column.')
                        ->maxLength(150),
                ])->columns(2),
            ]);
    }

    private static function textTimeline(): Block
    {
        return Block::make('text_timeline')
            ->label('Text with timeline')
            ->icon(Heroicon::OutlinedClock)
            ->schema([
                static::richText('body', 'Text (left column)', 'Use Heading 2 for section headings.', headings: true),
                TextInput::make('timeline_heading')->label('Timeline heading')->default('Our journey so far')->maxLength(60),
                Repeater::make('milestones')
                    ->label('Timeline points')
                    ->schema([
                        TextInput::make('year')->label('Year')->helperText('e.g. 1996 or "Today"')->required()->maxLength(12),
                        Textarea::make('text')->label('Text')->rows(2)->required()->maxLength(160),
                        Toggle::make('highlight')->label('Highlight in teal'),
                    ])
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['year'] ?? null)
                    ->addActionLabel('Add timeline point'),
            ]);
    }

    private static function phrase(): Block
    {
        return Block::make('phrase')
            ->label('Stand-out phrase (navy band)')
            ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
            ->schema([
                Textarea::make('phrase')->label('Phrase')->helperText('One short sentence, shown large in italic.')->rows(2)->required()->maxLength(140),
                static::image('artwork', 'Artwork', 'White artwork on a transparent background (PNG or WebP), about 240px wide.', 'pages/art'),
            ]);
    }

    private static function statementChips(): Block
    {
        return Block::make('statement_chips')
            ->label('Centred statement with tags')
            ->icon(Heroicon::OutlinedTag)
            ->schema([
                TextInput::make('heading')->label('Heading')->maxLength(60),
                Textarea::make('text')->label('Text')->rows(3)->maxLength(400),
                TagsInput::make('chips')->label('Tags')->helperText('Type a word or short phrase and press Enter.')->reorderable(),
            ]);
    }

    private static function vision(): Block
    {
        return Block::make('vision')
            ->label('Statement with image')
            ->icon(Heroicon::OutlinedSparkles)
            ->schema([
                TextInput::make('label')->label('Small label')->placeholder('e.g. Our vision')->maxLength(40),
                static::richText('statement', 'Statement', 'Shown large. Words in bold appear in teal.'),
                static::image('image', 'Image', 'PNG or WebP, at least 700px wide. Transparent backgrounds work best.', 'pages/images'),
                TextInput::make('image_alt')->label('Image description (alt text)')->maxLength(150),
            ]);
    }

    private static function valueCards(): Block
    {
        return Block::make('value_cards')
            ->label('Value cards')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->schema([
                TextInput::make('heading')->label('Heading')->maxLength(60),
                Textarea::make('intro')->label('Intro')->rows(2)->maxLength(300),
                Repeater::make('cards')
                    ->label('Cards')
                    ->helperText('Shown three across on desktop.')
                    ->schema([
                        TextInput::make('title')->label('Title')->placeholder('e.g. Respect')->required()->maxLength(40),
                        TextInput::make('letters')
                            ->label('Big letter(s)')
                            ->helperText('Shown large, and coloured inside the title. e.g. "AL" for Active Learner.')
                            ->maxLength(3),
                        ColorPicker::make('colour')->label('Colour')->default('#2776BB'),
                        Textarea::make('text')->label('Text')->rows(2)->maxLength(160),
                        static::image('image', 'Character image', 'Square PNG with a transparent background, at least 220 × 220px.', 'pages/values'),
                        TextInput::make('image_alt')->label('Image description (alt text)')->maxLength(100),
                    ])
                    ->columns(2)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->addActionLabel('Add card'),
            ]);
    }

    private static function numberedCards(): Block
    {
        return Block::make('numbered_cards')
            ->label('Numbered cards')
            ->icon(Heroicon::OutlinedListBullet)
            ->schema([
                TextInput::make('heading')->label('Heading')->maxLength(60),
                Textarea::make('intro')->label('Intro')->rows(2)->maxLength(300),
                Repeater::make('items')
                    ->label('Cards')
                    ->helperText('Numbered automatically, three across on desktop.')
                    ->schema([
                        TextInput::make('title')->label('Title')->required()->maxLength(80),
                        Textarea::make('summary')->label('Summary (optional)')->rows(2)->maxLength(300),
                        Repeater::make('points')
                            ->label('Bullet points')
                            ->simple(Textarea::make('text')->rows(2)->required()->maxLength(300))
                            ->reorderable()
                            ->defaultItems(0)
                            ->addActionLabel('Add bullet point'),
                    ])
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->addActionLabel('Add card'),
            ]);
    }

    private static function members(): Block
    {
        return Block::make('members')
            ->label('People list')
            ->icon(Heroicon::OutlinedUserGroup)
            ->schema([
                TextInput::make('heading')->label('Heading')->placeholder('e.g. School Board members')->maxLength(60),
                Textarea::make('intro')->label('Intro (optional)')->rows(2)->maxLength(300),
                Repeater::make('members')
                    ->label('People')
                    ->helperText('Initials are made from the name automatically.')
                    ->schema([
                        TextInput::make('name')->label('Name')->required()->maxLength(80),
                        TextInput::make('role')->label('Role')->maxLength(150),
                    ])
                    ->columns(2)
                    ->reorderable()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->addActionLabel('Add person'),
            ]);
    }

    private static function downloads(): Block
    {
        return Block::make('downloads')
            ->label('Downloads')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->schema([
                TextInput::make('heading')->label('Heading (optional)')->maxLength(60),
                Textarea::make('intro')->label('Intro (optional)')->rows(2)->maxLength(300),
                Toggle::make('two_columns')->label('Show in two columns'),
                static::downloadItems('files'),
            ]);
    }
}
