<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // Honeypot: real visitors never fill this hidden field, so a filled
        // value marks the submission as spam. Pretend it succeeded instead
        // of validating, so bots get no signal on how the trap works.
        if ($request->filled('website')) {
            return back()->with('status', 'contact-sent');
        }

        $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'name.required'    => __('Nama wajib diisi.'),
            'email.required'   => __('Email wajib diisi.'),
            'email.email'      => __('Format email tidak valid.'),
            'message.required' => __('Pesan wajib diisi.'),
        ]);

        $contactMessage = ContactMessage::create($request->only('name', 'email', 'message'));

        Mail::to(config('mail.contact_to'))->send(new ContactMessageReceived($contactMessage));

        return back()->with('status', 'contact-sent');
    }
}
