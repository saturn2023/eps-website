<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\ContentFields;
use App\Models\PageContent;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

/**
 * An ACF-style editing screen: one page of fields, saved as JSON
 * against a key in the page_contents table.
 *
 * @property-read Schema $form
 */
abstract class ContentPage extends Page
{
    use ContentFields;

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    /** The page_contents key this screen edits. */
    abstract protected static function contentKey(): string;

    /** The form fields (usually a Tabs component). */
    abstract protected function fields(): array;

    public function mount(): void
    {
        $this->form->fill(PageContent::for(static::contentKey()));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->fields())
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save changes')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View website')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(url('/'))
                ->openUrlInNewTab(),
        ];
    }

    public function save(): void
    {
        PageContent::store(static::contentKey(), $this->form->getState());

        Notification::make()
            ->success()
            ->title('Saved')
            ->body('Your changes are now live on the website.')
            ->send();
    }
}
