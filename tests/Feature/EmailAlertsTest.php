<?php

namespace Tests\Feature;

use App\Filament\Pages\EditSiteSettings;
use App\Mail\DocumentsReceived;
use App\Mail\EnquiryReceived;
use App\Models\DocumentSubmission;
use App\Models\Enquiry;
use App\Models\PageContent;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EmailAlertsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        Mail::fake();
        $this->seed(ContentSeeder::class);
    }

    private function setRecipients(array $enquiries, array $documents): void
    {
        $site = PageContent::for(PageContent::SITE);
        PageContent::store(PageContent::SITE, ['alert_enquiries' => $enquiries, 'alert_documents' => $documents] + $site);
    }

    public function test_no_emails_are_sent_until_recipients_are_set(): void
    {
        $this->from('/contact')->post('/enquiries', ['name' => 'Ann', 'email' => 'ann@example.com', 'message' => 'Hi']);

        $this->assertSame(1, Enquiry::count());
        Mail::assertNothingSent();
    }

    public function test_enquiry_emails_the_office_with_reply_to_the_family(): void
    {
        $this->setRecipients(['office@example.com', 'principal@example.com'], []);

        $this->from('/contact')->post('/enquiries', [
            'name' => 'Ann Lee', 'email' => 'ann@example.com', 'phone' => '0400 111 222',
            'topic' => 'Enrolments', 'message' => 'Is there room in Year 3?',
        ])->assertRedirect(url('/contact') . '#contact');

        Mail::assertSent(EnquiryReceived::class, function (EnquiryReceived $mail) {
            $html = $mail->render();

            return $mail->hasTo('office@example.com') && $mail->hasTo('principal@example.com')
                && $mail->hasReplyTo('ann@example.com')
                && $mail->envelope()->subject === 'New website enquiry: Enrolments from Ann Lee'
                && str_contains($html, 'Is there room in Year 3?')
                && str_contains($html, '/admin/enquiries/' . $mail->enquiry->id);
        });
    }

    public function test_document_upload_emails_without_attaching_files(): void
    {
        $this->setRecipients([], ['enrolments@example.com']);

        $this->from('/enrolments')->post('/documents', [
            'form' => 'enrolment', 'parent_name' => 'Sam Parent', 'email' => 'sam@example.com',
            'phone' => '0400 000 000', 'student_name' => 'Alex Parent', 'year_level' => 'Year 2', 'consent' => '1',
            'documents' => [UploadedFile::fake()->create('birth-certificate.pdf', 200, 'application/pdf')],
        ]);

        Mail::assertNotSent(EnquiryReceived::class);
        Mail::assertSent(DocumentsReceived::class, function (DocumentsReceived $mail) {
            $html = $mail->render();

            return $mail->hasTo('enrolments@example.com')
                && $mail->hasReplyTo('sam@example.com')
                && $mail->attachments === []
                && $mail->envelope()->subject === 'New school enrolment documents for Alex Parent'
                && str_contains($html, 'birth-certificate.pdf')
                && str_contains($html, '/admin/document-submissions/' . DocumentSubmission::sole()->id);
        });
    }

    public function test_admin_can_set_recipients_and_send_a_test_email(): void
    {
        config(['mail.default' => 'smtp']);
        $this->actingAs(User::factory()->create(['email' => 'me@example.com']));

        Livewire::test(EditSiteSettings::class)
            ->fillForm(['alert_enquiries' => ['office@example.com'], 'alert_documents' => ['not-an-email']])
            ->call('save')
            ->assertHasFormErrors(['alert_documents.0'])
            ->fillForm(['alert_documents' => ['enrolments@example.com']])
            ->call('save')
            ->assertHasNoFormErrors()
            ->callAction('testEmail', ['to' => 'me@example.com'])
            ->assertNotified('Test email sent');

        $this->assertSame(['enrolments@example.com'], PageContent::for(PageContent::SITE)['alert_documents']);
    }
}
