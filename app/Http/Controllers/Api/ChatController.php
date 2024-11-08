<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageEvent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Pusher\Pusher;

class ChatController extends Controller
{
    public function allMessages()
    {
        $user = auth()->user();
        $user_id = $user->id;
        $chat = Chat::has('messages')->with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id) {
            $q->where('sender_id', $user_id);
        })->orWhere(function ($q) use ($user_id) {
            $q->where('receiver_id', $user_id);
        })->orderBy('id', 'DESC')->get();
        if (count($chat)) return response()->json(['status' => true, 'Message' => "Chat Found", 'chat' => $chat], 200);
        else return response()->json(['status' => false, 'Message' => "Chat not Found", 'chat' => $chat]);
    }

    public function adminShowChat($id)
    {
        $user_id = $id;
        $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id) {
            $q->where('sender_id', $user_id);
        })->orWhere(function ($q) use ($user_id) {
            $q->where('receiver_id', $user_id);
        })->orderBy('id', 'DESC')->get();
        if (count($chat)) return response()->json(['status' => true, 'Message' => "Chat Found", 'chat' => $chat], 200);
        else return response()->json(['status' => false, 'Message' => "Chat not Found", 'chat' => $chat]);
    }

    public function chat(Request $request)
    {
        $user = auth()->user();
        $receiver_id = $request->receiver_id;
        $user_id = $user->id;
        $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
            $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
        })->orWhere(function ($q) use ($receiver_id, $user_id) {
            $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
        })->first();
        if (!is_object($chat)) {
            $chat = new Chat();
            $chat->sender_id = $user_id;
            $chat->receiver_id = $receiver_id;
            if ($chat->save()) {
                $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
                    $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
                })->orWhere(function ($q) use ($receiver_id, $user_id) {
                    $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
                })->first();
            }
        }
        return response()->json(['status' => true, 'Message' => "Done", 'chat' => $chat], 200);
    }

    public function message(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'receiver_id' => 'required',
            'message' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        try {
            DB::beginTransaction();
            $user = auth()->user();
            $user_id = $user->id;
            $receiver_id = $request->receiver_id;
            $receiver = User::where('id', $receiver_id)->first();
            $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
                $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
            })->orWhere(function ($q) use ($receiver_id, $user_id) {
                $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
            })->first();
            if (!is_object($chat)) {
                $chat = new Chat();
                $chat->sender_id = $user_id;
                $chat->receiver_id = $receiver_id;
                if (!$chat->save()) throw new Error("Chat not save!");
            }
            $message = new Message();
            $message->chat_id = $chat->id;
            $message->sender_id = $user_id;
            if (!empty($request->image)) {
                $message->image = $request->image;
                $message->title = $request->message;
            } else $message->message = $request->message;
            if (!$message->save()) throw new Error("Message not save!");
            $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
                $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
            })->orWhere(function ($q) use ($receiver_id, $user_id) {
                $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
            })->first();
            $data = ['user_id' => $user_id, 'message' => $message];
            $pusher = new \Pusher\Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), array('cluster' => env('PUSHER_APP_CLUSTER')));
            if (!$pusher->trigger('chat-' . $chat->id, 'message', $data)) throw new Error("Message not send!");
            NotiSend::sendNotif($receiver->device_token, $user_id, 'You have a new message. Please respond.', $request->message);
            // event(new MessageEvent($request->message, $chat->id));
            DB::commit();
            return response()->json(['status' => true, 'Message' => "Chat Found", 'chat' => $chat], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }
}
