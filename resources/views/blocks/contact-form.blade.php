{{-- Block: Enquiry form — messages appear under Enquiries in the admin --}}
<div class="blk" style="margin-top:32px">
<div style="border:1.5px solid #D8E6EC;border-radius:24px;overflow:hidden;max-width:820px">
@include('partials.enquiry-form', [
    'heading' => $data['heading'] ?? null,
    'intro' => $data['intro'] ?? null,
    'success' => $data['success'] ?? null,
    'topics' => $data['topics'] ?? ['Enrolments', 'Something else'],
    'padding' => '40px',
])
</div>
</div>
