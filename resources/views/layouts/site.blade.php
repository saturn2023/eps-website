@php
    $phone = data_get($site, 'phone', '(08) 9297 7300');
    $tel = 'tel:' . preg_replace('/^0/', '+61', preg_replace('/\D+/', '', $phone));
    $email = data_get($site, 'email');
    $absenceUrl = data_get($site, 'absence_url') ?: '#absence';
@endphp
<!doctype html>
<html lang="en-AU">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? data_get($site, 'school_name', 'Ellenbrook Primary School') }}</title>
@isset($metaDescription)<meta name="description" content="{{ $metaDescription }}">@endisset
<link rel="icon" href="{{ asset('images/logo-blue.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&amp;family=Libre+Baskerville:ital@1&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
<style>#translate-panel[hidden]{display:none}</style>
</head>
<body>
<div class="eps">
<a class="skip" href="#main">Skip to content</a>

<!-- Utility bar -->
<div class="util" style="background:#032855;color:#ffffff;font-size:14px">
<div class="wrap" style="display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:48px;flex-wrap:wrap">
<div class="util-hide" style="display:flex;align-items:center;gap:8px">
@if ($hoursLine = data_get($site, 'hours_line'))
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5FD6DC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a9 9 0 1 0 0 18a9 9 0 1 0 0-18z M12 7v5l3 2"></path></svg>
<span>{{ $hoursLine }}</span>
@endif
</div>
<div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap">
@if ($email)
<a class="util-hide" href="mailto:{{ $email }}" style="display:inline-flex;align-items:center;gap:8px">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5FD6DC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18v12H3z M3 7l9 6 9-6"></path></svg>
{{ $email }}</a>
@endif
<a class="btn btn-teal" href="{{ $absenceUrl }}" style="min-height:34px;padding:0 16px;font-size:14px;color:#032855">Record an absence</a>
</div>
</div>
</div>

@include('partials.header', compact('phone', 'tel', 'absenceUrl'))

<main id="main">
@yield('content')
</main>

@include('partials.footer', compact('phone', 'tel', 'email', 'absenceUrl'))

<!-- Floating translate control (Google Translate, loaded only once a language is chosen) -->
@php
    $languages = collect(data_get($site, 'translate_languages') ?: \App\Support\Translate::DEFAULT_LANGUAGES)
        ->filter(fn ($l) => filled($l['code'] ?? null) && filled($l['label'] ?? null));
@endphp
<div class="tr-wrap notranslate" translate="no" style="position:fixed;right:0;top:300px;z-index:40;display:flex;align-items:flex-start">
<div id="translate-panel" hidden style="width:290px;background:#ffffff;border-radius:16px 0 16px 16px;box-shadow:0 18px 40px rgba(3,40,85,.25);padding:22px">
<p style="margin:0 0 4px;font-weight:600;font-size:18px;color:#032855">Translate this page</p>
<p style="margin:0 0 14px;font-size:14px;color:#4A5D72">Choose a language</p>
<div class="field"><label for="tr-lang" class="sr-only">Language</label>
<select id="tr-lang"><option value="en">English</option>@foreach ($languages as $lang)<option value="{{ $lang['code'] }}">{{ $lang['label'] }}</option>@endforeach</select></div>
<p style="margin:12px 0 0;font-size:12px;color:#4A5D72">{{ data_get($site, 'translate_note') ?: 'Translated automatically by Google. Some words may not be exact.' }}</p>
</div>
<button class="tr-btn" type="button" id="tr-btn" aria-expanded="false" aria-controls="translate-panel" style="font:inherit;cursor:pointer;background:#032855;color:#ffffff;border:0;border-left:4px solid #01ADB6;border-radius:14px 0 0 14px;padding:18px 0;width:54px;display:flex;flex-direction:column;align-items:center;gap:10px;box-shadow:0 10px 24px rgba(3,40,85,.3)">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#5FD6DC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a9 9 0 1 0 0 18a9 9 0 1 0 0-18z M3 12h18 M12 3c2.5 2.5 3.8 5.6 3.8 9s-1.3 6.5-3.8 9 M12 3C9.5 5.5 8.2 8.6 8.2 12s1.3 6.5 3.8 9"></path></svg>
<span class="tr-tab" style="font-weight:500;font-size:15px;letter-spacing:.02em">Translate</span>
</button>
</div>

</div>

<div id="google_translate_element" hidden></div>
<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}"></script>
</body>
</html>
