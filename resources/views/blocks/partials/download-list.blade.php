@use('App\Models\PageContent')
<ul @class(['dl-list', 'dl-grid' => ! empty($grid)])>
@foreach ($files as $file)
@php
    $href = PageContent::url($file['file'] ?? null) ?: ($file['url'] ?? null);
    $ext = $file['file'] ?? null ? strtoupper(pathinfo($file['file'], PATHINFO_EXTENSION)) : ($href ? 'WEB' : 'PDF');
    $note = $file['note'] ?? null;
@endphp
<li>
@if ($href)
<a class="dl" href="{{ $href }}" @if (empty($file['file'])) target="_blank" rel="noopener" @endif>
<span @class(['ft', 'web' => $ext === 'WEB'])>{{ $ext }}</span><span class="t">{{ $file['label'] ?? '' }}@if ($note)<small>{{ $note }}</small>@endif</span></a>
@else
<a class="dl pending" href="#" aria-disabled="true" onclick="return false"><span class="ft">{{ $ext }}</span><span class="t">{{ $file['label'] ?? '' }}<small>{{ $note ?: 'File to be uploaded' }}</small></span></a>
@endif
</li>
@endforeach
</ul>
