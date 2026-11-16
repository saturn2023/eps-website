@extends('layouts.site', [
    'title' => data_get($home, 'seo.title') ?: data_get($site, 'school_name', 'Ellenbrook Primary School'),
    'metaDescription' => data_get($home, 'seo.description'),
])

@use('App\Models\PageContent')
@use('App\Support\QuickLinkIcons')

@php
    $rich = fn ($html) => str($html ?? '')->sanitizeHtml();
    // The dotted "badge" ring used behind icons and timeline points.
    $ring = '<g fill="#01ADB6"><circle cx="60" cy="32" r="3.2"></circle><circle cx="51.8" cy="51.8" r="3.2"></circle><circle cx="32" cy="60" r="3.2"></circle><circle cx="12.2" cy="51.8" r="3.2"></circle><circle cx="4" cy="32" r="3.2"></circle><circle cx="12.2" cy="12.2" r="3.2"></circle><circle cx="32" cy="4" r="3.2"></circle><circle cx="51.8" cy="12.2" r="3.2"></circle></g>';

    $hero = data_get($home, 'hero', []);
    $heroImage = PageContent::url($hero['image'] ?? null);
    $heroVideo = ($hero['media_type'] ?? 'image') === 'video' ? PageContent::url($hero['video'] ?? null) : null;
    $heroVideoMobile = $heroVideo ? PageContent::url($hero['video_mobile'] ?? null) : null;
    $heroPosition = ['left' => '20% 40%', 'center' => '50% 50%', 'right' => '80% 40%'][$hero['focus'] ?? 'left'] ?? '20% 40%';
@endphp

@section('content')

{{-- 1. Hero --}}
<section class="hero" aria-label="Welcome" style="position:relative;min-height:640px;background:#032855;overflow:hidden;display:flex;align-items:center">
@if ($heroVideo)
<video id="hero-video" class="hero-media" autoplay muted loop playsinline preload="metadata" @if ($heroImage) poster="{{ $heroImage }}" @endif aria-hidden="true" style="object-position:{{ $heroPosition }}">
@if ($heroVideoMobile)<source src="{{ $heroVideoMobile }}" type="video/mp4" media="(max-width: 760px)">@endif
<source src="{{ $heroVideo }}" type="video/mp4">
</video>
@elseif ($heroImage)
<img class="hero-media" src="{{ $heroImage }}" alt="{{ \App\Models\Media::altFor($hero['image'] ?? null) }}" style="object-position:{{ $heroPosition }}">
@endif
<div class="hero-scrim" style="position:absolute;inset:0"></div>
<div class="wrap" style="position:relative;width:100%;padding-bottom:64px">
<div style="max-width:560px;color:#ffffff">
@if (! empty($hero['tagline']))
<p class="serif" style="margin:0 0 14px;font-size:22px;color:#7FE3E8">{{ $hero['tagline'] }}</p>
@endif
<h1 style="margin:0;font-weight:600;font-size:64px;line-height:1.04;letter-spacing:-0.02em;color:#ffffff">{{ $hero['heading'] ?? '' }}</h1>
@if (! empty($hero['intro']))
<p style="margin:22px 0 0;font-size:19px;line-height:1.55;color:#E6F1F5;max-width:480px">{{ $hero['intro'] }}</p>
@endif
</div>
</div>
@if ($heroVideo)
<button id="hero-pause" class="hero-pause" type="button" aria-label="Pause background video" style="position:absolute;right:32px;bottom:104px;width:48px;height:48px;border-radius:50%;border:2px solid rgba(255,255,255,.8);background:rgba(3,40,85,.55);display:flex;align-items:center;justify-content:center;cursor:pointer">
<svg width="18" height="18" viewBox="0 0 24 24" fill="#ffffff" aria-hidden="true"><path d="M7 5h3.5v14H7z M13.5 5H17v14h-3.5z"></path></svg>
</button>
@endif
</section>

{{-- 2. Quick links --}}
@php $quick = data_get($home, 'quick_links', []); @endphp
@if (! empty($quick['items']))
<section class="quick" aria-labelledby="quick-h" style="position:relative;z-index:2;margin-top:-64px">
<div class="wrap">
<div style="background:#ffffff;border-radius:20px;box-shadow:0 18px 40px rgba(3,40,85,.16);padding:28px 32px 24px">
<h2 id="quick-h" style="margin:0 0 18px;font-size:15px;font-weight:500;color:#4A5D72">{{ $quick['heading'] ?? 'Quick links for families' }}</h2>
<div class="quick-grid" style="display:grid;grid-template-columns:repeat({{ min(count($quick['items']), 6) }},minmax(0,1fr));gap:12px">
@foreach ($quick['items'] as $item)
<a href="{{ $item['url'] ?? '#' }}" style="display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center;padding:8px 4px">
<img class="ql-icon" src="{{ QuickLinkIcons::url($item['icon'] ?? null) }}" alt="">
<span class="ql" style="font-weight:500;font-size:16px;line-height:1.3">{{ $item['label'] ?? '' }}</span></a>
@endforeach
</div>
</div>
</div>
</section>
@endif

