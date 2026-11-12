<x-mail::message>
# New website enquiry

Someone sent a message through the website. Reply to this email to answer them directly.

<x-mail::table>
| | |
|:--|:--|
| **Name** | {{ $enquiry->name }} |
| **Email** | {{ $enquiry->email }} |
| **Phone** | {{ $enquiry->phone ?: '—' }} |
| **Topic** | {{ $enquiry->topic ?: '—' }} |
| **Sent** | {{ $enquiry->created_at->timezone(config('app.timezone'))->format('j M Y, g:ia') }} |
</x-mail::table>

**Message**

{{ $enquiry->message }}

<x-mail::button :url="$adminUrl">
Open in the admin
</x-mail::button>

{{ config('app.name') }} website
</x-mail::message>
