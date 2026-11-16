<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ManageMedia extends ManageRecords
{
    protected static string $resource = MediaResource::class;

    public function mount(): void
    {
        parent::mount();

        // Pick up files added outside the admin (seeders, FTP) and drop missing ones.
        Media::sync();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Add media files')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->modalHeading('Add media files')
                ->modalSubmitActionLabel('Upload')
                ->schema([
                    FileUpload::make('files')
                        ->hiddenLabel()
                        ->multiple()
                        ->maxParallelUploads(3)
                        ->disk(Media::DISK)
                        ->visibility('public')
                        ->directory(fn () => 'library/' . now()->format('Y/m'))
                        ->acceptedFileTypes([
                            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
                            'video/mp4',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(20480)
                        ->helperText('Drop files here or click to browse. Images (JPG, PNG, WebP, GIF), MP4 videos, PDF, Word and Excel, up to 20 MB each. '
                            . 'Images: about 2000px wide is plenty for the website.')
                        ->saveUploadedFileUsing(function (FileUpload $component, TemporaryUploadedFile $file): ?string {
                            $path = $component->saveUploadedFile($file);
                            $path && Media::register($path, $file->getClientOriginalName(), auth()->id());

                            return $path;
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $count = count($data['files'] ?? []);
                    Notification::make()->success()
                        ->title($count === 1 ? '1 file uploaded' : "{$count} files uploaded")
                        ->body('Click a file to add its alt text, title and caption.')
                        ->send();
                }),
        ];
    }
}