{{-- 3. Welcome --}}
@php
    $welcome = data_get($home, 'welcome', []);
    $welcomeArt = PageContent::url($welcome['art'] ?? null);
    $welcomePhoto = PageContent::url($welcome['photo'] ?? null);
@endphp
<section class="welcome" aria-labelledby="welcome-h" style="display:grid;grid-template-columns:minmax(0,5fr) minmax(0,7fr);margin-top:72px;align-items:end">
<div class="welcome-art" style="position:relative;min-height:600px;align-self:stretch">
@if ($welcomeArt)
<img src="{{ $welcomeArt }}" alt="" style="position:absolute;left:0;bottom:0;width:min(100%,520px);height:auto;display:block">
@endif
@if ($welcomePhoto)
<img src="{{ $welcomePhoto }}" alt="{{ ($welcome['photo_alt'] ?? null) ?: \App\Models\Media::altFor($welcome['photo'] ?? null) }}" style="position:absolute;left:30%;top:24px;width:min(70%,440px);aspect-ratio:3 / 2;object-fit:cover;border-radius:24px;border:6px solid #ffffff;box-shadow:0 18px 40px rgba(3,40,85,.22)">
@endif
</div>
<div class="welcome-copy" style="padding:24px 40px 96px 56px;max-width:720px">
<h2 id="welcome-h" class="big-h2" style="margin:0 0 22px;font-weight:600;font-size:44px;line-height:1.1;letter-spacing:-0.015em;color:#032855">{{ $welcome['heading'] ?? '' }}</h2>
<div class="rich" style="margin:0 0 32px">{!! $rich($welcome['body'] ?? '') !!}</div>
@if (! empty($welcome['buttons']))
<div style="display:flex;gap:14px;flex-wrap:wrap">
@foreach ($welcome['buttons'] as $button)
<a class="btn btn-{{ $button['style'] ?? 'teal' }}" href="{{ $button['url'] ?? '#' }}">{{ $button['label'] ?? '' }}</a>
@endforeach
</div>
@endif
</div>
</section>

{{-- 4. Stand-out phrase --}}
@php $belief = data_get($home, 'belief', []); $beliefIcon = PageContent::url($belief['icon'] ?? null); @endphp
@if (! empty($belief['phrase']))
<section aria-label="Our belief" style="background:#032855;color:#ffffff;position:relative;overflow:hidden">
<div class="wrap" style="display:flex;align-items:center;gap:56px;min-height:300px;padding-top:56px;padding-bottom:56px;flex-wrap:wrap">
@if ($beliefIcon)
<img src="{{ $beliefIcon }}" alt="" style="width:150px;height:auto;flex:0 0 auto;opacity:.95">
@endif
<p class="phrase serif" style="margin:0;flex:1 1 520px;font-size:38px;line-height:1.35;color:#ffffff;max-width:860px">{{ $belief['phrase'] }}</p>
</div>
<div class="dotline-dark" style="position:absolute;left:0;right:0;bottom:22px;opacity:.9"></div>
</section>
@endif

{{-- 5. Community and history --}}
@php $community = data_get($home, 'community', []); $milestones = $community['milestones'] ?? []; @endphp
<section id="about" class="section-pad" aria-labelledby="story-h" style="background:#EEF6F8;padding:96px 0">
<div class="wrap">
<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:32px;flex-wrap:wrap;margin-bottom:56px">
<div style="max-width:680px">
<h2 id="story-h" style="margin:0 0 18px;font-weight:600;font-size:40px;line-height:1.12;letter-spacing:-0.015em;color:#032855">{{ $community['heading'] ?? '' }}</h2>
<div class="rich">{!! $rich($community['intro'] ?? '') !!}</div>
</div>
@if (! empty($community['button_label']))
<a class="btn btn-line" href="{{ $community['button_url'] ?? '#' }}">{{ $community['button_label'] }}</a>
@endif
</div>
@if ($milestones)
<div class="dotline timeline-line" style="margin:0 0 -42px 36px"></div>
<ol class="timeline" style="list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat({{ min(count($milestones), 4) }},minmax(0,1fr));gap:32px">
@foreach ($milestones as $milestone)
<li style="position:relative">
<svg width="72" height="72" viewBox="0 0 64 64" aria-hidden="true" style="display:block;background:#EEF6F8;border-radius:50%">{!! $ring !!}<circle cx="32" cy="32" r="21" fill="{{ ! empty($milestone['highlight']) ? '#01ADB6' : '#032855' }}"></circle></svg>
<p style="margin:18px 0 6px;font-weight:600;font-size:30px;color:#032855;line-height:1">{{ $milestone['year'] ?? '' }}</p>
<p style="margin:0;max-width:260px">{{ $milestone['text'] ?? '' }}</p>
</li>
@endforeach
</ol>
@endif
</div>
</section>

