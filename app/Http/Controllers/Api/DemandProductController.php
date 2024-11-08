<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DemandProduct;
use App\Models\DemandProductImage;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\NotiSend;
use App\Models\CompleteDemandProduct;
use Illuminate\Support\Facades\Validator;

class DemandProductController extends Controller
{
    public function demandProduct()
    {
        $demand = DemandProduct::with(['user', 'demand_image'])->where('status', false)->get();
        if (count($demand)) return response()->json(['status' => true, 'Message' => 'Demand Products found', 'demand' => $demand ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Demand Product not found', 'demand' => $demand ?? []]);
    }

    public function userPendingDemandProduct()
    {
        $demand = DemandProduct::with(['demand_image'])->where('user_id', auth()->user()->id)->where('status', false)->get();
        if (count($demand)) return response()->json(['status' => true, 'Message' => 'Demand Products found', 'demand' => $demand ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Demand Product not found', 'demand' => $demand ?? []]);
    }

    public function userActiveDemandProduct()
    {
        $demand = DemandProduct::with(['demand_image'])->where('user_id', auth()->user()->id)->where('status', true)->get();
        if (count($demand)) return response()->json(['status' => true, 'Message' => 'Demand Products found', 'demand' => $demand ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Demand Product not found', 'demand' => $demand ?? []]);
    }

    public function completeDemandProduct()
    {
        $demand = CompleteDemandProduct::with(['shop', 'demand_product.user', 'demand_product.demand_image'])->where('user_id', auth()->user()->id)->get();
        if (count($demand)) return response()->json(['status' => true, 'Message' => 'Complete Demand Products found', 'completeDemand' => $demand ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Complete Demand Product not found', 'completeDemand' => $demand ?? []]);
    }

    public function addDemandProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required',
            'detail' => 'required',
            'qty' => 'required',
            'phone' => 'required',
            'images' => 'required|array',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            $demand = new DemandProduct();
            $demand->user_id = auth()->user()->id;
            $demand->name = $request->name;
            $demand->detail = $request->detail;
            $demand->qty = $request->qty;
            $demand->phone = $request->phone;
            $demand->timer = date('Y-m-d H:i:s', strtotime(Carbon::now()));
            if (!$demand->save()) throw new Error('Demand Product not save');
            if (!count($request->images)) throw new Error("Image Not found!");
            foreach ($request->images as $value) {
                $demandImages = new DemandProductImage();
                $demandImages->demand_product_id = $demand->id;
                $filename = "DemandImages-" . time() . "-" . rand() . "." . $value->getClientOriginalExtension();
                $value->storeAs('DemandImages', $filename, "public");
                $demandImages->images = "DemandImages/" . $filename;
                if (!$demandImages->save()) throw new Error("Home Page Image Not Added!");
            }
            $users = User::whereHas('role', function ($query) {
                $query->where('name', 'wholesaler')->orWhere('name', 'retailer');
            })->get();
            if (!count($users)) return response()->json(['status' => false, 'Message' => "Users not found"]);
            $title = 'DEMAND PRODUCTS';
            $message = 'New Request for Demand Product';
            foreach ($users as  $user) {
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $request->title, $request->message);
            }
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Demanded Product has been Successfully done'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function completeDemand(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'demand_product_id' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        try {
            DB::beginTransaction();
            $demand = DemandProduct::where('id', $request->demand_product_id)->first();
            if (empty($demand)) throw new Error('Demand Product not found');
            $demand->status = true;
            $demand->timer = date('Y-m-d H:i:s', strtotime(Carbon::now()));
            if (!$demand->save()) throw new Error('Demand status not update');
            $completeDemand = new CompleteDemandProduct();
            $completeDemand->user_id = auth()->user()->id;
            $completeDemand->demand_product_id = $request->demand_product_id;
            $completeDemand->timer = date('Y-m-d H:i:s', strtotime(Carbon::now()));
            if (!$completeDemand->save()) throw new Error('Complete Demand not saved');
            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Demand Request has been Successfully Completed'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }
}
