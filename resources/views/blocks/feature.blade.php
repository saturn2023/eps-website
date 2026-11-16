{{-- Block: Feature row — icon with a heading and short text on a light panel --}}
@use('App\Models\PageContent')
@php $img = PageContent::url($data['icon'] ?? null); @endphp
<div class="blk"><div class="feature-row">
@if ($img)<img src="{{ $img }}" alt="{{ \App\Models\Media::altFor($data['icon'] ?? null) }}">@endif
<div>
@if (! empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
{!! str($data['body'] ?? '')->sanitizeHtml() !!}
</div>
</div></div>
