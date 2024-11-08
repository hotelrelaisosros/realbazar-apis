<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\NotiSend;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function allNotification($skip, $take, $status)
    {
        $not = AppNotification::with('user');
        $not_count = AppNotification::query();

        if ($status == '1') {
            $not->where('status', 1);
            $not_count->where('status', 1);
        }
        if ($status == '0') {
            $not->where('status', 0);
            $not_count->where('status', 0);
        }
        // if ($skip && $take) {
            $not->skip($skip)->take($take);
        // }
        $noti = $not->orderBy('id', 'DESC')->get();
        $noti_count = $not_count->count();
        $total_noti_count = AppNotification::count();
        if (count($noti)) return response()->json(['status' => true, 'Message' => 'Notification Found', 'notifications' => $noti ?? [], 'notifications_count' => $noti_count ?? [], 'total_notifications_count' => $total_noti_count ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Notification not Found', 'notifications' => $noti ?? [], 'notifications_count' => $noti_count ?? [], 'total_notifications_count' => $total_noti_count ?? []]);
    }

    public function notification()
    {
        $noti = AppNotification::orderBy('id', 'DESC')->where('user_id', auth()->user()->id)->where('status', '0')->get();
        $notifications_count = AppNotification::where('user_id', auth()->user()->id)->where('status', '0')->count();
        if (count($noti)) return response()->json(['status' => true, 'Message' => "Notifications found", 'Notifications' => $noti ?? [], "notifications_count" => $notifications_count ?? []], 200);
        else return response()->json(['status' => false, 'Message' => "Notifications not found", 'Notifications' => $noti ?? [], "notifications_count" => $notifications_count ?? []]);
    }

    public function notification_change($id = null)
    {
        if (!empty($id)) {
            $noti = AppNotification::where('user_id', auth()->user()->id)->where('status', '0')->where('id', $id)->first();
            $noti->status = 1;
            $noti->save();
        } else {
            $noti = AppNotification::where('user_id', auth()->user()->id)->where('status', '0')->get();
            if (!count($noti)) return response()->json(['status' => false, 'Message' => "Notifications not found"]);
            foreach ($noti as $key => $value) {
                $value->status = 1;
                $value->save();
            }
        }
        $notifications_count = AppNotification::where('user_id', auth()->user()->id)->where('status', '0')->count();
        return response()->json(['status' => true, 'Message' => "Notifications found", 'Notifications' => $noti ?? [], "notifications_count" => $notifications_count ?? []], 200);
    }

    public function sendNotification(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'role' => 'required',
            'message' => 'required',
            'title' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $role = $request->role;
            $users = User::whereHas('role', function ($query) use ($role) {
                $query->where('name', $role);
            })->get();
            if (!count($users)) return response()->json(['status' => false, 'Message' => "Users not found"]);
            foreach ($users as  $user) {
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $request->message;
                $appnot->navigation = $request->title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $request->title, $request->message);
            }
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Notification Send'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function singleNotification(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'message' => 'required',
            'title' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $user = User::where('id', $request->id)->first();
            if (empty($user)) return response()->json(['status' => false, 'Message' => "User not found"]);
            $appnot = new AppNotification();
            $appnot->user_id = $user->id;
            $appnot->notification = $request->message;
            $appnot->navigation = $request->title;
            $appnot->save();
            NotiSend::sendNotif($user->device_token, '', $request->title, $request->message);
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Notification Send'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function sendAllNotification(Request $request)
    {
        $valid = Validator::make($request->all(), [
            // 'role' => 'required',
            'message' => 'required',
            'title' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $role = 'admin';
            $users = User::whereHas('role', function ($query) use ($role) {
                $query->where('name', '!=', $role);
            })->get();
            if (!count($users)) return response()->json(['status' => false, 'Message' => "Users not found"]);
            foreach ($users as  $user) {
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $request->message;
                $appnot->navigation = $request->title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $request->title, $request->message);
            }
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Notification Send'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }
}
