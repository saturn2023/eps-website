<?php

namespace App\Mail;

use App\Filament\Resources\DocumentSubmissions\DocumentSubmissionResource;
use App\Models\DocumentSubmission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The files are not attached: they contain private family documents,
 * so staff open them in the admin after logging in.
 */
class DocumentsReceived extends Mailable
{
    public function __construct(public DocumentSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->submission->email, $this->submission->parent_name)],
            subject: 'New ' . lcfirst($this->submission->formLabel()) . ' for ' . $this->submission->student_name,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.documents-received', with: [
            'adminUrl' => DocumentSubmissionResource::getUrl('view', ['record' => $this->submission], panel: 'admin'),
        ]);
    }
}
