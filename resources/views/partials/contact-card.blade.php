@php
    $cardPhone = data_get($site, 'phone', '');
    $cardEmail = data_get($site, 'email');
@endphp
<aside class="aside-card" aria-label="Contact the office">
<h2>{{ data_get($site, 'card_heading') ?: 'Need help?' }}</h2>
<p>{{ data_get($site, 'card_text') ?: 'The office is open on school days. We are happy to answer your questions.' }}</p>
<a class="tel" href="tel:{{ preg_replace('/^0/', '+61', preg_replace('/\D+/', '', $cardPhone)) }}">{{ $cardPhone }}</a>
@if ($cardEmail)<a class="mail" href="mailto:{{ $cardEmail }}">{{ $cardEmail }}</a>@endif
</aside>
