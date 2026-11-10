{{-- Block: Document upload form — files are stored privately and listed under Document uploads in the admin --}}
@php
    $form = $data['form'] ?? 'enrolment';
    $id = $form . '-upload';
    $years = $data['year_levels'] ?? ['Kindergarten', 'Pre-primary', 'Year 1', 'Year 2', 'Year 3', 'Year 4', 'Year 5', 'Year 6'];
    $mine = old('form') === $form;   // only show old input / errors on the form that was sent
    $sent = session('upload_sent') === $form;
@endphp
<div class="blk">
<form class="upload" id="{{ $id }}" data-upload method="post" action="{{ route('documents.store') }}#{{ $id }}" enctype="multipart/form-data" aria-labelledby="{{ $id }}-h" novalidate>
@csrf
<input type="hidden" name="form" value="{{ $form }}">
<h3 id="{{ $id }}-h">{{ $data['heading'] ?? 'Upload your documents' }}</h3>
<p class="hint">{{ $data['hint'] ?? 'Send your completed form and supporting documents straight to the school. PDF, JPG or PNG files, up to 10 MB each and 10 files in total. Fields marked * are required.' }}</p>
@if ($sent)
<p class="status ok" role="status">{{ $data['success'] ?? 'Thank you. Your documents have been sent to the school office.' }}</p>
@endif
@if ($mine && $errors->any())
<p class="status err" role="alert">{{ $errors->first() }}</p>
@endif
<div class="row">
<div class="field"><label for="{{ $id }}-pn">Parent or carer name *</label><input id="{{ $id }}-pn" name="parent_name" type="text" autocomplete="name" required value="{{ $mine ? old('parent_name') : '' }}"></div>
<div class="field"><label for="{{ $id }}-em">Email *</label><input id="{{ $id }}-em" name="email" type="email" autocomplete="email" required value="{{ $mine ? old('email') : '' }}"></div>
</div>
<div class="row">
<div class="field"><label for="{{ $id }}-ph">Phone *</label><input id="{{ $id }}-ph" name="phone" type="tel" autocomplete="tel" required value="{{ $mine ? old('phone') : '' }}"></div>
<div class="field"><label for="{{ $id }}-sn">Child&#39;s full name *</label><input id="{{ $id }}-sn" name="student_name" type="text" required value="{{ $mine ? old('student_name') : '' }}"></div>
</div>
<div class="row">
<div class="field"><label for="{{ $id }}-yr">Year level</label><select id="{{ $id }}-yr" name="year_level"><option value="">Select</option>@foreach ($years as $year)<option @selected($mine && old('year_level') === $year)>{{ $year }}</option>@endforeach</select></div>
<div></div>
</div>
<label class="drop" for="{{ $id }}-files">
<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#01ADB6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4 M7 9l5-5 5 5 M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"></path></svg>
<strong>Drag your documents here</strong>
<span>or <u>choose files from your device</u></span>
<small>On a phone you can take a photo of each document</small>
<input id="{{ $id }}-files" name="documents[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
</label>
<ul class="files" aria-live="polite"></ul>
<label class="check"><input type="checkbox" name="consent" value="1" required><span>{{ $data['consent'] ?? 'I am the parent or legal guardian of this child, and I agree to these documents being sent to Ellenbrook Primary School for enrolment purposes.' }} *</span></label>
<button class="btn btn-teal" type="submit">Send documents</button>
<p class="status" role="status" aria-live="polite"></p>
</form>
</div>
