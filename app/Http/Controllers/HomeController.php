<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\PageContent;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function show()
    {
        return view('home', [
            'home' => PageContent::for(PageContent::HOME),
            'site' => PageContent::for(PageContent::SITE),
        ]);
    }

    public function storeEnquiry(Request $request)
    {
        // Back to whichever page the form was on (home or Contact), at the form.
        $back = strtok(url()->previous(), '#') . '#contact';

        // Honeypot field filled in = a bot. Pretend it worked.
        if ($request->filled('website')) {
            return redirect()->to($back)->with('enquiry_sent', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:190'],
            'topic' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Enquiry::create($data);

        return redirect()->to($back)->with('enquiry_sent', true);
    }
}
