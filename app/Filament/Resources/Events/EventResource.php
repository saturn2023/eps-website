<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\ManageEvents;
use App\Models\Event;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Calendar events';

    protected static ?string $modelLabel = 'event';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')->label('Event')->required()->maxLength(120)->columnSpanFull(),
            DatePicker::make('starts_on')->label('Date (or first day)')->native(false)->displayFormat('j M Y')->required()->live(),
            DatePicker::make('ends_on')->label('Last day (optional)')
                ->helperText('Only for events that run over several days, like school holidays.')
                ->native(false)->displayFormat('j M Y')
                ->afterOrEqual('starts_on')
                ->minDate(fn (Get $get) => $get('starts_on')),
            TextInput::make('time')->label('Time (optional)')->placeholder('e.g. 12.30pm')->maxLength(30),
            Select::make('type')->label('Type')->options(Event::TYPES)->default('event')->selectablePlaceholder(false)
                ->helperText('Holidays show in teal and Development Days in brown on the calendar.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on')
            ->columns([
                TextColumn::make('starts_on')->label('Date')->date('D j M Y')->sortable()
                    ->description(fn (Event $record) => $record->ends_on ? 'to ' . $record->ends_on->format('D j M Y') : null),
                TextColumn::make('title')->label('Event')->searchable()->weight('medium'),
                TextColumn::make('time')->placeholder('—'),
                TextColumn::make('type')->badge()
                    ->formatStateUsing(fn (string $state) => Event::TYPES[$state] ?? $state)
                    ->color(fn (string $state) => ['holiday' => 'primary', 'development_day' => 'warning'][$state] ?? 'gray'),
            ])
            ->filters([
                Filter::make('upcoming')->label('Upcoming only')->default()
                    ->query(fn (Builder $query) => $query->where(fn ($q) => $q->whereDate('starts_on', '>=', today())->orWhereDate('ends_on', '>=', today()))),
                SelectFilter::make('type')->options(Event::TYPES),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()->label('Duplicate'),
                DeleteAction::make(),
            ])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageEvents::route('/')];
    }
}
