<x-mail::message>
# {{ $submission->formLabel() }}

A family has uploaded documents through the website. For privacy the files are not attached. Log in to the admin to view and download them.

<x-mail::table>
| | |
|:--|:--|
| **Student** | {{ $submission->student_name }} |
| **Year level** | {{ $submission->year_level ?: '—' }} |
| **Parent / guardian** | {{ $submission->parent_name }} |
| **Email** | {{ $submission->email }} |
| **Phone** | {{ $submission->phone }} |
| **Files** | {{ count($submission->files ?? []) }} |
| **Sent** | {{ $submission->created_at->timezone(config('app.timezone'))->format('j M Y, g:ia') }} |
</x-mail::table>

@foreach ($submission->files ?? [] as $file)
- {{ $file['name'] }}
@endforeach

<x-mail::button :url="$adminUrl">
View the documents
</x-mail::button>

{{ config('app.name') }} website
</x-mail::message>
