<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function show()
    {
        return view('support');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
            'email'    => 'required|email|max:150',
            'subject'  => 'required|string|max:200',
            'category' => 'nullable|in:Technical Issue,Billing,General Inquiry',
            'message'  => 'required|string|min:10|max:5000',
        ], [
            'name.regex' => 'Name may only contain letters, spaces and hyphens.',
        ]);

        return redirect()->route('support.thanks');
    }

    public function thanks()
    {
        return view('support-thanks');
    }
}
