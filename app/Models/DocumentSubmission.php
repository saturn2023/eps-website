<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentSubmission extends Model
{
    /** Upload forms that can be placed on pages: key => label. */
    public const FORMS = [
        'enrolment' => 'School enrolment documents',
        'iec' => 'IEC enrolment documents',
    ];

    /** Files are kept on the private "local" disk; only logged-in staff can download them. */
    public const DISK = 'local';

    protected $fillable = ['form', 'parent_name', 'email', 'phone', 'student_name', 'year_level', 'files', 'read_at'];

    protected function casts(): array
    {
        return ['files' => 'array', 'read_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        // Remove the uploaded files when a submission is deleted.
        static::deleted(function (DocumentSubmission $submission) {
            foreach ($submission->files ?? [] as $file) {
                Storage::disk(self::DISK)->delete($file['path'] ?? '');
            }
        });
    }

    public function formLabel(): string
    {
        return self::FORMS[$this->form] ?? $this->form;
    }
}
