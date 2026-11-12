<?php

namespace App\Filament\Pages;

use App\Models\PageContent;
use App\Support\StaffAlert;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EditSiteSettings extends ContentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static ?string $slug = 'site-settings';

    protected static ?int $navigationSort = 90;

    protected static function contentKey(): string
    {
        return PageContent::SITE;
    }

    protected function fields(): array
    {
        return [
            Tabs::make('Site settings')
                ->vertical()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('School details')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('school_name')->label('School name')->required()->maxLength(80),
                                TextInput::make('motto')->label('Motto')->helperText('Shown in the footer under the school name.')->maxLength(80),
                            ]),
                            static::image(
                                'logo',
                                'Header logo',
                                'PNG, WebP or SVG-exported PNG, at least 300px tall. Shown 80px tall in the header.',
                                'site',
                            )->required(),
                            Grid::make(2)->schema([
                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->helperText('Written the way it should appear, e.g. (08) 9297 7300. Used for every "Call" button.')
                                    ->required()
                                    ->maxLength(30),
                                TextInput::make('email')->label('Email')->email()->maxLength(120),
                            ]),
                            TextInput::make('hours_line')
                                ->label('School hours (one line)')
                                ->helperText('Shown in the top bar and the Parent Hub menu.')
                                ->maxLength(100),
                            static::link('absence_url', 'Record an absence link')
                                ->helperText('Where the "Record an absence" buttons go, e.g. the absence form.'),
                        ]),

                    Tab::make('Inner pages')
                        ->schema([
                            static::image(
                                'page_header_art',
                                'Page header artwork',
                                'Decorative artwork on the right of every page header. Transparent PNG or WebP, about 600px wide.',
                                'site',
                            ),
                            TextInput::make('cta_heading')
                                ->label('Bottom band heading')
                                ->helperText('The teal band at the bottom of every inner page.')
                                ->placeholder('Have a question about our school?')
                                ->maxLength(80),
                            Textarea::make('cta_text')
                                ->label('Bottom band text')
                                ->rows(2)
                                ->maxLength(200),
                            static::link('cta_message_url', '"Send us a message" link')
                                ->helperText('Where the "Send us a message" button goes, e.g. /contact.'),
                            TextInput::make('card_heading')
                                ->label('Office card heading')
                                ->helperText('The navy "Need help?" card beside page content. Phone and email come from School details.')
                                ->placeholder('Need help?')
                                ->maxLength(60),
                            Textarea::make('card_text')->label('Office card text')->rows(2)->maxLength(200),
                        ]),

                    Tab::make('Translate')
                        ->schema([
                            Repeater::make('translate_languages')
                                ->label('Languages in the Translate panel')
                                ->helperText('English is always first. Pages are translated automatically by Google. '
                                    . 'Codes: Arabic ar, Chinese (Simplified) zh-CN, Dari fa-AF, Hindi hi, Punjabi pa, Swahili sw, Vietnamese vi, '
                                    . 'Persian fa, Urdu ur, Somali so, Tamil ta, Korean ko, Japanese ja, Indonesian id. Full list: cloud.google.com/translate/docs/languages')
                                ->schema([
                                    TextInput::make('label')->label('Name shown')->placeholder('e.g. العربية (Arabic)')->required()->maxLength(60),
                                    TextInput::make('code')->label('Google language code')->placeholder('e.g. ar')->required()->maxLength(10)
                                        ->regex('/^[a-z]{2,3}(-[A-Za-z]{2,4})?$/'),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->default(\App\Support\Translate::DEFAULT_LANGUAGES)
                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                ->addActionLabel('Add language'),
                            TextInput::make('translate_note')
                                ->label('Note under the language list')
                                ->placeholder('Translated automatically by Google. Some words may not be exact.')
                                ->maxLength(150),
                        ]),

                    Tab::make('Email alerts')
                        ->schema([
                            Text::make(fn () => static::mailIsConfigured()
                                ? 'Emails are sent through ' . config('mail.mailers.smtp.host') . ' from ' . config('mail.from.address') . '. Use "Send test email" at the top to check it works.'
                                : 'Email sending is not set up on this server yet (MAIL_ settings in the .env file), so no alerts will be sent. Forms are still saved in the admin.'),
                            static::alertRecipients(StaffAlert::ENQUIRIES, 'Send new enquiries to')
                                ->helperText('Messages from the "Send us a message" forms. Press Enter after each address. Leave empty for no emails. Replying to an alert replies to the family.'),
                            static::alertRecipients(StaffAlert::DOCUMENTS, 'Send new document uploads to')
                                ->helperText('Enrolment and IEC document uploads. For privacy the files are not attached: staff open them in the admin.'),
                        ]),

                    Tab::make('Footer')
                        ->schema([
                            Grid::make(2)->schema([
                                Textarea::make('address')->label('Address')->rows(2)->maxLength(200),
                                static::link('directions_url', 'Get directions link')
                                    ->helperText('A Google Maps link to the school.'),
                            ]),
                            static::richText('footer_hours', 'School hours (footer)'),
                            Repeater::make('footer_links')
                                ->label('Quick links')
                                ->schema([
                                    TextInput::make('label')->label('Text')->required()->maxLength(40),
                                    static::link('url')->required(),
                                ])
                                ->columns(2)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                ->addActionLabel('Add link'),
                            static::richText('acknowledgement', 'Acknowledgement of Country'),
                            Grid::make(2)->schema([
                                static::image(
                                    'footer_artwork',
                                    'Artwork strip',
                                    'Full-width artwork across the top of the footer. At least 2000px wide, roughly 4:1.',
                                    'site',
                                )->rules(['dimensions:min_width=2000']),
                                static::image(
                                    'footer_logo',
                                    'Footer logo',
                                    'Light version of the logo for the navy background. About 300px wide.',
                                    'site',
                                ),
                            ]),
                        ]),
                ]),
        ];
    }

    protected static function alertRecipients(string $name, string $label): TagsInput
    {
        return TagsInput::make($name)
            ->label($label)
            ->placeholder('office@example.com')
            ->nestedRecursiveRules(['email'])
            ->splitKeys(['Tab', ',', ' ']);
    }

    protected static function mailIsConfigured(): bool
    {
        return ! in_array(config('mail.default'), ['log', 'array'], true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Send test email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->schema([
                    TextInput::make('to')
                        ->label('Send a test email to')
                        ->email()
                        ->required()
                        ->default(fn () => auth()->user()?->email),
                ])
                ->modalSubmitActionLabel('Send')
                ->action(function (array $data): void {
                    if (! static::mailIsConfigured()) {
                        Notification::make()->warning()->title('Email is not set up yet')
                            ->body('Add the MAIL_ settings to the .env file on the server first.')->persistent()->send();

                        return;
                    }
                    try {
                        Mail::raw(
                            'This is a test email from the ' . config('app.name') . " website.\n\nIf you can read this, email alerts are working.",
                            fn ($message) => $message->to($data['to'])->subject('Test email from the ' . config('app.name') . ' website'),
                        );
                        Notification::make()->success()->title('Test email sent')
                            ->body("Sent to {$data['to']}. Check the inbox (and the junk folder).")->send();
                    } catch (Throwable $e) {
                        Notification::make()->danger()->title('The email could not be sent')
                            ->body($e->getMessage())->persistent()->send();
                    }
                }),
            ...parent::getHeaderActions(),
        ];
    }
}
