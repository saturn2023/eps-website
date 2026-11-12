<?php

namespace App\Mail;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EnquiryReceived extends Mailable
{
    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->enquiry->email, $this->enquiry->name)],
            subject: 'New website enquiry' . ($this->enquiry->topic ? ': ' . $this->enquiry->topic : '') . ' from ' . $this->enquiry->name,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.enquiry-received', with: [
            'adminUrl' => EnquiryResource::getUrl('view', ['record' => $this->enquiry], panel: 'admin'),
        ]);
    }
}
