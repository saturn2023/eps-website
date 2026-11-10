<?php

namespace App\Filament\Resources\DocumentSubmissions;

use App\Filament\Resources\DocumentSubmissions\Pages\ListDocumentSubmissions;
use App\Filament\Resources\DocumentSubmissions\Pages\ViewDocumentSubmission;
use App\Models\DocumentSubmission;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class DocumentSubmissionResource extends Resource
{
    protected static ?string $model = DocumentSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $navigationLabel = 'Document uploads';

    protected static ?string $modelLabel = 'document upload';

    protected static ?int $navigationSort = 51;

    public static function getNavigationBadge(): ?string
    {
        $unread = DocumentSubmission::whereNull('read_at')->count();

        return $unread ? (string) $unread : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextEntry::make('form')->label('Form')->formatStateUsing(fn ($state, DocumentSubmission $record) => $record->formLabel()),
            TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i a'),
            TextEntry::make('student_name')->label('Child'),
            TextEntry::make('year_level')->label('Year level')->placeholder('—'),
            TextEntry::make('parent_name')->label('Parent or carer'),
            TextEntry::make('phone')->copyable(),
            TextEntry::make('email')->copyable()->url(fn (DocumentSubmission $record) => "mailto:{$record->email}"),
            Section::make('Documents')
                ->description('Files are stored privately. Only logged-in staff can download them.')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('file_links')
                        ->hiddenLabel()
                        ->html()
                        ->getStateUsing(fn (DocumentSubmission $record) => collect($record->files ?? [])
                            ->map(fn (array $file, int $i) => sprintf(
                                '<div style="padding:6px 0">📎 <a href="%s" style="color:#00666D;text-decoration:underline;font-weight:500">%s</a> <span style="color:#64748b">(%s)</span></div>',
                                e(route('documents.download', [$record, $i])),
                                e($file['name'] ?? 'file'),
                                Number::fileSize((int) ($file['size'] ?? 0), 1),
                            ))->implode('')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('read_at')->label('')
                    ->getStateUsing(fn (DocumentSubmission $record) => $record->read_at === null)
                    ->boolean()
                    ->trueIcon(Heroicon::Envelope)->falseIcon(Heroicon::OutlinedEnvelopeOpen)
                    ->trueColor('primary')->falseColor('gray')
                    ->tooltip(fn (DocumentSubmission $record) => $record->read_at ? 'Opened' : 'New'),
                TextColumn::make('student_name')->label('Child')->searchable()
                    ->weight(fn (DocumentSubmission $record) => $record->read_at ? null : 'bold'),
                TextColumn::make('form')->badge()->formatStateUsing(fn ($state, DocumentSubmission $record) => $record->formLabel()),
                TextColumn::make('parent_name')->label('Parent or carer')->searchable(),
                TextColumn::make('files')->label('Files')->formatStateUsing(fn ($state, DocumentSubmission $record) => count($record->files ?? [])),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([SelectFilter::make('form')->options(DocumentSubmission::FORMS)])
            ->recordActions([ViewAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentSubmissions::route('/'),
            'view' => ViewDocumentSubmission::route('/{record}'),
        ];
    }
}