{{-- 6. Find your way around --}}
@php $explore = data_get($home, 'explore', []); @endphp
@if (! empty($explore['columns']))
<section aria-labelledby="explore-h" style="padding:96px 0 88px">
<div class="wrap">
<h2 id="explore-h" style="margin:0 0 40px;font-weight:600;font-size:40px;line-height:1.12;letter-spacing:-0.015em;color:#032855">{{ $explore['heading'] ?? '' }}</h2>
<div class="explore explore-grid" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:40px">
@foreach ($explore['columns'] as $column)
@php $wide = ! empty($column['full_width']); @endphp
<nav id="{{ \Illuminate\Support\Str::slug($column['title'] ?? '') }}" aria-label="{{ $column['title'] ?? '' }}" style="border-top:4px solid {{ $wide ? '#01ADB6' : '#032855' }};padding-top:22px{{ $wide ? ';grid-column:1 / -1' : '' }}">
<h3 style="margin:0 0 14px;font-weight:600;font-size:22px;color:#032855">{{ $column['title'] ?? '' }}</h3>
@if (! empty($column['summary']))
<p style="margin:0 0 10px">{{ $column['summary'] }}</p>
@endif
@if (! empty($column['links']))
<ul @if ($wide) class="explore-grid" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px 18px" @endif>
@foreach ($column['links'] as $link)
<li><a href="{{ $link['url'] ?? '#' }}">{{ $link['label'] ?? '' }}</a></li>
@endforeach
</ul>
@endif
</nav>
@endforeach
</div>
</div>
</section>
@endif

{{-- 7. Enrolments + enquiry form --}}
@php
    $enrol = data_get($home, 'enrol', []);
    $enrolArt = PageContent::url($enrol['art'] ?? null);
    $topics = $enrol['topics'] ?? [];
@endphp
<section id="enrol" aria-label="Enrolments and enquiries" style="padding:0 0 96px">
<div class="wrap">
<div class="split" style="display:grid;grid-template-columns:minmax(0,5fr) minmax(0,7fr);border-radius:28px;overflow:hidden;box-shadow:0 18px 40px rgba(3,40,85,.12)">
<div class="split-pad" style="background:#01ADB6;color:#032855;padding:56px 48px;position:relative;overflow:hidden">
<h2 style="margin:0 0 16px;font-weight:600;font-size:36px;line-height:1.12;letter-spacing:-0.015em;color:#032855">{{ $enrol['heading'] ?? '' }}</h2>
<div class="rich" style="margin:0 0 28px;font-weight:400;color:#032855">{!! $rich($enrol['body'] ?? '') !!}</div>
<div style="display:flex;flex-direction:column;gap:12px;align-items:flex-start;position:relative;z-index:1">
@if (! empty($enrol['primary_label']))
<a class="btn btn-navy" href="{{ $enrol['primary_url'] ?? '#' }}">{{ $enrol['primary_label'] }}</a>
@endif
@if (! empty($enrol['show_call_button']))
@php $phone = data_get($site, 'phone', ''); @endphp
<a class="btn" href="tel:{{ preg_replace('/^0/', '+61', preg_replace('/\D+/', '', $phone)) }}" style="border-color:#032855;color:#032855">Call {{ $phone }}</a>
@endif
</div>
@if ($enrolArt)
<img src="{{ $enrolArt }}" alt="" style="position:absolute;right:-20px;bottom:24px;width:220px;height:auto;opacity:.9">
@endif
</div>
@include('partials.enquiry-form', [
    'heading' => $enrol['form_heading'] ?? null,
    'intro' => $enrol['form_intro'] ?? null,
    'success' => $enrol['form_success'] ?? null,
    'topics' => $topics,
])
</div>
</div>
</section>

@endsection
