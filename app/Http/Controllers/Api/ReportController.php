<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\NotiSend;
use Error;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function report(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'skip' => 'required',
            'take' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $skip = $request->skip;
        $take = $request->take;
        $search = $request->search;
        $report = Report::orderBy('id', 'DESC')->with('users:id,username')->selectRaw('user_id, count(user_id) AS total')->groupBy('user_id');
        $report_count = Report::with('users:id,username')->selectRaw('user_id,count(user_id) AS total')->groupBy('user_id');
        if (!empty($search)) {
            $report->whereHas('users', function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%');
            });
            $report_count->whereHas('users', function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%');
            });
        }
        $reports = $report->skip($skip)->take($take)->get();
        $report_counts = $report_count->get()->count();
        if (count($reports)) return response()->json(['status' => true, 'Message' => 'Reports found', 'count' => $reports ?? [], 'totalCount' => $report_counts ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Reports not found', 'count' => $reports ?? [], 'totalCount' => $report_counts ?? []]);
    }

    public function reports(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'skip' => 'required',
            'take' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $id = $request->id;
        $skip = $request->skip;
        $take = $request->take;
        $search = $request->search;
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);
        $report = Report::orderBy('id', 'DESC')->with(['users', 'shop.role'])->where('user_id', $id);
        $report_count = Report::with(['users', 'shop.role'])->where('user_id', $id);
        if (!empty($search)) {
            $report->where(function ($q) use ($search) {
                $q->whereRelation('users', 'username', 'like', '%' . $search . '%')
                    ->orWhereRelation('shop', 'username', 'like', '%' . $search . '%');
            });
            $report_count->where(function ($q) use ($search) {
                $q->whereRelation('users', 'username', 'like', '%' . $search . '%')
                    ->orWhereRelation('shop', 'username', 'like', '%' . $search . '%');
            });
        }
        $reports = $report->skip($skip)->take($take)->get();
        $report_counts = $report_count->count();
        if (count($reports)) return response()->json(['status' => true, 'Message' => 'Reports found', 'reports' => $reports ?? [], 'reportsCount' => $report_counts ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Reports not found', 'reports' => $reports ?? [], 'reportsCount' => $report_counts ?? []]);
    }

    public function addReport(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'message' => 'required',
            'shop_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $report = new Report();
            $report->user_id = auth()->user()->id;
            $report->shop_id = $request->shop_id;
            $report->reason = $request->message;
            if (!$report->save()) throw new Error("Report not added!");
            $user = User::whereRelation('role', 'name', 'admin')->first();
            $title = 'NEW REPORT';
            $message = 'You have recieved new report';
            $appnot = new AppNotification();
            $appnot->user_id = $user->id;
            $appnot->notification = $message;
            $appnot->navigation = $title;
            $appnot->save();
            NotiSend::sendNotif($user->device_token, '', $title, $message);
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Report Added'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function deleteReport($id)
    {
        $report = Report::where('id', $id)->first();
        if (!empty($report)) {
            if ($report->delete()) return response()->json(['status' => true, 'Message' => 'Successfully deleted Report'], 200);
            else return response()->json(["status" => false, 'Message' => 'Report not deleted']);
        } else return response()->json(["status" => false, 'Message' => 'Report not found']);
    }

    public function deleteAllUserReport($user_id)
    {
        $report = Report::where('id', $user_id)->get();
        if (count($report)) {
            foreach ($report as $key => $value) {
                $value->delete();
            }
            return response()->json(['status' => true, 'Message' => 'Successfully deleted User Reports'], 200);
        } else return response()->json(["status" => false, 'Message' => 'Report not found']);
    }

    public function deleteAllReport()
    {
        $report = Report::all();
        if (count($report)) {
            foreach ($report as $key => $value) {
                $value->delete();
            }
            return response()->json(['status' => true, 'Message' => 'Successfully deleted Reports'], 200);
        } else return response()->json(["status" => false, 'Message' => 'Report not found']);
    }
}
