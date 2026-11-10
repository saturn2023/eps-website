<?php

namespace App\Filament\Resources\DocumentSubmissions\Pages;

use App\Filament\Resources\DocumentSubmissions\DocumentSubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDocumentSubmission extends ViewRecord
{
    protected static string $resource = DocumentSubmissionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->read_at === null) {
            $this->record->forceFill(['read_at' => now()])->save();
        }
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Delete (and its files)')];
    }
}
