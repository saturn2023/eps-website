<?php

namespace App\Filament\Concerns;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;

/** Shared field builders so every editing screen behaves the same way. */
trait ContentFields
{
    /** Image upload to the public disk, with the guidance text shown under it. */
    protected static function image(string $name, string $label, string $guidance, string $folder): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->helperText($guidance)
            ->image()
            ->imageEditor()
            ->disk('public')
            ->visibility('public')
            ->directory($folder)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(4096)
            ->validationMessages([
                'dimensions' => 'This image is too small. Check the size guidance under the field.',
                'max' => 'This image is over 4 MB. Please resize or compress it and upload again.',
            ]);
    }

    /** Document upload (PDF, Word, Excel) to the public disk. */
    protected static function document(string $name, string $label, string $folder): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->helperText('PDF preferred. Word and Excel files also work. Up to 20 MB.')
            ->disk('public')
            ->visibility('public')
            ->directory($folder)
            ->preserveFilenames()
            ->acceptedFileTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->maxSize(20480)
            ->openable()
            ->downloadable();
    }

    /** Rich text (WYSIWYG) editor with a short toolbar so the design can't be broken. */
    protected static function richText(string $name, string $label, ?string $guidance = null, bool $headings = false): RichEditor
    {
        return RichEditor::make($name)
            ->label($label)
            ->helperText($guidance)
            ->toolbarButtons(array_values(array_filter([
                ['bold', 'italic', 'link'],
                $headings ? ['h2', 'h3'] : null,
                ['bulletList', 'orderedList'],
                ['undo', 'redo'],
            ])));
    }

    /** A link field that accepts full URLs, page paths (/enrolments) or anchors (#contact). */
    protected static function link(string $name, string $label = 'Link'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->placeholder('https://… or /page-name')
            ->maxLength(500);
    }
}
