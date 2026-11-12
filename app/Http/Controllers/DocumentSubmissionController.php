<?php

namespace App\Http\Controllers;

use App\Mail\DocumentsReceived;
use App\Models\DocumentSubmission;
use App\Support\StaffAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'form' => ['required', Rule::in(array_keys(DocumentSubmission::FORMS))],
            'parent_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'student_name' => ['required', 'string', 'max:120'],
            'year_level' => ['nullable', 'string', 'max:40'],
            'documents' => ['required', 'array', 'min:1', 'max:10'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'consent' => ['accepted'],
        ], [
            'documents.required' => 'Please add at least one document.',
            'documents.max' => 'Please send no more than 10 files at a time.',
            'documents.*.mimes' => 'Only PDF, JPG and PNG files can be sent.',
            'documents.*.max' => 'Each file must be 10 MB or smaller.',
            'consent.accepted' => 'Please tick the box to confirm you are the parent or guardian.',
        ]);

        // Private storage: not reachable from the web, only through the admin.
        $folder = 'submissions/' . now()->format('Y/m') . '/' . Str::uuid();
        $files = [];
        foreach ($request->file('documents') as $upload) {
            $files[] = [
                'name' => $upload->getClientOriginalName(),
                'path' => $upload->store($folder, DocumentSubmission::DISK),
                'size' => $upload->getSize(),
            ];
        }

        $submission = DocumentSubmission::create([...collect($data)->except(['documents', 'consent'])->all(), 'files' => $files]);
        StaffAlert::send(StaffAlert::DOCUMENTS, new DocumentsReceived($submission));

        return redirect()->to(url()->previous() . '#' . $data['form'] . '-upload')
            ->with('upload_sent', $data['form']);
    }

    /** Staff-only download of a submitted file. */
    public function download(DocumentSubmission $submission, int $index)
    {
        $file = $submission->files[$index] ?? abort(404);

        return Storage::disk(DocumentSubmission::DISK)->download($file['path'], $file['name']);
    }
}
