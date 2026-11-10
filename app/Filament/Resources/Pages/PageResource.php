<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Blocks\PageBlocks;
use App\Filament\Concerns\ContentFields;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Support\Navigation;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group as TableGroup;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageResource extends Resource
{
    use ContentFields;

    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $navigationLabel = 'Site pages';

    protected static ?string $modelLabel = 'page';

    protected static string|\UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make('Page header')
                        ->description('The light blue band at the top of the page.')
                        ->schema([
                            TextInput::make('title')
                                ->label('Page name')
                                ->helperText('Used in the menu, breadcrumb, section tabs and Previous/Next links.')
                                ->required()
                                ->maxLength(80)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation) {
                                    // Fill the web address from the name on new pages, until someone edits it.
                                    if ($operation === 'create' && ($get('slug') === null || $get('slug') === Str::slug($old ?? ''))) {
                                        $set('slug', Str::slug($state ?? ''));
                                    }
                                }),
                            TextInput::make('heading')
                                ->label('Heading (page H1)')
                                ->helperText('Leave empty to use the page name. e.g. name "Our School", heading "Ellenbrook Primary School History".')
                                ->maxLength(120),
                            Textarea::make('intro')
                                ->label('Intro line (optional)')
                                ->helperText('One sentence under the heading.')
                                ->rows(2)
                                ->maxLength(250),
                            static::image(
                                'icon',
                                'Page icon',
                                'Square PNG with a transparent background, at least 272 × 272px. Shown 136px wide next to the heading.',
                                'pages/icons',
                            )->rules(['dimensions:min_width=272,min_height=272']),
                        ]),

                    Section::make('Content')
                        ->description('Add blocks and drag them into order. Each block is a section of the page.')
                        ->schema([
                            Builder::make('blocks')
                                ->hiddenLabel()
                                ->blocks(PageBlocks::all())
                                ->collapsible()
                                ->cloneable()
                                ->blockNumbers(false)
                                ->addActionLabel('Add a block'),
                        ]),
                ]),

                Group::make()->columnSpan(1)->schema([
                    Section::make('Settings')->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('Turn off to hide the page from the website while you work on it.')
                            ->default(true),
                        Select::make('section')
                            ->label('Menu section')
                            ->options(Navigation::SECTIONS)
                            ->live()
                            ->required(),
                        Select::make('group')
                            ->label('Parent Hub group')
                            ->helperText('Where the page sits in the Parent Hub menu and icon grid. Leave empty for the Parent Hub page itself.')
                            ->options(Navigation::GROUPS)
                            ->visible(fn (Get $get) => $get('section') === Navigation::GROUPED_SECTION),
                        Toggle::make('show_contact_card')
                            ->label('Show "Need help?" office card')
                            ->helperText('The navy card with the office phone and email, on the right of the content.'),
                        TextInput::make('sort')
                            ->label('Order in section')
                            ->helperText('Lower numbers come first in the section tabs and Previous/Next links.')
                            ->numeric()
                            ->default(10),
                        TextInput::make('slug')
                            ->label('Web address')
                            ->prefix(url('/') . '/')
                            ->helperText('Lowercase words joined by dashes. Changing it breaks old links to the page.')
                            ->required()
                            ->maxLength(80)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->rule(Rule::notIn(Page::RESERVED_SLUGS))
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'regex' => 'Use lowercase letters, numbers and dashes only.',
                                'not_in' => 'This web address is used by the system. Choose another.',
                            ]),
                    ]),
                    Section::make('SEO')->collapsible()->schema([
                        TextInput::make('seo_title')
                            ->label('Page title for Google')
                            ->helperText('Leave empty to use "Page name | Ellenbrook Primary School". Aim for 50–60 characters.')
                            ->maxLength(70),
                        Textarea::make('seo_description')
                            ->label('Meta description')
                            ->helperText('The summary under the title in Google results. Aim for 120–155 characters.')
                            ->rows(3)
                            ->maxLength(160),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup(
                TableGroup::make('section')
                    ->label('Section')
                    ->getTitleFromRecordUsing(fn (Page $record) => $record->sectionLabel() ?? $record->section)
                    // Groups follow the menu order (About, Teaching & Learning, ...), not A–Z.
                    ->orderQueryUsing(fn ($query, string $direction) => $query->orderByRaw(
                        'CASE section ' . collect(array_keys(Navigation::SECTIONS))
                            ->map(fn ($key, $i) => "WHEN '{$key}' THEN {$i}")->implode(' ') . ' ELSE 99 END ' . ($direction === 'desc' ? 'desc' : 'asc')
                    ))
                    ->collapsible()
            )
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('icon')->label('')->disk('public')->imageSize(36),
                TextColumn::make('title')->label('Page')->searchable()->weight('medium')
                    ->description(fn (Page $record) => '/' . $record->slug),
                TextColumn::make('group')->label('Group')->badge()->color('gray')
                    ->formatStateUsing(fn (?string $state) => Navigation::groupLabel($state))
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('is_published')->label('Published')->boolean(),
                TextColumn::make('updated_at')->label('Last edited')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('section')->options(Navigation::SECTIONS),
                SelectFilter::make('group')->label('Parent Hub group')->options(Navigation::GROUPS),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Page $record) => $record->url())
                    ->openUrlInNewTab(),
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->modalDescription('Makes an unpublished copy of this page that you can edit.')
                    ->beforeReplicaSaved(function (Page $replica): void {
                        $replica->title .= ' (copy)';
                        $replica->slug = $replica->slug . '-copy-' . Str::lower(Str::random(4));
                        $replica->is_published = false;
                    })
                    ->successRedirectUrl(fn (Page $replica): string => static::getUrl('edit', ['record' => $replica])),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
