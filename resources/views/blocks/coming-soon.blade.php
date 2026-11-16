{{-- Block: Coming soon — dashed placeholder panel --}}
@use('App\Models\PageContent')
@php $img = PageContent::url($data['icon'] ?? null) ?? asset('images/icons/school.png'); @endphp
<div class="blk"><div class="soon" role="note">
<img src="{{ $img }}" alt="{{ \App\Models\Media::altFor($data['icon'] ?? null) }}" width="84" height="84">
<div><strong>{{ $data['heading'] ?? 'Content coming soon' }}</strong>{!! str($data['body'] ?? '')->sanitizeHtml() !!}</div>
</div></div>
