<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function sendMail(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Send email (optional)
        Mail::raw($request->message, function ($mail) use ($request) {
            $mail->to('your-email@example.com')
                ->subject($request->subject)
                ->from($request->email, $request->name);
        });

        return response()->json([
            'message' => 'Your message has been sent successfully!'
        ], 200);
    }
}
