@extends('layouts.site', [
    'title' => $page->seo_title ?: $page->title . ' | ' . data_get($site, 'school_name', 'Ellenbrook Primary School'),
    'metaDescription' => $page->seo_description,
])

@use('App\Models\PageContent')
@use('App\Filament\Blocks\PageBlocks')

@php
    $phone = data_get($site, 'phone', '');
    $tel = 'tel:' . preg_replace('/^0/', '+61', preg_replace('/\D+/', '', $phone));
    $icon = $page->iconUrl();

    // Split blocks into runs: "column" blocks share one content column (with the
    // optional office card beside it); "wide" blocks are full-width bands.
    $segments = [];
    foreach ($page->blocks ?? [] as $block) {
        $wide = in_array($block['type'], PageBlocks::WIDE, true);
        $last = array_key_last($segments);
        if (! $wide && $last !== null && $segments[$last]['wide'] === false) {
            $segments[$last]['blocks'][] = $block;
        } else {
            $segments[] = ['wide' => $wide, 'blocks' => [$block]];
        }
    }
    $cardShown = false;
@endphp

@section('content')

<!-- Page title -->
<section aria-labelledby="page-h1" style="background:#EEF6F8;position:relative;overflow:hidden">
<img src="{{ PageContent::url(data_get($site, 'page_header_art')) ?? asset('images/roo-navy.webp') }}" alt="" style="position:absolute;right:-30px;top:40px;width:300px;height:auto;opacity:.9">
<div class="wrap" style="position:relative;padding-top:36px;padding-bottom:44px">
<nav aria-label="Breadcrumb" class="crumb"><a href="{{ url('/') }}">Home</a> &#160;›&#160; @if ($sectionHome && $sectionHome->id !== $page->id)<a href="{{ $sectionHome->url() }}">{{ $page->sectionLabel() }}</a> &#160;›&#160; @endif<span aria-current="page">{{ $page->title }}</span></nav>
<div class="title-row">
@if ($icon)
<img class="page-icon" src="{{ $icon }}" alt="">
@endif
<div style="max-width:760px">
<h1 id="page-h1" class="page-h1" style="margin:0;font-weight:600;font-size:52px;line-height:1.06;letter-spacing:-0.02em;color:#032855">{{ $page->heading ?: $page->title }}</h1>
@if ($page->intro)
<p style="margin:14px 0 0;font-size:19px;line-height:1.55;color:#33475E">{{ $page->intro }}</p>
@endif
</div>
</div>
@if ($secNav->isNotEmpty())
<nav aria-label="In the {{ $page->sectionLabel() }} section" class="sec-nav">@foreach ($secNav as $link)<a href="{{ $link['url'] }}" @if ($link['current']) aria-current="page" @endif>{{ $link['label'] }}</a>@endforeach</nav>
@endif
</div>
<div class="dotline" style="position:absolute;left:0;right:0;bottom:0"></div>
</section>

{{-- Content blocks --}}
@foreach ($segments as $segment)
@if ($segment['wide'])
@include('blocks.' . str_replace('_', '-', $segment['blocks'][0]['type']), ['data' => $segment['blocks'][0]['data'] ?? []])
@else
@php $withCard = $page->show_contact_card && ! $cardShown; $cardShown = $cardShown || $withCard; @endphp
<section class="content-wrap"><div @class(['wrap', 'layout' => $withCard])><div class="prose" @if (! $withCard) style="max-width:none" @endif>
@foreach ($segment['blocks'] as $block)
@includeIf('blocks.' . str_replace('_', '-', $block['type']), ['data' => $block['data'] ?? []])
@endforeach
</div>
@if ($withCard) @include('partials.contact-card') @endif
</div></section>
@endif
@endforeach

@if ($page->show_contact_card && ! $cardShown)
<section class="content-wrap"><div class="wrap layout"><div class="prose"></div>@include('partials.contact-card')</div></section>
@endif

<!-- Next page and contact -->
<section aria-label="Keep exploring" style="padding:56px 0 96px">
<div class="wrap">
@if ($previous || $next)
<div class="next" style="display:flex;justify-content:space-between;gap:24px;flex-wrap:wrap;padding-bottom:40px;margin-bottom:48px;border-bottom:1px solid #D8E6EC">
@if ($previous)
<a href="{{ $previous->url() }}" style="display:flex;flex-direction:column;gap:4px"><span style="font-size:14px;color:#4A5D72">Previous</span><span class="nx" style="font-weight:600;font-size:22px">‹ {{ $previous->title }}</span></a>
@else <span></span> @endif
@if ($next)
<a href="{{ $next->url() }}" style="display:flex;flex-direction:column;gap:4px;text-align:right"><span style="font-size:14px;color:#4A5D72">Next</span><span class="nx" style="font-weight:600;font-size:22px">{{ $next->title }} ›</span></a>
@endif
</div>
@endif
<div style="background:#01ADB6;border-radius:24px;padding:40px 44px;display:flex;justify-content:space-between;align-items:center;gap:28px;flex-wrap:wrap">
<div style="max-width:620px">
<h2 style="margin:0 0 8px;font-weight:600;font-size:30px;line-height:1.15;color:#032855">{{ data_get($site, 'cta_heading') ?: 'Have a question about our school?' }}</h2>
<p style="margin:0;color:#032855;font-weight:400">{{ data_get($site, 'cta_text') ?: 'Our office team is happy to help. Call during school hours or send us a message.' }}</p>
</div>
<div style="display:flex;gap:12px;flex-wrap:wrap">
<a class="btn" href="{{ $tel }}" style="background:#032855;color:#ffffff">Call {{ $phone }}</a>
<a class="btn" href="{{ data_get($site, 'cta_message_url') ?: url('/#contact') }}" style="border-color:#032855;color:#032855">Send us a message</a>
</div>
</div>
</div>
</section>

@endsection
