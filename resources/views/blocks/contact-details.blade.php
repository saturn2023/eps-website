{{-- Block: Contact details and map — phone, email and address come from Site settings --}}
@php
    $cPhone = data_get($site, 'phone', '');
    $cEmail = data_get($site, 'email');
    $cAddress = data_get($site, 'address');
    $hours = $data['hours'] ?? null ?: data_get($site, 'hours_line');
    $icon = fn (string $path) => '<span class="ci"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#032855" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $path . '"></path></svg></span>';
@endphp
<div class="blk"><div class="layout">
<div>
@if (! empty($data['lead']))<p class="lead">{{ $data['lead'] }}</p>@endif
<ul class="contact-list">
<li>{!! $icon('M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2') !!}<span><strong>Call</strong><a href="tel:{{ preg_replace('/^0/', '+61', preg_replace('/\D+/', '', $cPhone)) }}">{{ $cPhone }}</a></span></li>
@if ($cEmail)<li>{!! $icon('M3 6h18v12H3z M3 7l9 6 9-6') !!}<span><strong>Email us</strong><a href="mailto:{{ $cEmail }}">{{ $cEmail }}</a></span></li>@endif
@if ($cAddress)<li>{!! $icon('M12 21s-7-6.5-7-12a7 7 0 0 1 14 0c0 5.5-7 12-7 12z M12 11.5a2.5 2.5 0 1 0 0-5a2.5 2.5 0 0 0 0 5z') !!}<span><strong>Visit</strong>{!! nl2br(e($cAddress)) !!}</span></li>@endif
@if ($hours)<li>{!! $icon('M12 3a9 9 0 1 0 0 18a9 9 0 1 0 0-18z M12 7v5l3 2') !!}<span><strong>School hours</strong>{{ $hours }}</span></li>@endif
</ul>
</div>
@if (! empty($data['map_embed_url']))
<div class="map"><iframe title="Map showing {{ data_get($site, 'school_name', 'the school') }}" src="{{ $data['map_embed_url'] }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>
@endif
</div></div>
