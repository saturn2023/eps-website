<?php

namespace App\Filament\Resources\Enquiries;

use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Filament\Resources\Enquiries\Pages\ViewEnquiry;
use App\Models\Enquiry;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnquiryResource extends Resource
{
    protected static ?string $model = Enquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 50;

    public static function getNavigationBadge(): ?string
    {
        $unread = Enquiry::whereNull('read_at')->count();

        return $unread ? (string) $unread : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('topic')->placeholder('—'),
            TextEntry::make('email')->copyable()->url(fn (Enquiry $record) => "mailto:{$record->email}"),
            TextEntry::make('phone')->placeholder('—')->copyable(),
            TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i a'),
            TextEntry::make('message')->columnSpanFull()->prose(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('read_at')
                    ->label('')
                    ->boolean()
                    ->getStateUsing(fn (Enquiry $record) => $record->read_at === null)
                    ->trueIcon(Heroicon::Envelope)
                    ->falseIcon(Heroicon::OutlinedEnvelopeOpen)
                    ->trueColor('primary')
                    ->falseColor('gray')
                    ->tooltip(fn (Enquiry $record) => $record->read_at ? 'Read' : 'Unread'),
                TextColumn::make('name')->searchable()->weight(fn (Enquiry $record) => $record->read_at ? null : 'bold'),
                TextColumn::make('topic')->badge()->placeholder('—'),
                TextColumn::make('email')->searchable(),
                TextColumn::make('message')->limit(60)->toggleable(),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('topic')->options(fn () => Enquiry::query()->whereNotNull('topic')->distinct()->pluck('topic', 'topic')->all()),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnquiries::route('/'),
            'view' => ViewEnquiry::route('/{record}'),
        ];
    }
}
