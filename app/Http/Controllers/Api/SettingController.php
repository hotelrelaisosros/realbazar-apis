<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function subscribe(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required',
            'content' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $email = $request->email;
        $content = $request->content;
        Mail::send('web.mail.subscribe',  compact('email', 'content'), function ($message) use ($email) {
            $message->to($email);
            $message->subject('Subscribe');
        });
        return response()->json(['status' => true, 'Message' => "Email send to {$email}"], 200);
    }
}
