{{-- Block: Text — lead paragraph, rich text, optional downloads, signature and side photo --}}
@use('App\Models\PageContent')
@php
    $photo = PageContent::url($data['photo'] ?? null);
    $hasSide = $photo || filled($data['photo_placeholder'] ?? null);
@endphp
<div class="blk">
@if ($hasSide)<div class="layout" style="grid-template-columns:minmax(0,1fr) 300px">@endif
<div @class(['ol-cards' => ! empty($data['numbered_cards'])])>
@if (! empty($data['lead']))
<p class="lead">{{ $data['lead'] }}</p>
@endif
{!! str($data['body'] ?? '')->sanitizeHtml() !!}
@if (! empty($data['downloads']))
@include('blocks.partials.download-list', ['files' => $data['downloads']])
@endif
@if (! empty($data['signature_name']))
<div class="sig"><strong>{{ $data['signature_name'] }}</strong>@if (! empty($data['signature_role']))<br>{{ $data['signature_role'] }}@endif</div>
@endif
</div>
@if ($hasSide)
<div>
@if ($photo)
<img class="principal-photo-img" src="{{ $photo }}" alt="{{ ($data['photo_alt'] ?? null) ?: \App\Models\Media::altFor($data['photo'] ?? null) }}">
@if (! empty($data['photo_caption']))<p class="photo-caption">{{ $data['photo_caption'] }}</p>@endif
@else
<div class="principal-photo" role="img" aria-label="{{ $data['photo_placeholder'] }}">{{ $data['photo_placeholder'] }}</div>
@endif
</div>
</div>
@endif
</div>
