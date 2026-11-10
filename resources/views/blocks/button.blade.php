{{-- Block: Button — a single call-to-action button, with an optional note --}}
@php
    $url = $data['url'] ?? null;
    $external = $url && str_starts_with($url, 'http');
@endphp
<div class="blk">
<p>
@if ($url)
<a class="btn btn-{{ $data['style'] ?? 'teal' }}" href="{{ $url }}" @if ($external) target="_blank" rel="noopener" @endif>{{ $data['label'] ?? '' }}</a>
@else
<a class="btn btn-{{ $data['style'] ?? 'teal' }}" href="#" aria-disabled="true" onclick="return false">{{ $data['label'] ?? '' }}</a>
@endif
</p>
@if (! empty($data['note']))<p class="note-pending">{{ $data['note'] }}</p>@endif
</div>
