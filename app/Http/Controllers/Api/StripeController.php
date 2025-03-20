<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ReceiptMail;
use App\Mail\RefundNotificationMail;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\Mail;
use Stripe\Customer;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Refund;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentMethod;


class StripeController extends Controller
{
    protected $testKeys = 'stripe.test.sk';
    protected $liveKeys = 'stripe.live.sk';

    public function getStripeTransactions()
    {
        try {
            Stripe::setApiKey(config($this->testKeys));

            // Fetch all charges (transactions) from Stripe
            $charges = Charge::all([]);

            return response()->json(['status' => true, 'charges' => $charges], 200);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle error appropriately
            return back()->withError('Failed to fetch transactions from Stripe: ' . $e->getMessage());
        }
    }
    public function getSingleCharge(Request $request)
    {
        try {
            Stripe::setApiKey(config($this->testKeys));

            // Fetch all charges (transactions) from Stripe
            $charge = Charge::retrieve($request->id);

            return response()->json(['status' => true, 'charges' => $charge], 200);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle error appropriately
            return back()->withError('Failed to fetch transactions from Stripe: ' . $e->getMessage());
        }
    }
    public function getCustomerTransactions(Request $request)
    {
        try {
            Stripe::setApiKey(config($this->testKeys));

            // Fetch all charges (transactions) from Stripe
            $charges = Charge::all();
            $filteredCharges = array_filter($charges->data, function ($charge) use ($request) {
                return isset($charge->billing_details['email']) &&
                    $charge->billing_details['email'] === $request->email;
            });

            return response()->json(['status' => true, 'charges' => $filteredCharges], 200);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle error appropriately
            return back()->withError('Failed to fetch transactions from Stripe: ' . $e->getMessage());
        }
    }
    public function checkout()
    {
        return view('checkout');
    }
    public function test(Request $request)
    {

        Stripe::setApiKey(config('stripe.test.sk'));

        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json(['status' => false, 'Message' => 'Order does not exist Failed!'], 404);
        }
        // if ($order->pay_status == "paid") {
        //     return response()->json(['status' => false, 'Message' => 'Order is already paid!'], 404);
        // }

        $orderProducts = $order->user_orders()->get();


        Customer::create([
            'email' => $order->email,
            'name'  => $order->customer_name,
            'phone' => $order->phone,
        ]);
        $lineItems = $orderProducts->map(function ($product) {
            return [
                'price_data' => [
                    'currency'     => 'usd',
                    'product_data' => [
                        'name'        => 'Product ' . $product->title,
                        'description' => 'Description',
                        $product->description,
                    ],
                    'unit_amount'  => $product->product_price * 100,
                ],
                'quantity'   => $product->qty,
            ];
        })->toArray();

        // Create the Stripe session
        $session = Session::create([
            'line_items'  => $lineItems,
            'mode'        => 'payment',
            'customer_email' => $order->email,
            'metadata' => [
                'order_number'    => $order->order_number,
                'customer_name'   => $order->customer_name,
                'phone'           => $order->phone,
                'delivery_address' => $order->delivery_address,
                'gross_amount'    => $order->gross_amount,
                'net_amount'      => $order->net_amount,
            ],
            'payment_intent_data' => [
                'setup_future_usage' => 'off_session',
                'metadata' => [
                    'order_number'    => $order->order_number,
                    'customer_name'   => $order->customer_name,
                    'phone'           => $order->phone,
                    'delivery_address' => $order->delivery_address,
                    'gross_amount'    => $order->gross_amount,
                    'net_amount'      => $order->net_amount,
                ],
            ],
            'success_url' => route('checkout', ['order' => $order->id]),
            'cancel_url'  => route('checkout', ['order' => $order->id]),
        ]);


