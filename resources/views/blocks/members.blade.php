{{-- Block: People list — e.g. School Board members, with initials badges --}}
@php
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim(preg_replace('/\(.*?\)/', '', $name))))
        ->filter()->pipe(fn ($w) => mb_strtoupper(mb_substr($w->first(), 0, 1) . ($w->count() > 1 ? mb_substr($w->last(), 0, 1) : '')));
@endphp
<div class="blk">
@if (! empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
@if (! empty($data['intro']))<p style="max-width:780px">{{ $data['intro'] }}</p>@endif
<ul class="members">
@foreach ($data['members'] ?? [] as $member)
<li><span class="av" aria-hidden="true">{{ $initials($member['name'] ?? '') }}</span><span><strong>{{ $member['name'] ?? '' }}</strong><span>{{ $member['role'] ?? '' }}</span></span></li>
@endforeach
</ul>
</div>
