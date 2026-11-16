<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?string $modelLabel = 'file';

    protected static ?string $pluralModelLabel = 'Media Library';

    protected static ?string $slug = 'media';

    protected static ?int $navigationSort = 30;

    /** The "Attachment details" panel: preview on the left, details on the right. */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // 60% preview, 40% details. columnSpanFull: the modal's form is two columns by default.
            Grid::make(['default' => 1, 'md' => 5])->columnSpanFull()->schema([
                View::make('filament.media.preview')->columnSpan(['default' => 1, 'md' => 3]),
                Group::make([
                    View::make('filament.media.info'),
                    Textarea::make('alt')
                        ->label('Alternative text')
                        ->rows(2)
                        ->maxLength(300)
                        ->helperText('Describe the image for people who can\'t see it, e.g. "Four Year 6 students smiling in their leavers shirts". '
                            . 'Leave empty if the image is only decoration. Used on the website wherever this image appears.')
                        ->visible(fn (?Media $record) => $record?->isImage()),
                    TextInput::make('title')->label('Title')->maxLength(250),
                    Textarea::make('caption')->label('Caption')->rows(2)->maxLength(1000),
                    Textarea::make('description')->label('Description')->rows(3)->maxLength(5000),
                    TextInput::make('file_url')
                        ->label('File URL')
                        ->dehydrated(false)
                        ->readOnly()
                        ->copyable(copyMessage: 'URL copied')
                        ->afterStateHydrated(fn (TextInput $component, ?Media $record) => $component->state($record?->url())),
                ])->columnSpan(['default' => 1, 'md' => 2]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->contentGrid(['default' => 2, 'sm' => 3, 'md' => 4, 'xl' => 6, '2xl' => 8])
            ->paginated([24, 48, 96])
            ->defaultPaginationPageOption(48)
            ->columns([
                Stack::make([
                    ViewColumn::make('path')->view('filament.media.thumb'),
                    TextColumn::make('name')
                        ->label('File name')
                        ->searchable(['name', 'title', 'alt', 'caption'])
                        ->size('xs')
                        ->limit(30)
                        ->tooltip(fn (Media $record) => $record->name),
                ])->space(2),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label('Type')
                    ->placeholder('All media items')
                    ->options(Media::KINDS)
                    ->query(fn (Builder $query, array $data) => $query->ofKind($data['value'] ?? null)),
                SelectFilter::make('month')
                    ->label('Date')
                    ->placeholder('All dates')
                    ->options(fn () => Media::orderByDesc('created_at')->pluck('created_at')
                        ->mapWithKeys(fn (Carbon $date) => [$date->format('Y-m') => $date->format('F Y')])
                        ->all())
                    ->query(function (Builder $query, array $data) {
                        if (! ($month = $data['value'] ?? null)) {
                            return $query;
                        }
                        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();

                        return $query->whereBetween('created_at', [$start, $start->copy()->endOfMonth()]);
                    }),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->recordAction('edit')
            ->recordActions([
                EditAction::make()
                    ->label('Details')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->modalHeading('Attachment details')
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalSubmitActionLabel('Save')
                    ->successNotificationTitle('Saved')
                    ->extraModalFooterActions(fn (Media $record) => [
                        static::replaceAction($record),
                        Action::make('open')
                            ->label('Open file')
                            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                            ->color('gray')
                            ->url($record->url(), shouldOpenInNewTab: true),
                    ]),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading(fn (Media $record) => 'Delete ' . $record->name . '?')
                    ->modalDescription(fn (Media $record) => ($used = $record->usages())
                        ? 'This file is used on: ' . implode(', ', $used) . '. Deleting it will leave a gap there until you choose another file. This can\'t be undone.'
                        : 'The file will be removed from the website permanently. This can\'t be undone.'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->modalDescription('The selected files will be removed from the website permanently, even if a page still uses them. This can\'t be undone.'),
            ])
            ->emptyStateHeading('No files yet')
            ->emptyStateDescription('Upload images, videos and documents with "Add media files".');
    }

    /** Upload a new file in place of this one, everywhere it is used. */
    protected static function replaceAction(Media $record): Action
    {
        $types = match ($record->kind()) {
            'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'video' => ['video/mp4'],
            default => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        };

        return Action::make('replace')
            ->label('Replace file')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')
            ->modalHeading('Replace ' . $record->name)
            ->modalDescription(($used = $record->usages())
                ? 'The new file will take its place on: ' . implode(', ', $used) . '. The alt text, title and caption are kept. The old file is deleted.'
                : 'The alt text, title and caption are kept. The old file is deleted.')
            ->modalSubmitActionLabel('Replace')
            ->schema([
                FileUpload::make('file')
                    ->label('New file')
                    ->disk(Media::DISK)
                    ->visibility('public')
                    ->directory(trim(dirname($record->path), './'))
                    ->acceptedFileTypes($types)
                    ->maxSize(20480)
                    ->image($record->isImage())
                    ->helperText($record->dimensions()
                        ? "The current file is {$record->dimensions()}. Use the same shape so it fits the page the same way."
                        : null)
                    ->storeFileNamesIn('file_name')
                    ->required(),
                Hidden::make('file_name'),
            ])
            ->action(function (array $data) use ($record): void {
                $record->replaceWith($data['file'], $data['file_name'] ?? null);

                Notification::make()->success()->title('File replaced')
                    ->body('The new file is now used everywhere the old one was.')->send();
            })
            ->cancelParentActions();
    }

    public static function getPages(): array
    {
        return ['index' => ManageMedia::route('/')];
    }
}
