<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEnquiry extends ViewRecord
{
    protected static string $resource = EnquiryResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Opening an enquiry marks it as read.
        if ($this->record->read_at === null) {
            $this->record->forceFill(['read_at' => now()])->save();
        }
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
