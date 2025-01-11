<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\AppNotification;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Gemshape;
use App\Models\AccentStoneTypes;
use App\Models\BandWidth;
use App\Models\BespokeCustomization;
use App\Models\BespokeCustomizationType;
use App\Models\BirthStone;
use App\Models\MetalKerat;
use App\Models\SettingHeight;
use App\Models\ProngStyle;
use App\Models\RingSize;
use App\Models\GemStoneColor;
use App\Models\GemStone;


use App\Models\ProductEnum;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\NotiSend;
use App\Models\RefundOrder;
use App\Models\UnpaidPackagePayment;
use App\Models\UnpaidRegisterUser;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function get_user_orders($status = null)
    {
        // Check if the user is authenticated
        if (!auth()->user()) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 401);
        }


        $order = Order::where('user_id', auth()->user()->id)
            ->with(['user_orders.variation.product_images', 'user_orders.products.images', 'user_payments.payments', 'users'])
            ->get();

        // Return response based on the query result
        if ($order->count()) {
            return response()->json([
                'status' => true,
                'message' => 'Order found2',
                'orders' => OrderResource::collection($order)
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
                'orders' => []
            ], 404);
        }
    }

    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'http')) {
            return url('storage/' . $imagePath);
        }

        return $imagePath;
    }

    public function order(Request $request)
    {
        if (!empty($request->order)) {
            try {
                DB::beginTransaction();
                $order_ids = [];
                $total = 0;
                $latestOrderId = 0;
                $latestOrder = Order::orderBy('created_at', 'DESC')->first();
                foreach ($request->order as $key => $orders) {
                    if (is_object($orders)) $orders = $orders->toArray();
                    $order = new Order();

                    $order->user_id = auth()->user()->id;
                    $order->seller_id = $orders['sellerId'];
                    if (empty($latestOrder)) $latestOrderId = 0;
                    else $latestOrderId = $latestOrder->id;
                    $order->order_number =  str_pad($latestOrderId + 1, 8, "0", STR_PAD_LEFT);
                    $order->customer_name = $orders['name'];
                    $order->email = auth()->user()->email;
                    $order->phone = $orders['phone'];
                    $order->delivery_address = $orders['address'];
                    $order->order_date = Carbon::now();
                    if (isset($orders['pay_status']) && $orders['pay_status'] == 'unpaid') $order->pay_status = 'unpaid';
                    // $order->area = $orders['area'];
                    // $order->city = $orders['city'];
                    $order->gross_amount = $orders['gross_amount'];
                    $order->net_amount = $orders['net_amount'];
                    // $order->note = $orders['note'];
                    $order->save();

                    $order_ids[] = $order->id;
                    if (!empty($orders['product'])) {
                        foreach ($orders['product'] as $key => $product) {
                            if (is_object($product)) $product = $product->toArray();
                            $product_price = Product::find($product['id']);
                            if ($product_price) {

                                $order_product = new OrderProduct();
                                $order_product->order_id = $order->id;
                                $order_product->product_id = $product['id'];
                                $order_product->variant_id = $product['variant_id'] ?? null;

                                $order_product->qty = $product['product_selected_qty'];
                                $order_product->size = $product['size'];
                                $order_product->product_price = $product['product_price'];

                                $price_counter = 0;

                                //get product enums
                                if (!empty($product["customizable"])) {

                                    // $order_product->metal_type_id = $product["customizable"]["metal_type_id"] ?? null;
                                    $order_product->metal_type_karat = $product["customizable"]["metal_type_karat"] ?? null;
                                    $order_product->faceting_id = $product["customizable"]["faceting_id"] ?? null;
                                    $order_product->gem_shape_id = $product["customizable"]["gem_shape_id"] ?? null;
                                    $order_product->band_width_id = $product["customizable"]["band_width_id"] ?? null;
                                    $order_product->accent_stone_type_id = $product["customizable"]["accent_stone_type_id"] ?? null;
                                    $order_product->setting_height_id = $product["customizable"]["setting_height_id"] ?? null;
                                    $order_product->prong_style_id = $product["customizable"]["prong_style_id"] ?? null;
                                    $order_product->ring_size_id = $product["customizable"]["ring_size_id"] ?? null;
                                    $order_product->bespoke_customization_types_id = $product["customizable"]["bespoke_customization_types_id"] ?? null;
                                    $order_product->birth_stone_id = $product["customizable"]["birth_stone_id"] ?? null;

                                    $order_product->gem_stone_id = $product["customizable"]["gem_stone_id"] ?? null;

                                    $order_product->gem_stone_color_id = $product["customizable"]["gem_stone_color_id"] ?? null;

                                    $order_product->engraved_text = $product["customizable"]["engraved_text"] ?? null;

                                    if ($product["customizable"]["bespoke_customization_types_id"]) {
                                        $bsp = json_decode($product["customizable"]["bespoke_customization_types_id"]);
                                        $bespoke_customization_types = BespokeCustomizationType::whereIn('id', $bsp)->get();
                                        foreach ($bespoke_customization_types as $bespoke_customization_type) {
                                            $price_counter += $bespoke_customization_type->price;
                                        }
                                    }
                                    if ($product["customizable"]["birth_stone_id"]) {
                                        $b_stone = json_decode($product["customizable"]["birth_stone_id"]);
                                        $b_stones = BirthStone::whereIn('id', $b_stone)->get();
                                        foreach ($b_stones as $birth_stone) {  // Changed variable name for clarity
                                            $price_counter += $birth_stone->price;
                                        }
                                    }
                                    if ($product["customizable"]["gem_stone_id"]) {
                                        $gem_stone = $product["customizable"]["gem_stone_id"];
                                        $gem_stone = GemStone::where('id', $gem_stone)->first();
                                        if ($gem_stone) {
                                            $price_counter += $gem_stone->price;
                                        }
                                    }
                                    $order_product->customization_price = $price_counter;
                                }

                                // danger function 3
                                // only 1 ring will be purchased if it has the updates and the quantity is more than 1 will not be paid for 
                                if ($product['product_selected_qty'] > 0) {
                                    $order_product->subtotal = $price_counter > 0
                                        ? $product['product_price'] + $order_product->customization_price
                                        : $product['product_selected_qty'] * $product['product_price'];
                                } else {
                                    $order_product->subtotal = 0;
                                }

                                $discount = $product_price->discount_price ? $product_price->discount_price * $product['product_selected_qty'] : 0;

                                $order_product->discount = $discount;
                                $order_product->save();
                                $total += ($product['product_selected_qty'] * $product['product_price']) - ($product_price->discount_price * $product['product_selected_qty']);
                            } else {
                                throw new Error("Product with ID {$product['id']} not found");
                            }
                        }
                    } else throw new Error("Order Request Failed!");
                }
                if ($total < 0) throw new Error("Order Request Failed because your total amount is 0!");
                if ($request->pay_status == "unpaid") {
                    $payment = new Payment();
                    $payment->payment_method = $request->payment_method;
                    $payment->total = $total;
                    $payment->txt_refno = $request->txt_refno;
                    $payment->response_code = $request->response_code;
                    $payment->response_message = $request->response_message;
                    $payment->save();
                    $payment->orders()->sync($order_ids);
                    // if ($request->response_code == 000 || $request->response_code == 0000) {
                    $user = User::whereRelation('role', 'name', 'admin')->first();
                    $title = 'NEW ORDER';
                    $message = 'You have recieved new order';
                    $appnot = new AppNotification();
                    $appnot->user_id = $user->id;
                    $appnot->notification = $message;
                    $appnot->navigation = $title;
                    $appnot->save();
                    // NotiSend::sendNotif($user->device_token, '', $title, $message);
                    DB::commit();
                    return response()->json(['status' => true, 'Message' => 'New Order Placed!'], 200);
                    // else {
                    //     DB::commit();
                    //     return response()->json(['status' => false, 'Message' => 'Order Failed!']);
                    // }
                } else {
                    $payment = new Payment();
                    $payment->payment_method = $request->payment_method;
                    $payment->total = $total;
                    $payment->txt_refno = "";
                    $payment->response_code = "";
                    $payment->response_message = "unpaid";
                    $payment->save();
                    $payment->orders()->sync($order_ids);
                    // if ($request->response_code == 000 || $request->response_code == 0000) {
                    $user = User::whereRelation('role', 'name', 'admin')->first();
                    $title = 'NEW ORDER';
                    $message = 'You have recieved new order';
                    $appnot = new AppNotification();
                    $appnot->user_id = $user->id;
                    $appnot->notification = $message;
                    $appnot->navigation = $title;
                    $appnot->save();
                    // NotiSend::sendNotif($user->device_token, '', $title, $message);
                    DB::commit();
                    return response()->json(['status' => true, 'Message' => 'New Order Placed!'], 200);
                }
            } catch (\Throwable $th) {
                DB::rollBack();
                // throw $th;
                return response()->json(['status' => false, 'Message' => $th->getMessage(), 'request' => $request->all()]);
            }
        } else return response()->json(['status' => false, 'Message' => 'Order Request Failed!', 'request' => $request->all()]);
    }


    public function show(Request $request)
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
        $role = $request->role;
        $search = $request->search;
        $status = $request->status;
        $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users.role'])->where('pay_status', 'paid');
        $order_count = Order::with(['user_orders.products.images', 'user_payments.payments', 'users.role'])->where('pay_status', 'paid');



        // print_r($order);
        //check order status
        if (!empty($status)) {
            $order->where('status', $status);
            $order_count->where('status', $status);
        }

        // check user role
        if (!empty($role)) {
            $order->whereHas('users', function ($q) use ($role) {
                $q->whereRelation('role', 'name', $role);
            });
            $order_count->whereHas('users', function ($q) use ($role) {
                $q->whereRelation('role', 'name', $role);
            });
        }

        //search function to check the order 
        if (!empty($search)) {
            $order->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('delivery_address', 'like', '%' . $search . '%')
                    ->orWhere('order_date', 'like', '%' . $search . '%');
            });
            $order_count->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('delivery_address', 'like', '%' . $search . '%')
                    ->orWhere('order_date', 'like', '%' . $search . '%');
            });
        }

        //search and replace customizables
        $orders = $order->skip($skip)->take($take)->get();
        $orders_counts = $order_count->count();

        $orders->transform(function ($order) {
            $order->user_orders->transform(function ($orderProduct) {


                //do product image migrations
                if ($orderProduct->metal_type_id) {

                    //wont be needed 
                    $query = "
                        SELECT pi.name, pi.image 
                        FROM product_images pi
                        JOIN order_products op ON pi.product_id = op.product_id
                        WHERE op.product_id = ? 
                          AND pi.id = ?
                    ";

                    $materials = DB::select($query, [
                        $orderProduct->product_id,
                        $orderProduct->metal_type_id
                    ]);


                    if (!empty($materials)) {
                        $material = $materials[0];
                        $metal_type = [
                            "name" => $material->name,
                            "image" => $this->formatImageUrl($material->image),
                        ];
                    }
                }
                if ($orderProduct->bespoke_customization_types_id) {
                    $query = "
                    SELECT bt.*, bc.name 
                    FROM bespoke_customization_types bt
                    JOIN bespoke_customizations bc ON bt.bespoke_customization_id = bc.id
                    WHERE bt.id = ?
                ";

                    $bespokeResults = DB::select($query, [$orderProduct->bespoke_customization_types_id]);

                    $bespoke = !empty($bespokeResults) ? $bespokeResults[0] : null;
                }

                //do product image migrations
                $orderProduct->customizables = [
                    'metal_type' => $metal_type ?? null,
                    'gem_shape' => $orderProduct->gem_shape_id ? GemShape::find($orderProduct->gem_shape_id) : null,
                    'band_width' => $orderProduct->band_width_id ? BandWidth::find($orderProduct->band_width_id) : null,
                    'accent_stone_type' => $orderProduct->accent_stone_type_id ? AccentStoneTypes::find($orderProduct->accent_stone_type_id) : null,
                    'setting_height' => $orderProduct->setting_height_id ? SettingHeight::find($orderProduct->setting_height_id) : null,
                    'prong_style' => $orderProduct->prong_style_id ? ProngStyle::find($orderProduct->prong_style_id) : null,
                    'ring_size' => $orderProduct->ring_size_id ? RingSize::find($orderProduct->ring_size_id) : null,
                    'bespoke_customization_type' => $bespoke ?? null,
                    'birth_stone' => $orderProduct->birth_stone_id ? BirthStone::find($orderProduct->birth_stone_id) : null,
                    'gem_stone' => $orderProduct->gem_stone_id ? GemStone::find($orderProduct->gem_stone_id) : null,
                    'gem_stone_color' => $orderProduct->gem_stone_color_id ? GemStoneColor::find($orderProduct->gem_stone_color_id) : null,
                    'engraved_text' => $orderProduct->engraved_text,
                    'metal_type_karat' => $orderProduct->metal_type_karat,
                ];

                // Remove the original fields
                unset(
                    $orderProduct->metal_type_id,
                    $orderProduct->gem_shape_id,
                    $orderProduct->band_width_id,
                    $orderProduct->accent_stone_type_id,
                    $orderProduct->setting_height_id,
                    $orderProduct->prong_style_id,
                    $orderProduct->ring_size_id,
                    $orderProduct->bespoke_customization_types_id,
                    $orderProduct->birth_stone_id,
                    $orderProduct->gem_stone_id,
                    $orderProduct->gem_stone_color_id,

                    $orderProduct->engraved_text,
                    $orderProduct->metal_type_karat
                );

                return $orderProduct;
            });

            return $order;
        });




        if (count($orders)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($orders) ?? [], 'OrdersCount' => $orders_counts ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $orders ?? [], 'OrdersCount' => $orders_counts ?? []]);
    }

    public function userOrder($status = null)
    {
        if ($status == null) {
            $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('user_id', auth()->user()->id)->where('pay_status', 'paid')->get();
        } else {
            $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('user_id', auth()->user()->id)->where('pay_status', 'paid')->where('status', $status)->get();
        }
        if (count($order)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($order)], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $order ?? []]);
    }

    public function sellerOrder($status = null)
    {
        if ($status == null) {
            $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('seller_id', auth()->user()->id)->where('pay_status', 'paid')->get();
        } else {
            $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users', 'seller'])->where('seller_id', auth()->user()->id)->where('pay_status', 'paid')->where('status', $status)->get();
        }
        if (count($order)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($order)], 200);
        else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $order ?? []]);
    }

    function orderRefundGet()
    {
        $orderRefund = RefundOrder::with('orders.user_payments.payments')->orderBy('id', 'DESC')->get();
        if (count($orderRefund)) return response()->json(['status' => true, 'Message' => 'Refund Order found', 'orderRefund' => $orderRefund ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Refund Order not found', 'orderRefund' => $orderRefund ?? []]);
    }

    public function orderStatusChange(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $order = Order::where('id', $request->id)->where('pay_status', 'paid')->first();
        $order->status = $request->status;
        if ($order->save()) {
            $user = $order->users;
            if ($order->status == 'delivered') {
                $title = 'YOUR ORDER HAS BEEN DELIVERED';
                $message = 'Dear ' . $user->username . ' your order has been delivered from admin-The Real Bazaar';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $title, $message);
                return response()->json(["status" => true, 'Message' => 'Order Status Change to Delivered Successfully'], 200);
            } elseif ($order->status == 'inprocess') {
                $title = 'YOUR ORDER HAS BEEN In Process';
                $message = 'Dear ' . $user->username . ' your order has been InProcess from admin-The Real Bazaar';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $title, $request->message);
                return response()->json(["status" => true, 'Message' => 'Order Status Change to In Process Successfully'], 200);
            } elseif ($order->status == 'rejected') {
                $title = 'YOUR ORDER HAS BEEN REJECTED';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $request->message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $title, $request->message);
                return response()->json(["status" => true, 'Message' => 'Order Status Change to Reject Successfully'], 200);
            } else {
                $title = 'YOUR ORDER HAS BEEN PENDING';
                $message = 'Dear ' . $user->username . ' your order has been pending from admin-The Pro Art';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $title, $message);
                return response()->json(["status" => true, 'Message' => 'Order Status Change to Pending Successfully'], 200);
            }
        } else return response()->json(["status" => false, 'Message' => 'Order Status Change not Successfully']);
    }

    public function orderRefundStatusChange(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $refundOrder = RefundOrder::where('id', $request->id)->first();
        if ($request->status == 'success') $status = 'Complete';
        else $status = 'Pending';
        $refundOrder->status = $status;
        if ($refundOrder->save()) {
            $order = Order::where('id', $refundOrder->order_id)->first();
            $user = $order->users;
            if ($refundOrder->status == 'Complete') {
                $title = 'YOUR REFUND REQUEST HAS BEEN COMPLETED';
                $message = 'Dear ' . $user->username . ' your refund has been completed from admin-The Real Bazaar';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $title, $message);
                return response()->json(["status" => true, 'Message' => 'Order Refund Status Change to Completed'], 200);
            } else {
                $title = 'YOUR REFUND REQUEST HAS BEEN PENDING';
                $message = 'Dear ' . $user->username . ' your refund request has been pending please contact with admin-The Real Bazaar';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $title, $message);
                return response()->json(["status" => true, 'Message' => 'Order Refund Status Change to Pending'], 200);
            }
        } else return response()->json(["status" => false, 'Message' => 'Order Refund Status Change not Successfully']);
    }

    public function jazzcashCheckout(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'price' => 'required|gt:0',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $price = $request->price ?? 0;

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //1.
        //get formatted price. remove period(.) from the price
        $pp_Amount     = $price * 100;

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //2.
        //get the current date and time
        //be careful set TimeZone in config/app.php
        $DateTime         = Carbon::now();
        $pp_TxnDateTime = $DateTime->format('YmdHis');

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //3.
        //to make expiry date and time add one hour to current date and time
        $ExpiryDateTime = $DateTime;
        $ExpiryDateTime->modify('+' . 1 . ' hours');
        $pp_TxnExpiryDateTime = $ExpiryDateTime->format('YmdHis');

        //NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
        //4.
        //make unique transaction id using current date
        $pp_TxnRefNo = \Str::random(15);


        //--------------------------------------------------------------------------------
        //postData
        $post_data =  array(
            "pp_Version"             => Config::get('jazzcashCheckout.jazzcash.VERSION'),
            "pp_TxnType"             => "",
            "pp_Language"             => Config::get('jazzcashCheckout.jazzcash.LANGUAGE'),
            "pp_MerchantID"         => Config::get('jazzcashCheckout.jazzcash.MERCHANT_ID'),
            "pp_SubMerchantID"         => "",
            "pp_Password"             => Config::get('jazzcashCheckout.jazzcash.PASSWORD'),
            "pp_BankID"             => "TBANK",
            "pp_TxnRefNo"             => $pp_TxnRefNo,
            "pp_Amount"             => $pp_Amount,
            "pp_TxnCurrency"         => Config::get('jazzcashCheckout.jazzcash.CURRENCY_CODE'),
            "pp_TxnDateTime"         => $pp_TxnDateTime,
            "pp_BillReference"         => "billRef",
            "pp_Description"         => "Description of transaction",
            "pp_TxnExpiryDateTime"     => $pp_TxnExpiryDateTime,
            "pp_ReturnURL"             => Config::get('jazzcashCheckout.jazzcash.RETURN_URL'),
            "pp_SecureHash"         => "",
            "pp_IsRegisteredCustomer" => "yes",
            "ppmpf_1"                 => "1",
            "ppmpf_2"                 => "2",
            "ppmpf_3"                 => "3",
            "ppmpf_4"                 => "4",
            "ppmpf_5"                 => "5",
        );

        $pp_SecureHash = $this->get_SecureHash($post_data);

        $post_data['pp_SecureHash'] = $pp_SecureHash;
        if (count($post_data)) return response()->json(['status' => true,  'url' => Config::get('jazzcashCheckout.jazzcash.TRANSACTION_POST_URL') ?? [], 'data' => $post_data ?? []], 200);
        else return response()->json(['status' => false,  'Message' => 'Request Failed']);
    }

    public function jazzcashCardRefund(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'price' => 'required|gt:0',
            'pp_TxnRefNo' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $price = $request->price ?? 0;
        $pp_Amount     = $price;
        $pp_TxnRefNo = $request->pp_TxnRefNo;


        //--------------------------------------------------------------------------------
        //postData
        $post_data =  array(
            "pp_TxnRefNo"             => $pp_TxnRefNo,
            "pp_Amount"             => $pp_Amount,
            "pp_TxnCurrency"         => Config::get('jazzcashCheckout.jazzcash.CURRENCY_CODE'),
            "pp_MerchantID"         => Config::get('jazzcashCheckout.jazzcash.MERCHANT_ID'),
            "pp_Password"             => Config::get('jazzcashCheckout.jazzcash.PASSWORD'),
            "pp_SecureHash"         => "",
        );

        $pp_SecureHash = $this->get_SecureHash($post_data);
        $post_data['pp_SecureHash'] = $pp_SecureHash;
        // return view('do_checkout_v', ['post_data' => $post_data]);
        if (count($post_data)) return response()->json(['status' => true,  'url' => Config::get('jazzcashCheckout.jazzcash.CARD_REFUND_POST_URL') ?? [], 'data' => $post_data ?? []], 200);
        else return response()->json(['status' => false,  'Message' => 'Request Failed']);
    }

    public function jazzcashMobileRefund(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'price' => 'required|gt:0',
            'pp_TxnRefNo' => 'required',
            'mpin' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $price = $request->price ?? 0;
        $pp_Amount     = $price;
        $pp_TxnRefNo = $request->pp_TxnRefNo;
        $pp_MerchantMPIN = $request->mpin;


        //--------------------------------------------------------------------------------
        //postData
        $post_data =  array(
            "pp_TxnRefNo"             => $pp_TxnRefNo,
            "pp_Amount"             => $pp_Amount,
            "pp_TxnCurrency"         => Config::get('jazzcashCheckout.jazzcash.CURRENCY_CODE'),
            "pp_MerchantID"         => Config::get('jazzcashCheckout.jazzcash.MERCHANT_ID'),
            "pp_Password"             => Config::get('jazzcashCheckout.jazzcash.PASSWORD'),
            "pp_MerchantMPIN"             => $pp_MerchantMPIN,
            "pp_SecureHash"         => "",
        );

        $pp_SecureHash = $this->get_SecureHash($post_data);
        $post_data['pp_SecureHash'] = $pp_SecureHash;
        // return view('do_checkout_v', ['post_data' => $post_data]);
        if (count($post_data)) return response()->json(['status' => true,  'url' => Config::get('jazzcashCheckout.jazzcash.MOBILE_REFUND_POST_URL') ?? [], 'data' => $post_data ?? []], 200);
        else return response()->json(['status' => false,  'Message' => 'Request Failed']);
    }

    public function jazzcashStatusInquiry(Request $request)
    {
        $valid = Validator::make($request->all(), [
            // 'price' => 'required|gt:0',
            'pp_TxnRefNo' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $pp_TxnRefNo = $request->pp_TxnRefNo;
        //--------------------------------------------------------------------------------
        //postData
        $post_data =  array(
            "pp_TxnRefNo"             => $pp_TxnRefNo,
            "pp_MerchantID"         => Config::get('jazzcashCheckout.jazzcash.MERCHANT_ID'),
            "pp_Password"             => Config::get('jazzcashCheckout.jazzcash.PASSWORD'),
            "pp_SecureHash"         => "",
        );

        $pp_SecureHash = $this->get_SecureHash($post_data);
        $post_data['pp_SecureHash'] = $pp_SecureHash;
        // return view('do_checkout_v', ['post_data' => $post_data]);
        if (count($post_data)) return response()->json(['status' => true,  'url' => Config::get('jazzcashCheckout.jazzcash.STATUS_INQUIRY_POST_URL') ?? [], 'data' => $post_data ?? []], 200);
        else return response()->json(['status' => false,  'Message' => 'Request Failed']);
    }

    private function get_SecureHash($data_array)
    {
        ksort($data_array);
        $str = '';
        foreach ($data_array as $key => $value) {
            if (!empty($value)) {
                $str = $str . '&' . $value;
            }
        }
        $str = Config::get('jazzcashCheckout.jazzcash.INTEGERITY_SALT') . $str;
        $pp_SecureHash = hash_hmac('sha256', $str, Config::get('jazzcashCheckout.jazzcash.INTEGERITY_SALT'));

        return $pp_SecureHash;
    }

    public function jazzcashPaymentStatus(Request $request)
    {
        $url = Config::get('jazzcashCheckout.jazzcash.WEB_RETURN_URL');
        if (!empty($request->pp_ResponseCode)) {
            if ($request->pp_ResponseCode == 000) {
                return redirect($url . '?response_code=' . $request->pp_ResponseCode . '&response_message=' . $request->pp_ResponseMessage . '&pp_TxnRefNo=' . $request->pp_TxnRefNo);
            } else {
                return redirect($url . '?response_code=' . $request->pp_ResponseCode . '&response_message=' . $request->pp_ResponseMessage . '&pp_TxnRefNo=' . $request->pp_TxnRefNo);
            }
        } else {
            return redirect($url);
        }
    }

    public function easypaisaCheckout(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'price' => 'required|gt:0',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $amount = $request->price;
        // $amount     = 10;

        $DateTime         = Carbon::now();
        $dateTime = $DateTime->format('dms');
        $orderRefNum = $dateTime;

        $timestampDateTime = $DateTime;
        $timestamp = $timestampDateTime->format('Y-m-d\TH:i:s');
        //postData
        $postbackurl = urlencode(Config::get('easypaisaCheckout.easypaisa.POST_BACK_URL'));
        $post_data =  array(
            "storeId"             => Config::get('easypaisaCheckout.easypaisa.STORE_ID'),
            "orderId"             => $orderRefNum,
            "transactionAmount"             => $amount,
            "mobileAccountNo"             => "",
            "emailAddress"             => "",
            "transactionType"             => Config::get('easypaisaCheckout.easypaisa.TransactionType'),
            "tokenExpiry"             => "",
            "bankIdentificationNumber"             => "",
            "encryptedHashRequest"         => "",
            "merchantPaymentMethod"             => "",
            "postBackURL"             => $postbackurl,
            "signature"             => "",
        );

        $str = "amount=" . $amount . "&orderRefNum=" . $orderRefNum . "&paymentMethod=" . Config::get('easypaisaCheckout.easypaisa.TransactionType') . "&postBackURL=" . Config::get('easypaisaCheckout.easypaisa.POST_BACK_URL') . "&storeId=" . Config::get('easypaisaCheckout.easypaisa.STORE_ID') . "&timeStamp=" . $timestamp;
        $hashKey = Config::get('easypaisaCheckout.easypaisa.HASH_KEY');
        $cipher = "aes-128-ecb";
        $crypttext = openssl_encrypt($str, $cipher, $hashKey, OPENSSL_RAW_DATA);
        $encryptedHashRequest = base64_encode($crypttext);
        $encryptedHashRequest = urlencode($encryptedHashRequest);
        $post_data['encryptedHashRequest'] = $encryptedHashRequest;
        $param = '';
        $i = 1;

        foreach ($post_data as $key => $value) {
            if (!empty($key)) {
                if ($i == 1) $param = $key . '=' . $value;
                else {
                    $param = $param . '&' . $key . '=' . $value;
                }
            }
            $i++;
        }

        if (count($post_data)) return response()->json(['status' => true, 'url' => Config::get('easypaisaCheckout.easypaisa.TRANSACTION_POST_URL') . $param], 200);
        else return response()->json(['status' => false,  'Message' => 'Request Failed']);
    }

    function orderRefund(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'order_id' => 'required',
        ], [], [
            'order_id' => 'Order Id',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $refundOrder = new RefundOrder();
        $refundOrder->order_id = $request->order_id;
        if ($refundOrder->save())  return response()->json(['status' => true, 'Message' => 'Order Refund Request Successfull', 'refundOrder' => $refundOrder ?? []], 200);
        else  return response()->json(['status' => false, 'Message' => 'Order Refund Request Failed', 'refundOrder' => $refundOrder ?? []]);
    }

    public function paymentInquiry(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'method' => 'required',
            'section' => 'required',
            'skip' => 'required',
            'take' => 'required',
        ], [], [
            'method' => 'Payment Method',
            'section' => 'Payment Section',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $skip = $request->skip;
        $take = $request->take;
        $method = $request->method;
        $role = $request->role;
        $search = $request->search;
        if ($request->section == 'orders') {
            $order = Order::orderBy('id', 'DESC')->with(['user_orders.products.images', 'user_payments.payments', 'users.role', 'seller.role'])->where('pay_status', 'unpaid');
            $order_count = Order::with(['user_orders.products.images', 'user_payments.payments', 'users.role', 'seller.role'])->where('pay_status', 'unpaid');
            if (!empty($role)) {
                $order->whereHas('users', function ($q) use ($role) {
                    $q->whereRelation('role', 'name', $role);
                });
                $order_count->whereHas('users', function ($q) use ($role) {
                    $q->whereRelation('role', 'name', $role);
                });
            }
            if (!empty($method)) {
                $order->whereHas('user_payments', function ($q) use ($method) {
                    $q->whereRelation('payments', 'payment_method', $method);
                });
                $order_count->whereHas('user_payments', function ($q) use ($method) {
                    $q->whereRelation('payments', 'payment_method', $method);
                });
            }
            if (!empty($search)) {
                $order->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', '%' . $search . '%')
                        ->orWhere('customer_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('delivery_address', 'like', '%' . $search . '%')
                        ->orWhere('order_date', 'like', '%' . $search . '%');
                });
                $order_count->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', '%' . $search . '%')
                        ->orWhere('customer_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('delivery_address', 'like', '%' . $search . '%')
                        ->orWhere('order_date', 'like', '%' . $search . '%');
                });
            }
            $orders = $order->skip($skip)->take($take)->get();
            $orders_counts = $order_count->count();
            if (count($orders)) return response()->json(['status' => true, 'Message' => 'Order found', 'Orders' => OrderResource::collection($orders) ?? [], 'OrdersCount' => $orders_counts ?? []], 200);
            else return response()->json(['status' => false, 'Message' => 'Order not found', 'Orders' => $orders ?? [], 'OrdersCount' => $orders_counts ?? []]);
        } else if ($request->section == 'users') {
            $user = UnpaidRegisterUser::orderBy('id', 'DESC')->with(['role']);
            $user_count = UnpaidRegisterUser::with(['role']);
            if (!empty($role)) {
                $user->whereRelation('role', 'name', $role);
                $user_count->whereRelation('role', 'name', $role);
            }
            if (!empty($method)) {
                $user->where('payment_method', $method);
                $user_count->where('payment_method', $method);
            }
            if (!empty($search)) {
                $user->where(function ($q) use ($search) {
                    $q->where('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
                $user_count->where(function ($q) use ($search) {
                    $q->where('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            }
            $users = $user->skip($skip)->take($take)->get();
            $users_counts = $user_count->count();
            if (count($users)) return response()->json(['status' => true, 'Message' => 'User found', 'Users' => $users ?? [], 'UsersCount' => $users_counts ?? []], 200);
            else return response()->json(['status' => false, 'Message' => 'User not found', 'Users' => $users ?? [], 'UsersCount' => $users_counts ?? []]);
        } else if ($request->section == 'packages') {
            $package = UnpaidPackagePayment::orderBy('id', 'DESC')->with(['user', 'package']);
            $package_count = UnpaidPackagePayment::with(['user', 'package']);
            if (!empty($role)) {
                $package->whereHas('user', function ($q) use ($role) {
                    $q->whereRelation('role', 'name', $role);
                });
                $package_count->whereHas('user', function ($q) use ($role) {
                    $q->whereRelation('role', 'name', $role);
                });
            }
            if (!empty($method)) {
                $package->where('payment_method', $method);
                $package_count->where('payment_method', $method);
            }
            if (!empty($search)) {
                $package->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
                $package_count->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            }
            $packages = $package->skip($skip)->take($take)->get();
            $packages_counts = $package_count->count();
            if (count($packages)) return response()->json(['status' => true, 'Message' => 'Packages found', 'Packages' => $packages ?? [], 'PackagesCount' => $packages_counts ?? []], 200);
            else return response()->json(['status' => false, 'Message' => 'Packages not found', 'Packages' => $packages ?? [], 'PackagesCount' => $packages_counts ?? []]);
        } else {
            return response()->json(['status' => false, 'Message' => 'Section not match']);
        }
    }
}