        // $receiptUrl = $session->url; 
        // Mail::to($order->email)->send(new ReceiptMail($receiptUrl, $order));
        return redirect()->away($session->url);
    }



    public function refundTransaction(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string'
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'Message' => 'Validation errors',
                'errors' => $valid->errors(),
                'request_data' => $request->all()
            ]);
        }


        $validated = $valid->validated();
        Stripe::setApiKey(config($this->testKeys)); // Set the Stripe API key

        // Retrieve the payment intent ID from the request
        $paymentIntentId = $validated["payment_intent_id"];

        if (!$paymentIntentId) {
            return response()->json(data: ['status' => false, 'message' => 'Payment Intent ID is required']);
        }

        try {
            // Create a full refund by omitting the amount
            $refund = Refund::create([
                'payment_intent' => $paymentIntentId,
            ]);

            return response()->json(['status' => true, 'message' => 'Full refund successful', 'refund' => $refund], 200);
        } catch (\Exception $e) {
            Log::info("Error from StripeController test" . $e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getUserCards()
    {
        Stripe::setApiKey(config('stripe.test.sk'));

        if (!auth()->user()) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 401);
        }

        $stripeCustomerId = auth()->user()->stripe_id;

        if ($stripeCustomerId == null) {
            return response()->json(['error' => "User token does not exist"], 404);
        }

        try {
            $customer = Customer::retrieve($stripeCustomerId);

            if (!$customer || isset($customer->deleted)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stripe customer does not exist'
                ], 404);
            }
            echo $customer;

            $paymentMethods = PaymentMethod::all([
                'customer' => $stripeCustomerId,
                'type' => 'card'
            ]);

            return response()->json([
                'status' => true,
                'cards' => $paymentMethods->data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('stripe.test.sk'));

        $endpoint_secret = 'whsec_f1ccc54d6e8a26288ff8b508ad6c048f2b6c26292a8a2e9efc28a1da9b68777b';

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Log::error('Invalid payload', ['error' => $e->getMessage()]);

            // Invalid payload
            return response('Invalid payload', 401);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Log::error('Invalid signature', ['error' => $e->getMessage()]);
            return response('Invalid signature', 402);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                try {

                    $paymentIntent = $event->data->object; // Contains a Stripe PaymentIntent object
                    $orderNumber = $paymentIntent->metadata->order_number ?? null;

                    $paymentSession = $paymentIntent->payment_intent ?? null;
                    $retrievedPaymentIntent = \Stripe\PaymentIntent::retrieve($paymentSession);
                    $chargeId = $retrievedPaymentIntent->latest_charge ?? null;
                    if ($orderNumber) {
                        $order = Order::where('order_number', $orderNumber)->first();
                        if ($order) {
                            try {
                                $receiptUrl = $paymentIntent->charges->data[0]->receipt_url ?? null;
                                Mail::to($order->email)->send(new ReceiptMail($order, $receiptUrl));

                                $order->pay_status = "paid";
                                $order->save();

                                $payment = $order->payments()->first();
                                if ($payment) {
                                    $payment->update([
                                        'txt_refno' => $retrievedPaymentIntent->id,
                                        'response_code' => $chargeId,
                                        'response_message' => "paid"
                                    ]);
                                }

                                return response()->json([
                                    'status' => true,
                                    'message' => 'Payment succeeded, email sent!',
                                ], 200);
                            } catch (\Exception $e) {
                                Log::error("Error processing payment for order {$orderNumber}: " . $e->getMessage());
                            }
                        } else {
                            Log::warning("Order not found for order number: {$orderNumber}");
                        }
                    } else {
                        Log::warning('Order number missing in payment intent metadata.');
                    }
                } catch (\Exception $e) {
                    Log::error("Error handling checkout.session.completed event from StripeContoller :  " . $e->getMessage());
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Order not found for payment intent',
                ], 405);


            case 'charge.refunded':
                try {
                    // Retrieve the charge object from the event data
                    $charge = $event->data->object; // Contains the Stripe Charge object

                    // Get the payment intent ID associated with the charge
                    $paymentIntentId = $charge->payment_intent ?? null;

                    if ($paymentIntentId) {
                        // Find the order using the payment intent ID
                        $order = Order::where('payment_intent_id', $paymentIntentId)->first();

                        if ($order) {


                            $payment = $order->payments()->first();
                            if ($payment) {
                                $payment->update([
                                    'response_message' => "refunded"
                                ]);
                            }

                            // Optionally, notify the user about the full refund
                            Mail::to($order->email)->send(new RefundNotificationMail($order));

                            // Log the refund process success
                            Log::info("Full refund processed for order {$order->order_number}, status updated to refunded.");

                            return response()->json([
                                'status' => true,
                                'message' => 'Full refund processed and order updated!',
                            ], 200);
                        } else {
                            Log::warning("Order not found for payment intent ID: {$paymentIntentId}");
                        }
                    } else {
                        Log::warning('Payment intent ID missing in refunded charge.');
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing charge.refunded event: " . $e->getMessage());
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to process refund.',
                ], 400);

            default:
                return response()->json(['status' => false, 'message' => 'Unhandled event type'], 400);
        }
    }

    public function live(): RedirectResponse
    {
        Stripe::setApiKey(config('stripe.live.sk'));

        $session = Session::create([
            'line_items'  => [
                [
                    'price_data' => [
                        'currency'     => 'gbp',
                        'product_data' => [
                            'name' => 'T-shirt',
                        ],
                        'unit_amount'  => 500,
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'        => 'payment',
            'success_url' => route('success'),
            'cancel_url'  => route('checkout'),
        ]);

        return redirect()->away($session->url);
    }

    public function success()
    {
        return view('success');
    }
    public function checkReceipt()
    {
        $order = Order::find(7);
        echo $order->email;
        try {
            Mail::to($order->email)->send(new ReceiptMail($order, null));

            echo "Mail sent successfully.";
        } catch (\Exception $e) {
            Log::error("Mail failed: " . $e->getMessage());
            echo "Mail sending failed: " . $e->getMessage();
        }
    }
}
