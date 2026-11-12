<?php

namespace App\Support;

use App\Models\PageContent;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails the office about new website enquiries and document uploads.
 * Recipients are set in Site settings > Email alerts. A mail failure is
 * logged but never stops the family's form from being saved.
 */
class StaffAlert
{
    public const ENQUIRIES = 'alert_enquiries';

    public const DOCUMENTS = 'alert_documents';

    /** @return list<string> */
    public static function recipients(string $setting): array
    {
        return collect(PageContent::for(PageContent::SITE)[$setting] ?? [])
            ->map(fn ($email) => trim((string) $email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    public static function send(string $setting, Mailable $mail): void
    {
        $to = static::recipients($setting);
        if (! $to) {
            return;
        }

        try {
            Mail::to($to)->send($mail);
        } catch (Throwable $e) {
            Log::error('Staff alert email failed: ' . $e->getMessage(), ['setting' => $setting]);
        }
    }
}
