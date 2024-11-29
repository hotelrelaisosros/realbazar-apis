<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ReceiptMail;
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

        $stripe = new \Stripe\StripeClient(config('stripe.test.sk'));
        Stripe::setApiKey(config('stripe.test.sk'));

        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json(['status' => false, 'Message' => 'Order Failed!'], 404);
        }
        $orderProducts = OrderProduct::where('order_id', $order->id)
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->get();


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
                'metadata' => [
                    'order_number'    => $order->order_number,
                    'customer_name'   => $order->customer_name,
                    'phone'           => $order->phone,
                    'delivery_address' => $order->delivery_address,
                    'gross_amount'    => $order->gross_amount,
                    'net_amount'      => $order->net_amount,
                ],
            ],
            'success_url' => route('checkout', ['order' => $order->order_number]),
            'cancel_url'  => route('checkout', ['order' => $order->order_number]),
        ]);


        // $receiptUrl = $session->url; 
        // Mail::to($order->email)->send(new ReceiptMail($receiptUrl, $order));
        return redirect()->away($session->url);
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
                $paymentIntent = $event->data->object; // Contains a Stripe PaymentIntent object
                $orderNumber = $paymentIntent->metadata->order_number ?? null;

                if ($orderNumber) {

                    $order = Order::where('order_number', $orderNumber)->first();
                    if ($order) {
                        $receiptUrl = $paymentIntent->charges->data[0]->receipt_url ?? null;
                        Mail::to($order->email)->send(new ReceiptMail($order, $receiptUrl));

                        return response()->json([
                            'status' => true,
                            'message' => 'Payment succeeded, email sent!',
                        ], 200);
                    }
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Order not found for payment intent',
                ], 405);

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
