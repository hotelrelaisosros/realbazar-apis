<?php

namespace App\Http\Controllers\Api;

use App\Models\Subscriber;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriberRequest;
use App\Http\Requests\UpdateSubscriberRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'emailphone' => 'required|email',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $valid->errors()
            ], 422);
        }

        $email = $request->emailphone;
        $token = hash('sha256', time());

        $subscriber = Subscriber::where('email', $email)->first();

        if ($subscriber) {
            if ($subscriber->status === 'Pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Subscription is already pending. Please check your email to verify.'
                ], 400);
            }

            if ($subscriber->status === 'Active') {
                return response()->json([
                    'status' => false,
                    'message' => 'You are already subscribed.'
                ], 400);
            }
            if ($subscriber->status === 'Pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Please check your email for subscription request'
                ], 400);
            }
        } else {
            $subscriber = new Subscriber();
            $subscriber->email = $email;
            $subscriber->token = $token;
            $subscriber->status = 'Pending';
            $subscriber->save();
        }

        // Send verification email
        $link = url('subscriber/verify/' . $token . '/' . $email);
        Mail::send('admin.mail.appRegister', compact('email', 'link'), function ($message) use ($email) {
            $message->to($email);
            $message->subject('Subscribe to Pros Art');
        });

        return response()->json([
            'status' => true,
            'message' => 'Please check your email to verify your subscription.'
        ], 200);
    }

    public function verify($token, $email)
    {
        $subscriber_data = Subscriber::where('token', $token)->where('email', $email)->first();

        if ($subscriber_data) {
            $subscriber_data->token = ''; // Clear the token after successful verification
            $subscriber_data->status = 'Active';
            $subscriber_data->save();

            return response()->json(['status' => true, 'message' => 'You have successfully verified as a subscriber to this system'], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Invalid or expired verification link'], 400);
        }
    }
}
