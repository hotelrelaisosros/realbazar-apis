<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\AddressResource;

class OrderResourceFront extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'order_id'        => $this->id,
            'order_number'    => $this->order_number,
            // 'customer_name'   => $this->customer_name,
            // 'email'           => $this->email,
            // 'phone'           => $this->phone,
            'order_date'      => $this->order_date,
            'status'          => $this->status,
            'pay_status'      => $this->pay_status,
            'total'           => $this->user_orders->sum('product_price'), // Sum all product prices
            // 'subtotal'        => $this->user_orders->sum('subtotal'),
            'address' => new AddressResource($this->addresses),
            'invoice_link'    => route('pdf.invoice', $this->id), // Invoice Download Link
            'delivery_status' => $this->status, // Delivery Status
            'ordered_products' => $this->user_orders->map(function ($order) {
                return [
                    // 'ordered_product_id'    => $order->id,
                    'total'         => $order->product_price,
                    'subtotal'   => $order->sub_total,
                    'quantity'      => $order->qty,
                    'per_product_price' => $order->initial_price,
                    'customization_price' => $order->customizables,
                    'details' => [
                        'product' => new ProductNonRingResource($order->products),
                        'image'   => new ProductImageResource($order->product_images),
                    ]
                ];
            }),
            // 'variation'        =>$this->user_orders->variation? new ProductImageResource($this->user_orders->product_images) ? null,
            'payments'        => new PaymentResource(optional($this->user_payments->first())),
        ];
    }
}
