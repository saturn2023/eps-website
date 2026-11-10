{{-- Enquiry form, used on the home page and the Contact page. Messages appear under Enquiries in the admin. --}}
<form id="contact" class="split-pad" method="post" action="{{ route('enquiries.store') }}" aria-labelledby="form-h" style="background:#ffffff;padding:{{ $padding ?? '48px' }}">
@csrf
<h2 id="form-h" style="margin:0 0 8px;font-weight:600;font-size:30px;color:#032855">{{ $heading ?: 'Send us a message' }}</h2>
@if (! empty($intro))
<p style="margin:0 0 24px;color:#4A5D72">{{ $intro }}</p>
@endif
@if (session('enquiry_sent'))
<p class="form-ok" role="status">{{ $success ?: 'Thanks, your message has been sent.' }}</p>
@endif
{{-- Honeypot: real people never fill this in --}}
<div class="sr-only" aria-hidden="true"><label for="f-website">Website</label><input id="f-website" type="text" name="website" tabindex="-1" autocomplete="off"></div>
<div class="form-row" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-bottom:16px">
<div class="field"><label for="f-name">Your name</label><input id="f-name" name="name" type="text" autocomplete="name" value="{{ old('name') }}" required>@error('name')<span class="err">{{ $message }}</span>@enderror</div>
<div class="field"><label for="f-phone">Phone</label><input id="f-phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}">@error('phone')<span class="err">{{ $message }}</span>@enderror</div>
</div>
<div class="form-row" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-bottom:16px">
<div class="field"><label for="f-email">Email</label><input id="f-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required>@error('email')<span class="err">{{ $message }}</span>@enderror</div>
<div class="field"><label for="f-topic">Enquiry about</label>
<select id="f-topic" name="topic">
@foreach ($topics ?? [] as $topic)
<option @selected(old('topic') === $topic)>{{ $topic }}</option>
@endforeach
</select></div>
</div>
<div class="field" style="margin-bottom:22px"><label for="f-msg">Message</label><textarea id="f-msg" name="message" rows="4" required>{{ old('message') }}</textarea>@error('message')<span class="err">{{ $message }}</span>@enderror</div>
<button class="btn btn-teal" type="submit" style="font:inherit;font-weight:500;cursor:pointer">Send message</button>
</form>
